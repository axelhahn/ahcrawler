<?php

require_once 'crawler-base.class.php';
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
 * RESSOURCES
 * 
 * usage:
 * require_once("../ressources.class.php");
 * 
 * */
class ressources extends crawler_base {

    /**
     * ids of already added ressources
     * @var type 
     */
    protected $_aRessourceIDs = array();
    protected $_aUrls2Crawl = array();
    private $iStartCrawl = 0;
    private $iSleep = 0;

    /**
     * number of crawled urls
     * @var integer
     */
    protected $_iUrlsCrawled = 0;

    // ----------------------------------------------------------------------
    /**
     * new crawler
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct($iSiteId = false) {
        $this->setSiteId($iSiteId);
        // $this->setLangFrontend();
        return true;
    }

    // ----------------------------------------------------------------------
    // ACTIONS CRAWLING
    // ----------------------------------------------------------------------

    /**
     * get type of an url (internal/ external)
     * @param string  $sHref  url to check
     * @return string
     */
    private function _getUrlType($sHref) {
        $sFirstUrl = (array_key_exists('urls2crawl', $this->aProfileEffective['searchindex']) && count($this->aProfileEffective['searchindex']['urls2crawl'])) ? $this->aProfileEffective['searchindex']['urls2crawl'][0] : false;
        if ($sFirstUrl) {
            $oHtml = new analyzerHtml('', $sFirstUrl);
            return $oHtml->getUrlType($sHref);
        }
        return 'external';
    }

    /**
     * get ressource id by given url 
     * @param string  $sUrl  url of ressource
     * @return string
     */
    private function _getRessourceId($sUrl) {
        // return $this->iSiteId ? md5($sUrl . $this->iSiteId) : false;
        $aCurrent = $this->oDB->select(
                'ressources', array('id'), array(
            'url' => $sUrl,
            'siteid' => $this->iSiteId
                )
        );
        return count($aCurrent) ? $aCurrent[0]['id'] : false;
    }

    /**
     * cleanup ressource table before newly fill in the data
     * @return type
     */
    public function cleanupRessources() {
        if (!(int)$this->iSiteId) {
            return false;
        }
        $this->cliprint('info', "========== Resources cleanup".PHP_EOL);
        $this->cliprint('info', 'starting point: '. __METHOD__.PHP_EOL);
        if (!$this->enableLocking(__CLASS__, 'index', $this->iSiteId)) {
            $this->cliprint('error', "ABORT: The crawler is still running (".__METHOD__.")\n");
            return false;
        }
        $this->cliprint('info', "Flushing resources...\n");
        $this->flushData(array('ressources'=>1), $this->iSiteId);
        $this->_aRessourceIDs = array();
        $this->disableLocking();

        $this->cliprint('info', "----- Cleanup of resources is done<br>\n");
        $this->_showResourceStatusOnCli();
    }

    /**
     * create an array for ressource table; it fills missing keys with
     * values
     * @param array    $a                  responsre item of a ressource
     * @param boolean  $bSkipMissingKeys   return missing keys - set true if updating items
     * 
     * @return type
     */
    protected function _sanitizeRessourceArray($a, $bSkipMissingKeys = false) {
        $aReturn = array();

        $aResDefaults = array(
            // 'id' => $this->_getRessourceId($a['url']),
            'siteid' => $this->iSiteId,
            'url' => false,
            'ressourcetype' => false,
            'type' => false,
            'header' => false,
            'content_type' => false,
            'isSource' => 0,
            'isLink' => 0,
            'isEndpoint' => 0,
            'isExternalRedirect' => NULL,
            'http_code' => NULL,
            'status' => NULL,
            'total_time' => false,
            'size_download' => false,
            'rescan' => 1,
            'ts' => false,
            'tsok' => false,
            'tserror' => false,
            'errorcount' => false,
            'lasterror' => false,
        );

        // remove url hash
        $a['url'] = preg_replace('/#.*/', '', $a['url']);
        $a['url'] = str_replace(' ', '%20', $a['url']);

        $a['type'] = $this->_getUrltype($a['url']);

        $sHeaderJson = (array_key_exists('header', $a) ? $a['header'] :
                (array_key_exists('lasterror', $a) ? $a['lasterror'] : '[]')
                );
        if ($sHeaderJson) {

            $a['content_type'] = str_replace(array('"', ' '), array('', ''), strtolower(''.$this->_getHeaderVarFromJson($sHeaderJson, 'content_type')));
            $a['http_code'] = (is_int($this->_getHeaderVarFromJson($sHeaderJson, 'http_code')) ? $this->_getHeaderVarFromJson($sHeaderJson, 'http_code') : -1 );
            $a['total_time'] = $this->_getHeaderVarFromJson($sHeaderJson, 'total_time') ? (int) ($this->_getHeaderVarFromJson($sHeaderJson, 'total_time') * 1000) / 1 : false;
            $a['size_download'] = (int) $this->_getHeaderVarFromJson($sHeaderJson, 'size_download') / 1;
            // $aData[''] = $this->_getHeaderVarFromJson($aData['header'], '');
        }
        $oHttpstatus = new httpstatus(isset($a['http_code']) ? $a['http_code'] : 0);
        $a['status'] = $oHttpstatus->getStatus();

        foreach (array_keys($aResDefaults) as $sKey) {
            if (array_key_exists($sKey, $a) || !$bSkipMissingKeys) {
                $aReturn[$sKey] = array_key_exists($sKey, $a) ? $a[$sKey] : $aResDefaults[$sKey];
            }
        }
        return $aReturn;
    }

