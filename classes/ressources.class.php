<?php

require_once 'crawler-base.class.php';
require_once 'analyzer.html.class.php';

/**
 * 
 * AXLES CRAWLER :: RESSOURCES
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
        $sFirstUrl = (array_key_exists('urls2crawl', $this->aProfile['searchindex']) && count($this->aProfile['searchindex']['urls2crawl'])) 
            ? $this->aProfile['searchindex']['urls2crawl'][0] 
            : false;
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
        if (!$this->iSiteId) {
            return false;
        }

        echo "CLEANUP ressources_rel<br>\n";
        $this->oDB->delete('ressources_rel', array(
            'AND' => array(
                'siteid' => $this->iSiteId,
            ),
        ));
        echo "CLEANUP ressources<br>\n";
        $this->oDB->delete('ressources', array(
            'AND' => array(
                'siteid' => $this->iSiteId,
            ),
        ));
        $this->_aRessourceIDs = array();
    }

    /*
      see crawler_base
      $this->_createTable("ressources", array(
      'id' => 'VARCHAR(32) NOT NULL PRIMARY KEY',
      'siteid' => 'INTEGER NULL',
      'url' => 'VARCHAR(1024)  NOT NULL',
      'type' => 'VARCHAR(32) NULL',
      'header' => 'VARCHAR(1024)  NULL',

      'last_status' => 'INTEGER NULL',
      'loadtime' => 'INTEGER NULL',
      'rescan' => 'BOOLEAN TRUE',

      'ts' => 'DATETIME DEFAULT CURRENT_TIMESTAMP NULL',
      'tserror' => 'DATETIME NULL',
      'errorcount' => 'INTEGER NULL',
      'lasterror' => 'VARCHAR(1024)  NULL',
      )
      );
      $this->_createTable("ressources_rel", array(
      'id' => 'VARCHAR(32) NOT NULL PRIMARY KEY',
      'id_ressource' => 'INTEGER  NULL',
      'id_ressource_to' => 'INTEGER  NULL',
      )
      );
     */

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

            $a['content_type'] = str_replace(array('"', ' '), array('',''), strtolower($this->_getHeaderVarFromJson($sHeaderJson, 'content_type')));
            $a['http_code'] = (is_int($this->_getHeaderVarFromJson($sHeaderJson, 'http_code')) ? $this->_getHeaderVarFromJson($sHeaderJson, 'http_code') : -1 );
            $a['total_time'] = $this->_getHeaderVarFromJson($sHeaderJson, 'total_time') ? (int) ($this->_getHeaderVarFromJson($sHeaderJson, 'total_time') * 1000) / 1 : false;
            $a['size_download'] = (int) $this->_getHeaderVarFromJson($sHeaderJson, 'size_download') / 1;
            // $aData[''] = $this->_getHeaderVarFromJson($aData['header'], '');
        }
        $oHttpstatus = new httpstatus($a['http_code']);
        $a['status']=$oHttpstatus->getStatus();

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



        $bExists = (array_key_exists($aData['url'], $this->_aRessourceIDs));


        // if (count($aCurrent) && $aCurrent[0]['id']) {
        if ($bExists) {
            if ($bSkipIfExist) {
                // echo 'SKIP ressource ' . $aData['url'] . "\n";
            } else {
                echo 'UPDATE ressource ' . $aData['url'] . "\n";
                /*
                  $aCurrent = $this->oDB->select('ressources', array('id'), array(
                  'url' => $aData['url'],
                  ));
                 * 
                 */
                $aResult = $this->oDB->update('ressources', $aData, array(
                    'url' => $aData['url'],
                        )
                );
                $this->_checkDbResult($aResult);
            }
        } else {
            echo 'INSERT ressource ' . $aData['url'] . "\n";
            $aResult = $this->oDB->insert('ressources', $aData);
            $this->_checkDbResult($aResult);
            $this->_aRessourceIDs[$aData['url']] = true;
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
            'url' => $aData['url'],
                )
        );
        $this->_checkDbResult($aResult);
        // echo $this->oDB->last() . "\n\n";
        return $aResult;
    }
    public function updateExternalRedirect() {
        
        // reset all
        $aResult = $this->oDB->update('ressources', 
            array('isExternalRedirect'=>false),
            array(
                'siteid' => $this->iSiteId,
            )
        );
        
        // set 
        $aResult = $this->oDB->update('ressources', 
            array('isExternalRedirect'=>true),
            array(
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
        echo "RESPONSE: $iLen byte - " . $aData['url'] . "<br>\n";
        if ($iLen) {
            echo "----- relitems for " . $aData['url'] . "<br>\n";
            $oHtml = new analyzerHtml($aData['response'], $aData['url']);
            $this->addPageRelItems($aData, $oHtml->getCss());
            $this->addPageRelItems($aData, $oHtml->getImages());
            $this->addPageRelItems($aData, $oHtml->getLinks());
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
            echo "ERROR: missing url key in " . print_r($aRelItem, 1) . "\n";
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
        $sOut = '';
        // $sOut.=$sSourceId . " - " . $aData['url'] . "\n" . print_r($aRelData); sleep (10);
        if (is_array($aRelData) && count($aRelData)) {
            foreach ($aRelData as $sGroup => $aItems) {
                if (is_array($aItems) && count($aItems) && array_search($sGroup, $aGroups) !== false) {
                    $sOut .= "--- addPageRelItems ... [" . $aData['url'] . "] .  found " . count($aItems) . " items of group $sGroup<br>\n";
                    $aRel = array();
                    foreach ($aItems as $aItem) {
                        if (array_key_exists('_url', $aItem) && $aItem['_url']!==$aData['url']) {

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
        echo $sOut;
    }

    /**
     * read crawled data from database and add it to ressources
     * @return boolean
     */
    public function addRessourcesFromPages() {
        if (!$this->iSiteId) {
            return false;
        }
        echo "INFO: reading pages ...<br>\n";
        $aResult = $this->oDB->select(
                'pages', '*', array(
            'AND' => array(
                'siteid' => $this->iSiteId,
            ),
                )
        );

        if (is_array($aResult) && count($aResult)) {
            echo "INFO: insert " . count($aResult) . " already crawled pages as ressource<br>\n";
            // sleep(2);
            $aPages = array();
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
            }
            $this->oDB->insert('ressources', $aPages);
            $this->_checkDbResult($aResult);

            echo "INFO ... adding relitems of already crawled pages<br>\n";
            sleep(2);
            foreach ($aResult as $aData) {
                unset($aData['id']);
                $this->addRelRessourcesOfAPage($aData);
            }
        }
        echo "<br>\n";
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
            'siteid' => $this->iSiteId,
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
     * mark an url to be crawled. It returns true if it was newly added to
     * the queue; it returns false if it was added or crawled already.
     * @param string $sUrl  url
     * @return boolean
     */
    private function _addUrl2Crawl($sUrl, $bDebug = false) {
        echo $bDebug ? __FUNCTION__ . "($sUrl)\n" : "";

        // remove url hash
        $sUrl = preg_replace('/#.*/', '', $sUrl);
        // ... and spaces
        $sUrl = str_replace(' ', '%20', $sUrl);

        if (array_key_exists($sUrl, $this->_aUrls2Crawl)) {
            echo $bDebug ? "... don't adding $sUrl - it was added already\n" : "";
            return false;
        } else {
            echo "... adding $sUrl\n";
            $this->_aUrls2Crawl[$sUrl] = true;

            return true;
        }
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
     * mark an url that it was crawled already
     * @param string $sUrl
     * @return boolean
     */
    private function _removeUrlFromCrawling($sUrl) {
        // echo __FUNCTION__."($sUrl)\n";
        return $this->_aUrls2Crawl[$sUrl] = false;
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
            echo "ERROR: fetching $url FAILED. Status: " . $oHttpstatus->getHttpcode() . " - " . $oHttpstatus->getStatus() . ".\n";
            $aRelItem = array(
                'id' => $iId,
                'url' => $url,
                'isEndpoint' => true,
                // 'header' => json_encode($info),
                // 'rescan' => 0,
                'errorcount[+]' => 1,
                'ts' => date("Y-m-d H:i:s"),
                'tserror' => date("Y-m-d H:i:s"),
                'lasterror' => json_encode($info),
            );
        }
        if ($oHttpstatus->isRedirect()) {
            $sNewUrl = $oHttpstatus->getRedirect();
            echo "REDIRECT: $url " . $oHttpstatus->getHttpcode() . " - " . $oHttpstatus->getStatus() . " -> " . $sNewUrl . ".\n";
            $aRelItem = array(
                'id' => $iId,
                'url' => $url,
                'isEndpoint' => false,
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
            echo "OK: http code " . $info['http_code'] . " $url \n";
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
        // print_r($aRessource);
        $this->updateRessource($aRessource);

        // $this->updateRessource($aRelItem, false);
        // echo $this->oDB->last()."\n"; 
        // die("STOP in ".__FUNCTION__);


        /*
          switch ($sType) {
          case 'text/html':
          $this->_processHtmlPage($response, $info);
          break;
          default:
          echo "WARNING: handling of MIME [$sType] was not implemented (yet). Cannot proceed with url $url ... ".print_r($info)."\n";
          return false;
          }
         */
        return true;
    }

    // TODO
    public function crawlRessoures() {
        $this->_aUrls2Crawl = array();
        $this->_iUrlsCrawled = 0;
        $this->_aRessourceIDs = array();
        $this->iStartCrawl = date("U");
        $bPause = false;
        $sMsgId = 'ressources-profile-' . $this->iSiteId;
        if (!$this->enableLocking(__CLASS__, 'index', $this->iSiteId)) {
            echo "ABORT: the action is still running.\n";
            return false;
        }

        $aUrls = $this->oDB->select(
                'ressources', 'url', array(
            'AND' => array(
                'siteid' => $this->iSiteId,
                'OR' => array(
                    'rescan' => 1,
                    'http_code' => 0,
                    'http_code' => 500,
                ),
            ),
                // "LIMIT" => 2,
                )
        );
        // echo $this->oDB->last()."\n"; die("STOP in ".__FUNCTION__);


        foreach ($aUrls as $sUrl) {
            $this->_addUrl2Crawl($sUrl, true);
        }
        echo "\n\n----- start http requests<br>\n";

        while (count($this->_getUrls2Crawl())) {
            if ($bPause && $this->iSleep) {
                $this->touchLocking('sleep ' . $this->iSleep . 's');
                echo "sleep ..." . $this->iSleep . "s\n";
                sleep($this->iSleep);
            }
            $bPause = true;
            $this->touchLocking('urls left ' . count($this->_getUrls2Crawl()) . ' ... ');
            $self = $this;
            $rollingCurl = new \RollingCurl\RollingCurl();
            foreach ($this->_getUrls2Crawl() as $sUrl) {
                $rollingCurl->get($sUrl);
            }
            $rollingCurl
                    ->setOptions(array(
                        CURLOPT_FOLLOWLOCATION => false,
                        CURLOPT_HEADER => true,
                        CURLOPT_NOBODY => true,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 10,
                        CURLOPT_USERAGENT => $this->sUserAgent,
                        CURLOPT_USERPWD => array_key_exists('userpwd', $this->aProfile) ? $this->aProfile['userpwd'] : '',
                        // TODO: this is unsafe .. better: let the user configure it
                        CURLOPT_SSL_VERIFYPEER => 0,

                        // v0.22 cookies
                        CURLOPT_COOKIEJAR,$this->sCcookieFilename,
                        CURLOPT_COOKIEFILE,$this->sCcookieFilename,
                    ))
                    ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use ($self) {
                        $self->processResponse($request);
                        $self->touchLocking('processing ' . $request->getUrl());
                        $rollingCurl->clearCompleted();
                        $rollingCurl->prunePendingRequestQueue();
                    })
                    ->setSimultaneousLimit($this->aProfile['ressources']['simultanousRequests'])
                    ->execute()
            ;
        }
        $this->updateExternalRedirect();
        $this->disableLocking();
        $iUrls = $this->oDB->count('ressources', array('url'), array(
            'AND' => array(
                'siteid' => $this->iSiteId,
            ),));
        echo "\n"
        . "Ressource Scan has finished.\n\n"
        . "STATUS: \n"
        . $this->_iUrlsCrawled . " urls were crawled\n"
        . "process needed " . (date("U") - $this->iStartCrawl) . " sec.\n"
        . "$iUrls ressource urls for profile [" . $this->iSiteId . "] are in the index now (table 'ressources')\n"
        ;
    }

}
