<?php

require_once __DIR__ . '/../vendor/medoo/Medoo.php';
require_once 'status.class.php';

/**
 * 
 * AXLES CRAWLER BASE CLASS
 * 
 * */
class crawler_base {

    public $aAbout = array(
        'product' => 'ahCrawler',
        'version' => 'v0.33',
        'date' => '2018-09-09',
        'author' => 'Axel Hahn',
        'license' => 'GNU GPL 3.0',
        'urlHome' => 'https://www.axel-hahn.de/ahcrawler',
        'urlDocs' => 'https://www.axel-hahn.de/docs/ahcrawler/index.htm',
        'urlSource' => 'https://github.com/axelhahn/ahcrawler',
    );

    /**
     * general options of the installation
     * @var array
     */
    protected $aOptions = array(
        'database' => array(
            'database_type' => 'sqlite',
            'database_file' => '__DIR__/data/ahcrawl.db',
        ),
        'auth' => array(
        ),
        'debug' => 'false',
        'lang' => 'en',
        'crawler' => array(
            'searchindex' => array(
                'simultanousRequests' => 2,
            ),
            'ressources' => array(
                'simultanousRequests' => 3,
            ),
        ),
    );
    protected $aProfileDefault = array(
        'label' => 'no [label]',
        'description' => 'no [description]',
        'searchindex' => array(
            'stickydomain' => 'no [searchindex]->[stickydomain]',
            'urls2crawl' => array(),
            'iDepth' => 7,
            'include' => array(),
            'includepath' => array(),
            'exclude' => array(),
            'simultanousRequests' => false,
        ),
        'searchcategories' => array(),
        'ressources' => array(
            'simultanousRequests' => false,
        ),
    );

    protected $_aDbSettings=array(
        'tables'=>array(
            "pages"=>array(
                'id' => 'INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT',
                // 'id' => 'VARCHAR(32) NOT NULL PRIMARY KEY',
                'url' => 'VARCHAR(1024)  NOT NULL',
                'siteid' => 'INTEGER  NOT NULL',
                'title' => 'VARCHAR(256)  NULL',
                'description' => 'VARCHAR(1024)  NULL',
                'keywords' => 'VARCHAR(1024)  NULL',
                'lang' => 'VARCHAR(8) NULL',
                'size' => 'INTEGER NULL',
                'time' => 'INTEGER NULL',
                'content' => 'MEDIUMTEXT',
                'header' => 'VARCHAR(2048) NULL',
                'response' => 'MEDIUMTEXT',
                'ts' => 'DATETIME DEFAULT CURRENT_TIMESTAMP NULL',
                'tserror' => 'DATETIME NULL',
                'errorcount' => 'INTEGER NULL',
                'lasterror' => 'VARCHAR(1024)  NULL',
            ),
            "words"=>array(
                'id' => 'INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT',
                'word' => 'VARCHAR(32) NOT NULL',
                'count' => 'INTEGER',
                'siteid' => 'INTEGER NOT NULL',
            ),
            "searches"=> array(
                'id' => 'INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT',
                'ts' => 'DATETIME DEFAULT CURRENT_TIMESTAMP NULL',
                'siteid' => 'INTEGER NOT NULL',
                'searchset' => 'VARCHAR(128)  NULL',
                'query' => 'VARCHAR(256)  NULL',
                'results' => 'INTEGER  NULL',
                'host' => 'VARCHAR(64)  NULL', // ipv4 and ipv6
                'ua' => 'VARCHAR(256)  NULL',
                'referrer' => 'VARCHAR(1024)  NULL'
            ),
            "ressources" => array(
                // 'id' => 'VARCHAR(32) NOT NULL PRIMARY KEY',
                'id' => 'INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT',
                'siteid' => 'INTEGER NOT NULL',
                'url' => 'VARCHAR(1024) NOT NULL',
                'ressourcetype' => 'VARCHAR(16) NOT NULL',
                'type' => 'VARCHAR(16) NOT NULL',
                'header' => 'VARCHAR(2048) NULL',
                // header vars
                'content_type' => 'VARCHAR(32) NULL',
                'isSource' => 'BOOLEAN NULL',
                'isLink' => 'BOOLEAN NULL',
                'isEndpoint' => 'BOOLEAN NULL',
                'isExternalRedirect' => 'BOOLEAN NULL',
                'http_code' => 'INTEGER NULL',
                'status' => 'VARCHAR(16) NOT NULL',
                'total_time' => 'INTEGER NULL',
                'size_download' => 'INTEGER NULL',
                'rescan' => 'BOOL DEFAULT TRUE',
                'ts' => 'DATETIME DEFAULT CURRENT_TIMESTAMP NULL',
                'tsok' => 'DATETIME NULL',
                'tserror' => 'DATETIME NULL',
                'errorcount' => 'INTEGER NULL',
                'lasterror' => 'VARCHAR(1024)  NULL',
            ),
            "ressources_rel"=> array(
                // 'id' => 'VARCHAR(32) NOT NULL PRIMARY KEY',
                'id_rel_ressources' => 'INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT',
                'siteid' => 'INTEGER NOT NULL',
                // 'id_ressource' => 'VARCHAR(32) NOT NULL',
                // 'id_ressource_to' => 'VARCHAR(32) NOT NULL',
                'id_ressource' => 'INTEGER NOT NULL',
                'id_ressource_to' => 'INTEGER NOT NULL',
                // 'references' => 'INTEGER NOT NULL',
            ),
        ),
    );
    protected $_aCurlopt=array();
        