    /**
     * insert or update an url object into ressource table
     * @param type $aData
     */
    public function addOrUpdateRessource($aData, $bSkipIfExist = false) {

        // get id if it should exist

        $sUrl=$aData['url'];

        $bExists = (isset($this->_aRessourceIDs[$sUrl])
                || isset($this->_aUrls2Crawl[$sUrl])
                );

        // if (count($aCurrent) && $aCurrent[0]['id']) {
        if ($bExists) {
            if ($bSkipIfExist) {
                // v 0.139 skip displaying this
                $this->cliprint('cli', 'SKIP resource ' . $sUrl . " (exists)\n");
            } else {
                $this->cliprint('info', 'UPDATE existing resource ' . $sUrl . "\n");
                /*
                  $aCurrent = $this->oDB->select('ressources', array('id'), array(
                  'url' => $aData['url'],
                  ));
                 * 
                 */
                $aResult = $this->oDB->update('ressources', $aData, array(
                    'AND' => array(
                        'url' => $sUrl,
                        'siteid' => $this->iSiteId
                        )
                    )
                );
                $this->_checkDbResult($aResult);
            }
        } else {
            $this->cliprint('info', 'INSERT new resource ' . $aData['url'] . "\n");
            $aResult = $this->oDB->insert('ressources', $aData);
            $this->_checkDbResult($aResult);
            $this->_aRessourceIDs[$sUrl] = true;
        }
    }

    /**
     * insert or update an url object into ressource table
     * @param type $aData
     */
    public function updateRessource($aData) {

        // echo 'UPDATE ressource ' . $aData['url'] . "\n";
        // echo print_r($aData) . "\n";
        $aResult = $this->oDB->update('ressources', $aData, array(
            'AND' => array(
                'url' => $aData['url'],
                'siteid' => $this->iSiteId
                )
            )
        );
        $this->_checkDbResult($aResult);
        // echo $this->oDB->last() . "\n\n";
        return $aResult;
    }

    public function updateExternalRedirect() {

        // reset all
        $aResult = $this->oDB->update('ressources', array('isExternalRedirect' => false), array(
            'siteid' => $this->iSiteId,
                )
        );

        // set 
        $aResult = $this->oDB->update('ressources', array('isExternalRedirect' => true), array(
            'isSource' => 0,
            'isLink' => 0,
            'isEndpoint' => 0,
            'siteid' => $this->iSiteId,
                )
        );
        return true;
    }

