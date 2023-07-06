<?php

require_once __DIR__ . '/../vendor/medoo/src/Medoo.php';
require_once 'status.class.php';
require_once 'logger.class.php';
require_once 'httpheader.class.php';

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
 * AXLES CRAWLER BASE CLASS
 * 
 * */
class crawler_base {

    public $aAbout = array(
        'product' => 'ahCrawler',
        'version' => '0.163',
        'date' => '2023-07-06',
        'author' => 'Axel Hahn',
        'license' => 'GNU GPL 3.0',
        'urlHome' => 'https://www.axel-hahn.de/ahcrawler',
        'urlDocs' => 'https://www.axel-hahn.de/docs/ahcrawler/',
        'urlSource' => 'https://github.com/axelhahn/ahcrawler',
        'requirements' => array(
            'phpversion'=>'7.3',
            'phpextensions'=>array('curl', 'PDO','xml','zip')
        ),
        
        // contributors @since v0.136
        'thanks' => array(
            'testing'=>array(
                array(
                    'label'=>'', 
                    'name'=>'Roberto José de Amorim', 
                    'image'=>'', 
                    'url'=>''
                ),
            ),
            'translation'=>array(
                array(
                    'label'=>'Russian', 
                    'name'=>'Artone Ozhiganov', 
                    'image'=>'', 
                    'url'=>'https://github.com/Ozhiganov'
                ),
            ),
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
            'error' => PDO::ERRMODE_EXCEPTION,
        ),
        'auth' => array(
        ),
        'cache' => false,
        'debug' => false,
        'lang' => 'en',
        
        // since v0.146
        'output' => array(
            'customfooter' => array(),
        ),
        
        // see backend.class
        'menu' => array(),
        'menu-public' => array(),
        
        'crawler' => array(
            'timeout' => 10,
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
            'ignoreNoindex' => false,
            'regexToRemove' => array(
                // '<!--googleoff\:\ index-->.*?<!--googleon\:\ index-->',
                // '<!--sphider_noindex-->.*?<!--/sphider_noindex-->',
                // '<!--.*?-->',
                // '<link rel[^<>]*>',
                '<footer[^>]*>.*?</footer>',
                // The <header> element represents a container for introductory content or a set of navigational links.
                // '<header[^>]*>.*?</header>',
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
            // TODO: 
            // - remove unwanted files and dirs
            // - hmmm ... and what about database upgrades?
            'toremove' => array(
                'files' => array(
                    'backend/pages/search.php',
                ),
                'dirs' => array(
                    
                ),
            )
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
            'blacklist' => array(),
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
     * remark http response header can be several kB --> set to MEDIUMTEXT
     * see https://stackoverflow.com/questions/686217/maximum-on-http-header-values
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
                    'title_wc' => 'INTEGER NULL',
                    'description' => 'VARCHAR(1024)  NULL',
                    'description_wc' => 'INTEGER NULL',
                    'keywords' => 'VARCHAR(1024)  NULL',
                    'keywords_wc' => 'INTEGER NULL',
                    'lang' => 'VARCHAR(8) NULL',
                    'size' => 'INTEGER NULL',
                    'time' => 'INTEGER NULL',
                    'content' => 'MEDIUMTEXT',
                    'header' => 'MEDIUMTEXT',
                    'response' => 'MEDIUMTEXT',
                    'ts' => 'DATETIME DEFAULT CURRENT_TIMESTAMP NULL',
                    'tserror' => 'DATETIME NULL',
                    'errorcount' => 'INTEGER NULL',
                    'lasterror' => 'MEDIUMTEXT',
                ),
                'indexes'=>array(
                    // PRIMARY KEY (`id`),
                    // INDEX `pages_siteid_IDX` (`siteid`) USING BTREE,
                    // FULLTEXT INDEX `pages_url_IDX` (`url`, `title`, `description`, `keywords`, `lang`, `content`)
                    // array('PRIMARY KEY', '', array('id')),
                    array('siteid', array('siteid')),
                    array('url',    array('url')),

                    // it won't be created on php7 ... and crashes on php8
                    // array('search', array('url', 'title', 'description', 'keywords', 'lang', 'content'), 'FULLTEXT'),
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
                    'header' => 'MEDIUMTEXT',
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
                    'lasterror' => 'MEDIUMTEXT',
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

            'counters' => array(
                'columns'=>array(
                    // 'id' => 'VARCHAR(32) NOT NULL PRIMARY KEY',
                    'id' => 'INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT',
                    'siteid' => 'INTEGER NOT NULL',
                    'counterid' => 'INTEGER NOT NULL', // ref id to counteritems table
                    'value' => 'MEDIUMTEXT',
                    'ts' => 'DATETIME DEFAULT CURRENT_TIMESTAMP NULL',
                ),
                'indexes'=>array(
                    array('siteid', array('siteid')),
                    array('counterid', array('counterid')),
                    array('ts', array('ts')),
                ),
            ),
            'counteritems' => array(
                'columns'=>array(
                    // 'id' => 'VARCHAR(32) NOT NULL PRIMARY KEY',
                    'id' => 'INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT',
                    'label' => 'VARCHAR(32)',
                ),
                'indexes'=>array(
                    array('label', array('label')),
                ),
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
     * @var array 
     */
    protected $aLang = array();

    /**
     * user agent for the crawler 
     * @var string 
     */
    protected $sUserAgent = false;

    protected $sCookieFilename = false;
    
    /**
     * filename of indexer log actions
     * @var string
     */
    protected $sLogFilename = false;
    protected $_oLog = false;
    
    /**
     * data in lock file during running indexer
     * @var array
     */
    protected $aStatus = [];

    // ----------------------------------------------------------------------

    /**
     * new crawler
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct($iSiteId = false) {

        $this->logAdd(__METHOD__.'('.htmlentities($iSiteId).') start');
        $this->_oLog=new logger();
        $this->logAdd(__METHOD__.'('.htmlentities($iSiteId).') logger was initialized');
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
     * @param string  $sRegexMatch   set a regex that must match
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
        
        // v0.149: upgrade medoo to 2.x
        if(isset($aDbConfig['database_type'])){
            $aDbConfig['type']=$aDbConfig['database_type'];
        }        
        if($aDbConfig['type']==='sqlite'){
            $aDbConfig['database']=$aDbConfig['database_file'];
        } else {
            $aDbConfig['database']=$aDbConfig['database_name'];
        }

        if(isset($aDbConfig['server'])){
            $aDbConfig['host']=$aDbConfig['server'];
        }
        // / v0.149: upgrade medoo to 2.x
        
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
        // $aUserConfig = json_decode(include($this->_getConfigFile()), true);
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


    /**
     * get html code for a progressbar during running resouce scan
     * @param  integer  $iUrlsTotal  count of urls total (=100%)
     * @param  integer  $iUrlsLeft   count of urls left
     * @return string
     */
    protected function _getStatus_urls_left($iUrlsTotal, $iUrlsLeft=false){
        $iPercent=100-round($iUrlsLeft*100/$iUrlsTotal);
        return '<div class="bar"><div class="progress" style="width: '.$iPercent.'%;"></div>'
        .$iPercent . '%: ' .$iUrlsLeft . '  of '.$iUrlsTotal.' urls left'
        .'</div> '
        ;
    }

    /**
     * get array with curl default options
     */
    protected function _getCurlOptions(){
        $aReturn=array(
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $this->aOptions['crawler']['userAgent'],
            // CURLOPT_USERPWD => isset($this->aProfileEffective['userpwd']) ? $this->aProfileEffective['userpwd'] : false,
            CURLOPT_VERBOSE => false,
            CURLOPT_ENCODING => 'gzip, deflate',  // to fetch encoding
            CURLOPT_HTTPHEADER => array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en',
                'DNT: 1',
            ),

            // TODO: this is unsafe .. better: let the user configure it
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            // CURLOPT_SSL_VERIFYSTATUS => false,
            // v0.22 cookies
            CURLOPT_COOKIEJAR => $this->sCookieFilename,
            CURLOPT_COOKIEFILE => $this->sCookieFilename,
            CURLOPT_TIMEOUT => $this->aOptions['crawler']['timeout'],
        );
        if($this->_getCurlCanHttp2()){
            $aReturn[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_2_0;
        }
        return $aReturn;
    }
    
    /**
     * make a single http(s) get request and return the response body
     * @param string   $url          url to fetch
     * @param boolean  $bHeaderOnly  optional: true=make HEAD request; default: false (=GET)
     * @return string
     */
    public function httpGet($url, $bHeaderOnly = false) {
        $ch = curl_init($url);
        foreach ($this->_getCurlOptions() as $sCurlOption=>$sCurlValue){
            curl_setopt($ch, $sCurlOption, $sCurlValue);
        }
        if ($bHeaderOnly) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        return ($res);
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
                        ."    <$field> {$sColumnType}";
            }
            $sql = "CREATE TABLE IF NOT EXISTS <{$sTable}> (\n" . $sqlTable . "\n)\n" . $aDb['createAppend'];
            if (!$this->oDB->query($sql)) {
                echo 'DATABASE - CREATION OF TABLES FAILED :-/<pre>'
                        . 'sql: '.$this->oDB->last()."\n"
                        . 'pre generated code for medoo:<br>'.htmlentities($sql) . "\n"
                    . '</pre><br>'
                    .print_r($this->oDB->error, 1)
                    .print_r($this->oDB->errorInfo, 1)
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
                    try{
                        if (!$this->oDB->query($sqlIndex)) {
                            /*
                            echo 'DATABASE - CREATION OF INDEX FAILED :-/<pre>'
                                    . 'sql: '.$this->oDB->last()."\n\n"
                                    . 'pre generated code for medoo:<br>'.htmlentities($sqlIndex) . "\n"
                                . '</pre><br>'
                                .print_r($this->oDB->error, 1)
                                .print_r($this->oDB->errorInfo, 1)
                                ;
                            die();
                            * 
                            */
                        }
                    } catch (Exception $ex) {
                        $this->logAdd(__METHOD__.'() ERROR: CREATION OF INDEX FAILED<pre>'
                                . 'sql: '.$this->oDB->last()."\n\n"
                                . 'pre generated code for medoo:<br>'.htmlentities($sqlIndex) . "\n"
                            . '</pre><br>'
                            .print_r($this->oDB->error, 1)
                            .print_r($this->oDB->errorInfo, 1)
                        )
                        ;
                        // return false;
                    }

                }
            }
            // /indexes

        }
        return true;
    }

    /**
     * init database 
     */
    private function _initDB() {
        $this->logAdd(__METHOD__.'() start ... init with options <pre>'.print_r($this->aOptions['database'],1).'</pre>');
        try{
            // $this->oDB = new Medoo\Medoo($this->aOptions['database']);
            $this->oDB = new Medoo\Medoo($this->_getRealDbConfig($this->aOptions['database']));
        } catch (Exception $ex) {
            $this->logAdd(__METHOD__.'() ERROR: the database could not be connected. Maybe the initial settings are wrong or the database is offline.', 'error');
            // $this->oDB = false;
            $this->oDB = false;
            echo 'CRITICAL ERROR: Unable to connect the database. Maybe the initial settings / credentials are wrong or the database is offline.<br><br>';
            // die('ERROR: the database could not be connected. Maybe the initial settings are wrong or the database is offline.');
            return false;
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
        $aErr = $this->oDB->errorInfo;
        $iMaxLenOfQuery=1000;
        if (isset($aErr[1])) {
            echo "!!! Database error detected :-/<br>\n";
            if ($this->aOptions['debug']) {
                $this->logAdd(''
                    . '... DB-QUERY : ' . substr($this->oDB->last(), 0, $iMaxLenOfQuery) . "\n"
                    . ($aResult ? '... DB-RESULT: ' . print_r($aResult, 1) . "\n" : '')
                    . '... DB-ERROR: ' . print_r($this->oDB->error, 1) . print_r($this->oDB->errorInfo, 1) . "\n"
                )
                ;
                echo ''
                    . '... DB-QUERY : ' . substr($this->oDB->last(), 0, $iMaxLenOfQuery) . "\n"
                    . ($aResult ? '... DB-RESULT: ' . print_r($aResult, 1) . "\n" : '')
                    . '... DB-ERROR: ' . print_r($this->oDB->error, 1) . print_r($this->oDB->errorInfo, 1) . "\n"
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
                $sWhere .= ($sWhere ? 'AND ' : '');
                if(is_array($value)){
                    $sValueitems='';
                    foreach($value as $singlevalue){
                        $sValueitems .= ($sValueitems ? 'OR ' : '')
                            . $sColumn . ' ' . ( $singlevalue === "NULL" ? 'IS NULL' : '=' . $this->oDB->quote($singlevalue)) . ' ';
                    }
                    $sWhere .= '( '.$sValueitems.'  ) ';
                } else {
                    $sWhere .= $sColumn . ' ' . ( $value === "NULL" ? 'IS NULL' : '=' . $this->oDB->quote($value)) . ' ';
                }
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
     * get id of corresponding table for the same url
     * @param  string  $sUrl    url to search for
     * @param  string  $sTable
     * @return integer
     */
    public function getIdsByUrl($sUrl, $sTable){
        $iReturn=0;
        $sSql = "SELECT id FROM $sTable WHERE url = '$sUrl'";
        $aData = $this->oDB->query($sSql)->fetchAll(PDO::FETCH_ASSOC);
        return isset($aData[0]['id']) ? $aData[0]['id'] : false;
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
     * @return string
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
     * @return integer
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
         * html check - get count pages with too short element
         * used in crawler.base.class and page htmlchecks (needs to be public)
         * @param string   $sKey        name of item; one of title|description|keywords
         * @param integer  $iMinLength  minimal length
         * @return integer
         */
        public function getHtmlchecksCount($sKey, $iMinLength){
            if(!$this->iSiteId){
                echo __METHOD__.' Warning: iSiteId is not set.'.PHP_EOL;
                return false;
            }
            $sSql='select count(*) count 
            from pages 
            where 
            siteid='.$this->iSiteId.' and errorcount=0 and length('.$sKey.')<'.$iMinLength;
            // echo "DEBUG: ".__METHOD__." - sql=$sSql<br>";
            $aTmp = $this->oDB->query($sSql)->fetchAll(PDO::FETCH_ASSOC);
            return isset($aTmp[0]['count']) ? $aTmp[0]['count'] : false;
        }
        /**
         * html check - get pages with too large values
         * @param string   $sKey    name of item; one of size|time
         * @param integer  $iMax    max value
         * @return integer
         */
        private function _getHtmlchecksLarger($sKey, $iMax){
            $aTmp = $this->oDB->query('
                    select count(*) count from pages 
                    where siteid='.$this->iSiteId.' and errorcount=0 and '.$sKey.'>'.$iMax
                )->fetchAll(PDO::FETCH_ASSOC);
            return isset($aTmp[0]['count']) ? $aTmp[0]['count'] : false;
        }
    
    /**
     * WIP ... 
     * 
     * get hash with counts ... of pages, resources, ... and more
     * to be used in 
     * - backend::_getStatusinfos 
     * - insert counters after crawling to collect them for historical data
     * 
     * @staticvar array  $aStatusinfo  static "cahce" of return data
     * @param string  $sPage  one of _global|htmlchecks|linkchecker|httpheaderchecks; default: get infos for all targets
     * @return array
     */
    public function getStatusCounters($sPage=false, $bReset=false){
        static $aStatuscounters;
        $aPagesArray=['_global', 'htmlchecks', 'linkchecker', 'httpheaderchecks'];
        $aWarnIfZero=[
            'responseheaderKnown',
            'responseheaderCache',
            'responseheaderCompression',
            'responseheaderSecurity',
        ];
        $aWarningCounterIds=[
            'countShortTitles', 
            'countShortDescr',
            'countShortKeywords',
            'countLargePages', 
            'countLongLoad',
            'statusWarning',
            'responseheaderUnknown',
            'responseheaderUnwanted',
            'responseheaderDeprecated',
            'responseheaderNonStandard',
        ];
        $aErrotCounterIds=[
            'statusError', 
        ];
        /*
        $oCache=new AhCache($this->_getCacheModule(), $this->_getCacheId(__METHOD__ . '-'. $this->iSiteId.'-'.$sPage));
        if(false && !$bIgnoreCache && $this->sLogFilename && $oCache->isNewerThanFile($this->sLogFilename)){
            // $this->logAdd(__METHOD__.' returning cache data ... aPages = '.print_r($aPages, 1).' ... ['.$this->sLogFilename.']' );
            return $oCache->read();
        }
         */
        if(!isset($aStatuscounters) || $bReset){
            $aStatuscounters=array();
        }
        if(!isset($aStatuscounters[$this->iSiteId])){
            $aStatuscounters[$this->iSiteId]=array();
        }

        if(isset($aStatuscounters[$this->iSiteId][$sPage])){
            return $aStatuscounters[$this->iSiteId][$sPage];
        }
        
        $aReturn=array();

        if(!$sPage){
            foreach($aPagesArray as $sMyPage){
                $aTmpCounters=$this->getStatusCounters($sMyPage);
                if($aTmpCounters){
                        $aReturn=array_merge($aReturn, $this->getStatusCounters($sMyPage));
                } 
            }
            // create counters of all found errors and warnings
            $aReturn['TotalErrors']=0;
            $aReturn['TotalWarnings']=0;
            foreach($aErrotCounterIds as $sCounterId){
                $aReturn['TotalErrors']+=isset($aReturn[$sCounterId]) ? $aReturn[$sCounterId] : 0;
            }
            foreach($aWarningCounterIds as $sCounterId){
                $aReturn['TotalWarnings']+=isset($aReturn[$sCounterId]) ? $aReturn[$sCounterId] : 0;
            }
            
            // add warning if a counter is zero
            foreach($aWarnIfZero as $sCounterId){
                $aReturn['TotalWarnings']+=isset($aReturn[$sCounterId])  
                        ? ($aReturn[$sCounterId] ? 0 : 1 )  
                        : 0
                        ;
            }
            $aReturn['TotalWarnings']+=isset($aReturn['responseheaderVersionStatus'])  
                    ? ($aReturn['responseheaderVersionStatus']==='warning' ? 1 : 0)
                    : 0
                    ;
            
            return $aReturn;
        }
        // $this->logAdd(__METHOD__.' reading source data ... aPages = '.print_r($aPages, 1).' ... ['.$this->sLogFilename.']');
        // $aOptions = $this->getEffectiveOptions();

        // (1) prepare
        if($sPage=='_global'){
            $iPagesCount=$this->getRecordCount('pages', array('siteid'=>$this->iSiteId));
            $iRessourcesCount=$this->getRecordCount('ressources', array('siteid'=>$this->iSiteId));
            $iSearchesCount=$this->getRecordCount('searches', array('siteid'=>$this->iSiteId));
        }
        switch ($sPage) {
            case '_global':
                $aReturn=array(
                    'pages'=>$iPagesCount,
                    'ressources'=>$iRessourcesCount,
                    'searches'=>$iSearchesCount,
                );
                break;

            case 'htmlchecks':
                $oCrawler=new crawler($this->iSiteId);
                $aOptions = $this->getEffectiveOptions();

                $aReturn['countCrawlerErrors']=$oCrawler->getCount(array(
                    'AND' => array(
                        'siteid' => $this->iSiteId,
                        'errorcount[>]' => 0,
                    )));
                $aReturn['countShortTitles']   = $this->getHtmlchecksCount('title',       $aOptions['analysis']['MinTitleLength']);
                $aReturn['countShortDescr']    = $this->getHtmlchecksCount('description', $aOptions['analysis']['MinDescriptionLength']);
                $aReturn['countShortKeywords'] = $this->getHtmlchecksCount('keywords',    $aOptions['analysis']['MinKeywordsLength']);
                $aReturn['countLargePages']    = $this->_getHtmlchecksLarger('size',       $aOptions['analysis']['MaxPagesize']);
                $aReturn['countLongLoad']      = $this->_getHtmlchecksLarger('time',       $aOptions['analysis']['MaxLoadtime']);
                break;
            case 'linkchecker':
                $oCrawler=new crawler($this->iSiteId);
                $aOptions = $this->getEffectiveOptions();
                
                $oRessources=new ressources($this->iSiteId);
                $aCountByStatuscode=$oRessources->getCountsOfRow(
                    'ressources', 'http_code', 
                    array(
                        'siteid'=> $this->iSiteId,
                        'isExternalRedirect'=>'0',
                    )
                );
                if (count($aCountByStatuscode)){

                    $oHttp=new httpstatus();
                    $aTmpItm=array('status'=>array(), 'total'=>0);
                    $aBoxes=array('Todo'=>$aTmpItm, 'Error'=>$aTmpItm,'Warning'=>$aTmpItm, 'Ok'=>$aTmpItm);

                    // echo '<pre>$aCountByStatuscode = '.print_r($aCountByStatuscode,1).'</pre>';
                    foreach ($aCountByStatuscode as $aStatusItem){
                        $iHttp_code=$aStatusItem['http_code'];
                        $iCount=$aStatusItem['count'];
                        $oHttp->setHttpcode($iHttp_code);

                        if ($oHttp->isError()){
                           $aBoxes['Error']['status'][$iHttp_code] = $iCount;
                           $aBoxes['Error']['total']+=$iCount;
                        }
                        if ($oHttp->isRedirect()){
                           $aBoxes['Warning']['status'][$iHttp_code] = $iCount;
                           $aBoxes['Warning']['total']+=$iCount;
                        }
                        if ($oHttp->isOperationOK()){
                           $aBoxes['Ok']['status'][$iHttp_code] = $iCount;
                           $aBoxes['Ok']['total']+=$iCount;
                        }
                        if ($oHttp->isTodo()){
                           $aBoxes['Todo']['status'][$iHttp_code] = $iCount;
                           $aBoxes['Todo']['total']+=$iCount;
                        }
                    } // foreach ($aCountByStatuscode as $aStatusItem){

                    foreach (array_keys($aBoxes) as $sSection){
                        $aReturn['status'.$sSection]=$aBoxes[$sSection]['total'];
                        foreach(array_keys($aBoxes[$sSection]['status']) as $iHttp_code){
                            $aReturn['status'.$sSection.'['.$iHttp_code.']']=$aBoxes[$sSection]['status'][$iHttp_code];
                        }
                    } // foreach (array_keys($aBoxes) as $sSection){                    
                }
                break;
            case 'httpheaderchecks':

                // default: detect first url in pages table
                $aPagedata = $this->oDB->select(
                    'pages', 
                    array('url', 'header'), 
                    array(
                        'AND' => array(
                            'siteid' => $this->iSiteId,
                        ),
                        "ORDER" => array("id"=>"ASC"),
                        "LIMIT" => 1
                    )
                );
                if (count($aPagedata)){
                    $oHttpheader=new httpheader();
                    $sInfos=$aPagedata[0]['header'];
                    $aInfos=json_decode($sInfos,1);
                    // _responseheader ?? --> see crawler.class - method processResponse()
                    $oHttpheader->setHeaderAsString($aInfos['_responseheader']);

                    $aFoundTags=$oHttpheader->getExistingTags();

                    $iTotalHeaders=count($oHttpheader->getHeaderAsArray());
                    $iKnown=$aFoundTags['http'];
                    $iUnkKnown=         isset($aFoundTags['unknown'])      ? $aFoundTags['unknown']      : 0;
                    $iUnwanted=         isset($aFoundTags['unwanted'])     ? $aFoundTags['unwanted']     : 0;
                    $iDeprecated=       isset($aFoundTags['deprecated'])   ? $aFoundTags['deprecated']   : 0;
                    $iNonStandard=      isset($aFoundTags['non-standard']) ? $aFoundTags['non-standard'] : 0;

                    $iCacheInfos=       isset($aFoundTags['cache'])        ? $aFoundTags['cache']        : 0;
                    $iCompressionInfos= isset($aFoundTags['compression'])  ? $aFoundTags['compression']  : 0;

                    $iSecHeader=        isset($aFoundTags['security'])     ? $aFoundTags['security']     : 0;
                    
                    $aReturn['responseheaderCount']=$iTotalHeaders;
                    $aReturn['responseheaderKnown']=$iKnown;
                    $aReturn['responseheaderUnknown']=$iUnkKnown;
                    $aReturn['responseheaderUnwanted']=$iUnwanted;
                    $aReturn['responseheaderDeprecated']=$iDeprecated;
                    $aReturn['responseheaderNonStandard']=$iNonStandard;
                    $aReturn['responseheaderCache']=$iCacheInfos;
                    $aReturn['responseheaderCompression']=$iCompressionInfos;
                    $aReturn['responseheaderSecurity']=$iSecHeader;
                    $aReturn['responseheaderVersion']=$oHttpheader->getHttpVersion();
                    $aReturn['responseheaderVersionStatus']=$oHttpheader->getHttpVersionStatus($aReturn['responseheaderVersion']);
                    
                }
                break;

            default:
                break;
        }
        if(!count($aReturn)){
            return false;
        }
        $aStatuscounters[$this->iSiteId][$sPage]=$aReturn;
        // $oCache->write($aReturn);
        
        // $oCache->write($aStatusinfo, 10);
        return $aReturn;
    }
    

    /**
     * delete database tables for crawled data. as a reminder: this deletes
     * all data for *all* defined profiles.
     * 
     * @param array   $aItems    array with these keys as flags:
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
        if ($bFull || array_key_exists('counters', $aItems) && $aItems['counters']) {
            $aTables[] = 'counter';
        }
        if (count($aTables)) {
            foreach ($aTables as $sTable) {
                
                $sql = (int)$iSiteId 
                        ? "DELETE FROM <".$sTable."> WHERE siteid=".(int)$iSiteId .";"
                        : "DROP TABLE IF EXISTS <{$sTable}>;"
                        ;
                // for CLI output
                echo "DEBUG: $sql\n";
                if (!$this->oDB->query($sql)) {
                    echo "Failed!!\n";
                    var_dump($this->oDB->error, 1);
                    var_dump($this->oDB->errorInfo, 1);
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
            /*
            if(php_sapi_name()==='cli'){
                echo $sMessage."\n";
            }
            */
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
            return '<div class="debugcontainer">'.$this->_oLog->render().'</div>';
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
        $this->logAdd(__METHOD__.'('.htmlentities($iSiteId).') start');
        $iSiteId= preg_replace('/[^a-z0-9]/', '', $iSiteId);

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
        if (!$this->oDB){
            return false;
        }

        if ($iSiteId && isset($aOptions['profiles'][$iSiteId])) {
            $this->iSiteId = $iSiteId;
            $this->aProfileSaved = $aOptions['profiles'][$iSiteId];

            // @since v0.22 
            $this->sCookieFilename = dirname(__DIR__).'/data/cookiefile-siteid-'.$iSiteId.'.txt';

            // @since v0.135
            $this->sLogFilename = dirname(__DIR__).'/data/indexlog-siteid-'.$iSiteId.'.log';
            $this->logAdd(__METHOD__.'('.htmlentities($iSiteId).') set $this->iSiteId = ' . $this->iSiteId);
            
        } else {
            $this->aProfileSaved = array();
            $this->logAdd(__METHOD__.'('.htmlentities($iSiteId).') no profile $this->iSiteId = FALSE');
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
    
    /**
     * UNUSED
     * get array of ahwi-updater
     * @return type
     */
    public function setupRemoveUnwanted(){
        foreach($this->aDefaultOptions['updater']['toremove']['files'] as $sFile){
            unlink($sFile);
        }
        foreach($this->aDefaultOptions['updater']['toremove']['dirs'] as $sDir){
            unlink($sDir);
        }
        return true;
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
    // Cache
    // ----------------------------------------------------------------------
    
    /**
     * get an id of the cache module
     * @return type
     */
    public function getCacheModule(){
        return "pages-siteid-".$this->iSiteId;
    }
    
    // ----------------------------------------------------------------------
    // LANGUAGE
    // ----------------------------------------------------------------------
    
    /**
     * get current language
     * @return string
     */
    public function getLang(){
        return $this->sLang;
    }
    /**
     * helper function to load language array
     * @param string  $sPlace  one of frontend|backend
     * @param string  $sLang   language (i.e. "de")
     * @return array
     */
    private function _getLangData($sPlace, $sLang = false) {
        if (!$sLang) {
            // $this->setSiteId(false);
            $sLang = $this->getLang();
        }
        $sPlace= preg_replace('/[^a-z]/', '', $sPlace);
        $sLang= preg_replace('/[^a-z\-\_]/', '', $sLang);
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
        // TODO: why id call this? --> to load profile and set lang
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
     * load texts for backend
     * @param string  $sLang   language (i.e. "de")
     * @return array
     */
    public function setLangPublic($sLang = false) {
        $this->_getLangData('backend', $sLang);
        $this->_getLangData('public', $sLang);
        $this->aLang['backend'] = array_merge($this->aLang['backend'], $this->aLang['public']);
        return true;
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

    /**
     * count words in a given text and return it as array with most used
     * words on top.
     * 
     * @param string  $sText   text to analyze
     * @param array   $aWords  optional: existing result list to expanc
     * @return int
     */
    public function getWordsInAText($sText, $aWords=array()){
        $characterMap='À..ÿ'; // chars #192 .. #255

        foreach(str_word_count(
                str_replace("'", '',$sText)
            ,2,$characterMap) as $sWord ){

            // strtolower destroyes umlauts
            // $sKey=strtolower($sWord);
            // $sKey=function_exists('mb_strtolower') ? mb_strtolower($sWord) : $sWord;
            $sKey=$sWord;
            if(strlen($sKey)>2){
                if(!isset($aWords[$sKey])){
                    $aWords[$sKey]=1;
                } else {
                    $aWords[$sKey]++;
                }
            }
        }
        arsort($aWords);
        return $aWords;
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
            $this->cliprint('error', "ABORT: The crawler is still running (".__METHOD__.")\n");
            return false;
        }
        $this->cliprint('info', __METHOD__."\n"); sleep(1);
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
        if(!isset($this->aStatus['messageid'])){
            return false;
        } 
        $oStatus = new status();
        $oStatus->updateAction($this->aStatus['messageid'], $sMessage);
    }

    public function disableLocking() {
        $this->cliprint('info', __METHOD__."\n"); sleep(1);
        if(!isset($this->aStatus['messageid'])){
            return false;
        } 
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
            $oCli->color($sColor, sprintf("%0.3f", memory_get_usage()/1024/1024 + 0.5) .' MB | '. $sMessage);
            $this->logfileAppend($sColor, $sMessage);
        }
        if($sNextColor){
            $oCli->color($sNextColor);
        }
        return true;
    }

    // ----------------------------------------------------------------------
    // LOGFILE of crawler/ indexer
    // ----------------------------------------------------------------------

    /**
     * delete crawler/ indexer logfile; returns true if a logfile does not
     * exist anymore
     * @return boolean
     */
    public function logfileDelete(){
        if($this->sLogFilename && file_exists($this->sLogFilename)){
            unlink($this->sLogFilename);
        }
        return !file_exists($this->sLogFilename);
    }
    
    /**
     * add a message to a logfile; the params are orientated by method cliprint()
     * where logfileAppend is called.
     * 
     * @see cliprint
     * 
     * @param string  $sColor      color key; one of head|input|cli|ok|info|warning|error
     * @param string  $sMessage    string to show
     * @return boolean
     */
    public function logfileAppend($sColor, $sMessage){
        if($this->sLogFilename){
            
            /*
            $sTextToAdd=(strstr($sMessage, "\n") 
                        ? date("Y-m-d_H:i:s").'  '.sprintf('%-10s', $sColor).$sMessage
                        : $sMessage
                    );
             */
            // remark: if you change the syntax of writing log data you need to
            //         update the method logfileToHtml() too
            $sTextToAdd=date("Y-m-d_H:i:s").'  '.sprintf('%-10s', $sColor).$sMessage . (strstr($sMessage, "\n") ? '' : "\n") ;

            return file_put_contents(
                    $this->sLogFilename, 
                    $sTextToAdd, 
                    FILE_APPEND
                    );
        }
        return false;
    }
    

    /**
     * read log file and get array of log lines
     * returns something like this:
     * Array
     * (
     *     [file] => /var/www/ahcrawler/public_html/data/indexlog-siteid-4.log
     *     [lines_total] => 7003
     *     [lines] => 124
     *     [skip] => 0
     *     [options] => Array
     *         (
     *             [cli] => 
     *             [info] => 
     *             [waring] => 1
     *             [error] => 1
     *             [linesperpage] => 5000
     *             [page] => 1
     *         )
     * 
     *     [data] => Array
     *         (
     *             [0] => Array
     *                 (
     *                     [count] => 1
     *                     [ts] => 2022-09-01_22:14:32
     *                     [loglevel] => error
     *                     [message] => ERROR: 404 https://www.xing.com/app/user?op=share;url=https://jb2018.iml.unibe.ch/editorial
     *                 )
     *              ...
     *     )
     * 
     * @param  $aOptions  array  options array
     *                           - linesperpage {int}   max number of lines per page
     *                           - page         {int}   number of current page
     *                           - cli          {bool}  false
     *                           - info         {bool}  false
     *                           - warning      {bool}  true
     *                           - error        {bool}  true
     * @return array
     */
    public function getLogs($aOptions){
        $_aFilter=array_merge([
            'cli'=>false,
            'info'=>false,
            'ok'=>false,
            'warning'=>true,
            'error'=>true,
        ], $aOptions);

        $iLimitLines=isset($aOptions['linesperpage']) ? (int)$aOptions['linesperpage'] : 0;
        $iPage=isset($aOptions['page']) ? (int)$aOptions['page']:1;

        $aReturn=[
            'file'=>$this->sLogFilename,
            'options'=>$_aFilter,
            'loglevels'=>[],
            'stats'=>[],
            'data'=>[],
        ];

        $iCounter=0;
        $iSkip=0;
        $aLevels=[];        
        
        $iStartLine=($iPage-1)*$iLimitLines + 1;
        $iEndLine=($iPage)*$iLimitLines;
        
        if($this->sLogFilename && file_exists($this->sLogFilename)){
            foreach(file($this->sLogFilename) as $line) {

                $aResult=array();
                if(strstr($line, '==========')){
                    $line=preg_replace('/========== (.*)/', '<h3 id="'.'action-'.md5($line).'">$1</h3>', $line);
                }
                preg_match_all('/(^[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}:[0-9]{2}:[0-9]{2})\ *([a-z]*)\ *(.*)/', $line, $aResult);
                // echo '<pre>'.print_r($aResult,1).'</pre>';

                $_sLogLevel=isset($aResult[2][0]) ? $aResult[2][0] : '';
                $_bShow=false;
                if (isset($_aFilter[$_sLogLevel]) && $_aFilter[$_sLogLevel]){
                    $_bShow=true;
                }

                if(!isset($aReturn['loglevels'][$_sLogLevel])){
                    $aReturn['loglevels'][$_sLogLevel]=1;
                } else {
                    $aReturn['loglevels'][$_sLogLevel]++;
                }

                if($_bShow){
                    $iCounter++;
                    if($iLimitLines 
                        && ($iCounter < $iStartLine || $iCounter>$iEndLine)
                    ){
                        $iSkip++;
                    } else {

                        $aReturn['data'][]=[
                            'count'=>$iCounter,
                            'ts'=>$aResult[1][0],
                            'loglevel'=>$_sLogLevel,
                            'message'=>$aResult[3][0]
                        ]; //=.'<div class="message message-'.$_sLogLevel.'">'.$aResult[1][0].'  '.sprintf('%-8s', $_sLogLevel).' '.$aResult[3][0];
                    }
                }
                if($iLimitLines && ($iCounter > $iEndLine)){
                    continue;
                }
            }
            $aReturn['stats']=[
                'loglines_total'=>count(file($this->sLogFilename)),
                'loglines_filtered'=>$iCounter  ,
                'lines_per_page'=>$iLimitLines,
                'line_from'=>$iStartLine,
                'line_to'=>$iEndLine,
                'lines_on_page'=>count($aReturn['data']),
                'skip'=>$iSkip,
                'pages'=>$iLimitLines ? round($iCounter/$iLimitLines + 0.5) : 1,
            ];
            ksort($aReturn['loglevels']);
        } else {
            $aReturn['error']='Logfile was not found.';
        }
        return $aReturn;
    }

    /**
     * read log file and get html code for page crawlerlog
     * @param  $aOptions  array  options array
     *                           - linesperpage {int}   max number of lines per page
     *                           - page         {int}   number of current page
     *                           - cli          {bool}  false
     *                           - info         {bool}  false
     *                           - warnings     {bool}  true
     *                           - error        {bool}  true
     * @return string
     */
    public function logfileToHtml($aOptions){
        $sReturn='';


        $aLogs=$this->getLogs($aOptions);
        
        // DEBUG: show options and stats
        // $aTmp=$aLogs; unset($aTmp['data']); $sReturn.= print_r($aTmp, 1);
        
        if(isset($aLogs['error']) && $aLogs['error']){
            return $sReturn;
        }


        foreach ($aLogs['data'] as $aLogline){
            $sReturn.='<div class="message message-'.$aLogline['loglevel'].'">'.$aLogline['ts'].'  '.sprintf('%-8s', $aLogline['loglevel']).' '.$aLogline['message'].'</div>';
        }

        $sNavi='';
        
        $sUrl=$_SERVER['REQUEST_URI'];
        $sUrl=preg_replace('|&logpage=[0-9]*|', '', $sUrl);
        $sUrl=preg_replace('|&full=[0-9]*|'    , '', $sUrl);

        $iLimitLines=$aLogs['stats']['lines_per_page'];
        $iLinesTotal=$aLogs['stats']['loglines_total'];
        $iCounter=$aLogs['stats']['loglines_filtered'];

        if($iLinesTotal!=$iCounter){
            $sUrl=preg_replace('|&loglevel=.*|'    , '', $sUrl);
            $sNavi.='<br><a href="'.$sUrl.'&loglevel=all" class="pure-button">'.$this->lB('crawlerlog.loglevel-all').' </a><br>';
        } else {
            $sNavi.='<br><a href="'.$sUrl.'&loglevel=" class="pure-button">'.$this->lB('crawlerlog.loglevel-filter').' </a><br><br>';
        }

        if($iLimitLines){
            // $sNavi.='<br>';
            $iPage=$aLogs['options']['page'];
            $iLastPage=$aLogs['stats']['pages'];
            if($iLastPage>1 || $iPage>$iLastPage){
                for($i=1; $i<=$iLastPage; $i++){
                    $sNavi.='<a href="'.$sUrl.'&logpage='.$i.'" class="pure-button'.($iPage==$i ? ' button-secondary' : '').'">'.$i.'</a> ';
                }
                $sNavi.=$iLastPage>1 ? '<a href="'.$sUrl.'&full=1" class="pure-button">'.$this->lB('crawlerlog.full').'</a> ' : '';
            }
            $sNavi.=$sNavi ? '<br><br>' : '';
            $iStartLine=$aLogs['stats']['line_from'];
            $iEndLine=$aLogs['stats']['line_to'];

            $sNavi.=($iStartLine==1 && $iEndLine >= $iCounter
                    ? sprintf($this->lB('crawlerlog.linestotal'), $iCounter)
                    : sprintf($this->lB('crawlerlog.lines'), $iStartLine, min($iEndLine, $iCounter), $iCounter)
                    ).'<br>';
        } else {
            $sNavi.='<a href="'.$sUrl.'" class="pure-button button-secondary"> << '.$this->lB('button.back').' </a><br><br>'
                    .sprintf($this->lB('crawlerlog.linestotal'), $iCounter).'<br>'
                ;
        }
        
        $sReturn=$sReturn 
                ?  $sNavi
                    . '<pre class="logdata">'.$sReturn.'</pre>'
                    . $sNavi
                : $sNavi;
        return $sReturn;
    }
}