    /**
     * the current set site ID (search profile)
     * @var integer
     */
    protected $iSiteId = false;

    /**
     * options with all crawl projects
     * @var array
     */
    protected $aProfile = array();

    /**
     * database object for indexer and search
     * @var object
     */
    protected $oDB;

    /**
     * default language
     * @var string
     */
    protected $sLang = 'en';

    /**
     * array for language texts
     * @var type 
     */
    protected $aLang = array();

    /**
     * user agent for the crawler 
     * @var type 
     */
    protected $sUserAgent = false;

    protected $sCcookieFilename = false;
    
    // ----------------------------------------------------------------------

    /**
     * new crawler
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct($iSiteId = false) {

        return $this->setSiteId($iSiteId);
    }

    // ----------------------------------------------------------------------
    // OPTIONS + DATA
    // ----------------------------------------------------------------------

    protected function _getOptionsfile() {
        return dirname(__DIR__) . '/config/crawler.config.json';
    }

    /**
     * load global options array
     * @return array
     */
    protected function _loadOptions() {
        $aOptions = json_decode(file_get_contents($this->_getOptionsfile()), true);
        if (!$aOptions || !is_array($aOptions) || !count($aOptions)) {
            die("ERROR: json file is invalid. Aborting");
        }
        if (!array_key_exists('options', $aOptions)) {
            die("ERROR: config requires a section [options].");
        }
        if (!array_key_exists('database', $aOptions['options'])) {
            die("ERROR: config requires a database definition.");
        }
        // make a relative path absolute
        if (array_key_exists('database_file', $aOptions['options']['database'])) {
            $aOptions['options']['database']['database_file'] = str_replace('__DIR__/', dirname(__DIR__) . '/', $aOptions['options']['database']['database_file']);
        }
        return $aOptions;
    }
    
    protected function _getCurlCanHttp2(){
        return (curl_version()["features"] & CURL_VERSION_HTTP2) !== 0;
    }


    protected function _getCurlOptions(){
        $aReturn=array(
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $this->sUserAgent,
            CURLOPT_USERPWD => array_key_exists('userpwd', $this->aProfile) ? $this->aProfile['userpwd']:false,
            CURLOPT_VERBOSE => false,
            CURLOPT_ENCODING => '',  // to fetch encoding

            // TODO: this is unsafe .. better: let the user configure it
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            // CURLOPT_SSL_VERIFYSTATUS => false,
            // v0.22 cookies
            CURLOPT_COOKIEJAR, $this->sCcookieFilename,
            CURLOPT_COOKIEFILE, $this->sCcookieFilename,
            
            CURLOPT_TIMEOUT => 10,
        );
        if($this->_getCurlCanHttp2()){
            $aReturn[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_2_0;
        }
        return $aReturn;
    }

    /**
     * set specialties for PDO queries in sifferent database types
     * 
     * @return array
     */
    private function _getPdoDbSpecialties() {
        $aReturn = array();
        switch ($this->aOptions['database']['database_type']) {
            case 'mysql':
                $aReturn = array(
                    'tablePre' => '`',
                    'tableSuf' => '`',
                    'createAppend' => 'CHARACTER SET utf8 COLLATE utf8_general_ci',
                );
                break;
            case 'sqlite':
                $aReturn = array(
                    'tablePre' => '[',
                    'tableSuf' => ']',
                    'createAppend' => '',
                );
                break;

            default:
                echo __FUNCTION__ . ' - type ' . $this->aOptions['database']['database_type'] . ' was not implemented yet.<br><pre>';
                print_r($this->aOptions['database']);
                die();
        }
        return $aReturn;
    }

    /**
     * init/ setup: create database tables
     * @param string $sTable
     * @param array $aFields
     */
    private function _createTable($sTable, $aFields) {
        $sql = '';

        $aDb = $this->_getPdoDbSpecialties();
        foreach ($aFields as $field => $type) {
            switch ($this->aOptions['database']['database_type']) {
                case 'mysql':
                    $type = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $type);
                    $type = str_replace('INTEGER', 'INT', $type);
                    // $type = str_replace('TEXT', 'LONGTEXT', $type);
                    $type = str_replace('DATETIME', 'TIMESTAMP', $type);
                    break;
            }
            $sql .= $sql ? ",\n" : '';
            $sql .= "    " . $aDb['tablePre'] . "${field}" . $aDb['tableSuf'] . " $type";
        }
        $sql = "CREATE TABLE IF NOT EXISTS " . $aDb['tablePre'] . "$sTable" . $aDb['tableSuf'] . "(\n" . $sql . "\n)\n" . $aDb['createAppend'];

        //echo "DEBUG: <pre>$sql</pre>";
        if (!$this->oDB->query($sql)) {
            echo $sql . "<br>";
            var_dump($this->oDB->error(), 1);
            die();
        }
    }