    /**
     * add a relation of a url object with a linked or required url
     * @param  string  $sSourceId  
     * @param  string  $sTargetId
     * @return type
      public function addOrUpdateRessourcesRel($sSourceId, $sTargetId) {
      $aCurrent = $this->oDB->select('ressources_rel', array('id'), array(
      'id_ressource' => $sSourceId,
      'id_ressource_to' => $sTargetId
      ));
      if (!count($aCurrent)) {
      echo 'INSERT ressources_rel for ' . $sSourceId . ' to ' . $sTargetId . "\n";
      $aResult = $this->oDB->insert('ressources_rel', array(
      'id' => $sSourceId . '_' . $sTargetId,
      'siteid' => $this->iSiteId,
      'id_ressource' => $sSourceId,
      'id_ressource_to' => $sTargetId
      )
      );

      // echo $this->oDB->last() . "<br>\n";
      } else {
      echo 'SKIP ressources_rel for ' . $sSourceId . ' to ' . $sTargetId . " (already exists)\n";
      }
      return $aResult;
      }
     */
    public function addRelRessourcesOfAPage($aData) {
        $iLen = strlen($aData['response']);
        $this->cliprint('cli', "RESPONSE: $iLen byte - " . $aData['url'] . "<br>\n");
        if ($iLen) {
            $this->cliprint('cli', "----- relitems for " . $aData['url'] . "<br>\n");
            $oHtml = new analyzerHtml($aData['response'], $aData['url']);
            $this->addPageRelItems($aData, $oHtml->getCss());
            $this->addPageRelItems($aData, $oHtml->getImages());
            $this->addPageRelItems($aData, $oHtml->getMedia());
            $this->addPageRelItems($aData, $oHtml->getLinks(false,false));
            $this->addPageRelItems($aData, $oHtml->getScripts());
        }
    }

    /*
      Array
      (
      [url] => http://www.medizinischelehre.unibe.ch/unibe/css/styles.min.css
      [siteid] => 4
      [ressourcetype] => css
      [href] => /unibe/css/styles.min.css
      [_url] => http://www.medizinischelehre.unibe.ch/unibe/css/styles.min.css
      [media] => all
      )
     */

    /**
     * prepare a rel item: insert into ressources and return an array that
     * can be added into table ressources_rel
     * 
     * @param  array   $aRelItem   related item (requires key "url")
     * @param  integer $sSourceId  id of the ressource that points to $aRelitem
     */
    private function _prepareRelitem($aRelItem, $sSourceId) {
        if (!array_key_exists('url', $aRelItem)) {
            $this->cliprint('error', "ERROR: missing url key in " . print_r($aRelItem, 1) . "\n");
            return false;
        }
        $aRelItem['siteid'] = $this->iSiteId;
        $aRessource = $this->_sanitizeRessourceArray($aRelItem);
        $this->addOrUpdateRessource($aRessource, true);
        $sRelId = $this->_getRessourceId($aRessource['url']);
        return array(
            'siteid' => $this->iSiteId,
            'id_ressource' => $sSourceId,
            'id_ressource_to' => $sRelId
        );
    }

    /**
     * add found ressources of a source page 
     * (this method is used for page in the searchindex only)
     * 
     * @param array  $aData     source item
     * @param array  $aRelData  array of ressource items
     */
    public function addPageRelItems($aData, $aRelData) {
        $this->touchLocking(__FUNCTION__."\n");
        // $sSourceId = $this->_getRessourceId($aData['_url']);
        $sSourceId = $this->_getRessourceId($aData['url']);
        $aGroups = array(
            'internal',
            'external',
                // 'data',
                // 'mailto',
        );
        unset($aData['content']);
        unset($aData['response']);
        // $sOut.=$sSourceId . " - " . $aData['url'] . "\n" . print_r($aRelData); sleep (10);
        if (is_array($aRelData) && count($aRelData)) {
            foreach ($aRelData as $sGroup => $aItems) {
                if (is_array($aItems) && count($aItems) && array_search($sGroup, $aGroups) !== false) {
                    $this->cliprint('cli', "--- addPageRelItems ... [" . $aData['url'] . "] .  found " . count($aItems) . " items of group $sGroup<br>\n");
                    $aRel = array();
                    foreach ($aItems as $aItem) {
                        if (array_key_exists('_url', $aItem) && $aItem['_url'] !== $aData['url']) {

                            // $this->_aRessourceIDs
                            // add the found ressource
                            $aRelItem = array_merge(array(
                                'url' => $aItem['_url'],
                                'siteid' => $this->iSiteId,
                                'isLink' => true,
                                    // 'rescan' => 1,
                                    ), $aItem);
                            // create a single target entry in "ressources" 
                            $aRel[] = $this->_prepareRelitem($aRelItem, $sSourceId);
                        }
                    }
                    // $sOut.="adding ".count($aRel). " relations.<br>\n" . print_r($aRel);
                    $aResult = $this->oDB->insert('ressources_rel', $aRel);
                    $this->_checkDbResult($aResult);
                } else {
                    // $sOut .= "--- skip " . (is_array($aItems) ? count($aItems) : 0 ). " items of group $sGroup<br>\n";
                }
            }
        }
        return true;
    }

