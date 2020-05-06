<?php

require_once __DIR__ . '/../vendor/medoo/Medoo.php';
require_once 'status.class.php';
require_once 'logger.class.php';

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
 * AXLES CRAWLER BASE CLASS
 * 
 * */
class crawler_base {

    public $aAbout = array(
        'product' => 'ahCrawler',
        'version' => '0.107',
        'date' => '2020-05-06',
        'author' => 'Axel Hahn',
        'license' => 'GNU GPL 3.0',
        'urlHome' => 'https://www.axel-hahn.de/ahcrawler',
        'urlDocs' => 'https://www.axel-hahn.de/docs/ahcrawler/index.htm',
        'urlSource' => 'https://github.com/axelhahn/ahcrawler',
        'requirements' => array(
            'phpversion'=>'5.5',
            'phpextensions'=>array('curl', 'PDO','xml','zip')
        ),
    );

    /**
     * general options of the application
     * @var array
     */
    protected $aOptions = array();
    /**
     * default options of the application
     * @var array
     */
    protected $aDefaultOptions = array(
        'database' => array(
            'database_type' => 'sqlite',
            'database_file' => '__DIR__/data/ahcrawl.db',
        ),
        'auth' => array(
        ),
        'debug' => false,
        'lang' => 'en',
        'menu' => array(),
        'crawler' => array(
            'userAgent' => false,
            'memoryLimit' => '512M',
            'searchindex' => array(
                'simultanousRequests' => 2,
            ),
            'ressources' => array(
                'simultanousRequests' => 3,
            ),
        ),
        'searchindex' => array(
            'regexToRemove' => array(
                // '<!--googleoff\:\ index-->.*?<!--googleon\:\ index-->',
                // '<!--sphider_noindex-->.*?<!--/sphider_noindex-->',
                // '<!--.*?-->',
                // '<link rel[^<>]*>',
                '<footer[^>]*>.*?</footer>',
                '<header[^>]*>.*?</header>',
                '<nav[^>]*>.*?</nav>',
                '<script[^>]*>.*?</script>',
                '<style[^>]*>.*?</style>',
            ),
            'rankingWeights' => array(
                'matchWord' => array(
                    'title' => 50,
                    'keywords' => 50,
                    'description' => 50,
                    'url' => 500,
                    'content' => 5,
                ),
                'WordStart' => array(
                    'title' => 20,
                    'keywords' => 20,
                    'description' => 20,
                    'url' => 30,
                    'content' => 3,
                ),
                'any' => array(
                    'title' => 2,
                    'keywords' => 2,
                    'description' => 2,
                    'url' => 5,
                    'content' => 1,
                ),
            ),
        ),
        'analysis' => array(
            'MinTitleLength' => 20,
            'MinDescriptionLength' => 40,
            'MinKeywordsLength' => 10,
            'MaxPagesize' => 150000, 
            'MaxLoadtime' => 500,
        ),
        // used in backend
        'updater' => array(
            'baseurl'=>'https://www.axel-hahn.de/versions/',
            'tmpdir'=>false,
            'ttl'=>86400,     // 1 day
        ),
    );
    /**
     * defaults for each web profile
     * @var array
     */
    protected $aProfileDefault = array(
        'label' => '',
        'description' => '',
        'searchindex' => array(
            'urls2crawl' => array(),
            'iDepth' => 7,
            'iMaxUrls' => 0,
            'include' => array(),
            'includepath' => array(),
            'exclude' => array(),
            'simultanousRequests' => false,
            'regexToRemove' => array(),
        ),
        'frontend' => array(
            'searchcategories' => array(),
            'searchlang' => array(),
        ),
        'ressources' => array(
            'simultanousRequests' => false,
        ),
    );