    /**
     * init database 
     * @param type $aOptions
     */
    private function _initDB() {
        $this->oDB = new Medoo\Medoo($this->aOptions['database']);
        if (!$this->_checkDbResult()) {
            die('ERROR: the database could not be connected. Maybe the initial settings are wrong or the database is offline.');
        }
        // TODO: put creation of tables into setup/ update
        foreach($this->_aDbSettings['tables'] as $sTable=>$aSettings){
            $this->_createTable($sTable, $aSettings);
        }

    }

    /**
     * check the status of the last database action and detect if an error occured.
     * @param array  $aResult  result of database query (used with enabled debug only)
     * @return boolean
     */
    protected function _checkDbResult($aResult = false) {
        $aErr = $this->oDB->error();
        if ($aErr[1]) {
            echo "!!! Database error detected :-/\n";
            if ($this->aOptions['debug'] || true) {
                echo ''
                . '... DB-QUERY : ' . $this->oDB->last() . "\n"
                . ($aResult ? '... DB-RESULT: ' . print_r($aResult, 1) . "\n" : '')
                . '... DB-ERROR: ' . print_r($this->oDB->error(), 1) . "\n"
                ;
                sleep(3);
            }
            return false;
        } elseif ($this->aOptions['debug']) {
            echo '... OK: DB-QUERY : ' . substr($this->oDB->last(), 0, 200) . " [...]\n";
        }
        return true;
    }

    /**
     * get count of existing values in a database table.
     * 
     * @param string  $sTable   name of database table
     * @param string  $sRow     name of the column to count
     * @param array   $aFilter  array with column name and value to filter
     * @return array
     */
    public function getCountsOfRow($sTable, $sRow, $aFilter = array()) {
        // table row can contain lower capital letters and underscore
        $sTable = preg_replace('/[^a-z\_\.]/', '', $sTable);
        $sRow = preg_replace('/[^a-z\_]/', '', $sRow);

        $sWhere = '';
        if (is_array($aFilter) && count($aFilter)) {
            foreach ($aFilter as $sColumn => $value) {
                $sWhere .= ($sWhere ? 'AND ' : '')
                        . $sColumn . ' ' . ( $value === "NULL" ? 'IS NULL' : '=' . $this->oDB->quote($value)) . ' ';
            }
        }
        $sSql = "SELECT $sRow, count(*) as count "
                . "FROM $sTable "
                . ($sWhere ? 'WHERE ' . $sWhere : '')
                . "GROUP BY $sRow "
                . "ORDER BY $sRow ASC";
        // echo "SQL: $sSql\n ... <br>"; print_r($aFilter);
        $aData = $this->oDB->query($sSql)->fetchAll(PDO::FETCH_ASSOC);

        return $aData;
    }
    
    /**
     * get latest record of a db table
     * 
     * @param string  $sTable   name of database table (pages|ressources)
     * @param array   $aFilter  array with column name and value to filter
     * @return array
     */
    public function getLastTsRecord($sTable, $aFilter = array()) {
        // table row can contain lower capital letters and underscore
        $sDbTable = preg_replace('/[^a-z\_\.]/', '', $sTable);
        $aData = $this->oDB->max(
                $sDbTable, "ts", $aFilter
        );
        // echo "SQL: " . $this->oDB->last() ."<br>";
        return $aData;
    }