    /**
     * read crawled data from database and add it to ressources
     * @return boolean
     */
    public function addRessourcesFromPages() {
        $iMaxRowsPerInsert=25;
        $this->cliprint('info', "========== Add searchindex items\n");
        $this->cliprint('info', 'starting point: '. __METHOD__.PHP_EOL);
        if (!$this->enableLocking(__CLASS__, 'index', $this->iSiteId)) {
            $this->cliprint('error', "ABORT: The crawler is still running (".__METHOD__.")\n");
            return false;
        }
        if (!$this->iSiteId) {
            return false;
        }
        $this->cliprint('info', "INFO: reading pages ...\n");
        $aResult = $this->oDB->select(
                'pages', '*', array(
            'AND' => array(
                'siteid' => $this->iSiteId,
            ),
                )
        );

        if (is_array($aResult) && count($aResult)) {
            $this->cliprint('cli', "INFO: insert " . count($aResult) . " already crawled pages as resource<br>\n");
            // sleep(2);
            $aPages = array();
            $iRow=0;
            foreach ($aResult as $aData) {
                $aData['rescan'] = 0;
                $aData['ressourcetype'] = 'page';
                $aData['isSource'] = true;
                $aData['isLink'] = true;
                $aData['isEndpoint'] = true;
                $aData['tsok'] = $aData['tserror'] ? false : $aData['ts'];
                // $aRessource = $this->_sanitizeRessourceArray($aData);
                // $this->addOrUpdateRessource($aRessource);
                $aPages[] = $this->_sanitizeRessourceArray($aData);
                $this->_aRessourceIDs[$aData['url']] = true;
                $iRow++;
                if(count($aPages)==$iMaxRowsPerInsert || $iRow>=count($aResult)){
                    $this->cliprint('cli', "INFO: reached row $iRow .. insert ".count($aPages)." rows...\n");
                    $this->oDB->insert('ressources', $aPages);
                    $this->_checkDbResult($aResult);
                    $aPages=array();
                }
            }

            $this->cliprint('cli', "INFO ... adding relitems of already crawled pages<br>\n");
            foreach ($aResult as $aData) {
                unset($aData['id']);
                $this->addRelRessourcesOfAPage($aData);
            }
            $this->cliprint('cli', "DONE ... adding relitems.<br>\n");
        }
        $this->disableLocking();

        $this->cliprint('info', "----- Adding pages and their linked urls is finished.\n");
        $this->_showResourceStatusOnCli();
    }

    protected function _showResourceStatusOnCli() {
        $iUrls = $this->oDB->count('ressources', array('url'), array(
            'AND' => array(
                'siteid' => $this->iSiteId,
            ),));
        $this->cliprint('info', "STATUS of profile [".$this->iSiteId."] " . $this->aProfileEffective['label'].":\n");
        $this->cliprint('info', "$iUrls resource urls (links, images, css, ...) are in the index now (table 'ressources')\n");
        return true;
    }
    // ----------------------------------------------------------------------
    // get resource details for reporting
    // ----------------------------------------------------------------------


    public function getLastRecord($aFilter = array()) {
        return $this->getLastTsRecord("ressources", $aFilter ? $aFilter : array('siteid' => $this->iSiteId));
    }

    public function getCount($aFilter = array()) {
        return $this->getRecordCount("ressources", $aFilter ? $aFilter : array('siteid' => $this->iSiteId));
    }

    /**
     * get ressources 
     * 
     * @param string/ array  $aFields  fiellist of colums to get; '*' or array with column names
     * @param array          $aWhere   array for filtering columns
     * @param array          $aOrder   sort infos
     * @return array
     */
    public function getRessources($aFields = '*', $aWhere = array(), $aOrder = array("url" => "asc")) {
        $aReturn = $this->oDB->select(
                'ressources', $aFields, array(
            'AND' => $aWhere,
            'ORDER' => $aOrder,
                )
        );
        // echo "DEBUG ".$this->oDB->last()."<br>";
        return $aReturn;
    }