    /**
     * database tables and indexes
     * 
     * structure
     * 'tables'
     *   -> [table name]
     *      -> 'columns'
     *          -> array of ([column name], def])
     *      -> 'indexes'
     *          -> array of ([index name], [list of columns], [index type])
     *              0 - {string}  name of index (without prefix for table
     *              1 - {array}   list of columns
     *              2 - {string}  type; one of none or [UNIQUE | FULLTEXT | SPATIAL]
     *
     * @var array 
     */
    protected $_aDbSettings=array(
        'tables'=>array(
            'pages'=>array(
                'columns'=>array(
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
                'indexes'=>array(
                    // PRIMARY KEY (`id`),
                    // INDEX `pages_siteid_IDX` (`siteid`) USING BTREE,
                    // FULLTEXT INDEX `pages_url_IDX` (`url`, `title`, `description`, `keywords`, `lang`, `content`)
                    // array('PRIMARY KEY', '', array('id')),
                    array('siteid', array('siteid')),
                    array('url',    array('url')),
                    array('search', array('url', 'title', 'description', 'keywords', 'lang', 'content'), 'FULLTEXT'),
                ),
            ),
                
            'words'=>array(
                'columns'=>array(
                    'id' => 'INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT',
                    'word' => 'VARCHAR(32) NOT NULL',
                    'count' => 'INTEGER',
                    'siteid' => 'INTEGER NOT NULL',
                ),
                'indexes'=>array(
                    array('siteid', array('siteid')),
                    array('search', array('word')),
                ),
            ),
            'searches'=> array(
                'columns'=>array(
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
                'indexes'=>array(
                    array('siteid', array('siteid')),
                    array('stats', array('ts')),
                    array('query', array('query')),
                ),
            ),
            'ressources' => array(
                'columns'=>array(
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
                'indexes'=>array(
                    array('siteid', array('siteid')),
                    array('url', array('url'), ''),
                    array('ressourcetype', array('ressourcetype')),
                    array('content_type', array('content_type')),
                    array('http_code', array('http_code')),
                ),
            ),
            'ressources_rel'=> array(
                'columns'=>array(
                    // 'id' => 'VARCHAR(32) NOT NULL PRIMARY KEY',
                    'id_rel_ressources' => 'INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT',
                    'siteid' => 'INTEGER NOT NULL',
                    // 'id_ressource' => 'VARCHAR(32) NOT NULL',
                    // 'id_ressource_to' => 'VARCHAR(32) NOT NULL',
                    'id_ressource' => 'INTEGER NOT NULL',
                    'id_ressource_to' => 'INTEGER NOT NULL',
                    // 'references' => 'INTEGER NOT NULL',
                ),
                'indexes'=>array(
                    array('siteid', array('siteid')),
                    array('id_ressource', array('id_ressource')),
                    array('id_ressource_to', array('id_ressource_to')),
                ),
            ),
            /*
            'stats' => array(
                'columns'=>array(
                    // 'id' => 'VARCHAR(32) NOT NULL PRIMARY KEY',
                    'id' => 'INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT',
                    'siteid' => 'INTEGER NOT NULL',
                    'itemid' => 'VARCHAR(16) NOT NULL',
                    'count' => 'INTEGER NULL',
                    'ts' => 'DATETIME DEFAULT CURRENT_TIMESTAMP NULL',
                ),
                'indexes'=>array(
                    array('siteid', array('siteid')),
                    array('itemid', array('itemid')),
                    array('ts', array('url'), ''),
                ),
            ),
             * 
             */
        ),
    );
    protected $_aCurlopt=array();
        
    /**
     * the current set site ID (search profile)
     * @var integer
     */
    protected $iSiteId = false;

    /**
     * saved config data of a webite profile
     * @var array
     */
    protected $aProfileSaved = array();
    /**
     * effetive config data of a webite profile: saved data merged with the defaults
     * @var array
     */
    protected $aProfileEffective = array();

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
    protected $_oLog = false;
    
    // ----------------------------------------------------------------------

    /**
     * new crawler
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct($iSiteId = false) {

        $this->_oLog=new logger();
        return $this->setSiteId($iSiteId);
    }

    // ----------------------------------------------------------------------
    // OPTIONS + DATA
    // ----------------------------------------------------------------------

    /**
     * get full path of local config file
     * @return string
     */
    protected function _getConfigFile() {
        return dirname(__DIR__) . '/config/crawler.config.json';
    }
    /**
     * return value of a $_POST or $_GET variable if it exists
     * 
     * @param string  $sVarname      name of post or get variable (POST has priority)
     * @param regex   $sRegexMatch   set a regex that must match
     * @param string  $sType         force type: false|int
     * @return type
     */
    protected function _getRequestParam($sVarname, $sRegexMatch=false, $sType=false) {
        $this->logAdd(__METHOD__."($sVarname, $sRegexMatch, $sType) start");
        
        // check if it exist
        if(!isset($_POST[$sVarname]) && !isset($_GET[$sVarname])){
            $this->logAdd(__METHOD__."($sVarname) $sVarname does not exist");
            return false;
        }
        
        // set it to POST or GET variable
        $return = isset($_POST[$sVarname]) && $_POST[$sVarname]
                ? $_POST[$sVarname] 
                : ((isset($_GET[$sVarname]) && $_GET[$sVarname])
                    ? $_GET[$sVarname] 
                    : false
                  )
            ;
            $this->logAdd(__METHOD__."($sVarname, $sRegexMatch, $sType) verify [".print_r($return, 1)."]");
        
        // verify regex
        if ($sRegexMatch && !preg_match($sRegexMatch,$return)){
            $this->logAdd(__METHOD__."($sVarname) $sVarname does not match regex $sRegexMatch");
            return false;
        }
        
        // force given type
        switch ($sType){
            case 'int': 
                $return=(int)$return;
                break;
        }
        $this->logAdd(__METHOD__."($sVarname, $sRegexMatch, $sType) returns $sVarname = [".print_r($return, 1)."]");
        return $return;
    }
    
    /**
     * get fixed array of $aOptions['options']['database'] 
     * @param array  $aDbConfig  $aOptions['options']['database'] 
     * @return array 
     */
    protected function _getRealDbConfig($aDbConfig) {
        // expand sqlite value __DIR__ to [approot]
        if(isset($aDbConfig['database_file'])){
            $aDbConfig['database_file'] = str_replace('__DIR__/', dirname(__DIR__) . '/', $aDbConfig['database_file']);
        }
        return $aDbConfig;
    }

    /**
     * check if the config file exists (used to detect if a setup is required
     * @return boolean
     */
    protected function _configExists(){
        return file_exists($this->_getConfigFile());
    }


    /**
     * load config file with settings and all profiles
     * @return array
     */
    protected function _loadConfigfile($bForce=false) {
        $this->logAdd(__METHOD__.'() start');
        if(!$this->_configExists()){
            return false;
        }
        static $aUserConfig;
        if(isset($aUserConfig) && !$bForce){
            $this->logAdd(__METHOD__.'() use static variable');
            return $aUserConfig;
        }
        $this->logAdd(__METHOD__.'() read from file');
        $aUserConfig = json_decode(file_get_contents($this->_getConfigFile()), true);
        if (!$aUserConfig || !is_array($aUserConfig) || !count($aUserConfig)) {
            die("ERROR: json file is invalid. Aborting");
        }        
        if (!array_key_exists('options', $aUserConfig)) {
            die("ERROR: config requires a section [options].");
        }
        if (!array_key_exists('database', $aUserConfig['options'])) {
            die("ERROR: config requires a database definition.");
        }
        return $aUserConfig;
    }
    /**
     * save options array
     * @see backend/pages/setup.php + profiles.php
     * @return boolean
     */
    protected function _saveConfig($aNewData) {
        $aUserconfig=$this->_loadConfigfile();
        $aNewData=array(
            'options'=>(isset($aNewData['options']) && is_array($aNewData['options']) && count($aNewData['options']) 
                        ? $aNewData['options'] 
                        : $this->getEffectiveOptions()
            ),
            'profiles'=>(isset($aNewData['profiles']) && is_array($aNewData['profiles']) && count($aNewData['profiles']) 
                        ? $aNewData['profiles'] 
                        : $aUserconfig['profiles']
            ),
        );
        // echo '<pre>'.print_r($aNewData, 1).'</pre>'; die("ABORT in ". __METHOD__);
        $sCfgfile=$this->_getConfigFile();
        $sBakfile=$sCfgfile.'.bak';
        if(file_exists($sCfgfile)){
            copy($sCfgfile, $sBakfile);
        }
        if (file_put_contents($sCfgfile, json_encode($aNewData, JSON_PRETTY_PRINT))){
            return true;
        }
        return false;
    }
    
    /**
     * helper function: find a value in a structured hash by giving
     * the structure with a string using the divider "."
     * the subkeys can contain letters a-z, A-Z and numbers
     * 
     * $foundVar=&$this->_getArrayValueByKeysAsString($aItem, $sKey);
     * 
     * @param array   $aItem  array to scan
     * @param string  $sKey   
     * @return type
     */
    public function &getArrayValueByKeysAsString(&$aItem, $sKey=false) {
        $sDivider='\.';
        if(!isset($aItem)){
            return NULL;
        }
        if($sKey){
            $sFirstKey= preg_replace('/'.$sDivider.'.*/', '', $sKey);
            if(!isset($aItem[$sFirstKey])){
                return NULL;
            }
            $sLeftkeys=str_replace($sFirstKey, '', preg_replace('/^[a-z0-9]*\./i', '', $sKey));
            if($sLeftkeys){
                return $this->getArrayValueByKeysAsString($aItem[$sFirstKey], $sLeftkeys);
            }
            return $this->getArrayValueByKeysAsString($aItem[$sFirstKey]);
        }
        return $aItem;
    }
    /**
     * helper make a config item integer or set it false
     * @see backend/pages/setup.php + profiles.php
     * 
     * @param array  $aItem  config item (global config or specific config item)
     * @param string $sKey   optional key sequence with "." as delimiter
     * @return boolean
     */
    protected function _configMakeInt(&$aItem, $sKey=false) {
        $foundVar=&$this->getArrayValueByKeysAsString($aItem, $sKey);
        if($foundVar===NULL){
            return false;
        }
        $foundVar=(int)$foundVar ? (int)$foundVar/1 : ($foundVar==="0" ? 0 : false);
        return true;
    }
    /**
     * save options array
     * @see backend/pages/setup.php + profiles.php
     * @return boolean
    protected function _saveConfigVerify($aNewData) {
        
        return false;
    }
     */
    
    /**
     * check if httpd v2 is available in PHP and curl lib
     * @return boolean
     */
    protected function _getCurlCanHttp2(){
        if (!defined('CURL_VERSION_HTTP2')){
           return false;
        }
        $aVers=curl_version();
        return ($aVers["features"] & CURL_VERSION_HTTP2) !== 0;        
    }


    protected function _getCurlOptions(){
        $aReturn=array(
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $this->aOptions['crawler']['userAgent'],
            CURLOPT_USERPWD => array_key_exists('userpwd', $this->aProfileEffective) ? $this->aProfileEffective['userpwd']:false,
            CURLOPT_VERBOSE => false,
            CURLOPT_ENCODING => '',  // to fetch encoding

            // TODO: this is unsafe .. better: let the user configure it
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            // CURLOPT_SSL_VERIFYSTATUS => false,
            // v0.22 cookies
            CURLOPT_COOKIEJAR => $this->sCcookieFilename,
            CURLOPT_COOKIEFILE => $this->sCcookieFilename,
            CURLOPT_TIMEOUT => 10,
        );
        if($this->_getCurlCanHttp2()){
            $aReturn[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_2_0;
        }
        return $aReturn;
    }
    
    /**
     * make an http GET request
     * @param string $sUrl  any url
     * @return array
     */
    private function _getSingleCurlrequest($sUrl) {
        // $this->cliprint('info', "INFO: respect the ROBOTS.TXT: reading $urlRobots\n");
        $rollingCurl = new \RollingCurl\RollingCurl();
        $self = $this;
        $rollingCurl->setOptions($this->_getCurlOptions())
            ->get($sUrl)
            ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use ($self) {
                $self->addExcludesFromRobotsTxt($request->getResponseText());
            })
            ->execute()    
            ;
        return true;
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
                    'AUTOINCREMENT' => 'AUTO_INCREMENT',
                    'DATETIME' => 'TIMESTAMP',
                    'INTEGER' => 'INT',
                    // 'TEXT' => 'LONGTEXT',
                    
                    'createAppend' => 'CHARACTER SET utf8 COLLATE utf8_general_ci',
                    
                    'canIndex' => true,
                    'canIndexUNIQUE' => true,
                    'canIndexFULLTEXT' => false,
                    'canIndexSPACIAL' => false,
                );
                break;
            case 'sqlite':
                $aReturn = array(
                    'createAppend' => '',
                    
                    'canIndex' => true,
                    'canIndexUNIQUE' => true,
                    'canIndexFULLTEXT' => false,
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
     */
    private function _createTable() {
        $sql = '';

        // get replacements and db type specific stuff
        $aDb = $this->_getPdoDbSpecialties();
        
        foreach($this->_aDbSettings['tables'] as $sTable=>$aSettings){
            $sqlTable = '';
            $sqlIndex = '';
        
            // ----- columns
            foreach ($aSettings['columns'] as $field => $type) {
                $sColumnType= str_replace(array_keys($aDb), array_values($aDb), $type);
                $sqlTable .= ($sqlTable ? ",\n" : '')
                        ."    <$field> ${sColumnType}";
            }
            $sql = "CREATE TABLE IF NOT EXISTS <${sTable}> (\n" . $sqlTable . "\n)\n" . $aDb['createAppend'];
            if (!$this->oDB->query($sql)) {
                echo 'DATABASE - CREATION OF TABLES FAILED :-/<pre>'
                        . 'sql: '.$this->oDB->last()."\n"
                        . 'pre generated code for medoo:<br>'.htmlentities($sql) . "\n"
                    . '</pre><br>'
                    .print_r($this->oDB->error(), 1)
                    ;
                die();
            }
            // /columns
            
            // ----- indexes
            if (isset($aSettings['indexes']) ){
                $sqlTable.="\n";
                foreach ($aSettings['indexes'] as $aIndexItems) {
                    
                    // 0 - {string}  name of index (without prefix for table)
                    // 1 - {array}   list of columns
                    // 2 - {string}  index type; one of none or [UNIQUE | FULLTEXT | SPATIAL]
                    $sIndexId='IDX_'.$sTable.'_'.$aIndexItems[0];
                    $sIndexType=(isset($aIndexItems[2]) && $aIndexItems[2] && $aDb['canIndex'.$aIndexItems[2]] ? $aIndexItems[2] . ' ' : '');
                    $sqlIndex=''
                            . 'CREATE '.$sIndexType.'INDEX IF NOT EXISTS '.'<'.$sIndexId.'> '
                            . 'ON <'.$sTable.'> '
                            . '( <'.implode('>, <', $aIndexItems[1]).'> )'
                            ;
                    if (!$this->oDB->query($sqlIndex)) {
                        echo 'DATABASE - CREATION OF INDEX FAILED :-/<pre>'
                                . 'sql: '.$this->oDB->last()."\n\n"
                                . 'pre generated code for medoo:<br>'.htmlentities($sqlIndex) . "\n"
                            . '</pre><br>'
                            .print_r($this->oDB->error(), 1)
                            ;
                        die();
                    }
                }
            }
            // /indexes

        }
        return true;
    }

    /**
     * init database 
     * @param type $aOptions
     */
    private function _initDB() {
        $this->logAdd(__METHOD__.'() start ... init with options <pre>'.print_r($this->aOptions['database'],1).'</pre>');
        try{
            // $this->oDB = new Medoo\Medoo($this->aOptions['database']);
            $this->oDB = new Medoo\Medoo($this->_getRealDbConfig($this->aOptions['database']));
        } catch (Exception $ex) {
            $this->logAdd(__METHOD__.'() ERROR: the database could not be connected. Maybe the initial settings are wrong or the database is offline.', 'error');
            $this->oDB = false;
            return false;
            // die('ERROR: the database could not be connected. Maybe the initial settings are wrong or the database is offline.');
        }
        // if (!$this->_checkDbResult()) {
        if (!$this->oDB) {
            die('ERROR: the database could not be connected. Maybe the initial settings are wrong or the database is offline.');
        }
        $this->logAdd(__METHOD__.'() itialized');
        $this->logAdd(__METHOD__.'() databases is connected');
        // TODO: put creation of tables into setup/ update
        $this->_createTable();
        $this->logAdd(__METHOD__.'() databases tables were checked');

    }

    /**
     * check the status of the last database action and detect if an error occured.
     * @param array  $aResult  result of database query (used with enabled debug only)
     * @return boolean
     */
    protected function _checkDbResult($aResult = false) {
        $aErr = $this->oDB->error();
        if ($aErr[1]) {
            echo "!!! Database error detected :-/<br>\n";
            if ($this->aOptions['debug']) {
                $this->logAdd(''
                    . '... DB-QUERY : ' . $this->oDB->last() . "\n"
                    . ($aResult ? '... DB-RESULT: ' . print_r($aResult, 1) . "\n" : '')
                    . '... DB-ERROR: ' . print_r($this->oDB->error(), 1) . "\n"
                )
                ;
                sleep(3);
            }
            return false;
        } elseif ($this->aOptions['debug']) {
            $this->logAdd('... OK: DB-QUERY : ' . substr($this->oDB->last(), 0, 200) . " [...]");
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
     * get available languages for that exist language files
     * @param string  $sTarget  target; one of backend|frontend; default is backend
     * @return array
     */
    public function getLanguages($sTarget='backend') {
        $aReturn=array();
        foreach(glob(dirname(__DIR__).'/lang/'.$sTarget.'.*.json') as $sJsonfile){
            $aData = json_decode(file_get_contents($sJsonfile), true);
            if(isset($aData['id'])){
                $sKey2=str_replace($sTarget.'.','',basename($sJsonfile));
                $sKey2=str_replace('.json','',$sKey2);
                $aReturn[$sKey2]=$aData['id'] ? $aData['id'] : $sKey2;
            }
        }
        return $aReturn;
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
     * @param type    $aItems  array with these keys as flags:
     *                           searchindex => true|false
     *                           ressources => true|false
     *                           searches => true|false
     *                           all => true|false - means:searchindex + ressources
     *                           full => true|false - means:searchindex + ressources + searches
     * @param integer $iSiteId  optional: id of a profile; 
     *                           default: false (drop tables for all profiles)
     *                           integer: empty values in a table with this id
     * @return boolean
     */
    public function flushData($aItems, $iSiteId=false) {
        $aTables = array();
        $bAll = isset($aItems['all']);
        $bFull = isset($aItems['full']);
        if ($bFull || $bAll || (array_key_exists('searchindex', $aItems) && $aItems['searchindex'])) {
            $aTables[] = 'pages';
            $aTables[] = 'words';
        }
        if ($bFull || $bAll || (array_key_exists('ressources', $aItems) && $aItems['ressources'])) {
            $aTables[] = 'ressources';
            $aTables[] = 'ressources_rel';
        }
        if ($bFull || array_key_exists('searches', $aItems) && $aItems['searches']) {
            $aTables[] = 'search';
        }
        if (count($aTables)) {
            foreach ($aTables as $sTable) {
                
                $sql = (int)$iSiteId 
                        ? "DELETE FROM <".$sTable."> WHERE siteid=".(int)$iSiteId .";"
                        : "DROP TABLE IF EXISTS <".$sTable.">;"
                        ;
                // for CLI output
                echo "DEBUG: $sql\n";
                if (!$this->oDB->query($sql)) {
                    echo "Failed!!\n";
                    var_dump($this->oDB->error(), 1);
                    die();
                }
            }
        }
        // echo "flushing was successful.\n";
        return true;
    }

    /**
     * add a log message for debug output
     * @param string  $sMessage  message text
     * @param strin   $sLevel    one of ok|info|warning|error
     * @return boolean
     */
    public function logAdd($sMessage, $sLevel = "info"){
        if($this->_oLog){
            if(php_sapi_name()==='cli'){
                echo $sMyMsg."\n";
            }
            return $this->_oLog->add($sMessage, $sLevel);
        }
        return false;
    }
    
    /**
     * render debug log output (visible if debugging is enabled only)
     * @return boolean
     */
    public function logRender(){
        $aOptions = $this->_loadConfigfile();
        if($this->_oLog && isset($aOptions['options']['debug']) && $aOptions['options']['debug']){
            return '<div style="position: absolute; left: 20em; top: 1000em;">'.$this->_oLog->render().'</div>';
        }
        return false;
    }

    /**
     * set the id of the active project
     * This method loads the profile too
     * 
     * @param integer $iSiteId
     */
    public function setSiteId($iSiteId = false) {
        $this->logAdd(__METHOD__.'('.$iSiteId.') start');

        $this->iSiteId = false;
        $this->aProfileSaved = array();
        $this->aDefaultOptions['crawler']['userAgent']=$this->aAbout['product'] . ' ' . $this->aAbout['version'] . ' (GNU GPL crawler and linkchecker for your website; ' . $this->aAbout['urlDocs'] . ')';
        
        $aOptions = $this->_loadConfigfile();

        $this->getEffectiveOptions($aOptions);
        
        // $this->sLang = (array_key_exists('lang', $this->aOptions)) ? $this->sLang = $this->aOptions['lang'] : $this->sLang;
        $this->sLang=$this->_getRequestParam('lang') ? $this->_getRequestParam('lang') : $this->aOptions['lang'];


        // curl options:
        // $aDefaultOptions['crawler']['userAgent']=$this->aAbout['product'] . ' ' . $this->aAbout['version'] . ' (GNU GPL crawler and linkchecker for your website; ' . $this->aAbout['urlHome'] . ')';
        $this->sUserAgent = $this->aAbout['product'] . ' ' . $this->aAbout['version'] . ' (GNU GPL crawler and linkchecker for your website; ' . $this->aAbout['urlHome'] . ')';
        
        $this->_initDB();

        if ($iSiteId && isset($aOptions['profiles'][$iSiteId])) {
            $this->iSiteId = $iSiteId;
            $this->aProfileSaved = $aOptions['profiles'][$iSiteId];

            // @since v0.22 
            $this->sCcookieFilename = dirname(__DIR__).'/data/cookiefile-siteid-'.$iSiteId.'.txt';
            
        } else {
            $this->aProfileSaved = array();
        }
        $this->getEffectiveProfile($iSiteId);
        return true;
    }

    /**
     * get a flat array with ids of all existing profiles
     * @return array
     */
    public function getProfileIds() {
        $aOptions = $this->_loadConfigfile();
        if (
                is_array($aOptions) 
                && array_key_exists('profiles', $aOptions)
                && is_array($aOptions['profiles']) 
        ) {
            return array_keys($aOptions['profiles']);
        }
        return false;
    }
    
    /**
     * get pogram settings based on internal defaults and merge it loaded config
     * used in setSiteId() after initializing $this->aProfileSaved
     * 
     * @param array  $aOptions  array of loaded options file; if false or mising it will be loaded
     * @return type
     */
    public function getEffectiveOptions($aOptions=false) {
        $this->logAdd(__METHOD__.'() start');
        if(!$aOptions){
            $aOptions = $this->_loadConfigfile();
        }
        
        // builtin default options ... these will be overrided with crawler.config.json
        if (isset($aOptions['options']) && array_key_exists('options', $aOptions)) {
            // $this->aOptions = array_merge($this->aOptions, $aOptions['options']);
            $this->aOptions = $aOptions['options'];
        }
        
        // builtin default options ... these will be overrided with crawler.config.json
        if (isset($aOptions['options']) && array_key_exists('options', $aOptions)) {
            $this->aOptions = array_merge($this->aDefaultOptions, $aOptions['options']);
        }
        
        // if there are new defaults that aren't in the config file of an older version yet
        foreach($this->aDefaultOptions as $sKey1=>$data1){
            $this->aOptions[$sKey1]=isset($this->aOptions[$sKey1]) ? $this->aOptions[$sKey1] : $data1;
            if (is_array($data1)){
                foreach($data1 as $sKey2=>$data2){
                        $this->aOptions[$sKey1][$sKey2]=isset($this->aOptions[$sKey1][$sKey2]) ? $this->aOptions[$sKey1][$sKey2] : $data2;
                    }
                }
            
        }
        $this->aOptions['crawler']['memoryLimit']=isset($this->aOptions['crawler']['memoryLimit']) && $this->aOptions['crawler']['memoryLimit']
            ? $this->aOptions['crawler']['memoryLimit']
            : $this->aDefaultOptions['crawler']['memoryLimit']
        ;
        $this->aOptions['crawler']['userAgent']=isset($this->aOptions['crawler']['userAgent']) && $this->aOptions['crawler']['userAgent']
            ? $this->aOptions['crawler']['userAgent']
            : $this->aDefaultOptions['crawler']['userAgent']
        ;
        
        foreach(array('matchWord', 'WordStart', 'any') as $sMatchSection){
            foreach(array('title', 'keywords', 'description', 'url', 'content') as $sMatchField){
                $this->aOptions['searchindex']['rankingWeights'][$sMatchSection][$sMatchField]=isset($this->aOptions['searchindex']['rankingWeights'][$sMatchSection][$sMatchField]) && $this->aOptions['searchindex']['rankingWeights'][$sMatchSection][$sMatchField]
                        ? $this->aOptions['searchindex']['rankingWeights'][$sMatchSection][$sMatchField]
                        : $this->aDefaultOptions['searchindex']['rankingWeights'][$sMatchSection][$sMatchField]
                        ;
            }
        }
        
        /*
        echo '<pre>aDefaultOptions = '. htmlentities(print_r($this->aDefaultOptions, 1)).'</pre><hr>';
        echo '<pre>aOptions = '. htmlentities(print_r($this->aOptions, 1)).'</pre>';
        die();
         */
        return $this->aOptions;
    }
    /**
     * get profile for given SiteId tht is merged by defauls and loaded 
     * profile settings
     * loaded in setSiteId() after initializing $this->aProfileSaved
     * 
     */
    public function getEffectiveProfile() {
        $this->logAdd(__METHOD__.'() start');
        // $aOptions = $this->_loadOptions();
        // $iSiteId = $iSiteId ? $iSiteId : $this->iSiteId;
        $iSiteId = $this->iSiteId;
        $aProfile = $this->aProfileSaved;

        $aReturn=$this->aProfileDefault;
        if ($iSiteId && isset($aProfile)) {
            $this->iSiteId = $iSiteId;

            // merge defaults with user settings for this profile 
            foreach(array_keys($this->aProfileDefault) as $sKey0){
                if (!is_array($aReturn[$sKey0])){
                    $aReturn[$sKey0] = array_key_exists($sKey0, $aProfile) ? $aProfile[$sKey0] : $this->aProfileDefault[$sKey0];
                } else {
                    $aReturn[$sKey0]=array_key_exists($sKey0, $aProfile) ? array_merge($this->aProfileDefault[$sKey0], $aProfile[$sKey0]) : $this->aProfileDefault[$sKey0];
                }
            }

            if (!isset($aReturn['searchindex']['includepath']) || !is_array($aReturn['searchindex']['includepath']) || !count($aReturn['searchindex']['includepath'])) {
                $aReturn['searchindex']['includepath'][] = '.*';
            }
            if (!isset($aReturn['searchindex']['exclude']) || !is_array($aReturn['searchindex']['exclude'])){
                $aReturn['searchindex']['exclude']=array();
            } 
            if (!isset($aReturn['searchindex']['simultanousRequests']) || $aReturn['searchindex']['simultanousRequests']==false ) {
                $aReturn['searchindex']['simultanousRequests'] = $this->aOptions['crawler']['searchindex']['simultanousRequests'];
            }
            
            if (!isset($aReturn['searchindex']['regexToRemove']) 
                    || $aReturn['searchindex']['regexToRemove']==false 
                    || !is_array($aReturn['searchindex']['regexToRemove'])
                    || !count($aReturn['searchindex']['regexToRemove'])
            ) {
                $aReturn['searchindex']['regexToRemove'] = $this->aOptions['searchindex']['regexToRemove'];
            }
            if (!isset($aReturn['ressources']['simultanousRequests']) || $aReturn['ressources']['simultanousRequests']==false ) {
                $aReturn['ressources']['simultanousRequests'] = $this->aOptions['crawler']['ressources']['simultanousRequests'];
            }
            
        }
        // detect sticky domains for content crawling
        $aIncludeurls=array();
        $aReturn['searchindex']['_vhosts']=array();
        if(count($aReturn['searchindex']['urls2crawl'])){
            foreach($aReturn['searchindex']['urls2crawl'] as $sMyUrl){
                $sKeepUrl='^'.preg_replace('#(http.*//.*)/(.*)$#U', '$1', $sMyUrl).'.*';
                
                // remove user and pw from https://myuser:password@examle.com
                if(strstr($sKeepUrl, '@')){
                    $sKeepUrl2=preg_replace('#(http.*//)(.*)@(.*)$#U', '$1.*@$3', $sKeepUrl);
                    $sKeepUrl=preg_replace('#(http.*//)(.*)@(.*)$#U', '$1$3', $sKeepUrl);
                    $aIncludeurls[$sKeepUrl2]=true;
                }
                $aIncludeurls[$sKeepUrl]=true;
            }
            if(count($aIncludeurls)){
                foreach(array_keys($aIncludeurls) as $sMyUrl){
                    $aReturn['searchindex']['_vhosts'][]=$sMyUrl;
                }
            }
        }
        $this->logAdd(__METHOD__.'() profile defaults<pre>'.print_r($this->aProfileDefault,1).'</pre>');
        $this->logAdd(__METHOD__.'() saved profile data<pre>'.print_r($aProfile,1).'</pre>');
        $this->logAdd(__METHOD__.'() merged effective profile<pre>'.print_r($aReturn,1).'</pre>');
        $this->aProfileEffective=$aReturn;
        return $aReturn;
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
     * return boolean if an initial setup was done
     * @return boolean
     */
    public function installationWasDone(){
        return ($this->_configExists());
    }
    
    /**
     * get language specific text of backend
     * @param string    $sId     id of a text
     * @return string
     */
    public function lB($sId, $sAltId = false) {
        return $this->getTxt('backend', $sId, $sAltId);
    }

    /**
     * get language specific text of frontend
     * @param string    $sId     id of a text
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
            $this->clicolor('error');
            $oStatus->showStatus();
            $this->cliprint('error', "ABORT: the action is still running (".__METHOD__.")\n");
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

    /**
     * 
     * @param type $sMessage
     */
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
    
    // ----------------------------------------------------------------------
    // COLORS n CLI mode
    // ----------------------------------------------------------------------

    /**
     * print a colored text but on cli only; after the output the color will be switched to 'cli'
     * @param string  $sColor    color key; one of head|input|cli|ok|info|warning|error
     * @param string  $sMessage  string to show
     * Description
     */
    public function clicolor($sColor){
        $this->cliprint($sColor, '', '');
        return true;
    }
    /**
     * print a colored text but on cli only; after the output the color will be 
     * switched to 'cli' or a given color code.
     * 
     * @param string  $sColor      color key; one of head|input|cli|ok|info|warning|error
     * @param string  $sMessage    string to show
     * @param string  $sNextColor  color key after printing message; default is 'cli'
     * Description
     */
    public function cliprint($sColor, $sMessage='', $sNextColor='cli'){
        static $oCli;
        if (php_sapi_name() !== "cli" && php_sapi_name() !== "cgi-fcgi") {
            return false;
        }
        if(!$oCli){
            require_once 'cli.class.php';
            $oCli=new axelhahn\cli();
        }
        if($sMessage){
            $oCli->color($sColor, $sMessage);
        }
        if($sNextColor){
            $oCli->color($sNextColor);
        }
        return true;
    }

}