    /**
     * get count of records in a db table
     * 
     * @param string  $sTable   name of database table (pages|ressources)
     * @param array   $aFilter  array with column name and value to filter
     * @return array
     */
    public function getRecordCount($sTable, $aFilter = array()) {
        // table row can contain lower capital letters and underscore
        $sDbTable = preg_replace('/[^a-z\_\.]/', '', $sTable);
        $aData = $this->oDB->count(
                $sDbTable, "*", $aFilter
        );
        // echo "SQL: " . $this->oDB->last() ."<br>";
        return $aData;
    }

    /**
     * delete database tables for crawled data. as a reminder: this deletes
     * all data for *all* defined profiles.
     * 
     * @param type $aItems
     */
    public function flushData($aItems) {
        $aTables = array();
        $bAll = array_key_exists('all', $aItems) && $aItems['all'];
        if ($bAll || (array_key_exists('searchindex', $aItems) && $aItems['searchindex'])) {
            $aTables[] = 'pages';
            $aTables[] = 'words';
        }
        if ($bAll || (array_key_exists('ressources', $aItems) && $aItems['ressources'])) {
            $aTables[] = 'ressources';
            $aTables[] = 'ressources_rel';
        }
        if (array_key_exists('searches', $aItems) && $aItems['searches']) {
            $aTables[] = 'search';
        }
        if (count($aTables)) {
            $aDb = $this->_getPdoDbSpecialties();
            foreach ($aTables as $sTable) {
                $sql = "DROP TABLE IF EXISTS " . $aDb['tablePre'] . "$sTable" . $aDb['tableSuf'] . ";";
                echo "DEBUG: $sql\n";
                if (!$this->oDB->query($sql)) {
                    echo $sql . "<br>";
                    var_dump($this->oDB->error(), 1);
                    die();
                }
            }
        }
        echo "flushing was successful.\n";
        return true;
    }

    /**
     * set the id of the active project (for crawling or search)
     * @param integer $iSiteId
     */
    public function setSiteId($iSiteId = false) {
        $aOptions = $this->_loadOptions();
        $this->iSiteId = false;
        $this->aProfile = array();

        // builtin default options ... these will be overrided with crawler.config.json
        if (array_key_exists('options', $aOptions)) {
            $this->aOptions = array_merge($this->aOptions, $aOptions['options']);
        }

        // $this->sLang = (array_key_exists('lang', $this->aOptions)) ? $this->sLang = $this->aOptions['lang'] : $this->sLang;
        $this->sLang = $this->aOptions['lang'];

        // curl options:
        $this->sUserAgent = $this->aAbout['product'] . ' ' . $this->aAbout['version'] . ' (GNU GPL crawler and linkchecker for your website; ' . $this->aAbout['urlHome'] . ')';
        
        
        $this->_initDB();

        if ($iSiteId) {
            if (!array_key_exists('profiles', $aOptions) || !array_key_exists($iSiteId, $aOptions['profiles'])) {
                die("ERROR: a config with siteId $iSiteId does not exist.");
            }
            $this->iSiteId = $iSiteId;
            $aProfile = $aOptions['profiles'][$iSiteId];

            // merge defaults with user settings for this profile 
            $aNewProfile=$this->aProfileDefault;
            foreach(array_keys($this->aProfileDefault) as $sKey0){
                if (!is_array($aNewProfile[$sKey0])){
                    $aNewProfile[$sKey0] = array_key_exists($sKey0, $aProfile) ? $aProfile[$sKey0] : $this->aProfileDefault[$sKey0];
                } else {
                    $aNewProfile[$sKey0]=array_key_exists($sKey0, $aProfile) ? array_merge($this->aProfileDefault[$sKey0], $aProfile[$sKey0]) : $this->aProfileDefault[$sKey0];
                }
            }
            $this->aProfile = $aNewProfile;

            if (!array_key_exists('includepath', $this->aProfile['searchindex']) || !count($this->aProfile['searchindex']['includepath'])) {
                $this->aProfile['searchindex']['includepath'][] = '.*';
            }
            if (!array_key_exists('simultanousRequests', $this->aProfile['searchindex']) || $this->aProfile['searchindex']['simultanousRequests']==false ) {
                $this->aProfile['searchindex']['simultanousRequests'] = $this->aOptions['crawler']['searchindex']['simultanousRequests'];
            }
            if (!array_key_exists('simultanousRequests', $this->aProfile['ressources']) || $this->aProfile['ressources']['simultanousRequests']==false ) {
                $this->aProfile['ressources']['simultanousRequests'] = $this->aOptions['crawler']['ressources']['simultanousRequests'];
            }
            
            // @since v0.22 
            $this->sCcookieFilename = dirname(__DIR__).'/data/cookiefile-siteid-'.$iSiteId.'.txt';
            touch($this->sCcookieFilename);
            
            // print_r($this->aProfile); sleep(5);
        }
    }