    /**
     * get ressource details by given ressource id
     * @param integer  $iId  ressource id
     * @return array
     */
    public function getRessourceDetails($iId) {
        return $this->oDB->select(
                        'ressources', '*', array(
                    'AND' => array(
                        'id' => $iId,
                    ),
                        )
        );
    }

    /**
     * get ressource details by given url
     * 
     * @param stringg $sUrl  url of a resource
     * @return array
     */
    public function getRessourceDetailsByUrl($sUrl, $bUseLike = false) {
        $aData = $this->getRessources(
                '*', array(
            'url' . ($bUseLike ? '[~]' : '' ) => $sUrl,
            'siteid' .((int)$this->iSiteId>0 ? '' : '[>]') => ((int)$this->iSiteId>0 ? $this->iSiteId : '0'),
                ), array('url' => 'ASC')
        );
        // echo $this->oDB->last()."\n"; 

        if ($aData && is_array($aData) && count($aData)) {
            return $aData;
        }
        return false;
    }

    /**
     * get ressource details of all incoming or outgoing ressources related to 
     * a given ressource id
     * @param integer  $iRessourceId  ressource id
     * @param string   $sDirection    direction; one of (in|out)
     * @return array
     */
    private function _getRessourceDetailsRelated($iRessourceId, $sDirection) {
        $iId = (int) $iRessourceId;
        if (!$iId) {
            return false;
        }
        switch ($sDirection) {
            case 'in':
                $sSearchRow = 'id_ressource_to';
                $sRelRow = 'id_ressource';
                break;
            case 'out':
                $sSearchRow = 'id_ressource';
                $sRelRow = 'id_ressource_to';
                break;

            default:
                return false;
        }
        $aReturn = $this->oDB->select(
                'ressources', array(
            '[>]ressources_rel' => array('id' => $sRelRow)
                ), '*', array(
            'AND' => array(
                'ressources_rel.' . $sSearchRow => $iId,
            ),
            'ORDER' => array('url' => 'ASC')
                )
        );
        /*
          echo "SQL: ".$this->oDB->last()."<br>\n"
          // . "<pre>".print_r($aReturn, 1).'</pre>'
          ;
         */

        return $aReturn;
    }

    /**
     * get ressource details of all ressources that point to a given
     * ressource id
     * @param integer  $iRessourceId  ressource id
     * @return array
     */
    public function getRessourceDetailsIncoming($iRessourceId) {
        return $this->_getRessourceDetailsRelated($iRessourceId, 'in');
    }

    /**
     * get ressource details of all outgoing ressources of a given ressource id
     * @param integer  $iRessourceId  ressource id
     * @return array
     */
    public function getRessourceDetailsOutgoing($iRessourceId) {
        return $this->_getRessourceDetailsRelated($iRessourceId, 'out');
    }

    // ----------------------------------------------------------------------
    // crawling
    // ----------------------------------------------------------------------

    /**
     * get string of first matching regex the given url matches in the deny
     * list ... or false
     * @param string  $sUrl  url to analyze
     * @return boolean
     */
    public function isInDenyList($sUrl){
        // profile settings if siteid N are in $this->aProfileSaved
        if(isset($this->aProfileSaved['ressources']['blacklist']) && count($this->aProfileSaved['ressources']['blacklist'])){
            foreach ($this->aProfileSaved['ressources']['blacklist'] as $sDenyitem){
                try {
                    /*
                    if (strpos($sUrl, $sBlackitem)!==false){
                        return true;
                    }
                     */
                    $sMyRegex='#'.$sDenyitem.'#';
                    if (@preg_match($sMyRegex, $sUrl)){
                        return $sDenyitem;
                    }
                } catch (Exception $exc) {
                    // nop
                }
            }
        }        
        return false;
    }
    /**
     * mark an url to be crawled. It returns true if it was newly added to
     * the queue; it returns false if it was added or crawled already.
     * @param string $sUrl  url
     * @return boolean
     */
    private function _addUrl2Crawl($sUrl, $bDebug = false) {
        // $this->cliprint('cli', $bDebug ? __FUNCTION__ . "($sUrl)\n" : "");

        // remove url hash
        $sUrl = preg_replace('/#.*/', '', $sUrl);
        // ... and spaces
        $sUrl = str_replace(' ', '%20', $sUrl);

        // check deny klist
        $sMatchingRegex=$this->isInDenyList($sUrl);
        if($sMatchingRegex){
            $this->cliprint('warning', $bDebug ? "... don't adding $sUrl - it matches deny list regex [$sMatchingRegex]\n" : "");
            // sleep(3);
            return false;
        }
        if (array_key_exists($sUrl, $this->_aUrls2Crawl)) {
            // $this->cliprint('cli', $bDebug ? "... don't adding $sUrl - it was added already\n" : "");
            return false;
        } else {
            // $this->cliprint('cli', "... adding $sUrl\n");
            $this->_aUrls2Crawl[$sUrl] = true;

            return true;
        }
    }

