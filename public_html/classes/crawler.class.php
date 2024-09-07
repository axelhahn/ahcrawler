<?php

require_once 'crawler-base.class.php';
require_once 'status.class.php';
require_once 'httpstatus.class.php';
require_once __DIR__ . '/../vendor/rolling-curl/src/RollingCurl/RollingCurl.php';
require_once __DIR__ . '/../vendor/rolling-curl/src/RollingCurl/Request.php';
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
 * DOCS https://www.axel-hahn.de/docs/ahcrawler/
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
 * 2024-09-06  v0.167  php8 only; add typed variables; use short array syntax
 **/
class crawler extends crawler_base
{

    /**
     * Array with urls to crawl
     * @var array
     */
    private array $_aUrls2Crawl = [];

    /**
     * Array of urls to skip
     * @var array
     */
    private array $_aUrls2Skip = [];

    /**
     * Number of urls that have been crawled
     * @var int
     */
    private int $_iUrlsCrawled = 0;

    /**
     * Flag: scan content and follow links?
     * @var bool 
     */
    private bool $_bFollowLinks = true;

    /**
     * Sleep time during crawling [seconds]
     * @var integer
     */
    private int $iSleep = 0;

    /**
     * Unix timestamp when crawling of a project was started
     * @var int
     */
    private int $iStartCrawl = 0;

    /**
     * Array of extensions to skip in found links (in targets of a href="")
     * @var array
     */
    private array $aSkipExtensions = [
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
        'jpeg',
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
    ];

    /**
     * List of regex with links to remove
     * @var array
     */
    public array $aExclude = [
        '^javascript\:',
        '^mailto\:',
    ];

    // ----------------------------------------------------------------------
    // cleanup settings - when to remove content from index
    // ----------------------------------------------------------------------

    /**
     * Max count of errors for an url before it is removed from search index
     * @var integer
     */
    private int $_iMaxAllowedErrors = 5;

    /**
     * Max age of last crawling date in seconds befor it will be removed from index
     * @var integer
     */
    private int $_iMaxAllowedAgeOfLastIndex = 604800; // 1 week
    // public $_iMaxAllowedAgeOfLastIndex=86400;  // 1 day


    // ----------------------------------------------------------------------

