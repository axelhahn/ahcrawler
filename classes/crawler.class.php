<?php

require_once 'crawler-base.class.php';
require_once 'status.class.php';
require_once 'httpstatus.class.php';
require_once __DIR__.'/../vendor/rolling-curl/src/RollingCurl/RollingCurl.php';
require_once __DIR__.'/../vendor/rolling-curl/src/RollingCurl/Request.php';
require_once 'analyzer.html.class.php';

/**
 * ____________________________________________________________________________
 *          __    ______                    __             
 *   ____ _/ /_  / ____/________ __      __/ /__  _____    
 *  / __ `/ __ \/ /   / ___/ __ `/ | /| / / / _ \/ ___/    
 * / /_/ / / / / /___/ /  / /_/ /| |/ |/ / /  __/ /        
 * \__,_/_/ /_/\____/_/   \__,_/ |__/|__/_/\___/_/         
 * ____________________________________________________________________________ 
 * Free software and OpenSource * GNU GPL 3
 * DOCS https://www.axel-hahn.de/docs/ahcrawler/index.htm
 * 
 * THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE <br>
 * LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR <br>
 * OTHER PARTIES PROVIDE THE PROGRAM ?AS IS? WITHOUT WARRANTY OF ANY KIND, <br>
 * EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED <br>
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE <br>
 * ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. <br>
 * SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY <br>
 * SERVICING, REPAIR OR CORRECTION.<br>
 * 
 * ----------------------------------------------------------------------------
 * CRAWLER AND INDEXER
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
    private $_aUrls2Skip = array();
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
        // documents
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
        'webm',
        // archives
        '7z',
        'ace',
        'gz',
        'hqx',
        'rar',
        'sit',
        'tar',
        'tgz',
        'zip',
        // disc images
        'iso',
        // installer
        'deb',
        'dmg',
        'exe',
        'msi',
        'rpm',
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
        $this->_aUrls2Skip = array();
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
        $this->cliprint('info', "INFO: respect the ROBOTS.TXT: reading $urlRobots\n");
        $rollingCurl = new \RollingCurl\RollingCurl();
        $self = $this;

        $aCurlOpt=$this->_getCurlOptions();

        $rollingCurl->setOptions($aCurlOpt)
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
        foreach(explode("\n", $sContent) as $line) {
            // echo "DEBUG: $line\n";
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
                    
                    if (!array_search($sEntry, $this->aProfileEffective['searchindex']['exclude'])) {
                        $this->aProfileEffective['searchindex']['exclude'][] = $sEntry;
                    }
                }
            }
        }
        // print_r($this->aProfile['searchindex']['exclude']); die();
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
    private function _checkRegexArray($sOptionKey, $sString, $bDefault=true) {
        $bFound = $bDefault;
        if (array_key_exists($sOptionKey, $this->aProfileEffective['searchindex']) && count($this->aProfileEffective['searchindex'][$sOptionKey])) {
            $bFound = false;
            foreach ($this->aProfileEffective['searchindex'][$sOptionKey] as $sRegex) {
                // TODO: mask regex - i.e. $sRegex is "/*search_ger\.html$.*"
                // echo "DEBUG check $sOptionKey -> $sRegex\n";
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
        $this->cliprint('cli', $bDebug ? __FUNCTION__."($sUrl)\n" : "");
        $bDebug=true;
        
        // remove url hash
        $sUrl = preg_replace('/#.*/', '', $sUrl);
        // ... and spaces
        $sUrl = str_replace(' ', '%20', $sUrl);

        if (array_key_exists($sUrl, $this->_aUrls2Crawl)) {
            // $this->cliprint('cli', $bDebug ? "... was added already: $sUrl\n" : "");
            return false;
        }
        if (array_key_exists($sUrl, $this->_aUrls2Skip)) {
            $this->cliprint('cli', $bDebug ? "... was marked to skip already: $sUrl\n" : "");
            return false;
        }

        // check if the given url matches any vhost in the start urls
        
        if(!$this->_checkRegexArray('_vhosts', $sUrl)){
            $this->cliprint('info', "... SKIP outside search url(s): $sUrl\n");
            return false;
        }

        $sPath = parse_url($sUrl, PHP_URL_PATH);

        $bFound = $this->_checkRegexArray('include', $sUrl);
        if ($bFound) {
            if ($this->_checkRegexArray('includepath', $sPath)){
                if ($bFound && $this->_checkRegexArray('exclude', $sUrl, false)) {
                    $this->cliprint('cli', "... SKIP Found in exclude for $sUrl\n");
                    return false;
                }
            } else {
                $this->cliprint('cli', "... SKIP found in a vhost of start url(s) but does not match any includepath rule: $sUrl\n");
                return false;
            }
        } else {
            $this->cliprint('cli', "... SKIP found in a vhost of start url(s) but does not match any include: $sUrl\n");
            return false;
        }
        /*
        if (!$bFound) {
            $this->cliprint('info', $bDebug ? "... SKIP by config $sUrl (no include for it)\n" : "");
            return false;
        }
         * 
         */
        foreach ($this->aExclude as $sRegex) {
            // echo "check regex $sRegex on $sUrl\n";
            if (preg_match('#' . $sRegex . '#', $sUrl)) {
                // $this->cliprint('info', $bDebug ? "... SKIP - it matches exclude $sRegex \n" : "\n");
                $this->cliprint('info', "... SKIP - it matches exclude $sRegex \n");
                return false;
            }
        }

        $sSkipExtensions='/(\.'.implode('|\.', $this->aSkipExtensions).')$/i';
        if (preg_match($sSkipExtensions, $sUrl)) {
            // $this->cliprint('cli', $bDebug ? "... SKIP by extension: $sUrl\n" : "");
            $this->cliprint('cli', "... SKIP by extension: $sUrl\n");
            return false;
        }

        
        $curr_depth = substr_count(str_replace("//", "/", parse_url($sUrl, PHP_URL_PATH)), "/");
        if ($this->aProfileEffective['searchindex']['iDepth'] && $curr_depth > $this->aProfileEffective['searchindex']['iDepth']) {
            $this->cliprint('info', $bDebug ? "... don't adding $sUrl - max depth is ".$this->aProfileEffective['searchindex']['iDepth']."\n" : "");
            return false;
        }
        
        if (!array_key_exists($sUrl, $this->_aUrls2Crawl)) {

            $iCount=count($this->_aUrls2Crawl)+1;
            $iMax=isset($this->aProfileEffective['searchindex']['iMaxUrls']) ? (int)$this->aProfileEffective['searchindex']['iMaxUrls'] : 0;
            if($iMax 
                && count($this->_aUrls2Crawl) >= $this->aProfileEffective['searchindex']['iMaxUrls']
            ){
                $this->cliprint('warning', "... SKIP: maximum of $iMax urls to crawl was reached. I do not add $sUrl\n");
                $this->_aUrls2Skip[$sUrl] = true;
                return false;
            }
            $this->cliprint('cli', "... adding #$iCount".($iMax ? ' of ' .$iMax : '').": $sUrl\n");
            $this->_aUrls2Crawl[$sUrl] = true;

            return true;
        }
        $this->cliprint('cli',  $bDebug ? "... was added already." : "");
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
        // list($sHttpHeader, $sHttpBody)=explode("\r\n\r\n", $response->getResponseText(), 2);
        $aTmp=explode("\r\n\r\n", $response->getResponseText(), 2);
        $sHttpHeader=$aTmp[0];
        $sHttpBody=isset($aTmp[1]) ? $aTmp[1] : false;

        $info = $response->getResponseInfo();
        $info['_responseheader']=$sHttpHeader;
        
        $oHttpstatus=new httpstatus($info);
        
        $this->_iUrlsCrawled++;
        $this->_removeUrlFromCrawling($url);

        // echo "DEBUG: ".__FUNCTION__."$url  - STATUS " . $oHttpstatus->getHttpcode() . "\n";
        if ($oHttpstatus->isError()) {
            $this->cliprint('error', "ERROR: fetching $url FAILED. Status: ".$oHttpstatus->getHttpcode()." (".$oHttpstatus->getStatus().").\n");
            // print_r($aTmp); sleep(5);
            return false;
        }
        if ($oHttpstatus->isRedirect()) {
            $sNewUrl=$oHttpstatus->getRedirect();
            $this->cliprint('cli', "REDIRECT: $url ".$oHttpstatus->getHttpcode()." - ".$oHttpstatus->getStatus()." -> ".$sNewUrl.".\n");
            if ($sNewUrl){
                $this->_addUrl2Crawl($sNewUrl, true);
            }
            return true;
        }
        if ($oHttpstatus->isOK()) {
            switch ($oHttpstatus->getContenttype()) {
                case 'text/html':
                    // $this->cliprint('cli', "Analyzing html code of $url ... \n");
                    $this->_processHtmlPage($sHttpBody, $info);
                    break;
                default:
                    $this->cliprint('cli', "SKIP: MIME [".$oHttpstatus->getContenttype()."] of $url ... \n");
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
        // $bIngoreNoIndex=isset($this->aProfileSaved['searchindex']['ignoreNoindex']) && $this->aProfileSaved['searchindex']['ignoreNofollow'];
        $oHtml=new analyzerHtml($response, $url);
        $bIngoreNoFollow=isset($this->aProfileSaved['searchindex']['ignoreNofollow']) && $this->aProfileSaved['searchindex']['ignoreNofollow'];
        $bIngoreNoIndex=isset($this->aProfileSaved['searchindex']['ignoreNoindex']) && $this->aProfileSaved['searchindex']['ignoreNoindex'];
        echo "DEBUG: bIngoreNoFollow = $bIngoreNoFollow \n";
        echo "DEBUG: this-bFollowLinks = $this->_bFollowLinks \n";
        echo "DEBUG oHtml->canFollowLinks() = " . $oHtml->canFollowLinks() . "\n";
        if ($bIngoreNoFollow || ($this->_bFollowLinks && $oHtml->canFollowLinks())){
            // TODO:
            // use external links too and check domain with sticky domain array
            $aLinks=$oHtml->getLinks("internal");
            if (array_key_exists('internal', $aLinks) && is_array($aLinks['internal']) && count($aLinks['internal'])){
                foreach ($aLinks['internal'] as $aLink){
                    if (!array_key_exists('rel', $aLink) 
                        || (array_key_exists('rel', $aLink) && $aLink['rel']!='nofollow')){
                        
                        // echo basename($url) ." - internal -> ";
                        // echo ($this->_addUrl2Crawl($aLink['_url']) ? "" : "(skip) ".$aLink['_url'] ). "\n"; 
                        $this->_addUrl2Crawl($aLink['_url']);
                    }
                }
            }
        } else {
                $this->cliprint('info', "SKIP: do not following links in url $url\n");
        }
        if ($bIngoreNoIndex || $oHtml->canIndexContent()){
            $this->_preparePageForIndex(array('header' => $info, 'body' => $response));
        } else  {
            if($oHtml->hasOtherCanonicalUrl()){
                $sCanonicalUrl=$oHtml->getCanonicalUrl();
                $this->cliprint('info', "SKIP: do not index because canonical url was found: $sCanonicalUrl\n");
                $this->_addUrl2Crawl($sCanonicalUrl);
            } else {
                $this->cliprint('warning', "SKIP: a noindex flag was found (see html head section or http response header): $url\n");
            }
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
        if (!$bMissesOnly){
            $this->logfileDelete();
        }
        $this->cliprint('info', "========== Searchindex".PHP_EOL);
        $this->cliprint('info', 'starting point: '. __METHOD__.PHP_EOL);
        if (!$this->enableLocking(__CLASS__, 'index', $this->iSiteId)) {
            $this->cliprint('error', "ABORT: the action is still running (".__METHOD__.")\n");
            return false;
        }
        
        // ------------------------------------------------------------
        // get start urls ...
        // ------------------------------------------------------------
        $aStartUrls=array();
        if ($bMissesOnly){
            // ... pages with error
            $this->cliprint('info', "I do a rescan of pages with error.\n");
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
            $this->cliprint('info', "I do a rescan of ALL pages.\n");
            if(!count($this->aProfileEffective['searchindex']['urls2crawl'])){
                $this->cliprint('warning', 'WARNING: no urls in profiles->'.$this->iSiteId.'->urls2crawl->searchindex<br>'."\n");
            } else  {
                foreach ($this->aProfileEffective['searchindex']['urls2crawl'] as $sUrl) {
                    $aStartUrls[]=$sUrl;
                }
            }
        }
        
        // ------------------------------------------------------------
        // remove cookies
        // ------------------------------------------------------------
        /* v0.65 disable deletion of cookies
        if (file_exists($this->sCcookieFilename)){
            unlink($this->sCcookieFilename);
        }
         */

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

        $iTotal=date("U") - $this->iStartCrawl;
        $this->disableLocking();

        $this->cliprint('info', "----- Crawler has finished.\n");
        $this->cliprint('info', $this->_iUrlsCrawled . " urls were crawled\n");
        $this->cliprint('info', "process needed $iTotal sec; ". ($iTotal ? number_format($this->_iUrlsCrawled/$iTotal, 2)." urls per sec." : '')."\n");
        $this->cliprint('info', "STATUS of profile [".$this->iSiteId."] " . $this->aProfileEffective['label'].":\n");
        $this->cliprint('info', "$iUrls urls are in the search index now (table 'pages')\n");
        
        if($iUrls<$this->_iUrlsCrawled){
            $this->cliprint('warning', "Remark: Not all html pages were added (crawling denied or arror)\n");
        }
        
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
            $this->cliprint(
                $bFollowLinks ? 'error': 'info', 
                $bFollowLinks 
                    ? "ERROR: list of starting urls is empty. This seems to be a misconfiguration.\n"
                    : "INFO: Nothing to do. No updatetable urls with errors were found to reindex them.\n"
            )
            ;
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
        $rollingCurl = new \RollingCurl\RollingCurl();
        $aCurlOpt=$this->_getCurlOptions();
        $aCurlOpt[CURLOPT_USERPWD] = isset($this->aProfileEffective['userpwd']) ? $this->aProfileEffective['userpwd'] : false;
        $rollingCurl
            ->setOptions($aCurlOpt)
            ->setSimultaneousLimit((int)$this->aProfileEffective['searchindex']['simultanousRequests'])
            ;
        while (count($this->_getUrls2Crawl())) {
            if ($bPause && $this->iSleep) {
                $this->touchLocking('sleep ' . $this->iSleep . 's');
                $this->cliprint('info', "sleep ..." . $this->iSleep . "s\n");
                sleep($this->iSleep);
            }
            $bPause = true;
            $this->touchLocking('urls left ' . count($this->_getUrls2Crawl()). ' ... ');
            
            foreach ($this->_getUrls2Crawl() as $sUrl) {
                $rollingCurl->get($sUrl);
            }

            $rollingCurl->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use ($self) {
                    // echo $request->getResponseText();
                    // echo "... content: " . substr($request->getResponseText(), 0 ,10) . " (...) \n";
                    $self->processResponse($request);
                    $self->touchLocking('processing ' . $request->getUrl());
                    $rollingCurl->clearCompleted();
                })
                ->execute()
                ;
            $rollingCurl->prunePendingRequestQueue();
            
        }
        
        
        $this->touchLocking('update index and keywords');
        $this->updateIndexAndKeywords();
        
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
            $this->cliprint('error', "ERROR: http code ".$aPage['header']['http_code']."\n" . print_r($aPage['header'], 1));
            if ($aPage['header']['http_code']<500){
                $this->cliprint('error', " - increasing error counter: $url<br>\n");
                $this->_markAsErrorInSearchIndex($url, $aPage['header']);
            } else {
                $this->cliprint('info', " - NO UPDATE: $url<br>\n");
            }
            // echo "\n";sleep(5);
            return false;
        }

        $bIngoreNoIndex=isset($this->aProfileSaved['searchindex']['ignoreNoindex']) && $this->aProfileSaved['searchindex']['ignoreNoindex'];
        if($bIngoreNoIndex){
            $this->cliprint('info', "Ignoring Noindex for url $url\n");
        } else {
            // X-Robots-Tag in http response header
            // see https://developers.google.com/search/reference/robots_meta_tag
            // to test: $aPage['header']['X-Robots-Tag']='none';
            $sRobotsX=(isset($aPage['header']['X-Robots-Tag']) ? $aPage['header']['X-Robots-Tag'] : '');
            if (
                $sRobotsX && (
                    strpos($sRobotsX, 'noindex') === 0 || strpos($sRobotsX, 'noindex') > 0
                    || strpos($sRobotsX, 'none') === 0 || strpos($sRobotsX, 'none') > 0)
            ) {
                $this->cliprint('info', "Skip: X-Robots-Tag $sRobotsX for url $url\n");
                return false;
            }
        }
        
        // if it is NOT utf8 then utf8_decode()
        $sContent = (function_exists('mb_detect_encoding') && !mb_detect_encoding($aPage['body'], 'UTF-8, ISO-8859-1') === 'UTF-8')
            ? utf8_encode($aPage['body'])
            : $aPage['body']
        ;
        
        $sRobots=$this->_getMetaHead($sContent, 'robots');
        
        if (!$bIngoreNoIndex && $sRobots && (strpos($sRobots, 'noindex') === 0 || strpos($sRobots, 'noindex') > 0)) {
            $this->cliprint('info', "Skip: meta robots $sRobots for url $url\n");
            return false;
        }

        $sTitle = $this->_getMetaHead($sContent, 'title');
        $sDescr = $this->_getMetaHead($sContent, 'description');
        $sKeywords = $this->_getMetaHead($sContent, 'keywords');

        // get lang from <html lang=...>
        preg_match("@\<html.*\ lang=[\"\'](.*)[\"\']@iU", $sContent, $aTmp);
        $sLang=isset($aTmp[1]) ? $aTmp[1] : '';
        
        // print_r($this->aProfileEffective['searchindex']['regexToRemove']); echo count($this->aProfileEffective['searchindex']['regexToRemove']); die();
        if(!strlen($sContent)){
            $this->cliprint('warning', "WARNING: content is EMPTY for url [$url]?!\n");
        } else if(isset($this->aProfileEffective['searchindex']['regexToRemove']) 
                && is_array($this->aProfileEffective['searchindex']['regexToRemove'])
                && count($this->aProfileEffective['searchindex']['regexToRemove'])>0
        ){
            foreach ($this->aProfileEffective['searchindex']['regexToRemove'] as $sRegex){
                if($sRegex){
                    try{
                        $sContent = preg_replace("@".$sRegex."@si", " ", $sContent);
                        if(!strlen($sContent)){
                            $this->cliprint('warning', "WARNING: content is EMPTY after applying regex [$sRegex] on $url\n");
                        }
                    } 
                    catch(Exception $e) {
                        $this->logAdd(__METHOD__.'() - regex ['.$sRegex.'] seems to be wrong. ', error);
                    }
                }
            }
        }

        /*
        $sContent = preg_replace("/<link rel[^<>]*>/i", " ", $sContent);
        $sContent = preg_replace("@<!--sphider_noindex-->.*?<!--\/sphider_noindex-->@si", " ", $sContent);
        $sContent = preg_replace("@<!--.*?-->@si", " ", $sContent);
        // $sContent = preg_replace("@<table class=\"xdebug-error.*?</table>@si", " ", $sContent);
        $sContent = preg_replace("@<script[^>]*?>.*?</script>@si", " ", $sContent);
        $sContent = preg_replace("@<nav.*?>.*?</nav>@si", " ", $sContent);

        $sContent = preg_replace("@<style[^>]*>.*?<\/style>@si", " ", $sContent);
         * 
         */

        // create spaces between tags, so that removing tags doesnt concatenate strings
        $sContent = preg_replace("/>/", "\\0 ", $sContent);
        $sContent = strip_tags($sContent);
        $sContent = preg_replace("/&nbsp;/", " ", $sContent);
        $sContent = preg_replace('/\s+/', ' ', $sContent);
        $sContent = html_entity_decode($sContent);
        // echo "DEBUG content $sContent\n";
        // echo "DEBUG content strlen is finally ".strlen($sContent)." byte\n";

        $this->_addToSearchIndex(
                array(
                    'url' => $url,
                    'title' => $sTitle,
                    'description' => $sDescr,
                    'keywords' => $sKeywords,
                    'content' => $sContent,
                    'lang' => $sLang,
                    'size' => $aPage['header']['size_download'], // is byte
                    'time' => $aPage['header']['total_time'] ? (int)($aPage['header']['total_time']*1000) : -1, // is sec as float value
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

    protected function _getWordCount($s){
        $characterMap='À..ÿ'; // chars #192 .. #255
        return count(str_word_count(
                str_replace("'", '', $s),
                2,
                $characterMap
        ));
    }
    /**
     * add / update page data in search index
     * @param type $aData
     */
    private function _addToSearchIndex($aData) {
        if (!$this->iSiteId) {
            $this->cliprint('info', "WARNING: you need to set the siteId first.\n");
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
                $this->cliprint('cli', 'NO CHANGE '.$aData['url']."\n");
            } else {
                $this->cliprint('cli', 'UPDATE CONTENT for '.$aData['url']."\n");
            }
            // echo ' ('.$aCurrent[0]['errorcount'] . ' errors) ';
            $aResult = $this->oDB->update('pages', array(
                'url' => $aData['url'],
                'siteid' => $this->iSiteId,
                'title' => $aData['title'],
                'title_wc' => $this->_getWordCount($aData['title']),
                'description' => $aData['description'],
                'description_wc' => $this->_getWordCount($aData['description']),
                'keywords' => $aData['keywords'],
                'keywords_wc' => $this->_getWordCount($aData['keywords']),
                'content' => html_entity_decode($aData['content']),
                'lang' => $aData['lang'],
                'size' => $aData['size'],
                'time' => $aData['time'],
                'header' => json_encode($aData['header']),
                'response' => $aData['response'],
                'ts' => date("Y-m-d H:i:s"),
                'tserror' => '0000-00-00 00:00:00',
                'errorcount' => 0,
                'lasterror' => '',
                ), 
                array(
                    // 'id' => $aCurrent[0]['id'],
                    'url' => $aData['url'],
                    'siteid' => $this->iSiteId,
                )
            );
            
        } else {
            $this->cliprint('info', 'INSERT data for ' . $aData['url'] . "\n");
            // echo "  title: " . $aData['title'] . "\n";
            $aResult = $this->oDB->insert(
                    'pages', 
                    array(
                        // 'id' => $this->_getPageId($aData['url']),
                        'url' => $aData['url'],
                        'siteid' => $this->iSiteId,
                        'title' => $aData['title'],
                        'title_wc' => $this->_getWordCount($aData['title']),
                        'description' => $aData['description'],
                        'description_wc' => $this->_getWordCount($aData['description']),
                        'keywords' => $aData['keywords'],
                        'keywords_wc' => $this->_getWordCount($aData['keywords']),
                        'lang' => $aData['lang'],
                        'size' => $aData['size'],
                        'time' => $aData['time'],
                        'content' => html_entity_decode($aData['content']),
                        'header' => json_encode($aData['header']), // TODO: handle umlauts in response
                        'response' => $aData['response'],
                        'ts' => date("Y-m-d H:i:s"),
                        'tserror' => '0000-00-00 00:00:00',
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
            $this->cliprint('warning', "WARNING: you need to set the siteId first.");
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
        $characterMap='À..ÿ'; // chars #192 .. #255
        $this->cliprint('cli', "BUILD INDEX ... finding words ");
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
        $this->cliprint('cli', "... and count ");
        foreach($aResult as $aRow){
            // print_r($aRow);
            foreach(str_word_count(
                    str_replace("'", '',
                        $aRow['description']
                        .' ' . $aRow['title']
                        .' ' . $aRow['keywords']
                        .' ' . $aRow['content']
                    )
                ,2,$characterMap) as $sWord ){
                // $sWord= str_replace("'", '', $sWord);
                
                // strtolower destroyes umlauts
                // $sKey=strtolower($sWord);
                // $sKey=function_exists('mb_strtolower') ? mb_strtolower($sWord) : $sWord;
                $sKey=$sWord;
                if(strlen($sKey)>2){
                    if(!array_key_exists($sKey, $aWords)){
                        $aWords[$sKey]=1;
                    } else {
                        $aWords[$sKey]++;
                    }
                }
            }
        }
        $this->cliprint('cli', "... and sort ");
        arsort($aWords);
        // print_r($aWords);


        $aResult = $this->oDB->delete('words', array('siteid'=>$this->iSiteId));
        $this->_checkDbResult($aResult);
        
        $this->cliprint('cli', "... and insert " .count($aWords). " words to siteid " . $this->iSiteId) ;
        $aInsertdata=array();
        $iCounter=0;
        foreach ($aWords as $sWord=>$iCount){
            $iCounter++;
            $aInsertdata[]=array('word'=>$sWord, 'count'=>$iCount, 'siteid'=>$this->iSiteId);
            if($iCounter>99 || $iCounter==count($aWords)){
                $aResult = $this->oDB->insert('words', $aInsertdata);
                // $this->cliprint('cli', ".");
                // echo "\n" . $this->oDB->last_query() . "<br>\n";
                $this->_checkDbResult($aResult);
                $aInsertdata=array();
                $iCounter=0;
            }
        }
        $this->cliprint('cli', "\n") ;
        /*
        if (count($aInsertdata)){
            
            $aResult = $this->oDB->insert('words', $aInsertdata);
        }
         * 
         */
        // echo "\n" . $this->oDB->last() . "\n"; die("ABOORT in ". __FILE__ .' '.__METHOD__);
        // $this->cliprint('cli', "\n");
        return $aResult;

    }


}