    /**
     * get the urls that are known to be crawled (their count can increase
     * during crawl process by analysing links in pages)
     * @return array
     */
    private function _getUrls2Crawl() {
        // echo __FUNCTION__."()\n";
        $aReturn = array();
        foreach ($this->_aUrls2Crawl as $sUrl => $bToDo) {
            if ($bToDo) {
                $aReturn[] = $sUrl;
            }
        }
        //print_r($aReturn);
        return $aReturn;
    }

    /**
     * mark an url that it was crawled already
     * @param string $sUrl
     * @return boolean
     */
    private function _removeUrlFromCrawling($sUrl) {
        // echo __FUNCTION__."($sUrl)\n";
        // FIX for PHP8: do not return the variable setting
        if (isset($this->_aUrls2Crawl[$sUrl]) && $this->_aUrls2Crawl[$sUrl]) {
            $this->_aUrls2Crawl[$sUrl] = 0;
        }
        return true;
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
        $aHttpHeader=explode("\r\n\r\n", $response->getResponseText(), 1);
        
        $info = $response->getResponseInfo();
        $info['_responseheader'] = $aHttpHeader;
        if(!is_array($aHttpHeader) || !isset($aHttpHeader[0]) || !$aHttpHeader[0] ){
            $info['_curlerror']=$response->getResponseError();
            if ($info['_curlerror']) {
                $info['_curlerrorcode']=$response->getResponseErrno();
            };
        }

        $oHttpstatus = new httpstatus($info);

        $this->_iUrlsCrawled++;
        $this->_removeUrlFromCrawling($url);

        $sType = $oHttpstatus->getContenttype();
        /*
          if ($info['http_code'] === 0) {
          echo "ERROR: fetching $url FAILED. There is no connection, it was refused or or there is a problem with SSL.\n";
          }
         */

        // print_r($rollingCurl); die();
        // print_r($response); 
        // print_r($info); 

        $iId = $this->_getRessourceId($url);

        if ($oHttpstatus->isError()) {
            $this->cliprint('error', "ERROR: " . $oHttpstatus->getHttpcode() . " $url\n");
            $aRelItem = array(
                'id' => $iId,
                'url' => $url,
                'isEndpoint' => true,
                // 'header' => json_encode($info),
                // 'rescan' => 0,
                // v0.115: remove to add later (see below) 'errorcount[+]' => 1,
                'ts' => date("Y-m-d H:i:s"),
                'tserror' => date("Y-m-d H:i:s"),
                'lasterror' => json_encode($info),
            );
        }
        if ($oHttpstatus->isRedirect()) {
            $oHtml=new analyzerHtml();
            $sNewUrl=$oHtml->getFullUrl($oHttpstatus->getRedirect(), $url);
            $this->cliprint('cli', "REDIRECT: $url " . $oHttpstatus->getHttpcode() . " -> " . $sNewUrl . "\n");
            $aRelItem = array(
                'id' => $iId,
                'url' => $url,
                'isEndpoint' => $this->isInDenyList($sNewUrl),
                'header' => json_encode($info),
                'rescan' => 0,
                'ts' => date("Y-m-d H:i:s"),
                'tsok' => date("Y-m-d H:i:s"),
                'errorcount' => 0,
                'tserror' => false,
                'lasterror' => false,
            );
            // add url
            // if(array_key_exists('redirect_url', $info)){
            if ($sNewUrl) {
                $aRel = $this->_prepareRelitem(array(
                    'url' => $sNewUrl,
                    // 'ressourcetype' => 'page',
                    'ressourcetype' => 'link',
                        ), $iId);
                $aResult = $this->oDB->insert('ressources_rel', $aRel);
                $this->_checkDbResult($aResult);
                $this->_addUrl2Crawl($sNewUrl, true);                
            }
        }
        if (!$oHttpstatus->isError() && !$oHttpstatus->isRedirect()) {
            $this->cliprint('ok', "OK: " . $info['http_code'] . " $url \n");
            $aRelItem = array(
                'id' => $iId,
                'url' => $url,
                'isEndpoint' => true,
                'header' => json_encode($info),
                'rescan' => 0,
                'ts' => date("Y-m-d H:i:s"),
                'tsok' => date("Y-m-d H:i:s"),
                'errorcount' => 0,
                'tserror' => false,
                'lasterror' => false,
            );
        }
        $aRessource = $this->_sanitizeRessourceArray($aRelItem, true);
        
        // v0.115 FIX error counter
        if ($oHttpstatus->isError()) {
            $aRessource['errorcount[+]']= 1;
            
            // detect reason for status no connection
            if($oHttpstatus->getHttpcode()===0){
                
                // check: does the domain exist
                $sTargetHost= parse_url($url, PHP_URL_HOST);
                $sTargetIp= gethostbyname($sTargetHost);
                $sTargetIp= preg_match('/^[0-9\.\:a-f]*$/', $sTargetIp) ? $sTargetIp : false;
                if(!$sTargetIp){
                    $aRessource['http_code']=1;
                    $this->cliprint('error', "... REMARK: domain [$sTargetHost] does not exist (anymore).\n");
                } else {
                    $this->cliprint('error', "... REMARK: domain [$sTargetHost] exists ($sTargetIp).\n");
                }
                // TODO: check port and set code 2
            } 
        }

        $this->updateRessource($aRessource);

        return true;
    }