    /**
     * get all existing search profiles
     * @return array
     */
    public function getProfileIds() {
        $aOptions = $this->_loadOptions();
        if (
                is_array($aOptions) && array_key_exists('profiles', $aOptions)
        ) {
            return array_keys($aOptions['profiles']);
        }
        return false;
    }

    // ----------------------------------------------------------------------
    // content
    // ----------------------------------------------------------------------
    protected function _getHeaderVarFromJson($sJson, $sKey) {
        $aTmp = json_decode($sJson, 1);
        return (is_array($aTmp) && array_key_exists($sKey, $aTmp)) ? $aTmp[$sKey] : FALSE
        ;
    }

    // ----------------------------------------------------------------------
    // LANGUAGE
    // ----------------------------------------------------------------------

    /**
     * helper function to load language array
     * @param string  $sPlace  one of frontend|backend
     * @param string  $sLang   language (i.e. "de")
     * @return array
     */
    private function _getLangData($sPlace, $sLang = false) {
        if (!$sLang) {
            // $this->setSiteId(false);
            $sLang = $this->sLang;
        }
        $sJsonfile = '/lang/' . $sPlace . '.' . $sLang . '.json';
        $aLang = json_decode(file_get_contents(dirname(__DIR__) . $sJsonfile), true);
        if (!$aLang || !is_array($aLang) || !count($aLang)) {
            die("ERROR: json lang file $sJsonfile is invalid. Aborting.");
        }
        $this->aLang[$sPlace] = $aLang;
        return true;
    }

    /**
     * load texts for backend
     * @param string  $sLang   language (i.e. "de")
     * @return array
     */
    public function setLangBackend($sLang = false) {
        $this->setSiteId(false);
        return $this->_getLangData('backend', $sLang);
    }

    /**
     * load texts for frontend
     * @param string  $sLang   language (i.e. "de")
     * @return array
     */
    public function setLangFrontend($sLang = false) {
        return $this->_getLangData('frontend', $sLang);
    }

    /**
     * get language specific text
     * @param string  $sPlace  one of frontend|backend
     * @param type    $sId     id of a text
     * @return string
     */
    public function getTxt($sPlace, $sId, $sAltId = false) {
        if (!array_key_exists($sPlace, $this->aLang)) {
            die(__FUNCTION__ . ' init text with setLangNN for ' . $sPlace . ' first.');
        }
        return array_key_exists($sId, $this->aLang[$sPlace]) ? $this->aLang[$sPlace][$sId] : ($sAltId ? (array_key_exists($sAltId, $this->aLang[$sPlace]) ? $this->aLang[$sPlace][$sAltId] : '[' . $sPlace . ': ' . $sId . ']'
                ) : '[' . $sPlace . ': ' . $sId . ']'
                )
        ;
    }

    /**
     * get language specific text of backend
     * @param type    $sId     id of a text
     * @return string
     */
    public function lB($sId, $sAltId = false) {
        return $this->getTxt('backend', $sId, $sAltId);
    }

    /**
     * get language specific text of frontend
     * @param type    $sId     id of a text
     * @return string
     */
    public function lF($sId) {
        return $this->getTxt('frontend', $sId);
    }

    // ----------------------------------------------------------------------
    // STATUS / LOCKING
    // ----------------------------------------------------------------------

    public function enableLocking($sLockitem, $sAction = false, $iProfile = false) {
        $oStatus = new status();
        $sMsgId = $sLockitem . '-' . $sAction . '-' . $iProfile;
        if (!$oStatus->startAction($sMsgId, $iProfile)) {
            $oStatus->showStatus();
            echo "ABORT: the action is still running.\n";
            return false;
        }
        $this->aStatus = array(
            'lockitem' => $sLockitem,
            'action' => $sAction,
            'profile' => $iProfile,
            'messageid' => $sMsgId,
        );

        return true;
    }

    public function touchLocking($sMessage) {
        $oStatus = new status();
        $oStatus->updateAction($this->aStatus['messageid'], $sMessage);
    }

    public function disableLocking() {
        $oStatus = new status();
        $oStatus->finishAction($this->aStatus['messageid']);
        $this->aStatus = false;
        return true;
    }

}