    /**
     * Constructor
     * new crawler
     * 
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct(int $iSiteId = 0)
    {
        $this->setSiteId($iSiteId);
        $this->_aUrls2Crawl = [];
        $this->_aUrls2Skip = [];
    }


    // ----------------------------------------------------------------------
    // ACTIONS CRAWLING
    // ----------------------------------------------------------------------

    /**
     * Get disallow entries from reading robots.txt and add them to the exclude
     * rules; it follows exclude rules for 
     * - $this->aAbout['agent "*" and 
     * - $this->aAbout['product'] (what is "ahCrawler")
     * 
     * @param string $sUrl  any url ... the robots.txt fill be detected from it
     * @return boolean
     */
    private function _GetExcludesFromRobotsTxt(string $sUrl): bool
    {
        $urlRobots = preg_replace('#^(http.*//.*)/.*$#U', '$1', $sUrl) . "/robots.txt";
        $this->cliprint('info', "INFO: respect the ROBOTS.TXT: reading $urlRobots\n");
        $rollingCurl = new \RollingCurl\RollingCurl();
        $self = $this;

        $aCurlOpt = $this->_getCurlOptions();

        $rollingCurl->setOptions($aCurlOpt)
            ->get($urlRobots)
            ->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use ($self) {
                $self->addExcludesFromRobotsTxt($request->getResponseText());
            })
            ->execute()
        ;
        return true;
    }


    /**
     * Get disallow entries from reading robots.txt and add them to the exclude
     * rules; it follows exclude rules for 
     * - agent "*" and 
     * - $this->aAbout['product'] what is "ahCrawler")
     * Found disallow entries will be added to the exclude rules
     * It returns flase if no robots.txt data is found. Otherwise it returns 
     * true.
     * 
     * @param string $sContent  robots.txt content
     * @return boolean
     */
    public function addExcludesFromRobotsTxt(string $sContent): bool
    {
        $regs = [];

        $bCheck = 1;
        if (!$sContent) {
            return false;
        }
        foreach (explode("\n", $sContent) as $line) {
            // echo "DEBUG: $line\n";
            $line = preg_replace('/#.*/', '', $line);
            if (preg_match("/^user-agent: *([^#]+) */i", $line, $regs)) {
                $this_agent = trim($regs[1]);
                $bCheck = ($this_agent == '*' || strtolower($this->aAbout['product']) === strtolower($this_agent));
            }
            if ($bCheck == 1 && preg_match("/disallow: *([^#]+)/i", $line, $regs)) {
                // echo "ROBOTS:TXT: $line\n";
                $disallow_str = preg_replace("/[\n ]+/i", "", $regs[1]);
                if (trim($disallow_str) != "") {
                    // $sEntry = '.*\/\/'. $sHost . '/' . $disallow_str . '.*';
                    $sEntry = $disallow_str;
                    $sEntry = str_replace('//', '/', $sEntry);
                    // ?? $sEntry = str_replace('*\.*', '.*', $sEntry);
                    // $sEntry = str_replace('.', '\.', $sEntry);
                    foreach (['.', '?'] as $sMaskMe) {
                        $sEntry = str_replace($sMaskMe, '\\' . $sMaskMe, $sEntry);
                    }
                    $sEntry = str_replace('*', '.*', $sEntry);

                    if (!strpos($sEntry, '$')) {
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
     * Helper function for _addUrl2Crawl
     * find a matching with one of the include or exclude regex to a given 
     * string (url or path)
     * 
     * @param string   $sOptionKey  key in $this->aProfile
     * @param string   $sString     url or path
     * @param boolean  $bDefault    default return value; true|false
     * @return boolean
     */
    private function _checkRegexArray(string $sOptionKey, string $sString, $bDefault = true)
    {
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
     * Mark an url to be crawled. It returns true if it was newly added to
     * the queue; it returns false if it was added or crawled already.
     * It returns false if the urls wasn't added for rescan because
     * - it was added alredy
     * - it was marked to skip
     * - it was excluded
     * - it was not included (if include was defined)
     * - it matches exclude regex
     * - it matches an exclude extension
     * - the max count of urls to crawl was reached
     * 
     * @param string $sUrl    url
     * @param boolean $bDebug flag: print debug info; default: false
     * @return boolean
     */
    private function _addUrl2Crawl(string $sUrl, bool $bDebug = false): bool
    {
        $this->cliprint('cli', $bDebug ? __FUNCTION__ . "($sUrl)\n" : "");
        $bDebug = true;

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

        if (!$this->_checkRegexArray('_vhosts', $sUrl)) {
            $this->cliprint('info', "... SKIP outside search url(s): $sUrl\n");
            return false;
        }

        $sPath = parse_url($sUrl, PHP_URL_PATH);
        $sPath = $sPath ? $sPath : '/'; // FIX empty path and set it to "/"

        $bFound = $this->_checkRegexArray('include', $sUrl);
        if ($bFound) {
            if ($this->_checkRegexArray('includepath', $sPath)) {
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

        $sSkipExtensions = '/(\.' . implode('|\.', $this->aSkipExtensions) . ')$/i';

        if (preg_match($sSkipExtensions, $sPath)) {
            // $this->cliprint('cli', $bDebug ? "... SKIP by extension: $sUrl\n" : "");
            $this->cliprint('cli', "... SKIP by extension: $sPath from $sUrl\n");
            return false;
        }


        $curr_depth = substr_count(str_replace("//", "/", $sPath), "/");
        if ($this->aProfileEffective['searchindex']['iDepth'] && $curr_depth > $this->aProfileEffective['searchindex']['iDepth']) {
            $this->cliprint('info', $bDebug ? "... don't adding $sUrl - max depth is " . $this->aProfileEffective['searchindex']['iDepth'] . "\n" : "");
            return false;
        }

        if (!array_key_exists($sUrl, $this->_aUrls2Crawl)) {

            $iCount = count($this->_aUrls2Crawl) + 1;
            $iMax = isset($this->aProfileEffective['searchindex']['iMaxUrls']) ? (int) $this->aProfileEffective['searchindex']['iMaxUrls'] : 0;
            if (
                $iMax
                && count($this->_aUrls2Crawl) >= $this->aProfileEffective['searchindex']['iMaxUrls']
            ) {
                $this->cliprint('warning', "... SKIP: maximum of $iMax urls to crawl was reached. I do not add $sUrl\n");
                $this->_aUrls2Skip[$sUrl] = true;
                return false;
            }
            $this->cliprint('cli', "... adding #$iCount" . ($iMax ? ' of ' . $iMax : '') . ": $sUrl\n");
            $this->_aUrls2Crawl[$sUrl] = true;

            return true;
        }
        $this->cliprint('cli', $bDebug ? "... was added already." : "");
        return false;
    }

    /**
     * Mark an url that it was crawled already
     * 
     * @param string $sUrl
     * @return boolean
     */
    private function _removeUrlFromCrawling(string $sUrl): bool
    {
        // echo __FUNCTION__."($sUrl)\n";
        $this->_aUrls2Crawl[$sUrl] = false;
        return true;
    }

    /**
     * Get the urls that are known to be crawled (their count can increase
     * during crawl process by analysing links in pages)
     * @return array
     */
    private function _getUrls2Crawl(): array
    {
        // echo __FUNCTION__."()\n";
        $aReturn = [];
        foreach ($this->_aUrls2Crawl as $sUrl => $bToDo) {
            if ($bToDo > 0) {
                $aReturn[] = $sUrl;
            }
        }
        //print_r($aReturn);
        return $aReturn;
    }

    /**
     * Output helper for indexer: show count of urls, crawled urls und how 
     * many are left
     * 
     * @return string
     */
    private function _getCrawlStat(): string
    {
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
     * Do something with the response of a found url...
     * (this is the callback of rollingcurl - it must be public)
     * It returns false on http response error if mime is not text/html
     * On success it retunrs true
     * 
     * @param object $response  rolling curl response object
     * @return boolean
     */
    public function processResponse(object &$response): bool
    {
        $url = $response->getUrl();
        // list($sHttpHeader, $sHttpBody)=explode("\r\n\r\n", $response->getResponseText(), 2);
        $aTmp = explode("\r\n\r\n", $response->getResponseText(), 2);
        $aHttpHeader = $aTmp[0];

        $info = $response->getResponseInfo();
        $info['_responseheader'] = $aHttpHeader;
        $info = $response->getResponseInfo();
        $info['_responseheader'] = $aHttpHeader;
        if (!is_array($aHttpHeader) || !isset($aHttpHeader[0]) || !$aHttpHeader[0]) {
            $info['_curlerror'] = $response->getResponseError();
            if ($info['_curlerror']) {
                $info['_curlerrorcode'] = $response->getResponseErrno();
            }
            ;
        }

        unset($response);

        $oHttpstatus = new httpstatus($info);

        $this->_iUrlsCrawled++;
        $this->_removeUrlFromCrawling($url);

        // echo "DEBUG: ".__FUNCTION__."$url  - STATUS " . $oHttpstatus->getHttpcode() . "\n";
        if ($oHttpstatus->isError()) {
            $this->cliprint('error', "ERROR: fetching $url FAILED. Status: " . $oHttpstatus->getHttpcode() . " (" . $oHttpstatus->getStatus() . ").\n");
            // print_r($aTmp); sleep(5);
            return false;
        }
        if ($oHttpstatus->isRedirect()) {
            $oHtml = new analyzerHtml();
            $sNewUrl = $oHtml->getFullUrl($oHttpstatus->getRedirect(), $url);
            $this->cliprint('cli', "REDIRECT: $url " . $oHttpstatus->getHttpcode() . " - " . $oHttpstatus->getStatus() . " -> " . $sNewUrl . ".\n");
            if ($sNewUrl) {
                $this->_addUrl2Crawl($sNewUrl, true);
            }
            return true;
        }
        if ($oHttpstatus->isOK()) {
            switch ($oHttpstatus->getContenttype()) {
                case 'text/html':
                    // $this->cliprint('cli', "Analyzing html code of $url ... \n");
                    $sHttpBody = isset($aTmp[1]) ? $aTmp[1] : false;
                    $this->_processHtmlPage($sHttpBody, $info);
                    break;
                default:
                    $this->cliprint('cli', "SKIP: MIME [" . $oHttpstatus->getContenttype() . "] of $url ... \n");
                    return false;
            }
        }

        return true;
    }

    /**
     * Do something with the response of a found html page
     * 
     * @param string  $response  html response
     * @param array   $info      response header infos
     * @return boolean
     */
    private function _processHtmlPage(string $response, array $info): bool
    {
        $url = $info['url'];
        // $bIngoreNoIndex=isset($this->aProfileSaved['searchindex']['ignoreNoindex']) && $this->aProfileSaved['searchindex']['ignoreNofollow'];
        $oHtml = new analyzerHtml($response, $url);
        $bIngoreNoFollow = isset($this->aProfileSaved['searchindex']['ignoreNofollow']) && $this->aProfileSaved['searchindex']['ignoreNofollow'];
        $bIngoreNoIndex = isset($this->aProfileSaved['searchindex']['ignoreNoindex']) && $this->aProfileSaved['searchindex']['ignoreNoindex'];
        if ($bIngoreNoFollow || ($this->_bFollowLinks && $oHtml->canFollowLinks())) {
            // TODO:
            // use external links too and check domain with sticky domain array
            $aLinks = $oHtml->getLinks("internal");
            if (array_key_exists('internal', $aLinks) && is_array($aLinks['internal']) && count($aLinks['internal'])) {
                foreach ($aLinks['internal'] as $aLink) {
                    if (
                        !array_key_exists('rel', $aLink)
                        || (array_key_exists('rel', $aLink) && $aLink['rel'] != 'nofollow')
                    ) {

                        // echo basename($url) ." - internal -> ";
                        // echo ($this->_addUrl2Crawl($aLink['_url']) ? "" : "(skip) ".$aLink['_url'] ). "\n"; 
                        $this->_addUrl2Crawl($aLink['_url']);
                    }
                }
            }
        } else {
            $this->cliprint('info', "SKIP: do not following links in url $url\n");
        }
        if ($bIngoreNoIndex || $oHtml->canIndexContent()) {
            $this->_preparePageForIndex(['header' => $info, 'body' => $response]);
        } else {
            if ($oHtml->hasOtherCanonicalUrl()) {
                $sCanonicalUrl = $oHtml->getCanonicalUrl();
                $this->cliprint('info', "SKIP: do not index because canonical url was found: $sCanonicalUrl ... adding $sCanonicalUrl\n");
                $this->_addUrl2Crawl($sCanonicalUrl);
            } else {
                $this->cliprint('warning', "SKIP: a noindex flag was found (see html head section or http response header): $url\n");
            }
        }

        return true;
        // return array_keys($aUrls);
    }

    /**
     * Start crawler to index whole page or opdate last errors only
     * 
     * @param boolean  $bMissesOnly  flag: rescan pages with errors only; default: false
     * @return boolean
     */
    public function run(bool $bMissesOnly = false): bool
    {

        // TODO: UNDO
        // $bMissesOnly=false;
        $this->_iUrlsCrawled = 0;
        $this->iStartCrawl = date("U");

        // scan content and follow links
        $this->_bFollowLinks = true;
        if (!$bMissesOnly) {
            $this->logfileDelete();
        }
        $this->cliprint('info', "========== Searchindex - " . ($bMissesOnly ? 'UPDATE misssing pages' : 'REINDEX') . PHP_EOL);
        $this->cliprint('info', 'starting point: ' . __METHOD__ . PHP_EOL);
        if (!$this->enableLocking(__CLASS__, 'index', $this->iSiteId)) {
            $this->cliprint('error', "ABORT: The crawler is still running (" . __METHOD__ . ")\n");
            return false;
        }

        // ------------------------------------------------------------
        // get start urls ...
        // ------------------------------------------------------------
        $aStartUrls = [];
        if ($bMissesOnly) {
            // ... pages with error
            $this->cliprint('info', "I do a rescan of pages with error.\n");
            $this->_bFollowLinks = false;
            $aUrls = $this->oDB->select(
                'pages',
                ['url'],
                [
                    'AND' => [
                        'siteid' => $this->iSiteId,
                        'errorcount[>]' => 0,
                    ],
                ]
            );
            if (is_array($aUrls) && count($aUrls)) {
                foreach ($aUrls as $aRow) {
                    $aStartUrls[] = $aRow['url'];
                }

            }
        } else {
            // ... starturls in config
            $this->cliprint('info', "I do a rescan of ALL pages.\n");
            if (!count($this->aProfileEffective['searchindex']['urls2crawl'])) {
                $this->cliprint('warning', 'WARNING: no urls in profiles->' . $this->iSiteId . '->urls2crawl->searchindex<br>' . "\n");
            } else {
                foreach ($this->aProfileEffective['searchindex']['urls2crawl'] as $sUrl) {
                    $aStartUrls[] = $sUrl;
                }
            }
        }

        // ------------------------------------------------------------
        // remove cookies
        // ------------------------------------------------------------
        /* v0.65 disable deletion of cookies
        if (file_exists($this->sCookieFilename)){
            unlink($this->sCookieFilename);
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
        // print_r($this->oDB->select('pages', ['url', 'ts', 'siteid', 'errorcount', 'tserror']));

        $iUrls = $this->oDB->count(
            'pages', 
            ['url'], 
            [
                'AND' => [
                    'siteid' => $this->iSiteId,
                ],
            ]
        );

        $iTotal = date("U") - $this->iStartCrawl;
        $this->disableLocking();

        $this->cliprint('info', "----- Crawler has finished.\n");
        $this->cliprint('info', $this->_iUrlsCrawled . " urls were crawled\n");
        $this->cliprint('info', "process needed $iTotal sec; " . ($iTotal ? number_format($this->_iUrlsCrawled / $iTotal, 2) . " urls per sec." : '') . "\n");
        $this->cliprint('info', "STATUS of profile [" . $this->iSiteId . "] " . $this->aProfileEffective['label'] . ":\n");
        $this->cliprint('info', "$iUrls urls are in the search index now (table 'pages')\n");

        if ($iUrls < $this->_iUrlsCrawled) {
            $this->cliprint('warning', "Remark: Not all html pages were added (crawling denied or arror)\n");
        }

        return true;
    }

    /**
     * Update content of given urls in the search index 
     * (with or without following its links)
     * It returns false if an empty array of urls was given.
     * 
     * @param array   $aUrls          array of urls
     * @param boolean $bFollowLinks   flag: scan content and follow links (default: false)
     * @return boolean
     */
    public function updateMultipleUrls(array $aUrls, bool $bFollowLinks = false): bool
    {
        // echo "--- ".__FUNCTION__."([array], [$bFollowLinks]) \n";
        $sMsgId = 'crawler-' . ($bFollowLinks ? 'index' : 'update') . '-profile-' . $this->iSiteId;
        if (!count($aUrls)) {
            $this->cliprint(
                $bFollowLinks ? 'error' : 'info',
                $bFollowLinks
                ? "ERROR: list of starting urls is empty. This seems to be a misconfiguration.\n"
                : "INFO: Nothing to do. No updatetable urls with errors were found to reindex them.\n"
            )
            ;
            return false;
        }

        // scan content and follow links
        $this->_bFollowLinks = $bFollowLinks;

        $bPause = false;
        $bIsFirst = true;

        foreach ($aUrls as $sUrl) {
            if ($bIsFirst) {
                // get robots txt based on given url
                $this->_GetExcludesFromRobotsTxt($sUrl);
                $bIsFirst = false;
            }
            $this->_addUrl2Crawl($sUrl, true);
        }

        // print_r($this->aOptions); $oStatus->finishAction($sMsgId);die();
        $self = $this;
        $rollingCurl = new \RollingCurl\RollingCurl();
        $aCurlOpt = $this->_getCurlOptions();
        $aCurlOpt[CURLOPT_USERPWD] = isset($this->aProfileEffective['userpwd']) ? $this->aProfileEffective['userpwd'] : false;
        $rollingCurl
            ->setOptions($aCurlOpt)
            ->setSimultaneousLimit((int) $this->aProfileEffective['searchindex']['simultanousRequests'])
        ;
        while (count($this->_getUrls2Crawl())) {
            $aUrlsLeft = $this->_getUrls2Crawl();
            if ($bPause && $this->iSleep) {
                $this->touchLocking('sleep ' . $this->iSleep . 's');
                $this->cliprint('info', "sleep ..." . $this->iSleep . "s\n");
                sleep($this->iSleep);
            }
            $bPause = true;
            // $this->touchLocking('urls left ' . count($aUrlsLeft). ' ... ');
            // $this->cliprint('info', 'Urls left: ' . count($aUrlsLeft)."\n");
            $this->touchLocking(__METHOD__ . ' looping...');
            $this->cliprint('info', __METHOD__ . " looping...\n");

            // WIP: get data from database to get less response
            //      for already stored (cached) data
            $aNextUrls = array_flip($aUrlsLeft);
            $aHeadersOfUrls = $this->oDB->select(
                'pages',
                ['url', 'header', 'ts'],
                [
                    'AND' => [
                        'siteid' => $this->iSiteId,
                        'url' => $aUrlsLeft,
                        // 'header[~]' => 'etag: ',
                    ],
                ]
            );

            $aCurlDefaults = $this->_getCurlOptions();
            // $aCurlHttpHeader=$aCurlDefaults[CURLOPT_HTTPHEADER];
            // $aCurlHttpHeader=[];
            foreach ($aHeadersOfUrls as $aUrlrow) {
                preg_match("/etag\: ([a-z0-9-\.+_]*)/i", $aUrlrow['header'], $aEtagMatcher);
                $aCurlOptions = $aCurlDefaults;
                if (isset($aEtagMatcher[1])) {
                    $aCurlOptions[CURLOPT_HTTPHEADER][] = 'If-None-Match: "' . $aEtagMatcher[1] . '"';
                }
                // $aCurlOpt[CURLOPT_HTTPHEADER][]='Range: bytes=0-200000';
                $aCurlOptions[CURLOPT_HTTPHEADER][] = 'If-Modified-Since: ' . date("D, j M Y H:i:s T", strtotime($aUrlrow['ts']));
                $aNextUrls[$aUrlrow['url']] = $aCurlOptions;
            }
            // print_r($aNextUrls);sleep(1);

            foreach ($aUrlsLeft as $sUrl) {
                $rollingCurl->get(
                    $sUrl,
                    null,
                    (isset($aNextUrls[$sUrl]) && is_array($aNextUrls[$sUrl]) ? $aNextUrls[$sUrl] : null),
                );
            }
            $rollingCurl->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use ($self) {
                // echo $request->getResponseText();
                // echo "... content: " . substr($request->getResponseText(), 0 ,10) . " (...) \n";
                $self->processResponse($request);
                $self->touchLocking('GET ' . $request->getUrl());
                $rollingCurl->clearCompleted();
            })
                ->execute()
            ;
            $rollingCurl->prunePendingRequestQueue();
            $this->cliprint('info', "prunePendingRequestQueue was done.\n");

        }


        $this->touchLocking('update index and keywords');
        $this->updateIndexAndKeywords();

        return true;
    }

    /**
     * Update content of a single url in the search index 
     * (without following its links)
     * 
     * @param string $sUrl url to update in the index
     * @return boolean
     */
    public function updateSingleUrl(string $sUrl): bool
    {
        return $this->updateMultipleUrls([$sUrl]);
    }

    /**
     * Delete a single item from search index
     * @param int $sId
     * @return mixed
     */
    public function deleteFromIndex(int $sId): mixed
    {
        $aResult = $this->oDB->delete('pages', [
            'AND' => [
                'siteid' => $this->iSiteId,
                'id' => $sId,
            ],
        ]);
        $this->_checkDbResult($aResult);
        return $aResult;
    }

    /**
     * Get latest record from table "pages" (search index)
     * 
     * @param array $aFilter optional: filter in Meedoo where syntax (unused yet) 
     * @return string|null
     */
    public function getLastRecord(array $aFilter = []): string|null
    {
        return $this->getLastTsRecord("pages", $aFilter ?: ['siteid' => $this->iSiteId]);
    }

    /**
     * Get record count from table "pages" (search index)
     * 
     * @param array $aFilter optional: filter in Meedoo where syntax
     * @return string|null
     */
    public function getCount(array $aFilter = []): string|null
    {
        // return $this->getRecordCount("pages", $aFilter ? $aFilter : array('siteid' => $this->iSiteId));
        return $this->getRecordCount("pages", $aFilter ?: ['siteid' => $this->iSiteId]);
    }

    // ----------------------------------------------------------------------
    // ACTIONS INDEXING
    // ----------------------------------------------------------------------

    /**
     * Helper function: get a value from html header
     * called in _preparePageForIndex()
     * 
     * @param string $sHtml  html code of a document (response body)
     * @param string $sItem  head item to fetch
     * @return mixed
     */
    private function _getMetaHead(string $sHtml, string $sItem): mixed
    {
        preg_match("@<head[^>]*>(.*?)<\/head>@si", $sHtml, $regs);
        if (!is_array($regs) || count($regs) < 2) {
            return false;
        }
        $headdata = $regs[1];

        $res = [];
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
     * Index an html page
     * 
     * @param array  $aPage  array with keys "header" and "body"
     * @return boolean
     */
    private function _preparePageForIndex(array $aPage): bool
    {
        // print_r($aPage['header']);
        $url = $aPage['header']['url'];
        if ($aPage['header']['http_code'] >= '400') {
            $this->cliprint('error', "ERROR: http code " . $aPage['header']['http_code'] . "\n" . print_r($aPage['header'], 1));
            if ($aPage['header']['http_code'] < 500) {
                $this->cliprint('error', " - increasing error counter: $url<br>\n");
                $this->_markAsErrorInSearchIndex($url, $aPage['header']);
            } else {
                $this->cliprint('info', " - NO UPDATE: $url<br>\n");
            }
            // echo "\n";sleep(5);
            return false;
        }

        $bIngoreNoIndex = isset($this->aProfileSaved['searchindex']['ignoreNoindex']) && $this->aProfileSaved['searchindex']['ignoreNoindex'];
        if ($bIngoreNoIndex) {
            $this->cliprint('info', "Ignoring Noindex for url $url\n");
        } else {
            // X-Robots-Tag in http response header
            // see https://developers.google.com/search/reference/robots_meta_tag
            // to test: $aPage['header']['X-Robots-Tag']='none';
            $sRobotsX = (isset($aPage['header']['X-Robots-Tag']) ? $aPage['header']['X-Robots-Tag'] : '');
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

        $sRobots = $this->_getMetaHead($sContent, 'robots');

        if (!$bIngoreNoIndex && $sRobots && (strpos($sRobots, 'noindex') === 0 || strpos($sRobots, 'noindex') > 0)) {
            $this->cliprint('info', "Skip: meta robots $sRobots for url $url\n");
            return false;
        }

        $sTitle = $this->_getMetaHead($sContent, 'title');
        $sDescr = $this->_getMetaHead($sContent, 'description');
        $sKeywords = $this->_getMetaHead($sContent, 'keywords');

        // get lang from <html lang=...>
        preg_match("@\<html.*\ lang=[\"\'](.*)[\"\']@iU", $sContent, $aTmp);
        $sLang = isset($aTmp[1]) ? $aTmp[1] : '';

        // print_r($this->aProfileEffective['searchindex']['regexToRemove']); echo count($this->aProfileEffective['searchindex']['regexToRemove']); die();
        if (!strlen($sContent)) {
            $this->cliprint('warning', "WARNING: content is EMPTY for url [$url]?!\n");
        } else if (
            isset($this->aProfileEffective['searchindex']['regexToRemove'])
            && is_array($this->aProfileEffective['searchindex']['regexToRemove'])
            && count($this->aProfileEffective['searchindex']['regexToRemove']) > 0
        ) {
            foreach ($this->aProfileEffective['searchindex']['regexToRemove'] as $sRegex) {
                if ($sRegex) {
                    try {
                        $sContent = preg_replace("@" . $sRegex . "@si", " ", $sContent);
                        if (!strlen($sContent)) {
                            $this->cliprint('warning', "WARNING: content is EMPTY after applying regex [$sRegex] on $url\n");
                        }
                    } catch (Exception $e) {
                        $this->logAdd(__METHOD__ . '() - regex [' . $sRegex . '] seems to be wrong. ', $e);
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
            [
                'url' => $url,
                'title' => $sTitle,
                'description' => $sDescr,
                'keywords' => $sKeywords,
                'content' => $sContent,
                'lang' => $sLang,
                'size' => $aPage['header']['size_download'], // is byte
                'time' => $aPage['header']['total_time'] ? (int) ($aPage['header']['total_time'] * 1000) : -1, // is sec as float value
                'header' => $aPage['header'],
                'response' => $aPage['body'],
            ]
        );
        return true;
    }

    /**
     * Generate page id by given url (plus siteID)
     * 
     * @param string $sUrl
     * @return null|int
     */
    private function _getPageId(string $sUrl): null|int
    {
        $aCurrent = $this->oDB->select(
            'pages',
            ['id'],
            [
                'url' => $sUrl,
                'siteid' => $this->iSiteId
            ]
        );
        return $aCurrent[0]['id'] ?? null;
        // return count($aCurrent) ? $aCurrent[0]['id'] : false;
        // return $this->iSiteId ? md5($sUrl . $this->iSiteId) : false;
    }

    /**
     * Summary of _getWordCount
     * @param mixed $s
     * @return int
     */
    protected function _getWordCount($s)
    {
        $characterMap = 'À..ÿ'; // chars #192 .. #255
        return count(str_word_count(
            str_replace("'", '', $s),
            2,
            $characterMap
        ));
    }
    /**
     * add / update page data in search index
     * @param array $aData
     * @return boolean|PDOStatement
     */
    private function _addToSearchIndex(array $aData): bool|PDOStatement
    {
        if (!$this->iSiteId) {
            $this->cliprint('info', "WARNING: you need to set the siteId first.\n");
            return false;
        }

        // $this->cliprint('debug', __METHOD__ . " ".$aData['header']['http_code']. ' ' . $aData['url']."\n");

        // $iPageId=$this->_getPageId($aData['url']);

        $sFieldToCompare = 'response';
        $aCurrent = $this->oDB->select('pages', ['id', $sFieldToCompare, 'errorcount'], [
            'url' => $aData['url'],
            'siteid' => $this->iSiteId,
        ]);

        // fix charset 4 studmed (on iso-8859-1) .. TODO: check on UTF8 web
        foreach (['title', 'description', 'keywords', 'content', 'response'] as $sKey) {
            $aData[$sKey] = mb_convert_encoding($aData[$sKey], "UTF-8");
        }


        if (count($aCurrent) /* && $aCurrent[0][$sFieldToCompare] */) {
            // $this->cliprint('debug', __METHOD__ . " ".strlen($aCurrent[0][$sFieldToCompare]). ' bytes - in database ' . strlen($aData[$sFieldToCompare])." bytes\n");
            if ($aCurrent[0][$sFieldToCompare] == $aData[$sFieldToCompare]) {
                $this->cliprint('cli', 'NO CHANGE ' . $aData['url'] . "\n");
            } else {
                $this->cliprint('cli', 'UPDATE CONTENT for ' . $aData['url'] . "\n");
            }
            // echo ' ('.$aCurrent[0]['errorcount'] . ' errors) ';
            $aResult = $this->oDB->update(
                'pages',
                [
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
                ],
                [
                    // 'id' => $aCurrent[0]['id'],
                    'url' => $aData['url'],
                    'siteid' => $this->iSiteId,
                ]
            );

        } else {
            $this->cliprint('info', 'INSERT data for ' . $aData['url'] . "\n");
            // echo "  title: " . $aData['title'] . "\n";
            $aResult = $this->oDB->insert(
                'pages',
                [
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
                ]
            );
        }
        if (isset($aResult)) {
            $this->_checkDbResult($aResult);

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
     * Mark an url in search index that it has an error; it increases
     * the error counter.
     * It returns false if the siteId is not set
     * 
     * @param string  $sUrl
     * @param array   $aHeader  http response header (=last error)
     * @return null|PDOStatement
     */
    private function _markAsErrorInSearchIndex(string $sUrl, array $aHeader = []): ?PDOStatement
    {
        if (!$this->iSiteId) {
            $this->cliprint('warning', "WARNING: you need to set the siteId first.");
            return null;
        }
        $aResult = $this->oDB->update(
            'pages',
            [
                'errorcount[+]' => 1,
                'tserror' => date("Y-m-d H:i:s"),
                'lasterror' => json_encode($aHeader)
            ],
            ['id' => $this->_getPageId($sUrl)]
        );
        $this->_checkDbResult($aResult);
        return $aResult;
    }

    /**
     * cleanup search index: remove old pages and entries with error count > N
     * @return boolean
     */
    private function _cleanupSearchIndex():bool
    {
        $aResult = $this->oDB->delete('pages', [
            'AND' => [
                'siteid' => $this->iSiteId,
                'OR' => [
                    'errorcount[>]' => $this->_iMaxAllowedErrors,
                    'ts[<]' => date("Y-m-d H:i:s", (date("U") - $this->_iMaxAllowedAgeOfLastIndex)),
                ]
            ],
        ]);
        $this->_checkDbResult($aResult);

        return true;
    }

    /**
     * Read title, description, keywords and content from database of current 
     * site, count all words in them and store them in table "words"
     * 
     * @return PDOStatement|null
     */
    public function updateIndexAndKeywords(): PDOStatement|null
    {
        if (!$this->iSiteId) {
            return null;
        }
        $characterMap = 'À..ÿ'; // chars #192 .. #255
        $this->cliprint('cli', "BUILD INDEX ... finding words ");
        $aWords = [];
        $aResult = $this->oDB->select(
            'pages',
            ['title', 'description', 'keywords', 'content'],
            [
                'AND' => [
                    'siteid' => $this->iSiteId,
                    'errorcount' => 0,
                ]
            ]
        );
        $this->_checkDbResult($aResult);
        $this->cliprint('cli', "... and count ");
        foreach ($aResult as $aRow) {
            // print_r($aRow);
            foreach (str_word_count(str_replace("'", '', $aRow['description'] . ' ' . $aRow['title'] . ' ' . $aRow['keywords'] . ' ' . $aRow['content']), 2, $characterMap) as $sWord) {
                // $sWord= str_replace("'", '', $sWord);

                // strtolower destroyes umlauts
                // $sKey=strtolower($sWord);
                // $sKey=function_exists('mb_strtolower') ? mb_strtolower($sWord) : $sWord;
                $sKey = $sWord;
                if (strlen($sKey) > 2) {
                    if (!array_key_exists($sKey, $aWords)) {
                        $aWords[$sKey] = 1;
                    } else {
                        $aWords[$sKey]++;
                    }
                }
            }
        }
        $this->cliprint('cli', "... and sort ");
        arsort($aWords);
        // print_r($aWords);


        $aResult = $this->oDB->delete('words', ['siteid' => $this->iSiteId]);
        $this->_checkDbResult($aResult);

        $this->cliprint('cli', "... and insert " . count($aWords) . " words to siteid " . $this->iSiteId);
        $aInsertdata = [];
        $iCounter = 0;
        foreach ($aWords as $sWord => $iCount) {
            $iCounter++;
            $aInsertdata[] = ['word' => $sWord, 'count' => $iCount, 'siteid' => $this->iSiteId];
            if ($iCounter > 99 || $iCounter == count($aWords)) {
                $aResult = $this->oDB->insert('words', $aInsertdata);
                // $this->cliprint('cli', ".");
                // echo "\n" . $this->oDB->last_query() . "<br>\n";
                $this->_checkDbResult($aResult);
                $aInsertdata = [];
                $iCounter = 0;
            }
        }
        $this->cliprint('cli', "\n");
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