    /**
     * a main entry point: start crawling ressources
     * 
     * @param string  $sHttpMethod  optional: http method; default: GET
     * @return boolean
     */
    public function crawlRessoures($sHttpMethod='HEAD') {
        $this->_aUrls2Crawl = array();
        $this->_iUrlsCrawled = 0;
        $this->_aRessourceIDs = array();
        $this->iStartCrawl = date("U");
        $bPause = false;
        $iSimultanous=(int)$this->aProfileEffective['ressources']['simultanousRequests'];
        $this->cliprint('info', "========== Ressource scan using http $sHttpMethod".PHP_EOL);
        $this->cliprint('info', 'starting point: '. __METHOD__.PHP_EOL);
        $sMsgId = 'ressources-profile-' . $this->iSiteId;
        if (!$this->enableLocking(__CLASS__, 'index', $this->iSiteId)) {
            $this->cliprint('error', "ABORT: The crawler is still running (".__METHOD__.")\n");
            return false;
        }

        $aUrls = $this->oDB->select(
                'ressources', 'url', array(
                    'AND' => array(
                        'siteid' => $this->iSiteId,
                        'OR' => array(
                            'rescan' => 1,
                            'http_code[<]' => 1,
                            'http_code[>=]' => 500,
                        ),
                    ),
                // "LIMIT" => 2,
                )
        );
        foreach ($aUrls as $sUrl) {
            $this->_addUrl2Crawl($sUrl, true);
        }

        /*
        $aUrlsDone = $this->oDB->select(
                'ressources', 'url', array(
                    'AND' => array(
                        'siteid' => $this->iSiteId,
                        'OR' => array(
                            'rescan' => 0,
                            'http_code[>]' => 0,
                            'http_code[<]' => 500,
                        ),
                    ),
                )
        );
        // echo $this->oDB->last()."\n"; die("STOP in ".__FUNCTION__);

        foreach ($aUrlsDone as $sUrl) {
            $this->_aRessourceIDs[$sUrl]=1;
        }
         */

        $this->cliprint('info', "--- Starting http $sHttpMethod requests - $iSimultanous parallel".PHP_EOL);
        $rollingCurl = new \RollingCurl\RollingCurl();
        $aCurlOpt=$this->_getCurlOptions();
        if($sHttpMethod=="GET"){
            $aCurlOpt[CURLOPT_HTTPHEADER][]='Range: bytes=0-1023';
        }
        // $aCurlOpt[CURLOPT_NOBODY]=true; // means: fetch the ressponse header only
        
        $rollingCurl
            ->setOptions($aCurlOpt)
            ->setSimultaneousLimit($iSimultanous)
            ;
        while (count($this->_getUrls2Crawl())) {
            $iUrlsLeft=count($this->_getUrls2Crawl());
            $iUrlsTotal=count($this->_aUrls2Crawl);
            $sStatusPrefix=(100-round($iUrlsLeft*100/$iUrlsTotal)).'%: '.$iUrlsLeft . '  of '.$iUrlsTotal.' urls left';
            if ($bPause && $this->iSleep) {
                $this->touchLocking($this->_getStatus_urls_left($iUrlsTotal, $iUrlsLeft).'; sleep ' . $this->iSleep . 's');
                $this->cliprint('cli', "sleep ..." . $this->iSleep . "s\n");
                sleep($this->iSleep);
            }
            $bPause = true;
            // $this->touchLocking($sStatusPrefix);
            $this->touchLocking($this->_getStatus_urls_left($iUrlsTotal, $iUrlsLeft));
            $this->cliprint('info', $sStatusPrefix."\n");
            $self = $this;
            foreach ($this->_getUrls2Crawl() as $sUrl) {
                $rollingCurl->request($sUrl, $sHttpMethod);
            }

            $rollingCurl
                ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use ($self, $sHttpMethod) {
                        $self->processResponse($request);
                        $iUrlsLeft=count($this->_getUrls2Crawl());
                        $iUrlsTotal=count($this->_aUrls2Crawl);
                        // $self->touchLocking((100-round($iUrlsLeft*100/$iUrlsTotal)) . '%: ' .$iUrlsLeft . '  of '.$iUrlsTotal.' urls left; processing ' . $request->getUrl());
                        $self->touchLocking($this->_getStatus_urls_left($iUrlsTotal, $iUrlsLeft) . ' '.$sHttpMethod.' ' . $request->getUrl());
                        $rollingCurl->clearCompleted();
                    })
                ->execute()
            ;
            $rollingCurl->prunePendingRequestQueue();
            $this->cliprint('info', "prunePendingRequestQueue was done ... urls left " . count($this->_getUrls2Crawl()). " ... \n");
        }
        $this->updateExternalRedirect();
        $this->disableLocking();

