<?php

require_once 'crawler-base.class.php';
require_once 'status.class.php';
require_once 'httpstatus.class.php';
require_once __DIR__.'/../vendor/rolling-curl/src/RollingCurl/RollingCurl.php';
require_once __DIR__.'/../vendor/rolling-curl/src/RollingCurl/Request.php';
require_once 'analyzer.html.class.php';

/**
 * 
 * AXLES CRAWLER AND INDEXER
 * ... crawls with multicurl
 * ... 
 * 
 * usage:
 * require_once("../crawler.class.php");
 * $o=new crawler(1);
 * 
 * // update all
 * $o->run();
 * 
 * // or update a single page
 * $o->updateSingleUrl('https://example.com/mypage.html');
 * 
 **/
class crawler extends crawler_base{

    /**
     * array with urls to crawl
     * @var array
     */
    private $_aUrls2Crawl = array();
    private $_iUrlsCrawled = 0;
    
    /**
     * flag: scan content and follow links?
     * @var type 
     */
    private $_bFollowLinks = true;

    /**
     * sleep time during crawling [seconds]
     * @var integer
     */
    private $iSleep = 0;
    private $iStartCrawl = 0;

    /**
     * array of extensions to skip in found links (in targets of a href="")
     * @var string
     */
    private $aSkipExtensions = array(
        // images
        'doc',
        'docx',
        'pdf',
        'ppt',
        'pptx',
        'xls',
        'xlx',
        // images
        'gif',
        'jpg',
        'png',
        // audio
        'm4a',
        'mid',
        'mp3',
        'ogg',
        // video
        'avi',
        'mp4',
        // archives
        'gz',
        'tar',
        'tgz',
        'zip',
    );
    /**
     * list of regex with links to remove
     * @var array
     */
    public $aExclude = array(
        '^javascript\:',
        '^mailto\:',
    );


    // ----------------------------------------------------------------------
    // cleanup settings - when to remove content from index
    // ----------------------------------------------------------------------

    /**
     * max count of errors
     * @var integer
     */
    private $_iMaxAllowedErrors = 5;

    /**
     * max age of last crawling date
     * @var type 
     */
    private $_iMaxAllowedAgeOfLastIndex = 604800; // 1 week
    // public $_iMaxAllowedAgeOfLastIndex=86400;  // 1 day


    // ----------------------------------------------------------------------