        $iTotal=date("U") - $this->iStartCrawl;
        $this->cliprint('info', "----- Resource scan with http $sHttpMethod is finished.\n");
        $this->cliprint('info', $this->_iUrlsCrawled . " urls were crawled.\n");
        $this->cliprint('info', "process needed $iTotal sec; ". ($iTotal ? number_format($this->_iUrlsCrawled/$iTotal, 2)." urls per sec." : '') . "\n");
        $this->_showResourceStatusOnCli();

        $this->addAllCounters();

        $this->cliprint('info', "----- cleanup cache.\n");
        require_once(__DIR__ . "/../vendor/ahcache/cache.class.php");
        $this->cliprint('info', "deleting items in module ".$this->getCacheModule().".\n");
        $oCache = new AhCache($this->getCacheModule());
        $oCache->deleteModule(true);
        $this->cliprint('info', "Cache was deleted.\n");
        
        return true;
    }
    
    /**
     * 
     */
    public function addAllCounters() {

        // ----- add counters to get history data
        $this->cliprint('info', "----- add counters to get history data for site id $this->iSiteId.\n");
        require_once 'counter.class.php';
        $oCounter=new counter();
        $oCounter->mysiteid($this->iSiteId);
        $aCounterdata=$this->getStatusCounters(false, true); // 1st param: for all pages; 2nd: $bReset
        foreach($aCounterdata as $sKey=>$value){
            $this->cliprint('info', "add counter ... $sKey = $value\n");
            $oCounter->add($sKey, (string)$value);
        }
        $this->cliprint('info', "Cleanup old counter values.\n");
        $oCounter->cleanup();
        // $oCounter->add('hello', 'world');
        // $oCounter->dump();
        
    }

}