    /**
     * new crawler
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct($iSiteId = false) {
        $this->setSiteId($iSiteId);
        $this->_aUrls2Crawl = array();
        return true;
    }


    // ----------------------------------------------------------------------
    // ACTIONS CRAWLING
    // ----------------------------------------------------------------------
    
    /**
     * get disallow entries from read robots.txt and add them to the exclude
     * rules; it follows exclude rules for agent "*" and $this->aAbout['product']
     * (what is "ahCrawler")
     * @param string $sUrl  any url ... the robots.txt fill be detected from it
     * @return array
     */
    private function _GetExcludesFromRobotsTxt($sUrl) {
        $urlRobots = preg_replace('#^(http.*//.*)/.*$#U', '$1', $sUrl ) . "/robots.txt";
        echo "ROBOTS.TXT: reading $urlRobots\n";    
        $rollingCurl = new \RollingCurl\RollingCurl();
        $self = $this;
        $rollingCurl->setOptions(array(
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => $this->sUserAgent,
                CURLOPT_VERBOSE => false,

                // TODO: this is unsafe .. better: let the user configure it
                CURLOPT_SSL_VERIFYPEER => 0,
            ))
            ->get($urlRobots)
            ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use ($self) {
                $self->addExcludesFromRobotsTxt($request->getResponseText());
            })
            ->execute()    
            ;
        return true;
    }
    

    /**
     * get disallow entries from read robots.txt and add them to the exclude
     * rules; it follows exclude rules for agent "*" and $this->aAbout['product']
     * (what is "ahCrawler")
     */
    public function addExcludesFromRobotsTxt($sContent) {
        $regs = Array();
        
        $bCheck=1;
        if (!$sContent){
            return false;
        }
        $aTmp=explode("\n", $sContent);
        while (list ($id, $line) = each($aTmp)) {
            $line=preg_replace('/#.*/', '', $line);
            if (preg_match("/^user-agent: *([^#]+) */i", $line, $regs)) {
                $this_agent = trim($regs[1]);
                $bCheck = ($this_agent == '*' || strtolower($this->aAbout['product'])===strtolower($this_agent));
            }
            if ($bCheck == 1 && preg_match("/disallow: *([^#]+)/i", $line, $regs) ) {
                // echo "ROBOTS:TXT: $line\n";
                $disallow_str = preg_replace("/[\n ]+/i", "", $regs[1]);
                if (trim($disallow_str) != "") {
                    // $sEntry = '.*\/\/'. $sHost . '/' . $disallow_str . '.*';
                    $sEntry = $disallow_str;
                    $sEntry = str_replace('//', '/', $sEntry);
                    // ?? $sEntry = str_replace('*\.*', '.*', $sEntry);
                    // $sEntry = str_replace('.', '\.', $sEntry);
                    foreach (array('.', '?') as $sMaskMe){
                        $sEntry = str_replace($sMaskMe, '\\'.$sMaskMe, $sEntry);
                    }
                    $sEntry = str_replace('*', '.*', $sEntry);
                    
                    if (!strpos($sEntry, '$')){
                        $sEntry .= '.*';
                    }
                    
                    if (!array_key_exists('exclude', $this->aProfile['searchindex'])){
                        $this->aProfile['searchindex']['exclude']=array();
                    } 
                    if (!array_search($sEntry, $this->aProfile['searchindex']['exclude'])) {
                        $this->aProfile['searchindex']['exclude'][] = $sEntry;
                    }
                }
            }
        }
        // print_r($this->aProfile['exclude']); die();
        return true;
        
    }

    /**
     * helper function for _addUrl2Crawl
     * find a matching with one of the include or exclude regex to a given 
     * string (url or path)
     * 
     * @param string  $sOptionKey  key in $this->aProfile
     * @param string  $sString     url or path
     * @return boolean
     */
    private function _checkRegexArray($sOptionKey, $sString) {
        $bFound = false;
        if (array_key_exists($sOptionKey, $this->aProfile['searchindex']) && count($this->aProfile['searchindex'][$sOptionKey])) {
            foreach ($this->aProfile['searchindex'][$sOptionKey] as $sRegex) {
                // TODO: mask regex - i.e. $sRegex is "/*search_ger\.html$.*"
                if (preg_match('#' . $sRegex . '#', $sString)) {
                    // echo "OK\n";
                    $bFound = true;
                }
            }
        }
        return $bFound;
    }

    /**
     * mark an url to be crawled. It returns true if it was newly added to
     * the queue; it returns false if it was added or crawled already.
     * @param string $sUrl  url
     * @return boolean
     */
    private function _addUrl2Crawl($sUrl, $bDebug=false) {
        echo $bDebug ? __FUNCTION__."($sUrl)\n" : "";
        
        // remove url hash
        $sUrl = preg_replace('/#.*/', '', $sUrl);
        // ... and spaces
        $sUrl = str_replace(' ', '%20', $sUrl);

        if (array_key_exists($sUrl, $this->_aUrls2Crawl)) {
            echo $bDebug ? "... don't adding $sUrl - it was added already\n" : "";
            return false;
        }

        if (array_key_exists('stickydomain', $this->aProfile['searchindex']) && $this->aProfile['searchindex']['stickydomain'] && $this->aProfile['searchindex']['stickydomain'] != parse_url($sUrl, PHP_URL_HOST)) {
            echo $bDebug ? "... SKIP url is outside sticky domain " . $this->aProfile['searchindex']['stickydomain']."\n" : "";
            return false;
        }

        $sPath = parse_url($sUrl, PHP_URL_PATH);

        $bFound = $this->_checkRegexArray('include', $sUrl);
        if (!$bFound) {
            $bFound = $this->_checkRegexArray('includepath', $sPath);
        }
        if ($bFound && $this->_checkRegexArray('exclude', $sUrl)) {
            echo $bDebug ? "... SKIP Found in exclude for $sUrl\n" : "";
            $bFound = false;
        }
        if (!$bFound) {
            echo $bDebug ? "... SKIP by config $sUrl (no include for it)\n" : "";
            return false;
        }
        foreach ($this->aExclude as $sRegex) {
            // echo "check regex $sRegex on $sUrl\n";
            if (preg_match('#' . $sRegex . '#', $sUrl)) {
                echo $bDebug ? "... SKIP - it matches exclude $sRegex \n" : "";
                return false;
            }
        }

        $sSkipExtensions='/(\.'.implode('|\.', $this->aSkipExtensions).')/i';
        if (preg_match($sSkipExtensions, $sUrl)) {
            echo $bDebug ? "... SKIP ext " . $sSkipExtensions."\n" : "";
            return false;
        }

        
        $curr_depth = substr_count(str_replace("//", "/", parse_url($sUrl, PHP_URL_PATH)), "/");
        if ($this->aProfile['searchindex']['iDepth'] && $curr_depth > $this->aProfile['searchindex']['iDepth']) {
            echo $bDebug ? "... don't adding $sUrl - max depth is ".$this->aProfile['searchindex']['iDepth']."\n" : "";
            return false;
        }

        if (!array_key_exists($sUrl, $this->_aUrls2Crawl)) {
            echo "... adding $sUrl\n";
            $this->_aUrls2Crawl[$sUrl] = true;

            return true;
        }
        echo $bDebug ? "... was added already." : "";
        return false;
    }

    /**
     * mark an url that it was crawled already
     * @param string $sUrl
     * @return boolean
     */
    private function _removeUrlFromCrawling($sUrl) {
        // echo __FUNCTION__."($sUrl)\n";
        return $this->_aUrls2Crawl[$sUrl] = false;
    }

    /**
     * get the urls that are known to be crawled (their count can increase
     * during crawl process by analysing links in pages)
     * @return type
     */
    private function _getUrls2Crawl() {
        // echo __FUNCTION__."()\n";
        $aReturn = array();
        foreach ($this->_aUrls2Crawl as $sUrl => $bToDo) {
            if ($bToDo > 0) {
                $aReturn[] = $sUrl;
            }
        }
            //print_r($aReturn);
        return $aReturn;
    }

    /**
     * output helper for indexer: show count of urls, crawled urls und how many are left
     * @return type
     */
    private function _getCrawlStat() {
        // echo __FUNCTION__."()\n";
        $iTimer = date("U") - $this->iStartCrawl;

        $sReturn = "\n" . $iTimer . 's - total: ' . count($this->_aUrls2Crawl) . ' '
                // . '... requested: ' . (count($this->_aUrls2Crawl) - count($this->_getUrls2Crawl())) . ' '
                // . '... todo: ' . count($this->_getUrls2Crawl())
                . '... done: ' . $this->_iUrlsCrawled;
        if ($iTimer) {
            $sReturn .= ' ... ' . (round($this->_iUrlsCrawled / $iTimer * 100) / 100) . ' urls per sec. ';
        }
        $sReturn .= "\n";
        return $sReturn;
    }

    /**
     * do something with the response of a found url...
     * (this is the callback of rollingcurl - it must be public)
     * 
     * @param type $response
     * @param type $info
     * @return boolean
     */
    public function processResponse($response) {
        $url = $response->getUrl();
        $info = $response->getResponseInfo();
        $oHttpstatus=new httpstatus($info);
        
        $this->_iUrlsCrawled++;
        $this->_removeUrlFromCrawling($url);

        // echo "DEBUG: ".__FUNCTION__."$url  - STATUS " . $oHttpstatus->getHttpcode() . "\n";
        if ($oHttpstatus->isError()) {
            echo "ERROR: fetching $url FAILED. Status: ".$oHttpstatus->getHttpcode()." - ".$oHttpstatus->getStatus().".\n";
            return false;
        }
        if ($oHttpstatus->isRedirect()) {
            $sNewUrl=$oHttpstatus->getRedirect();
            echo "REDIRECT: $url ".$oHttpstatus->getHttpcode()." - ".$oHttpstatus->getStatus()." -> ".$sNewUrl.".\n";
            if ($sNewUrl){
                $this->_addUrl2Crawl($sNewUrl, true);
            }
            return true;
        }
        if ($oHttpstatus->isOK()) {
            switch ($oHttpstatus->getContenttype()) {
                case 'text/html':
                    $this->_processHtmlPage($response->getResponseText(), $info);
                    break;
                default:
                    echo "WARNING: handling of MIME [".$oHttpstatus->getContenttype()."] was not implemented (yet). Cannot proceed with url $url ... \n";
                    return false;
            }
        }
        
        return true;
    }

    /**
     * do something with the response of a found html page
     * 
     * @param string  $response  html response
     * @param array   $info      response header infos
     * 
     * @return type
     */
    private function _processHtmlPage($response, $info) {
        
        $url = $info['url'];
        $oHtml=new analyzerHtml($response, $url);
        if ($this->_bFollowLinks && $oHtml->canFollowLinks()){
            // TODO:
            // use external links too and check domain with sticky domain array
            $aLinks=$oHtml->getLinks("internal");
            if (array_key_exists('internal', $aLinks) && count($aLinks['internal'])){
                foreach ($aLinks['internal'] as $aLink){
                    if (!array_key_exists('rel', $aLink) 
                        || (array_key_exists('rel', $aLink) && $aLink['rel']!='nofollow')){
                        
                        // echo basename($url) ." - internal -> ";
                        // echo ($this->_addUrl2Crawl($aLink['_url']) ? "" : "(skip) ".$aLink['_url'] ). "\n"; 
                        $this->_addUrl2Crawl($aLink['_url']);
                    }
                }
            }
        }
        if ($oHtml->canIndexContent()){
            $this->_preparePageForIndex(array('header' => $info, 'body' => $response));
        }

        return true;
        // return array_keys($aUrls);
    }

    /**
     * start crawler to index whole page or opdate last errors only
     * @param boolean  $bMissesOnly  flag: rescan pages with errors only
     */
    public function run($bMissesOnly=false) {

        $this->_iUrlsCrawled = 0;
        $this->iStartCrawl = date("U");
        
        // scan content and follow links
        $this->_bFollowLinks=true;
        
        // ------------------------------------------------------------
        // get start urls ...
        // ------------------------------------------------------------
        $aStartUrls=array();
        if ($bMissesOnly){
            // ... pages with error
            echo "RESCAN index for pages with error.\n";
            $this->_bFollowLinks=false;
            $aUrls=$this->oDB->select('pages', 
                array('url'), 
                array(
                    'AND' => array(
                    'siteid' => $this->iSiteId,
                    'errorcount[>]' => 0,
                    ),
                ));
            if (is_array($aUrls) && count($aUrls)){
                foreach ($aUrls as $aRow){
                    $aStartUrls[]=$aRow['url'];
                }
                
            }
        } else {
            // ... starturls in config
            echo "RESCAN complete index.\n";
            if (array_key_exists('urls2crawl', $this->aProfile['searchindex'])) {
                foreach ($this->aProfile['searchindex']['urls2crawl'] as $sUrl) {
                    $aStartUrls[]=$sUrl;
                }
            }
        }
        
        // ------------------------------------------------------------
        // crawling
        // ------------------------------------------------------------
        $this->updateMultipleUrls($aStartUrls, $this->_bFollowLinks);
        

        // ------------------------------------------------------------
        // finalize
        // ------------------------------------------------------------
        
        $this->_cleanupSearchIndex();
        // print_r($this->oDB->select('pages', array('url', 'ts', 'siteid', 'errorcount', 'tserror')));
        
        $iUrls = $this->oDB->count('pages', array('url'), array(
            'AND' => array(
                'siteid' => $this->iSiteId,
            ),));

        echo "\n"
        . "Crawler has finished.\n\n"
        . "STATUS: \n"
                . $this->_iUrlsCrawled . " urls were crawled\n"
                . "process needed " . (date("U") - $this->iStartCrawl) . " sec.\n"
                . "$iUrls of profile [".$this->iSiteId."] the search index now (table 'pages')\n"
                ;
        
    }
    
    /**
     * update content of urls in the search index 
     * (with or without following its links)
     * @param array   $aUrls          array of urls
     * @param boolean $bFollowLinks   flag: scan content and follow links (default: false)
     */
    public function updateMultipleUrls($aUrls, $bFollowLinks=false) {
        // echo "--- ".__FUNCTION__."([array], [$bFollowLinks]) \n";
        $sMsgId='crawler-'.($bFollowLinks ? 'index':'update').'-profile-'.$this->iSiteId;
        if (!is_array($aUrls) || !count($aUrls)){
            echo "INFO: list of starting urls is empty.\n";
            return false;
        }
        if (!$this->enableLocking(__CLASS__, ($bFollowLinks ? 'index':'update'), $this->iSiteId)){
            echo "ABORT: the action is still running.\n";
            return false;
        }
        
        // scan content and follow links
        $this->_bFollowLinks=$bFollowLinks;

        $bPause = false;
        $bIsFirst=true;

        foreach($aUrls as $sUrl){
            if ($bIsFirst) {
                // get robots txt based on given url
                $this->_GetExcludesFromRobotsTxt($sUrl);
                $bIsFirst=false;
            }
            $this->_addUrl2Crawl($sUrl, true);
        }
        
        // print_r($this->aOptions); $oStatus->finishAction($sMsgId);die();
        $self = $this;
        while (count($this->_getUrls2Crawl())) {
            if ($bPause && $this->iSleep) {
                $this->touchLocking('sleep ' . $this->iSleep . 's');
                echo "sleep ..." . $this->iSleep . "s\n";
                sleep($this->iSleep);
            }
            $bPause = true;
            $this->touchLocking('urls left ' . count($this->_getUrls2Crawl()). ' ... ');
            
            $rollingCurl = new \RollingCurl\RollingCurl();
            foreach ($this->_getUrls2Crawl() as $sUrl) {
                $rollingCurl->get($sUrl);
            }
            $iSimRequests=array_key_exists('simultanousRequests', $this->aProfile['searchindex']) 
                    ? $this->aProfile['searchindex']['simultanousRequests']
                    : $this->aOptions['crawler']['searchindex']['simultanousRequests']
                    ;
            $rollingCurl->setOptions(array(
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_USERAGENT => $this->sUserAgent,
                    CURLOPT_USERPWD => array_key_exists('userpwd', $this->aProfile) ? $this->aProfile['userpwd']:false,
                    CURLOPT_VERBOSE => false,

                    // TODO: this is unsafe .. better: let the user configure it
                    CURLOPT_SSL_VERIFYPEER => 0,
                ))
                ->setSimultaneousLimit($iSimRequests)
                ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use ($self) {
                    // echo $request->getResponseText();
                    // echo "... content: " . substr($request->getResponseText(), 0 ,10) . " (...) \n";
                    $self->processResponse($request);
                    $self->touchLocking('processing ' . $request->getUrl());
                    $rollingCurl->clearCompleted();
                    $rollingCurl->prunePendingRequestQueue();
                })
                ->execute()    
                ;
            
        }
        
        
        $this->touchLocking('update index and keywords');
        $this->updateIndexAndKeywords();
        $this->disableLocking();
        
    }
    
    /**
     * update content of a single url in the search index 
     * (without following its links)
     * @param string $sUrl url to update in the index
     */
    public function updateSingleUrl($sUrl) {
        return $this->updateMultipleUrls(array($sUrl));
    }

    /**
     * delete a single item from search index
     * @param type $sId
     * @return type
     */
    public function deleteFromIndex($sId){
        $aResult = $this->oDB->delete('pages', array(
            'AND' => array(
                'siteid' => $this->iSiteId,
                'id' => $sId,
            ),
        ));
        $this->_checkDbResult($aResult);
        return $aResult;
    }
    
    public function getLastRecord($aFilter = array()) {
        return $this->getLastTsRecord("pages", $aFilter ? $aFilter : array('siteid'=>$this->iSiteId));
    }
    public function getCount($aFilter = array()) {
        return $this->getRecordCount("pages", $aFilter ? $aFilter : array('siteid'=>$this->iSiteId));
    }
    
    // ----------------------------------------------------------------------
    // ACTIONS INDEXING
    // ----------------------------------------------------------------------

    /**
     * helper function: get a value from html header
     * called in _preparePageForIndex()
     * 
     * @param type $sHtml
     * @param type $sItem
     * @return boolean|array
     */
    private function _getMetaHead($sHtml, $sItem) {
        preg_match("@<head[^>]*>(.*?)<\/head>@si", $sHtml, $regs);
        if (!is_array($regs) || count($regs) < 2) {
            return false;
        }
        $headdata = $regs[1];

        $res = Array();
        if ($headdata != "") {
            if (preg_match("@<" . $sItem . " *>(.*?)<\/" . $sItem . "*>@si", $sHtml, $regs)) {
                return trim($regs[1]);
            }
            preg_match("/<meta +name *=[\"']?" . $sItem . "[\"']? *content=[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res);
            if (isset($res) && count($res) > 1) {
                return $res[1];
            }
        }
        return false;
    }

    /**
     * index a html page
     * 
     * @param array  $aPage  array with keys header and body
     * @return boolean
     */
    private function _preparePageForIndex($aPage) {
        // print_r($aPage['header']);
        $url = $aPage['header']['url'];
        if ($aPage['header']['http_code'] >= '400') {
            echo "ERROR: http code ".$aPage['header']['http_code'];
            print_r($aPage['header']); 
            if ($aPage['header']['http_code']<500){
                echo " - remove from index: $url<br>\n";
                $this->_markAsErrorInSearchIndex($url, $aPage['header']);
            } else {
                echo " - NO UPDATE: $url<br>\n";
            }
            // echo "\n";sleep(5);
            return false;
        }
        $sContent = utf8_decode($aPage['body']);
        $sRobots = $this->_getMetaHead($sContent, 'robots');
        if ($sRobots && (strpos($sRobots, 'noindex') === 0 || strpos($sRobots, 'noindex') > 0)) {
            echo "Skip Index by meta robots $sRobots\n";
            return false;
        }

        $sTitle = $this->_getMetaHead($sContent, 'title');
        $sDescr = $this->_getMetaHead($sContent, 'description');
        $sKeywords = $this->_getMetaHead($sContent, 'keywords');

        $sContent = preg_replace("/<link rel[^<>]*>/i", " ", $sContent);
        $sContent = preg_replace("@<!--sphider_noindex-->.*?<!--\/sphider_noindex-->@si", " ", $sContent);
        $sContent = preg_replace("@<!--.*?-->@si", " ", $sContent);
        // $sContent = preg_replace("@<table class=\"xdebug-error.*?</table>@si", " ", $sContent);
        $sContent = preg_replace("@<script[^>]*?>.*?</script>@si", " ", $sContent);
        $sContent = preg_replace("@<nav.*?>.*?</nav>@si", " ", $sContent);

        $sContent = preg_replace("@<style[^>]*>.*?<\/style>@si", " ", $sContent);

        // create spaces between tags, so that removing tags doesnt concatenate strings
        $sContent = preg_replace("/>/", "\\0 ", $sContent);
        $sContent = strip_tags($sContent);
        $sContent = preg_replace("/&nbsp;/", " ", $sContent);
        $sContent = preg_replace('/\s+/', ' ', $sContent);

        $this->_addToSearchIndex(
                array(
                    'url' => $url,
                    'title' => $sTitle,
                    'description' => $sDescr,
                    'keywords' => $sKeywords,
                    'content' => $sContent,
                    'header' => $aPage['header'],
                    'response' => $aPage['body'],
                )
        );
    }

    /**
     * generate page id by given url (plus siteID)
     * @param type $sUrl
     * @return type
     */
    private function _getPageId($sUrl) {
        $aCurrent = $this->oDB->select(
            'pages', 
            array('id'), 
            array(
                'url' => $sUrl,
                'siteid' => $this->iSiteId
            )
        );
        return count($aCurrent) ? $aCurrent[0]['id'] : false;
        // return $this->iSiteId ? md5($sUrl . $this->iSiteId) : false;
    }

    /**
     * add / update page data in search index
     * @param type $aData
     */
    private function _addToSearchIndex($aData) {
        if (!$this->iSiteId) {
            echo "WARNING: you need to set the siteId first.";
            return false;
        }
        // echo "add to index: ".$aData['url']."\n";
        // first try: update - if it fails, then insert.
        
        // $iPageId=$this->_getPageId($aData['url']);
        
        $sFieldToCompare='response';
        $aCurrent = $this->oDB->select('pages', array('id', $sFieldToCompare, 'errorcount'), array(
            'url' => $aData['url'],
            'siteid' => $this->iSiteId,
        ));
        
        // fix charset 4 studmed (on iso-8859-1) .. TODO: check on UTF8 web
        foreach(array('title','description','keywords','content', 'response') as $sKey){
            $aData[$sKey]= mb_convert_encoding($aData[$sKey], "UTF-8");
        }
        
        if (count($aCurrent) && $aCurrent[0][$sFieldToCompare]) {
        // if ($iPageId) {

            if ($aCurrent[0][$sFieldToCompare] == $aData[$sFieldToCompare]){
                echo 'NO CHANGE';
            } else {
                echo 'UPDATE CONTENT';
            }
            
            // echo ' ('.$aCurrent[0]['errorcount'] . ' errors) ';
            $aResult = $this->oDB->update('pages', array(
                'url' => $aData['url'],
                'siteid' => $this->iSiteId,
                'title' => utf8_encode($aData['title']),
                'description' => utf8_encode($aData['description']),
                'keywords' => utf8_encode($aData['keywords']),
                'content' => utf8_encode($aData['content']),
                'header' => json_encode($aData['header']),
                'response' => $aData['response'],
                'ts' => date("Y-m-d H:i:s"),
                'tserror' => false,
                'errorcount' => 0,
                'lasterror' => '',
                ), 
                array(
                    // 'id' => $aCurrent[0]['id'],
                    'url' => $aData['url'],
                    'siteid' => $this->iSiteId,
                )
            );
            
            echo ' ' . $aData['url'] . "\n";
        } else {
            echo 'INSERT data for ' . $aData['url'] . "\n";
            $aResult = $this->oDB->insert(
                    'pages', 
                    array(
                        // 'id' => $this->_getPageId($aData['url']),
                        'url' => $aData['url'],
                        'siteid' => $this->iSiteId,
                        'title' => $aData['title'],
                        'description' => $aData['description'],
                        'keywords' => $aData['keywords'],
                        'content' => $aData['content'],
                        'header' => json_encode($aData['header']), // TODO: handle umlauts in response
                        'response' => $aData['response'],
                        'ts' => date("Y-m-d H:i:s"),
                        'tserror' => false,
                        'errorcount' => 0,
                    )
            );
        }
        if(isset($aResult)){
            $this->_checkDbResult($aResult, 1);
            
            /*
            // echo $this->oDB->last();
            $aCurrent = $this->oDB->select('pages', array('id', $sFieldToCompare, 'errorcount'), array(
                'url' => $aData['url'],
                'siteid' => $this->iSiteId,
            ));
        
            echo "\n";
            echo "NACHHER: " . $sFieldToCompare."\n";
            echo strlen($aData[$sFieldToCompare]) . " byte content\n";
            echo strlen($aCurrent[0][$sFieldToCompare]) . " byte db-inhalt\n";
            echo "\n";
             * 
             */
            
            return $aResult;
        }
        return true;
    }

    /**
     * mark an url in search index that it has an error; it increases
     * the error counter 
     * @param string  $sUrl
     * @param array   $aHeader  http response header (=last error)
     */
    private function _markAsErrorInSearchIndex($sUrl, $aHeader = array()) {
        if (!$this->iSiteId) {
            echo "WARNING: you need to set the siteId first.";
            return false;
        }
        $aResult = $this->oDB->update(
            'pages', array(
                'errorcount[+]' => 1,
                'tserror' => date("Y-m-d H:i:s"),
                'lasterror' => json_encode($aHeader)
            ), 
            array('id' => $this->_getPageId($sUrl))
        );
        $this->_checkDbResult($aResult);
        return $aResult;
    }

    /**
     * cleanup search index: remove old pages and entries with error count > N
     * @return boolean
     */
    private function _cleanupSearchIndex() {
        $aResult = $this->oDB->delete('pages', array(
            'AND' => array(
                'siteid' => $this->iSiteId,
                'OR' => array(
                    'errorcount[>]' => $this->_iMaxAllowedErrors,
                    'ts[<]' => date("Y-m-d H:i:s", (date("U") - $this->_iMaxAllowedAgeOfLastIndex)),
                )
            ),
        ));
        $this->_checkDbResult($aResult);
        
        return true;
    }

    public function updateIndexAndKeywords() {
        if (!$this->iSiteId) {
            return false;
        }
        echo "BUILD INDEX ... finding words ";
        $aWords=array();
        $aResult = $this->oDB->select(
            'pages', 
            array('title', 'description', 'keywords', 'content'), 
            array(
            'AND' => array(
                'siteid' => $this->iSiteId,
                'errorcount' => 0,
                )   
            )
        );
        $this->_checkDbResult($aResult);
        echo "... and count ";
        foreach($aResult as $aRow){
            // print_r($aRow);
            foreach(str_word_count(html_entity_decode(
                    $aRow['description']
                    .' ' . $aRow['title']
                    .' ' . $aRow['keywords']
                    .' ' . $aRow['content']
                ),2) as $sWord ){
                $sWord=str_replace("'", '', $sWord);
                $sKey=strtolower($sWord);
                if(strlen($sKey)>2){
                    if(!array_key_exists($sKey, $aWords)){
                        $aWords[$sKey]=1;
                    } else {
                        $aWords[$sKey]++;
                    }
                }
            }
        }
        echo "... and sort ";
        arsort($aWords);
        // print_r($aWords);


        $aResult = $this->oDB->delete('words', array('siteid'=>$this->iSiteId));
        $this->_checkDbResult($aResult);
        
        echo "... and insert " .count($aWords). " words to siteid " . $this->iSiteId ;
        $aInsertdata=array();
        $iCounter=0;
        foreach ($aWords as $sWord=>$iCount){
            $iCounter++;
            $aInsertdata[]=array('word'=>$sWord, 'count'=>$iCount, 'siteid'=>$this->iSiteId);
            if($iCounter>99){
                $aResult = $this->oDB->insert('words', $aInsertdata);
                echo ".";
                // echo "\n" . $this->oDB->last_query() . "<br>\n";
                $this->_checkDbResult($aResult);
                $aInsertdata=array();
                $iCounter=0;
            }
        }
        /*
        if (count($aInsertdata)){
            
            $aResult = $this->oDB->insert('words', $aInsertdata);
        }
         * 
         */
        // echo "\n" . $this->oDB->last_query() . "\n";
        echo "\n";
        return $aResult;

    }


}
