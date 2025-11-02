<?php

require_once 'adminacl.class.php';
require_once 'analyzer.html.class.php';
require_once 'crawler-base.class.php';
require_once 'crawler.class.php';
require_once 'httpheader.class.php';
require_once 'ressources.class.php';
require_once 'renderer.class.php';
require_once 'search.class.php';
require_once 'sslinfo.class.php';
require_once 'status.class.php';

require_once __DIR__ . '/../vendor/ahcache/src/cache.class.php';
require_once __DIR__ . '/../vendor/ahwebinstall/ahwi-updatecheck.class.php';

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
 * 
 * BACKEND
 * 
 * 2024-09-13  v0.167  php8 only; add typed variables; use short array syntax
 */
class backend extends crawler_base
{

    protected string $_sBaseHelpUrl = 'https://www.axel-hahn.de/docs/ahcrawler/Web_UI_backend/';

    /**
     * Array of menu items
     * @var array
     */
    private array $_aMenu = [
        'home' => [
            'children' => [
                'searchindexstatus' => ['needs' => ['pages'], 'permission' => 'viewer'],
                'searchindextester' => ['needs' => ['pages'], 'permission' => 'viewer'],
                'searches' => ['needs' => ['searches'], 'permission' => 'viewer'],
                'crawlerlog' => ['permission' => 'viewer'],
                'profiles' => ['permission' => 'admin'],
            ],
        ],
        // 'search'=>[],
        'analysis' => [
            'children' => [
                'sslcheck' => ['needs' => ['starturl'], 'permission' => 'viewer'],
                'httpheaderchecks' => ['needs' => ['pages'], 'permission' => 'viewer'],
                'cookies' => ['permission' => 'viewer'],
                'htmlchecks' => ['needs' => ['pages'], 'permission' => 'viewer'],
                'linkchecker' => ['needs' => ['ressources'], 'permission' => 'viewer'],
                'ressources' => ['needs' => ['ressources'], 'permission' => 'viewer'],
                'checkurl' => ['needs' => ['ressources'], 'permission' => 'viewer'],
                'ressourcedetail' => ['needs' => ['ressources'], 'permission' => 'viewer'],
                'counters' => ['needs' => ['pages'], 'permission' => 'viewer'],
                "viewurls" => ['permission' => 'viewer'],
            ],
        ],
        'tools' => [
            'children' => [
                'bookmarklet' => [],
                'httpstatuscode' => [],
                'langedit' => ['permission' => 'globaladmin'],
                // 'showicons'=>[], // coming soon
                'update' => ['permission' => 'globaladmin'],
            ],
        ],
        'settings' => [
            'children' => [
                'userprofile' => [],
                'userroles' => [],
                'setup' => ['permission' => 'globaladmin'],
                'vendor' => ['permission' => 'globaladmin'],
            ],
        ],
        'about' => [],
    ];

    /**
     * Array of public menu items
     * @var array
     */
    private array $_aMenuPublic = [
        'home' => [],
        'httpheaderchecks' => [],
        'sslcheck' => [],
        'about' => [],
    ];

    /**
     * Status variable: is the site public? For backend it is false
     * @var boolean
     */
    private bool $_bIsPublic = false;

    /**
     * page to display
     * @var string
     */
    private string $_sPage = '';

    /**
     * Pagefile to display; based on found page it is a mapped filename to 
     * include.
     * @var string
     */
    private string $_sPageFile = '';

    /**
     * active profile of a website
     * (In former times the profiles were shown as a tab)
     * @var string
     */
    private string $_sTab = '';

    /**
     * Array of defined icons in different sections
     * The values are font-awesome class values for icons
     * @var array
     */
    private array $_aIcons = [];
    /*
        see icons_lineawesome.php

        
        'menu' => [
            '1111' => 'fa-solid fa-user-lock',
            'login' => 'fa-solid fa-user-lock',
            'home' => 'fa-solid fa-home',
            'settings' => 'fa-solid fa-cogs',
            'setup' => 'fa-solid fa-sliders-h',
            'profiles' => 'fa-solid fa-globe-americas',
            'crawlerlog' => 'fa-solid fa-file-alt',
            'vendor' => 'fa-solid fa-box-open',
            'search' => 'fa-solid fa-database',
            'crawler' => 'fa-solid fa-flag',
            'searchindexstatus' => 'fa-solid fa-flag',
            'searchindextester' => 'fa-solid fa-search',
            'searches' => 'fa-solid fa-chart-pie',
            // 'analysis'=>'fa fa-newspaper-o', 
            'analysis' => 'fa-solid fa-chart-line',
            'sslcheck' => 'fa-solid fa-shield-alt',
            'ressources' => 'fa-regular fa-file-code',
            'linkchecker' => 'fa-solid fa-link',
            'htmlchecks' => 'fab fa-html5',
            'httpheaderchecks' => 'fa-regular fa-flag',
            'cookies' => 'fa-solid fa-cookie-bite',
            'checkurl' => 'fa-solid fa-globe-americas',
            'ressourcedetail' => 'fa-solid fa-map-marked',
            'tools' => 'fa-solid fa-tools',
            'bookmarklet' => 'fa-solid fa-bookmark',
            'httpstatuscode' => 'fab fa-font-awesome',
            'showicons' => 'fa-solid fa-icons',
            'langedit' => 'fa-regular fa-comment',
            'counters' => 'fa-solid fa-chart-simple',
            'about' => 'fa-solid fa-info-circle',
            'update' => 'fa-solid fa-cloud-download-alt',
            'useradmin' => 'fa-solid fa-user',
            'userprofile' => 'fa-solid fa-user',
            'project' => 'fa-solid fa-book',

            'logoff' => 'fa-solid fa-power-off',
        ],
        'cols' => [
            '1' => 'fa-regular fa-comment',
            '2' => 'fa-regular fa-comment',
            'id' => 'fa-solid fa-hashtag',
            'summary' => 'fa-regular fa-comment',
            'ranking' => 'fa-solid fa-chart-bar',
            'url' => 'fa-solid fa-link',
            'title' => 'fa-solid fa-chevron-right',
            'description' => 'fa-solid fa-chevron-right',
            'lang' => 'fa-solid fa-comment',
            'label' => 'fa-solid fa-chevron-right',
            'icon' => 'fa-regular fa-image',
            'errorcount' => 'fa-solid fa-bolt',
            'keywords' => 'fa-solid fa-key',
            'lasterror' => 'fa-solid fa-bolt',
            'actions' => 'fa-solid fa-check',
            'searchset' => 'fa-solid fa-cube',
            'query' => 'fa-solid fa-search',
            'results' => 'fa-solid fa-bullseye',
            'count' => 'fa-solid fa-thumbs-up',
            'host' => 'fa-solid fa-laptop',
            'ua' => 'fa-solid fa-paw',
            'referrer' => 'fa-solid fa-link',
            'status' => 'fa-regular fa-flag',
            'todo' => 'fa-solid fa-magic',
            'ts' => 'fa-solid fa-calendar',
            'ressourcetype' => 'fa-solid fa-cubes',
            'type' => 'fa-solid fa-cloud',
            'content_type' => 'fa-regular fa-file-code',
            'http_code' => 'fa-solid fa-retweet',
            'length' => 'fa-solid fa-arrows-alt-h',
            'size' => 'fa ',
            'time' => 'fa-regular fa-clock',
            'words' => 'fa-solid fa-arrows-alt-h',

            'updateisrunning' => 'fa-solid fa-spinner fa-spin',

            // cookies
            'domain' => 'fa-solid fa-atlas',
            'path' => 'fa-solid fa-folder',
            'name' => 'fa-solid fa-tag ',
            'value' => 'fa-solid fa-chevron-right',
            'httponly' => 'fa-regular fa-flag',
            'secure' => 'fa-solid fa-shield-alt',
            'expiration' => 'fa-regular fa-clock',

        ],
        'res' => [

            'filter' => 'fa-solid fa-filter',

            'url' => 'fa-solid fa-globe',
            'docs' => 'fa-solid fa-book',
            'source' => 'fa-solid fa-code',

            'ressources.showtable' => 'fa-solid fa-table',
            'ressources.showreport' => 'fa-regular fa-file',
            'ressources.ignorelimit' => 'fa-solid fa-unlock',

            'ssl.type-none' => 'fa-solid fa-lock-open',
            'ssl.type-selfsigned' => 'fa-solid fa-user-lock',
            'ssl.type-Business SSL' => 'fa-solid fa-lock',
            'ssl.type-EV' => 'fa-solid fa-shield-alt',

        ],
        'button' => [
            'button.add' => 'fa-solid fa-plus',
            'button.back' => 'fa-solid fa-chevron-left',
            'button.close' => 'fa-solid fa-times',
            'button.continue' => 'fa-solid fa-chevron-right',
            'button.create' => 'fa-regular fa-star',
            'button.delete' => 'fa-solid fa-trash',
            'button.down' => 'fa-solid fa-arrow-down',
            'button.download' => 'fa-solid fa-cloud-download-alt',
            'button.edit' => 'fa-solid fa-pencil-alt',
            'button.help' => 'fa-solid fa-question-circle',
            'button.home' => 'fa-solid fa-home',
            'button.login' => 'fa-solid fa-check',
            'button.logoff' => 'fa-solid fa-power-off',
            'button.openurl' => 'fa-solid fa-external-link-alt',
            'button.refresh' => 'fa-solid fa-sync',
            'button.save' => 'fa-solid fa-paper-plane',
            'button.search' => 'fa-solid fa-search',
            'button.truncateindex' => 'fa-solid fa-trash',
            'button.up' => 'fa-solid fa-arrow-up',
            'button.updatesinglestep' => 'fa-solid fa-chevron-right',
            'button.view' => 'fa-regular fa-eye',
        ],
    ];
    */

    private array $_aHelpPages = [

        'about' => 'About/index.html',
        'analysis' => 'Analysis/index.html',
        'bookmarklet' => 'Tools_and_information/Bookmarklets.html',
        'checkurl' => 'Analysis/Search_url.html',
        'cookies' => 'Analysis/Cookies.html',
        'counters' => 'Analysis/Counters.html',
        'crawlerlog' => 'Start/Crawler_log.html',
        'error404' => '',
        'home' => 'Start/index.html',
        'htmlchecks' => 'Analysis/Html_checks.html',
        'httpheaderchecks' => 'Analysis/Http_header_check.html',
        'httpstatuscode' => 'Tools_and_information/Http_status_codes.html',
        'installer' => '',
        'langedit' => 'Tools_and_information/Language_texts.html',
        'linkchecker' => 'Analysis/Link_checker.html',
        'logoff' => '',
        'profiles' => 'Start/Profiles.html',
        'public_about' => '',
        'public_home' => '',
        'public_httpheaderchecks' => '',
        'public_sslcheck' => '',
        'ressourcedetail' => 'Analysis/Resource_details.html',
        'ressources' => 'Analysis/Resources.html',
        'searches' => 'Start/Search_terms.html',
        'searchindexstatus' => 'Start/Search_index.html',
        'searchindextester' => 'Start/Search_test.html',
        'settings' => 'Settings/index.html',
        'setup' => 'Settings/Setup.html',
        'userroles' => 'Settings/User_roles.html',
        'userprofile' => 'Settings/My_profile.html',
        'showicons' => '',
        'sslcheck' => 'Analysis/SSL_check.html',
        'tools' => 'Tools_and_information/index.html',
        'update' => 'Tools_and_information/Update.html',
        'vendor' => 'Settings/Vendor_libs.html',
        'viewurls' => 'Analysis/Domain_files.html',
    ];

    /**
     * max number of resource to show
     * @var int
     */
    public int $iLimitRessourcelist = 1000;

    /**
     * Object of ahwebinstall updater class
     * @var ahwiupdatecheck
     */
    public ahwiupdatecheck $oUpdate;

    public adminacl $acl;

    // ----------------------------------------------------------------------
    /**
     * Constructor
     * new crawler
     * 
     * @param string   $iSiteId    site-id of search index
     * @param boolean  $bIsPublic  flag: is public page; in backend it is set to false
     */
    public function __construct(string $iSiteId = '', bool $bIsPublic = false)
    {
        $this->_oLog = new logger();
        if ($bIsPublic) {
            $this->_bIsPublic = true;
            $this->_aMenu = $this->_aMenuPublic;
        } else {
            if (!isset($_SESSION)) {
                // session_name('ahcrawler');
                session_start();
            }
            $this->acl = new adminacl();
            session_write_close();

        }

        // for settings: create a default array with all available menu items
        foreach ($this->_aMenuPublic as $sKey => $aItem) {
            $this->aDefaultOptions['menu-public'][$sKey] = false;
        }
        
        if ($bIsPublic) {
            $this->setSiteId(false);
            $this->aOptions['menu'] = $this->aOptions['menu-public'];
            $this->setLangPublic();
            $this->logAdd(__METHOD__ . ' public lang was set');
        } else {

            // for settings: create a default array with all available menu items
            foreach ($this->_aMenu as $sKey => $aItem) {
                $this->aDefaultOptions['menu'][$sKey] = true;
                if (isset($aItem['children'])) {
                    foreach (array_keys($aItem['children']) as $sKey2) {
                        $this->aDefaultOptions['menu'][$sKey2] = true;
                    }
                }
            }
            $iSiteId = $iSiteId ? $iSiteId : $this->_getRequestParam('siteid', false, 'int');
            $this->logAdd(__METHOD__ . ' iSiteId detected as ' . $iSiteId);
            $this->setSiteId($iSiteId);
            $this->logAdd(__METHOD__ . ' site id was set to ' . $this->iSiteId);
            $this->setLangBackend();
            $this->logAdd(__METHOD__ . ' backend lang was set');
            /*
             * 
             */
            $this->oUpdate = new ahwiupdatecheck([
                'product' => $this->aAbout['product'],
                'version' => $this->aAbout['version'],
                'baseurl' => $this->aOptions['updater']['baseurl'],
                'tmpdir' => ($this->aOptions['updater']['tmpdir'] ? $this->aOptions['updater']['tmpdir'] : __DIR__ . '/../tmp/'),
                'ttl' => $this->aOptions['updater']['ttl'],
            ]);
            // echo "getUpdateInfos : </pre>" . print_r($this->oUpdate->getUpdateInfos(), 1).'</pre>';
        }

        // print_r($this->aOptions); 
        // override fotawsome icons
        // $this->_aIcons = include 'icons_fontawesome.php';
        $this->_aIcons = include 'icons_tabler.php';
        // $this->_aIcons = include 'icons_lineawesome.php';

        $this->getPage();
        $this->logAdd(__METHOD__ . ' getPage was finished');
    }

    // ----------------------------------------------------------------------
    // LOGIN
    // ----------------------------------------------------------------------

    /**
     * Get if current backend page is cachable as boolean.
     * it is false for
     * - public pages
     * - if option for caching is off
     * - if debug is anbled
     * - backend pages named in $_nonCachable
     * 
     * @return bool
     */
    public function isCacheable(): bool
    {
        $_nonCachable = [
            'sslcheck',
            'searches',
            // 'searchindextester',
            'profiles',
            'langedit',
            'update',
            'setup',
            'vendor',
        ];
        if ($this->_bIsPublic) {
            $this->logAdd(__METHOD__ . ' - page is public - ignore page cache');
            $bReturn = false;
        }
        if (isset($this->aOptions['cache']) && !$this->aOptions['cache']) {
            $this->logAdd(__METHOD__ . ' - cache is disabled - ignore page cache');
            $bReturn = false;
        } else if (isset($this->aOptions['debug']) && $this->aOptions['debug']) {
            $this->logAdd(__METHOD__ . ' - debug is enabled - ignore page cache');
            $bReturn = false;
        } else {
            $bReturn = !array_search($this->_sPage, $_nonCachable);
            $this->logAdd(__METHOD__ . ' - page = ' . $this->_sPage . ' - ' . ($bReturn ? 'true' : 'false'));
        }
        return $bReturn;
    }

    /**
     * Check authentication if a user and password were configured
     * 
     * @global array  $aUserCfg  config from ./config/config_user.php
     * 
     * @return boolean
     */
    public function checkAuth(): bool
    {
        if ($this->_bIsPublic) {
            return true;
        }
        $aOptions = $this->_loadConfigfile();

        if(!($aOptions['options']['auth']['user']??false)){
            return true;
        }
        // handle POST request from login page
        if ( $_POST['AUTH_USER']??false  && $_POST['AUTH_PW']??false) {
            if(
                $aOptions['options']['auth']['user'] == $_POST['AUTH_USER']
                && password_verify($_POST['AUTH_PW'], $aOptions['options']['auth']['password'])
            )
            {
                $this->_setUser($_POST['AUTH_USER']);
                header('Location: ?' . $_SERVER['QUERY_STRING']);
                // print_r($_SESSION); echo " ... in _setUser()<br>";
                exit(0);
                return true;
            } else {
                return false;
            }
        }

        // if there is no acl config
        if(!$this->acl->hasConfig()) 
        {
            if (!isset($aOptions['options']['auth']['user']))
            {
                // no user in config + no acl --> superuser access without login
                return true;
            } else {
                // get current logged in user
                return !!$this->_getUser();
            }
        }

        return !!$this->acl->getUser();

    }

    /**
     * Get the username of the current user
     * @return string
     */
    private function _getUser(): string
    {

        // if ($this->acl->getUser() == "superuser") {
        //     return $_SESSION['AUTH_USER'] ?? '';
        // }
        // return $this->acl->getUser();

        // echo "
        
        // <h1>_getUser</h1>
        // <pre>" . print_r($_SESSION, 1) . "</pre>
        // acl->getUser = ".$this->acl->getUser()."<br>

        // "; die();

        // print_r($_SESSION); die();
        if(!$this->acl->hasConfig()){
            if($_SESSION['AUTH_USER']??false) return $_SESSION['AUTH_USER'];
        }
        if ($this->acl->getUser() == "superuser") {
            return $_SESSION['AUTH_USER'] ?? '';
        }
        return $this->acl->getUser();
    }

    /**
     * Check permission for a given name
     * 
     * @param string $sPermission  permission to check: one of globaladmin|appadmin|manager|viewer
     * @return bool
     */
    protected function _requiresPermission(string $sPermission, string $sApp = ''): bool
    {
        $bOK = false;
        $this->acl->setApp($sApp);
        switch ($sPermission) {
            case 'globaladmin':
                $bOK = $this->acl->isGlobalAdmin();
                break;
            case 'admin':
                $bOK = $this->acl->isAppAdmin();
                break;
            case 'manager':
                $bOK = $this->acl->canEdit();
                break;
            case 'viewer':
                $bOK = $this->acl->canView();
                break;
            default:
                throw new Exception('Unknown permission: ' . $sPermission);
                break;
        }
        return $bOK;
    }

    /**
     * Set a new authenticated user user
     * 
     * @param string  $sNewUser  set new user to be stored into the session
     * @return boolean
     */
    private function _setUser(string $sNewUser): bool
    {
        session_start();
        if (!$sNewUser) {
            // ... means: logoff
            unset($_SESSION['AUTH_USER']);
            session_destroy();
            return false;
        }
        $_SESSION['AUTH_USER'] = $sNewUser;
        session_write_close();
        return true;
    }

    /**
     * Get html code of a login form
     * @return string
     */
    private function _getLoginForm(): string
    {
        $sReturn = '';

        http_response_code(401);
        $sHref = '?' . str_replace('page=logoff', '', $_SERVER['QUERY_STRING']);

        $sReturn = ''
            /*
            . '<h3>' . $this->lB('login.title') . '</h3>'
             */
            . '<br><br><br>'
            . '<div class="actionbox">'
            . '<p>' . $this->lB('login.infotext') . '</p>'
            . '<br>'
            . '<form method="POST" action="' . $sHref . '" class="pure-form pure-form-aligned">'
            . '<div class="pure-control-group">'
            . '<label for="euser">' . $this->lB('login.username') . '</label>'
            . '<input type="text" id="euser" name="AUTH_USER" value="" required="required" placeholder="' . $this->lB('login.username') . '">'
            . '</div>'
            . '<div class="pure-control-group">'
            . '<label for="epw">' . $this->lB('login.password') . '</label>'
            . '<input type="password" id="epw" name="AUTH_PW" value="" required="required" placeholder="' . $this->lB('login.password') . '">'
            . '</div>'
            . '<br>'
            . '<div class="pure-control-group">'
            . '<label>&nbsp;</label>'
            . '<button type="submit" class="pure-button button-secondary">' . $this->_getIcon('button.login') . $this->lB('button.login') . '</button>'
            . '</div>'
            . '</form>'
            . '</div>'
        ;
        return $sReturn;
    }

    // ----------------------------------------------------------------------
    // NAVIGATION
    // ----------------------------------------------------------------------

    /**
     * Get new querystring - create the new querystring by existing query string
     * of current request and adding given new parameters
     * 
     * @global array $_GET GET parameters of current request
     * 
     * @param array $aQueryParams  new parameters for a link to a page
     * @return string
     */
    private function _getQs(array $aQueryParams): string
    {
        if ($_GET) {
            $aQueryParams = array_merge($_GET, $aQueryParams);
        }
        return '?' . preg_replace('/%5B[0-9]+%5D/simU', '[]', http_build_query($aQueryParams));
    }

    /**
     * Find the current page (returns one of the menu items of _aMenu)
     * @return string
     */
    public function getPage(): string
    {
        // $sPage = $this->_getRequestParam('page','/^[a-z]*$/');
        $sPage = $this->_getRequestParam('page');
        if (!$sPage) {
            $aKeys = array_keys($this->_aMenu);
            $sPage = $aKeys[0];
        }

        // if a page makes a db request for a profile
        if (!$this->iSiteId && !$this->_bIsPublic) {
            $this->setSiteId($this->_getTab());
        }

        $sFilename = dirname(__DIR__) . '/backend/pages/' . ($this->_bIsPublic ? 'public_' : '') . $sPage . '.php';
        if (!file_exists($sFilename)) {
            $sPage = 'error404';
            $sFilename = dirname(__DIR__) . '/backend/pages/' . $sPage . '.php';
            http_response_code(404);
        }
        $this->_sPage = $sPage;
        $this->_sPageFile = $sFilename;

        return $this->_sPage;
    }

    /**
     * Find the current tab from url param siteid=... 
     * or take the first id of given array (of profiles)
     * It returns 0..N (id of profile) or a string (of allowed GET param)
     * 
     * @param  bool            $bAllowSpecialSiteids  flag: allow next to site ids "all" and "add" as value; default: false (=no)
     * @return string
     */
    private function _getTab(bool $bAllowSpecialSiteids = false): string
    {
        $sAdd = $bAllowSpecialSiteids ? $this->_getRequestParam('siteid', '/add/') : '';
        $sAll = $bAllowSpecialSiteids ? $this->_getRequestParam('siteid', '/all/') : '';
        $this->_sTab = $sAdd . $sAll ? $sAdd . $sAll : $this->_getRequestParam('siteid', false, 'int');
        if (
            $this->_sTab && $this->_sTab !== 'add' 
            && isset($_SESSION['siteid']) 
            && $_SESSION['siteid'] !== $this->_sTab
            && $this->_requiresPermission('viewer', $this->_sTab)
        ) {
            session_start();
            $_SESSION['siteid'] = $this->_sTab;
            session_write_close();
        }

        if (!$this->_sTab) {
            foreach(array_keys($this->_getProfiles()) as $sId){
                if($this->_requiresPermission('viewer', $sId)){
                    $this->_sTab = $sId;
                    break;
                }
            }

            // $aTmp = array_keys($this->_getProfiles());
            // $this->_sTab = count($aTmp) ? $aTmp[0] : false;
        }

        return $this->_sTab;
    }

    /**
     * Helper for navigation: is a menu item hidden by user config?
     * @param string $sItem
     * @return boolean
     */
    public function isNavitemHidden(string $sItem = ''): bool
    {
        if (!$sItem) {
            $sItem = $this->_sPage;
        }
        return array_key_exists('menu', $this->aOptions)
            && array_key_exists($sItem, $this->aOptions['menu'])
            && !$this->aOptions['menu'][$sItem]
        ;
    }

    /**
     * Detect if a navigation item is enabled by 
     * - "needs" value that stands for the availability of a db table
     * - "permission" value for user permission
     *
     * @param array $aItem
     * @return bool
     */
    protected function _getNavAttrIsEnabled(array $aItem): bool
    {

        if ($aItem['permission'] ?? false) {
            return $this->_requiresPermission($aItem['permission'], $this->_sTab);
        }

        // if (!isset($aItem['needs']) || !is_array($aItem['needs']) || !count($aItem['needs'])) {
        if (!count($aItem['needs'] ?? [])) {
            return true;
        }
        foreach ($aItem['needs'] as $sTable) {
            if ($this->hasDataInDb($sTable)) {
                return true;
            }
            if ($sTable == 'starturl') {
                /*
                $sFirstUrl=isset($aProfile['searchindex']['urls2crawl'][0]) ? $aProfile['searchindex']['urls2crawl'][0] : false;
                if($sFirstUrl){
                    return true;
                }
                 * 
                 */
                return true;
            }
            /*
            if($sTable=='profile'){
                // ... 
            }

             */
        }
        return false;
    }

    /**
     * Get html code for navigation items
     * @param array  $aNav
     * @return string
     */
    private function _getNavItems($aNav): string
    {
        $sNavi = '';

        // echo '<pre>'.print_r($aDisabled,1).'</pre>';
        // echo '<pre>'.print_r($aNav,1).'</pre>'; die();
        foreach ($aNav as $sItem => $aSubItems) {
            $sNaviNextLevel = '';
            // echo $this->_sPage . '<pre>'.print_r($aNav, 1).'</pre>'; die();
            if (isset($aSubItems['children']) && count($aSubItems['children'])) {
                $sNaviNextLevel .= $this->_getNavItems($aSubItems['children']);
            }
            if (!$this->isNavitemHidden($sItem)) {
                $bHasActiveSubitem = strpos($sNaviNextLevel, 'pure-menu-link-active');
                $bIsActive = $this->_sPage == $sItem || $bHasActiveSubitem;
                $sClass = $bIsActive ? ' pure-menu-link-active' : '';
                $sUrl = '?page=' . $sItem . ($this->_bIsPublic ? '&amp;lang=' . $this->sLang : '');
                if ($this->_sTab) {
                    $sUrl .= '&amp;siteid=' . $this->_sTab;
                }

                if (!$this->_getNavAttrIsEnabled($aSubItems)) {
                    $sClass .= ' pure-menu-disabled';
                    $sUrl = '#';
                }

                // $sNavi.='<li class="pure-menu-item"><a href="?'.$sItem.'" class="pure-menu-link'.$sClass.'">'.$sItem.'</a></li>';
                $sNavi .= '<li class="pure-menu-item">'
                    . '<a href="' . $sUrl . '" class="pure-menu-link' . $sClass . '"'
                    . ' title="' . $this->lB('nav.' . $sItem . '.hint') . '"'
                    . '><i class="' . ($this->_aIcons['menu'][$sItem] ?? $sItem) . '"></i>'
                    . '<span> ' . $this->lB('nav.' . $sItem . '.label') . '</span>'
                    . '</a>'
                    . ($bIsActive ? $sNaviNextLevel : '')
                ;

                $sNavi .= '</li>';
            }
        }
        if ($sNavi) {
            $sNavi = "<ul class=\"pure-menu-list\">$sNavi</ul>";
        }

        return $sNavi;
    }

    /**
     * Get html code for navigation; the current page is highlighted
     * It can be empty if authentication failed or the installation was not 
     * finishes yet.
     * 
     * @return string
     */
    public function getNavi(): string
    {
        if (!$this->checkAuth()) {
            return '';
        }
        if (!$this->installationWasDone()) {
            return '';
        }

        $sNavi = $this->_getNavItems($this->_aMenu);
        return $sNavi;
    }

    /**
     * helper for method getBreadcrumb to get html code for a breadcrumb navigation
     * 
     * @param  array  $aNav    navigation items of a given lievel
     * @param  string $sDelim  chars to delim breadcrumb links
     * @return string
     */
    private function _getBreadcrumbitems(array $aNav, string $sDelim): string
    {
        $sNavi = '';
        foreach ($aNav as $sItem => $aSubItems) {
            $sNaviNextLevel = '';
            if (isset($aSubItems['children']) && count($aSubItems['children'])) {
                $sNaviNextLevel .= $this->_getBreadcrumbitems($aSubItems['children'], $sDelim);
            }
            if (!$this->isNavitemHidden($sItem)) {
                $bHasActiveSubitem = strpos($sNaviNextLevel, 'pure-button');
                $bIsActive = $this->_sPage == $sItem || $bHasActiveSubitem;

                if ($bIsActive) {
                    $sNavi .= $sDelim
                        . $this->_getLink2Navitem($sItem)
                        . ($bIsActive ? $sNaviNextLevel : '')
                    ;
                }
            }
        }
        return $sNavi;
    }

    /**
     * Get array of active navigation levels
     * @param array $aNav  array with navigation items; default is $this->_aMenu
     * @return array
     */
    public function getActiveNavLevels($aNav = null): array
    {
        $aReturn = [];
        if (!isset($aNav)) {
            $aNav = $this->_aMenu;
        }
        foreach ($aNav as $sItem => $aSubItems) {
            if (!$this->isNavitemHidden($sItem)) {

                $bHasActiveSubitem = strpos($this->_getBreadcrumbitems($aSubItems['children'] ?? [], '/'), 'pure-button');
                $bIsActive = $this->_sPage == $sItem || $bHasActiveSubitem;
                if ($bIsActive) {
                    $aReturn[] = $sItem;
                    $aReturn = array_merge($aReturn, $this->getActiveNavLevels($aSubItems['children'] ?? []));
                }
            }
        }
        return $aReturn;
    }

    /**
     * get html code for a breadcrumb navigation
     * @return string
     */
    public function getBreadcrumb(): string
    {
        $sMyDelim = '/';
        $sNavi = $this->_getBreadcrumbitems($this->_aMenu, $sMyDelim);

        // add HOME on non-home-level
        $sHomeLink = $this->_getLink2Navitem(array_key_first($this->_aMenu));
        if (!strstr($sNavi, $sHomeLink)) {
            $sNavi = "$sMyDelim$sHomeLink$sNavi";
        }
        return "<div class=\"breadcrumb\">$sNavi</div>";
    }

    /**
     * Get a flat array languages for a given context.
     * It returns false if no languages are found.
     * 
     * @param string $sLangobject optional: language object; one of frontend|backend
     * @return boolean|array
     */
    public function getLangs(string $sLangobject = ''): bool|array
    {
        $aLangfiles = [];
        $aLangkeys = [];

        // automatic set of object if not given
        $sLangobject = $sLangobject
            ? $sLangobject
            : ($this->_bIsPublic ? 'frontend' : 'backend')
        ;
        foreach (glob(dirname(__DIR__) . '/lang/' . $sLangobject . '.*.json') as $sJsonfile) {
            $sKey2 = str_replace($sLangobject . '.', '', basename($sJsonfile));
            $sKey2 = str_replace('.json', '', $sKey2);

            $aLangfiles[$sKey2] = $sJsonfile;
            $aLangkeys[] = $sKey2;
        }

        return count($aLangkeys)
            ? [
                'keys' => $aLangkeys,
                'files' => $aLangfiles,
            ]
            : false;
    }

    /**
     * Get html code for language selector in frontend.
     * It returns false if no language was found.
     * 
     * @return bool|string
     */
    public function getLangNavi(): bool|string
    {
        global $oRenderer;
        $sReturn = '';
        $aLangs = $this->getLangs();
        if (!$aLangs) {
            return false;
        }
        $aLangOptions = [];
        foreach ($aLangs['keys'] as $sLang) {
            $sClass = 'pure-menu-link' . ($sLang == $this->sLang ? ' pure-menu-link-active' : '');
            $sReturn .= '<li class="' . $sClass . '">' . $sLang . '</li>';
            $aOption = [
                'label' => $sLang,
            ];
            if ($sLang == $this->sLang) {
                $aOption['selected'] = 'selected';
            }
            $aLangOptions[] = $aOption;
        }
        return '<div class="langnav">'
            . '<form class="pure-form pure-form-aligned" method="GET" action="?">'
            . '<input type="hidden" name="page" value="' . (isset($_GET['page']) ? $_GET['page'] : '') . '">'
            . $oRenderer->oHtml->getFormSelect(
                [
                    'id' => 'sellang',
                    'name' => 'lang',
                    'onchange' => 'submit();',
                ],
                $aLangOptions
            ) . '</form>'
            . '</div>';
    }

    /**
     * Get html code for project selection
     * 
     * @param array    $aTabs        nav items
     * @param boolean  $bAddButton   flag for add button; default false; set true on profile setup
     * @param string   $sUpUrl       url for "up" tab in front of other tabs
     * 
     * @return string
     */
    private function _getNavi2(array $aTabs = [], bool $bAddButton = false, string $sUpUrl = ''): string
    {
        $sReturn = '';
        $sMore = '';
        if (!$this->_sTab) {
            $this->_getTab();
        }
        if ($bAddButton) {
            $aTabs['add'] = $this->_getIcon('button.add') . $this->lB('profile.new');
            if ($this->_getTab($bAddButton) !== 'add') {
                $sUrl = '?page=' . $this->_sPage . '&amp;siteid=add';
                $sMore = ' <a href="' . $sUrl . '" class="pure-button button-success">' . $this->_getIcon('button.add') . $this->lB('profile.new') . '</a>';
            }
        }

        // TODO:
        // the handlin of an upurl doesn't seem to work
        if ($sUpUrl) {
            $sReturn .= '<li class="pure-menu-item">'
                . '<a href="' . $sUpUrl . '" class="pure-menu-link"'
                . '>' . $this->_getIcon('button.up') . '</a></li>';
        }
        $sOptions = '';
        if (count($aTabs)) {
            foreach ($aTabs as $sId => $sLabel) {
                if ($this->_requiresPermission('viewer', $sId)) {

                    $sUrl = '?page=' . $this->_sPage . '&amp;siteid=' . $sId;
                    $sOptions .= '<option'
                        . ' value="' . $sUrl . '"'
                        . (($this->_sTab == $sId) ? ' selected="selected"' : '')
                        . '>'
                        . $this->_getIcon('project') . $sLabel . '</option>';
                }
            }
            if ($sOptions) {
                $sOptions = ''
                    . '<span>'
                    . $this->_getIcon('project')
                    . $this->lB('home.select-project') . ' '
                    . '</span>'
                    . '<select>'
                    . $sOptions
                    . '</select>'
                ;
            }
        }
        $this->acl->setApp($this->_sTab);
        $sReturn .= ''
            // . '<div class="pure-menu pure-menu-horizontal">'
            . '<form class="pure-form pure-form-aligned">'
            . '<div id="selectProject" class="pure-control-group">'
            . $sOptions
            . $sMore
            . '</div>'
            . '</form>';

        return $sReturn;
    }
    /**
     * Get html code for a link in a box 
     * used for child items
     * 
     * @param array  $aLink  array with link params
     *                       url
     *                       class  optional css class - "pure-menu-disabled"
     *                       hint
     *                       icon
     *                       target
     *                       title
     *                       text
     * @return string
     */
    protected function _getLinkAsBox(array $aLink): string
    {

        return
            '<a href="' . $aLink['url'] . '" '
            . 'class="childitem'
            . (isset($aLink['class']) ? ' ' . $aLink['class'] : '')
            . '"'
            . (isset($aLink['hint']) ? ' title="' . $aLink['hint'] . '"' : '')
            . (isset($aLink['target']) ? ' target="' . $aLink['target'] . '"' : '')
            . '>'
            . (isset($aLink['icon']) ? '<i class="' . $aLink['icon'] . '"></i> ' : '')
            . (isset($aLink['title']) ? '<strong>' . $aLink['title'] . '</strong>' : '')
            . (isset($aLink['text']) ? $aLink['text'] : '')
            . '</a>'
        ;
    }

    /**
     * get html code to link to a given navitem
     * It is used to render a breadcrumb link
     * 
     * @param array  $aLink  array with link params
     *                       url
     *                       hint
     *                       icon
     *                       title
     *                       text
     * @return string
     */
    protected function _getLink2Navitem(string $sNavid): string
    {
        global $oRenderer;
        return $oRenderer->renderLink2Page($sNavid, $this->_getIcon($sNavid), $this->_sTab);
    }

    /**
     * Get html code to render child items of the current page
     * 
     * @param array  $aNav  array with nav items
     * @return string The HTML code
     */
    private function _renderChildItems($aNav): string
    {
        $sReturn = '';
        if (!isset($aNav['children']) || !is_array($aNav['children']) || !count($aNav['children'])) {
            return '';
        }
        foreach ($aNav['children'] as $sItem => $aSubItems) {
            if ($this->_sPage !== $sItem) {
                $sUrl = '?page=' . $sItem;
                $sClass = '';
                if ($this->_sTab) {
                    $sUrl .= '&amp;siteid=' . $this->_sTab;
                }
                // $sNavi.='<li class="pure-menu-item"><a href="?'.$sItem.'" class="pure-menu-link'.$sClass.'">'.$sItem.'</a></li>';
                if (
                    array_key_exists('menu', $this->aOptions)
                    && array_key_exists($sItem, $this->aOptions['menu'])
                    && !$this->aOptions['menu'][$sItem]
                ) {
                    // hide item
                } else {

                    if (!$this->_getNavAttrIsEnabled($aSubItems)) {
                        $sClass .= ' pure-menu-disabled';
                        $sUrl = '#';
                    }

                    $sReturn .= $this->_getLinkAsBox([
                        'url' => $sUrl,
                        'class' => $sClass,
                        'hint' => $this->lB('nav.' . $sItem . '.hint'),
                        'icon' => $this->_aIcons['menu'][$sItem],
                        'title' => $this->lB('nav.' . $sItem . '.label'),
                        'text' => $this->lB('nav.' . $sItem . '.hint'),
                    ])
                    ;
                }
            }
        }
        $sReturn .= '<div style="clear: both"></div>';
        return $sReturn;
    }

    /**
     * Get html code for document header: headline and hint
     * @return string
     */
    public function getHead(): string
    {
        $this->logAdd(__METHOD__ . '() start; page = "' . $this->_sPage . '"');
        if (!$this->checkAuth()) {
            $this->_sPage = 'login';
        }
        $sH2 = $this->lB('nav.' . $this->_sPage . '.label');
        $sHint = $this->lB('nav.' . $this->_sPage . '.hint');

        $sRight = '';
        if (BACKEND && ($this->_aHelpPages[$this->_sPage] ?? false)) {
            $sRight .= $this->_getButton([
                'href' => $this->_sBaseHelpUrl . $this->_aHelpPages[$this->_sPage],
                'label' => 'button.help',
                'class' => 'button',
                'target' => 'help'
            ]) . ' ';
        }

        if (!$this->_bIsPublic && $this->checkAuth() && $this->_getUser()) {

            $sRight .=
                $this->_sPage == 'userprofile'
                ? 
                    $this->_getButton([
                        'href' => 'javascript:history.back();',
                        'class' => 'button button-secondary',
                        'title' => $this->lB('button.userprofile'),
                        'customlabel' => $this->_getIcon('button.userprofile') . ' ' . $this->_getUser(),
                    ]) 
                : 
                    $this->_getButton([
                        'href' => './?page=userprofile',
                        'class' => 'button',
                        'title' => $this->lB('button.userprofile'),
                        'customlabel' => $this->_getIcon('button.userprofile') . ' ' . $this->_getUser(),
                    ]) 
                    
                . ' '

            ;

        }
        if ($this->_bIsPublic) {
            $sRight.=$this->getLangNavi();
        }


        $sRight = $sRight ? '<span class="topright">' . $sRight . '</span>' : '';


        $this->logAdd(__METHOD__ . ' H2 = "' . $sH2 . '"');
        return ''
            . $sRight
            . (isset($sH2) && $sH2 ? '<h2>' : '')
            . (isset($this->_aIcons['menu'][$this->_sPage])
                ? '<i class="' . $this->_aIcons['menu'][$this->_sPage] . '"></i> '
                : ''
            )
            . (isset($sH2) && $sH2 ? $sH2 . '</h2><p class="pageHint">' . $sHint . '</p>' : '')
        ;
    }
    /**
     * get custom html code for document footer/ statistic tracking
     * @return string
     */
    public function getCustomFooter(): string
    {
        $sReturn = '';
        $this->logAdd(__METHOD__ . '() start;');
        return implode("\n", $this->aOptions['output']['customfooter']);
    }

    /**
     * Find page specific javascript to be loaded optional on footer of html document
     * It returns relative url to js file
     * 
     * @return string
     */
    public function getMoreJS(): string
    {
        $sUrlJs = "javascript/functions-{$this->_sPage}.js";
        $sPageJs = dirname(__DIR__) . '/backend/' . $sUrlJs;
        return file_exists($sPageJs) ? $sUrlJs : '';
    }

    /**
     * Get current skin; it is the string of the subdir 
     * 
     * @since v0.150
     * 
     * @return string
     */
    public function getSkin(): string
    {
        return (isset($this->aOptions['skin']) && $this->aOptions['skin'])
            ? $this->aOptions['skin']
            : 'default'
        ;
    }
    /**
     * Get an array with available skins (=names of subdirs in ./backend/skins/*)
     * 
     * @since v0.150
     * 
     * @return array
     */
    public function getSkinsAvailable(): array
    {
        $aReturn = [];
        foreach (glob(dirname(__DIR__) . '/backend/skins/*') as $sSkinname) {
            if (is_dir($sSkinname)) {
                $aInfos = file_exists($sSkinname . '/info.json') ? json_decode(file_get_contents($sSkinname . '/info.json'), true) : ['name' => ''];
                $aInfos['label'] = basename($sSkinname);
                $aReturn[$aInfos['name']] = $aInfos;
            }
        }
        ksort($aReturn);
        return $aReturn;
    }

    /**
     * Get status to render the running action as footer in frontend
     * @return string
     */
    public function getStatus(): string
    {
        $oStatus = new status();
        $aStatus = $oStatus->getStatus();
        $sStatus = '';
        if ($aStatus && is_array($aStatus)) {
            $sStatus .= ''
                . $this->_getIcon('updateisrunning')
                . 'Start: ' . date("H:i:s", $aStatus['start'])
                . ' (' . ($aStatus['last'] - $aStatus['start']) . ' s): '
                . (isset($aStatus['action']) ? $aStatus['action'] : '[unknown action]')
                . ' - '
                . (isset($aStatus['lastmessage']) ? $aStatus['lastmessage'] : '...')
                . '<br>'
                // .'<pre>'.print_r($aStatus, 1).'</pre>'
            ;
        } else {
            // $sStatus=$this->lB('status.no-action');
        }
        return $sStatus;
    }

    // ----------------------------------------------------------------------
    // PROFILE/ CONFIG
    // ----------------------------------------------------------------------

    /**
     * Get array with search profiles
     * @return array
     */
    private function _getProfiles(): array
    {
        $aOptions = $this->_loadConfigfile();
        $aReturn = [];
        if (isset($aOptions['profiles']) && count($aOptions['profiles'])) {
            foreach ($aOptions['profiles'] as $sId => $aData) {
                $aReturn[$sId] = $aData['label'];
            }
        }
        asort($aReturn);
        return $aReturn;
    }

    // ----------------------------------------------------------------------
    // ANALYZE DATA
    // ----------------------------------------------------------------------

    /**
     * Check the table in the database has data already and return its data
     * It is used in the frontend to disable nav items as long an indexer 
     * did'nt run
     * It returns false if no data exist
     * 
     * @param string  $sTableitem  database table to verify
     * @return array|bool
     */
    public function hasDataInDb($sTableitem): array|bool
    {
        /* $aData=$this->_getStatusinfos(['_global']);
        return isset($aData['_global'][$sTableitem]['value']) 
            ? $aData['_global'][$sTableitem]['value']
            : false
            ;
         */
        $aData = $this->getStatusCounters('_global');
        return $aData[$sTableitem] ?? false;
    }

    /**
     * Get percent value with 2 digits after "."; 
     * it returns an empty string if argument was zero or false
     * 
     * @param float $floatValue
     * @return string
     */
    protected function _getPercent(float $floatValue): string
    {
        return $floatValue
            ? (
                $floatValue == 1 ? '100' : sprintf("%01.2f", 100 * $floatValue)
            ) . '%'
            : ''
        ;
    }

    /**
     * Get module name for ahCache
     * 
     * @return string
     */
    protected function _getCacheModule(): string
    {
        return 'project-' . $this->_sTab . '-backend';
    }

    /**
     * Get cache ID for ahCache
     * @param string $sMethod
     * @return string
     */
    protected function _getCacheId($sMethod): string
    {
        return $sMethod;
    }

    /**
     * TODO: 
     * get counters in crawler-base::getStatusCounters() and build visual 
     * information here
     * 
     * get hash analyse data with rating and counter
     * @see _getAnalyseData()
     * @staticvar array  $aStatusinfo  return data
     * @param array  $aPages  array of target page name; default: get infos for all targets
     * @return array
     */
    public function _getStatusinfos($aPages = false): array
    {
        global $oRenderer;
        static $aStatusinfo;

        if ($aPages === false) {
            $aPages = array_merge(['_global'], array_keys($this->_aIcons['menu']));
        }
        if (!isset($aStatusinfo)) {
            $aStatusinfo = [];
        }
        /*
        $oCache=new AhCache($this->_getCacheModule(), $this->_getCacheId(__METHOD__ . '-'. md5(serialize($aPages) )));
        // if(!$oCache->isExpired()){

        if(!$bIgnoreCache && $this->sLogFilename && $oCache->isNewerThanFile($this->sLogFilename)){
            $this->logAdd(__METHOD__.' returning cache data ... aPages = '.print_r($aPages, 1).' ... ['.$this->sLogFilename.']' );
            return $oCache->read();
        }
         * 
         */
        $this->logAdd(__METHOD__ . ' reading source data ... aPages = ' . print_r($aPages, 1) . ' ... [' . $this->sLogFilename . ']');
        // $aOptions = $this->getEffectiveOptions();
        $iCounter = 0;

        /*
        $iPagesCount=$this->getRecordCount('pages', ['siteid'=>$this->iSiteId)];
        $iRessourcesCount=$this->getRecordCount('ressources', ['siteid'=>$this->iSiteId]);
        $iSearchesCount=$this->getRecordCount('searches', ['siteid'=>$this->iSiteId)];
         * 
         */
        $aMyGlobalCounters = $this->getStatusCounters('_global');
        $iPagesCount = $aMyGlobalCounters['pages'];
        $iRessourcesCount = $aMyGlobalCounters['ressources'];
        $iSearchesCount = $aMyGlobalCounters['searches'];
        foreach ($aPages as $sPage) {
            if (!isset($aStatusinfo[$sPage])) {
                $aMyCounters = $this->getStatusCounters($sPage); // crawler-base.class.php
                // echo 'DEBUG '.__METHOD__.': $sPage = '.$sPage.'<pre>$aMyCounters = '.print_r($aMyCounters, 1).'</pre>';
                $aMsg = [];
                switch ($sPage) {
                    case '_global':
                        $aMsg['pages'] = [
                            'counter' => $iCounter++,
                            'status' => $iPagesCount ? 'info' : 'error',
                            'value' => $iPagesCount,
                            'message' => $iPagesCount ? false : sprintf($this->lB('status.emptyindex'), $this->_sTab),
                            'thead' => $this->lB('nav.search.label'),
                            'tfoot' => $this->getLastTsRecord('pages', ['siteid' => $this->_sTab]) . '<br>'
                                . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('pages', ['siteid' => $this->_sTab])))),
                            'page' => 'searchindexstatus',
                        ];
                        $aMsg['ressources'] = [
                            'counter' => $iCounter++,
                            'status' => $iRessourcesCount ? ($iRessourcesCount > 1 ? 'info' : 'warning') : 'error',
                            'value' => $iRessourcesCount,
                            'message' => $iRessourcesCount ? false : sprintf($this->lB('ressources.empty'), $this->_sTab),
                            'thead' => $this->lB('nav.ressources.label'),
                            'tfoot' => $this->getLastTsRecord('ressources', ['siteid' => $this->_sTab]) . '<br>'
                                . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('ressources', ['siteid' => $this->_sTab])))),
                            'page' => 'ressources',
                        ];
                        $aMsg['searches'] = [
                            'counter' => $iCounter++,
                            'status' => 'info',
                            'value' => $iSearchesCount,
                            'message' => $iSearchesCount ? false : $this->lB('searches.empty'),
                            'thead' => $this->lB('nav.searches.label'),
                            'tfoot' => $this->getLastTsRecord('searches', ['siteid' => $this->_sTab]) . '<br>'
                                . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('searches', ['siteid' => $this->_sTab])))),
                            'page' => 'searches',
                        ];
                        break;

                    // Analysis --> HTML checks
                    case 'htmlchecks':

                        $aOptions = $this->getEffectiveOptions();
                        /*
                        $oCrawler=new crawler($this->_sTab);
                        $aCounter=[];
                        $aCounter['countCrawlerErrors']=$oCrawler->getCount([
                            'AND' => [
                                'siteid' => $this->_sTab,
                                'errorcount[>]' => 0,
                            ]]);

                        $aCounter['countShortTitles']   = $this->_getHtmlchecksCount('title',       $aOptions['analysis']['MinTitleLength']);
                        $aCounter['countShortDescr']    = $this->_getHtmlchecksCount('description', $aOptions['analysis']['MinDescriptionLength']);
                        $aCounter['countShortKeywords'] = $this->_getHtmlchecksCount('keywords',    $aOptions['analysis']['MinKeywordsLength']);
                        $aCounter['countLargePages']    = $this->_getHtmlchecksLarger('size',       $aOptions['analysis']['MaxPagesize']);
                        $aCounter['countLongLoad']      = $this->_getHtmlchecksLarger('time',       $aOptions['analysis']['MaxLoadtime']);
                         * 
                         */
                        // (floor($iCountCrawlererrors/$iRessourcesCount*1000)/10).'%';
                        // sprintf("%01.2f", $money)
                        $aMsg['countCrawlerErrors'] = [
                            'counter' => $iCounter++,
                            'status' => $aMyCounters['countCrawlerErrors'] ? 'error' : 'ok',
                            'value' => $aMyCounters['countCrawlerErrors'],
                            'message' => false,
                            'thead' => $this->lB('htmlchecks.tile-crawlererrors'),
                            'tfoot' => $iPagesCount ? $this->_getPercent($aMyCounters['countCrawlerErrors'] / $iPagesCount) : '',
                            'thash' => $aMyCounters['countCrawlerErrors'] ? '#tblcrawlererrors' : '',
                        ];
                        $aMsg['countShortTitles'] = [
                            'counter' => $iCounter++,
                            'status' => $aMyCounters['countShortTitles'] ? 'warning' : 'ok',
                            'value' => $aMyCounters['countShortTitles'],
                            'message' => false,
                            'thead' => sprintf($this->lB('htmlchecks.tile-check-short-title'), $aOptions['analysis']['MinTitleLength']),
                            'tfoot' => $iPagesCount ? $this->_getPercent($aMyCounters['countShortTitles'] / $iPagesCount) : '',
                            'thash' => $aMyCounters['countShortTitles'] ? '#tblshorttitle' : '',
                        ];
                        $aMsg['countShortDescr'] = [
                            'counter' => $iCounter++,
                            'status' => $aMyCounters['countShortDescr'] ? 'warning' : 'ok',
                            'value' => $aMyCounters['countShortDescr'],
                            'message' => false,
                            'thead' => sprintf($this->lB('htmlchecks.tile-check-short-description'), $aOptions['analysis']['MinDescriptionLength']),
                            'tfoot' => $iPagesCount ? $this->_getPercent($aMyCounters['countShortDescr'] / $iPagesCount) : '',
                            'thash' => $aMyCounters['countShortDescr'] ? '#tblshortdescription' : '',
                        ];
                        $aMsg['countShortKeywords'] = [
                            'counter' => $iCounter++,
                            'status' => $aMyCounters['countShortKeywords'] ? 'warning' : 'ok',
                            'value' => $aMyCounters['countShortKeywords'],
                            'message' => false,
                            'thead' => sprintf($this->lB('htmlchecks.tile-check-short-keywords'), $aOptions['analysis']['MinKeywordsLength']),
                            'tfoot' => $iPagesCount ? $this->_getPercent($aMyCounters['countShortKeywords'] / $iPagesCount) : '',
                            'thash' => $aMyCounters['countShortKeywords'] ? '#tblshortkeywords' : '',
                        ];
                        $aMsg['countLongLoad'] = [
                            'counter' => $iCounter++,
                            'status' => $aMyCounters['countLongLoad'] ? 'warning' : 'ok',
                            'value' => $aMyCounters['countLongLoad'],
                            'message' => false,
                            'thead' => sprintf($this->lB('htmlchecks.tile-check-loadtime-of-pages'), $aOptions['analysis']['MaxLoadtime']),
                            'tfoot' => $iPagesCount ? $this->_getPercent($aMyCounters['countLongLoad'] / $iPagesCount) : '',
                            'thash' => '#tblloadtimepages',
                        ];
                        $aMsg['countLargePages'] = [
                            'counter' => $iCounter++,
                            'status' => $aMyCounters['countLargePages'] ? 'warning' : 'ok',
                            'value' => $aMyCounters['countLargePages'],
                            'message' => false,
                            'thead' => sprintf($this->lB('htmlchecks.tile-check-large-pages'), $aOptions['analysis']['MaxPagesize']),
                            'tfoot' => $iPagesCount ? $this->_getPercent($aMyCounters['countLargePages'] / $iPagesCount) : '',
                            'thash' => '#tbllargepages',
                        ];

                        break;

                    // Analysis --> HTTP header checks
                    case 'httpheaderchecks':

                        // default: detect first url in pages table
                        $aPagedata = $this->oDB->select(
                            'pages',
                            ['url', 'header'],
                            [
                                'AND' => [
                                    'siteid' => $this->_sTab,
                                ],
                                "ORDER" => ["id" => "ASC"],
                                "LIMIT" => 1
                            ]
                        );
                        if (count($aPagedata)) {
                            /*
                            $oHttpheader=new httpheader();
                            $sInfos=$aPagedata[0]['header'];
                            $aInfos=json_decode($sInfos,1);
                            // _responseheader ?? --> see crawler.class - method processResponse()
                            $oHttpheader->setHeaderAsString($aInfos['_responseheader']);

                            $aFoundTags=$oHttpheader->getExistingTags();

                            $iTotalHeaders=count($oHttpheader->getHeaderAs[]);
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
                             
                            */
                            $iTotalHeaders = $aMyCounters['responseheaderCount'];
                            $iKnown = $aMyCounters['responseheaderKnown'];
                            $iUnkKnown = $aMyCounters['responseheaderUnknown'];
                            $iUnwanted = $aMyCounters['responseheaderUnwanted'];
                            $iDeprecated = $aMyCounters['responseheaderDeprecated'];
                            $iNonStandard = $aMyCounters['responseheaderNonStandard'];
                            $iExperimental = $aMyCounters['responseheaderExperimental'];
                            $iCacheInfos = $aMyCounters['responseheaderCache'];
                            $iCompressionInfos = $aMyCounters['responseheaderCompression'];
                            $iSecHeader = $aMyCounters['responseheaderSecurity'];

                            // $aSecHeader=$oHttpheader->getSecurityHeaders();
                            $aMsg['total'] = [
                                'counter' => $iCounter++,
                                'status' => 'info',
                                'value' => $iTotalHeaders,
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.total'),
                                'tfoot' => '',
                            ];
                            $aMsg['http'] = [
                                'counter' => $iCounter++,
                                'status' => ($iKnown + $iSecHeader === $iTotalHeaders ? 'ok' : ($iKnown > 0 ? 'info' : 'error')),
                                'value' => $iKnown,
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.http'),
                                'tfoot' => '',
                                'thash' => '',
                            ];
                            $aMsg['unknown'] = [
                                'counter' => $iCounter++,
                                'status' => ($iUnkKnown ? 'warning' : 'ok'),
                                'value' => $iUnkKnown,
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.unknown'),
                                'tfoot' => $iTotalHeaders ? $this->_getPercent($iUnkKnown / $iTotalHeaders) : '',
                                'thash' => ($iUnkKnown ? '#warnunknown' : ''),
                            ];
                            $aMsg['deprecated'] = [
                                'counter' => $iCounter++,
                                'status' => ($iDeprecated ? 'warning' : 'ok'),
                                'value' => $iDeprecated,
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.deprecated'),
                                'tfoot' => $iTotalHeaders ? $this->_getPercent($iDeprecated / $iTotalHeaders) : '',
                                'thash' => ($iDeprecated ? '#warndeprecated' : ''),
                            ];
                            $aMsg['unwanted'] = [
                                'counter' => $iCounter++,
                                'status' => ($iUnwanted ? 'warning' : 'ok'),
                                'value' => $iUnwanted,
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.unwanted'),
                                'tfoot' => $this->_getPercent($iUnwanted / $iTotalHeaders),
                                'thash' => ($iUnwanted ? '#warnunwanted' : ''),
                            ];
                            $aMsg['nonstandard'] = [
                                'counter' => $iCounter++,
                                'status' => ($iNonStandard ? 'warning' : 'ok'),
                                'value' => $iNonStandard,
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.non-standard'),
                                'tfoot' => $this->_getPercent($iNonStandard / $iTotalHeaders),
                                'thash' => ($iNonStandard ? '#warnnonstandard' : ''),
                            ];
                            $aMsg['experimental'] = [
                                'counter' => $iCounter++,
                                'status' => ($iExperimental ? 'warning' : 'ok'),
                                'value' => $iExperimental,
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.experimental'),
                                'tfoot' => $iTotalHeaders ? $this->_getPercent($iExperimental / $iTotalHeaders) : '',
                                'thash' => ($iExperimental ? '#warnexperimental' : ''),
                            ];
                            $aMsg['httpversion'] = [
                                'counter' => $iCounter++,
                                'status' => $aMyCounters['responseheaderVersionStatus'],
                                'value' => $aMyCounters['responseheaderVersion'],
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.httpversion'),
                                'tfoot' => '',
                                'thash' => $aMyCounters['responseheaderVersionStatus'] == 'ok' ? '' : '#warnhttpver',
                            ];
                            $aMsg['cacheinfos'] = [
                                'counter' => $iCounter++,
                                'status' => ($iCacheInfos ? 'ok' : 'warning'),
                                'value' => $iCacheInfos ? $iCacheInfos : $oRenderer->renderShortInfo('miss'),
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.cache'),
                                'tfoot' => '',
                                'thash' => ($iCacheInfos ? '' : '#warnnocache'),
                            ];
                            $aMsg['compression'] = [
                                'counter' => $iCounter++,
                                'status' => ($iCompressionInfos ? 'ok' : 'warning'),
                                'value' => $iCompressionInfos ? $iCompressionInfos : $oRenderer->renderShortInfo('miss'),
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.compression'),
                                'tfoot' => '',
                                'thash' => ($iCompressionInfos ? '' : '#warnnocompression'),
                            ];
                            $aMsg['security'] = [
                                'counter' => $iCounter++,
                                'status' => ($iSecHeader ? 'ok' : 'warning'),
                                'value' => $iSecHeader ? $iSecHeader : $oRenderer->renderShortInfo('miss'),
                                'message' => false,
                                'thead' => $this->lB('httpheader.header.security'),
                                'tfoot' => '',
                                'thash' => '#securityheaders',
                            ];

                        }

                        break;

                    // Analysis --> SSL check
                    case 'sslcheck':
                        $sFirstUrl = isset($this->aProfileSaved['searchindex']['urls2crawl'][0]) ? $this->aProfileSaved['searchindex']['urls2crawl'][0] : false;

                        if (!$sFirstUrl) {
                            // $sReturn.='<br>'.$this->_getMessageBox($this->lB('sslcheck.nostarturl'), 'warning');
                            $aMsg['certstatus'] = [
                                'counter' => $iCounter++,
                                'status' => 'error',
                                'value' => '',
                                'message' => $this->lB('sslcheck.nostarturl'),
                                'thead' => '',
                                'tfoot' => '',
                            ];
                        } else if (strstr($sFirstUrl, 'http://')) {

                            $aMsg['certstatus'] = [
                                'counter' => $iCounter++,
                                'status' => 'error',
                                'value' => $this->lB('sslcheck.httponly.description'),
                                'message' => $this->lB('sslcheck.httponly') . ' ' . $this->lB('sslcheck.httponly.description') . ' ' . $this->lB('sslcheck.httponly.hint'),
                                'thead' => $this->lB('sslcheck.httponly'),
                                'tfoot' => $this->lB('sslcheck.httponly.hint'),
                            ];
                        } else {
                            // TODO: cache infos ... for 1 h
                            $oSsl = new sslinfo();
                            $aSslInfos = $oSsl->getSimpleInfosFromUrl($sFirstUrl);
                            if (isset($aSslInfos['CN'])) {
                                $sStatus = $oSsl->getStatus();
                                $aSslInfosAll = $oSsl->getCertinfos($url = false);
                                $iDaysleft = round((date("U", strtotime($aSslInfos['validto'])) - date('U')) / 60 / 60 / 24);
                                $aMsg['certstatus'] = [
                                    'counter' => $iCounter++,
                                    'status' => $sStatus,
                                    'data' => $aSslInfos,
                                    'value' => $aSslInfos['issuer'],
                                    'message' => $aSslInfos['issuer'] . ': ' . $aSslInfos['CN'] . '; ' . $aSslInfos['validto'] . ' (' . $iDaysleft . ' d) '
                                        . ($aSslInfos['chaining'] ? '' : $this->lB('sslcheck.chaining.fail')),
                                    'thead' => $aSslInfos['CN'],
                                    'tfoot' => $aSslInfos['validto'] . ' (' . $iDaysleft . ' d)',
                                ];
                            }
                        }
                        break;
                    case 'linkchecker':
                        if ($iRessourcesCount) {
                            $oRessources = new ressources($this->_sTab);

                            $aCountByStatuscode = $oRessources->getCountsOfRow(
                                'ressources',
                                'http_code',
                                [
                                    'siteid' => $this->_sTab,
                                    'isExternalRedirect' => '0',
                                ]
                            );
                            if (!count($aCountByStatuscode)) {
                                /*
                                 * TODO: leave a messgae that scan is not finished
                                 * -a update -d ressources -p [N]
                                 * 
                                $aMsg['ressources-unfinished']=[
                                    'counter'=>$iCounter++,
                                    'status'=>'error', 
                                    'value'=>0, 
                                    'message'=>$iRessourcesCount ? false : sprintf($this->lB('ressources.not-finished'), $this->_sTab),
                                    'thead'=>$this->lB('nav.ressources.label'),
                                    'tfoot'=>$this->getLastTsRecord('ressources', ['siteid'=>$this->_sTab]).'<br>'
                                    . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('ressources', ['siteid'=>$this->_sTab])))),
                                    'page'=>'linkchecker',
                                ];
                                 */
                            } else {
                                $aTmpItm = ['status' => [], 'total' => 0];

                                // TODO: 
                                // use $aMyCounters['status...']
                                // 
                                // 4540 kB |add counter ... statusTodo = 10.
                                // 4540 kB |add counter ... statusTodo[-1] = 10.
                                // 4540 kB |add counter ... statusError = 0.
                                // 4540 kB |add counter ... statusWarning = 0.
                                // 4540 kB |add counter ... statusOk = 75.
                                // 4540 kB |add counter ... statusOk[200] = 75.
                                // 
                                $aBoxes = ['Todo' => $aTmpItm, 'Error' => $aTmpItm, 'Warning' => $aTmpItm, 'Ok' => $aTmpItm];
                                foreach (array_keys($aBoxes) as $sSection) {
                                    $aHttpStatus = [];
                                    foreach ($aMyCounters as $sKey => $ivalue) {
                                        if (strstr($sKey, $sSection . '[')) {
                                            $iStatuscode = preg_replace('/.*\[(.*)\]/i', '\1', $sKey);
                                            $aHttpStatus[$iStatuscode] = $ivalue;
                                        }
                                    }
                                    $iBoxvalue = $aMyCounters['status' . $sSection];
                                    $sStatus = (!$iBoxvalue || $sSection === 'ok' ? 'ok' : strtolower($sSection));
                                    $aMsg[strtolower($sSection)] = [
                                        'counter' => $iCounter++,
                                        '_data' => $aHttpStatus,
                                        'status' => $sStatus,
                                        'value' => $aMyCounters['status' . $sSection],
                                        'message' => false,
                                        'thead' => $this->lB('linkchecker.found-http-' . strtolower($sSection)),
                                        'tfoot' => $this->_getPercent($iBoxvalue / $iRessourcesCount),
                                        'thash' => ($iBoxvalue ? '#h3-' . strtolower($sSection) : ''),
                                    ];

                                }
                                // echo '<pre>'.print_r( $aMsg,1).'</pre>';
                            }
                        }
                        break;
                }
                if (count($aMsg)) {
                    foreach ($aMsg as $skey => $aItem) {
                        if (!isset($aItem['message']) || !$aItem['message']) {
                            $aMsg[$skey]['message'] = str_replace('<br>', ' ', '<strong>' . $aItem['value'] . '</strong> ' . $aItem['thead'] . ($aItem['tfoot'] ? ' (' . $aItem['tfoot'] . ')' : ''));
                        }
                    }
                    $aStatusinfo[$sPage] = $aMsg;
                }
            }
        }
        // $oCache->write($aStatusinfo, 10);
        return $aStatusinfo;
    }

    /**
     * Get hash of analytics messages based on level
     * 
     * @see _getStatusinfos()
     * 
     * @param string  $sLevel  level; one or error|warning|ok|info
     * @return array
     */
    protected function _getStatusInfoByLevel($sLevel = ''): array
    {
        $aReturn = [];
        foreach ($this->_getStatusinfos() as $sTarget => $aInfos) {
            foreach ($aInfos as $sCountername => $aData) {
                if ($aData['status'] === $sLevel) {
                    $aReturn[$aData['counter']] = $aData;
                    $aReturn[$aData['counter']]['target'] = $sTarget;
                }
            }
        }
        ksort($aReturn);
        return $aReturn;
    }

    /**
     * Get HTML code for tilees on top of a page
     * It detects the current page and "knowns" what to render
     * 
     * @return string
     */
    protected function _getTilesOfAPage(): string
    {
        global $oRenderer;
        $sReturn = '';
        $sPage = $this->getPage();
        if (!$sPage) {
            return '';
        }
        $aTileData = $this->_getStatusinfos([$sPage]);
        if (!isset($aTileData[$sPage])) {
            return '';
        }
        // echo '<pre>'.print_r($aTileData[$sPage], 1).'</pre>';
        foreach ($aTileData[$sPage] as $sKey => $aItem) {
            $sReturn .= $oRenderer->renderTile($aItem['status'], $aItem['thead'], $aItem['value'], $aItem['tfoot'], (isset($aItem['thash']) && $aItem['thash'] ? $aItem['thash'] : ''));
        }
        return $sReturn;
    }

    // ----------------------------------------------------------------------
    // OUTPUT RENDERING
    // ----------------------------------------------------------------------

    /**
     * Get html code for a result table.
     * It returns an empty string if $aResult is empty
     * 
     * @param array  $aResult          result of a select query
     * @param string $sLangTxtPrefix   langtext prefix
     * @param string $sTableId         value of id attribute for the table
     * @return string
     */
    private function _getHtmlTable(array $aResult, string $sLangTxtPrefix = '', string $sTableId = ''): string
    {
        if (!count($aResult)) {
            return '';
        }
        $sReturn = '';
        $aFields = false;
        foreach ($aResult as $aRow) {
            if (!$aFields) {
                $aFields = array_keys($aRow);
            }
            $sReturn .= '<tr>';
            foreach ($aFields as $sField) {
                $sReturn .= '<td class="td-' . $sField . '">' . $aRow[$sField] . '</td>';
            }
            $sReturn .= '</tr>';
        }
        if ($sReturn) {
            $sTh = '';
            foreach ($aFields as $sField) {
                $sIcon = array_key_exists($sField, $this->_aIcons['cols']) ? '<i class="' . $this->_aIcons['cols'][$sField] . '"></i> ' : '[' . $sField . ']';

                $sTh .= '<th class="th-' . $sField . '">' . $sIcon . $this->lB($sLangTxtPrefix . $sField) . '</th>';
            }
            // $sReturn = '<table class="pure-table pure-table-horizontal pure-table-striped datatable">'
            $sReturn = '<table' . ($sTableId ? ' id="' . $sTableId . '"' : '') . ' class="pure-table pure-table-horizontal datatable">'
                . '<thead><tr>' . $sTh . '</tr></thead>'
                . '<tbody>' . $sReturn . '</tbody>'
                . '</table>'
            ;
        }
        return $sReturn;
    }

    /**
     * Get html code for a simple table without table head
     * It returns an empty string if $aResult is empty
     * 
     * @param array  $aResult          result of a select query
     * @param array  $bFirstIsHeader   flag: first record is header line; default is false
     * @return string
     */
    private function _getSimpleHtmlTable(array $aResult, bool $bFirstIsHeader = false, string $sTableId = ''): string
    {
        if (!count($aResult)) {
            return '';
        }
        $sReturn = '';
        $bIsFirst = true;
        $sTHeader = '';
        foreach ($aResult as $aRow) {
            $sReturn .= '<tr>';
            foreach ($aRow as $sField) {
                if ($bFirstIsHeader && $bIsFirst) {
                    $sTHeader .= '<th>' . $sField . '</th>';
                } else {
                    $sReturn .= '<td>' . $sField . '</td>';
                }
            }
            $sReturn .= '</tr>';
            $bIsFirst = false;
        }
        if ($sReturn) {
            $sReturn = '<table' . ($sTableId ? ' id="' . $sTableId . '"' : '') . ' class="pure-table pure-table-horizontal datatable">'
                . '<thead>' . ($sTHeader ? '<tr>' . $sTHeader . '</tr>' : '') . '</thead>'
                . '<tbody>' . $sReturn . '</tbody>'
                . '</table>';
        }
        return $sReturn;
    }

    /**
     * Get HTML code for a button like link
     * 
     * @param array $aOptions  Options for the button; known subkeys are
     *                         - href        {string}  target url
     *                         - class       {string}  css class
     *                         - onclick     {string}  onclick value
     *                         - target      {string}  target window
     *                         - title       {string}  text in title attribue; default: lang specific text from 'label'
     *                         - label       {string}  language key for the button tor ender icon + text
     *                         - customlabel {string}  custom label
     * @return string
     */
    private function _getButton(array $aOptions = []): string
    {
        $sReturn = '';
        if (!array_key_exists('href', $aOptions)) {
            $aOptions['href'] = '#';
        }
        if (!array_key_exists('class', $aOptions)) {
            $aOptions['class'] = '';
        }
        if (!array_key_exists('target', $aOptions)) {
            $aOptions['target'] = '';
        }
        if (!array_key_exists('label', $aOptions)) {
            $aOptions['label'] = 'button.view';
        }
        /*
        if (!array_key_exists('popup', $aOptions)) {
            $aOptions['popup'] = true;
        }
         */
        $sReturn = '<a '
            . 'class="pure-button ' . $aOptions['class'] . '" '
            . 'href="' . $aOptions['href'] . '" '
            . 'target="' . $aOptions['target'] . '" '
            . 'title="' . ($aOptions['title']??false
                ? $aOptions['title'] 
                : $this->lB($aOptions['label'] . '.hint') 
            )
            . '" '

            . (isset($aOptions['onclick'])
                ? 'onclick="' . $aOptions['onclick'] . '" '
                : ''
            )
            // . ($aOptions['popup'] ? 'onclick="showModal(this.href); return false;"' : '')
            . '>' 
                . ($aOptions['customlabel']??false
                    ? $aOptions['customlabel']
                    : $this->_getIcon($aOptions['label']) . $this->lB($aOptions['label']) 
                )
            
            . '</a>';
        return $sReturn;
    }

    /**
     * Get HTML code for an icon
     * 
     * @param string  $sKey             index key for the icon group; one of menu|cols|res|button
     * @param bool    $bEmptyIfMissing  optional: flag: return empty string if icon not found; default is false to show a text for a missing icon
     * @return string
     */
    private function _getIcon(string $sKey, bool $bEmptyIfMissing = false): string
    {
        foreach (array_keys($this->_aIcons) as $sIconsection) {
            if (isset($this->_aIcons[$sIconsection][$sKey])) {
                return '<i class="' . $this->_aIcons[$sIconsection][$sKey] . '"></i> ';
            }
        }
        return $bEmptyIfMissing ? '' : '<span title="missing icon [' . $sKey . ']">[' . $sKey . ']</span>';
    }

    /**
     * Prettify table output: limit a string to a maximum and insert space
     * If the output text is larger N bytes then a textarea will be used
     * 
     * @param null|string  $sVal   string
     * @param int          $iMax   max length for the string to show as text; if larger a textarea will be used
     * @return string
     */
    private function _prettifyString(null|string $sVal, int $iMax = 500): string
    {
        if ($sVal) {
            $sVal = str_replace(',', ', ', $sVal);
            $sVal = str_replace(',  ', ', ', $sVal);
            return (strlen($sVal) > $iMax)
                ? '<textarea class="pure-input" cols="100" rows="10">' . $sVal . '</textarea><br>'
                : htmlentities($sVal)
            ;
        }
        return '';
        // $sVal = htmlentities($sVal);
        // return (strlen($sVal) > $iMax) ? substr($sVal, 0, $iMax) . '<span class="more"></span>' : $sVal;
    }

    /**
     * Get html code for a search index table
     * 
     * @param array  $aResult          result of a select query
     * @param string $sLangTxtPrefix   langtext prefix
     * @param string $sTableId         value of id attribute for the table
     * @param bool   $bShowLegend      flag: show a legend box below the table
     * @return string
     */
    private function _getSearchindexTable(array $aResult, string $sLangTxtPrefix = '', string $sTableId = '', bool $bShowLegend = true): string
    {
        $aTable = [];
        $oRenderer = new ressourcesrenderer($this->_sTab);
        foreach ($aResult as $aRow) {
            $sId = $aRow['id'];
            unset($aRow['id']);
            foreach ($aRow as $sKey => $sVal) {
                $aRow[$sKey] = $this->_prettifyString($sVal);
            }
            $sUrl = $aRow['url'];
            $aRow['url'] = '<a href="./?' . $_SERVER['QUERY_STRING'] . '&id=' . $sId . '">' . str_replace('/', '/&shy;', $aRow['url']) . '</a>';
            $aRow['actions'] = ''
                . '<a href="' . $sUrl . '" target="_blank" class="pure-button" title="' . $this->lB('ressources.link-to-url') . '">' . $oRenderer->_getIcon('link-to-url') . '</a>';
            /*
            $this->_getButton([
            // 'href' => 'overlay.php?action=viewindexitem&id=' . $sId,
            'href' => './?'.$_SERVER['QUERY_STRING'].'&id='.$sId,
            'popup' => false,
            'class' => 'pure-button',
            'label' => 'button.view'
        ]);
            */
            $aTable[] = $aRow;
        }
        $aKeys = array_keys($aResult[0]);
        if ($aKeys[0] === 'id') {
            unset($aKeys[0]);
        }
        return $this->_getHtmlTable($aTable, $sLangTxtPrefix, $sTableId)
            . ($bShowLegend ? $this->_getHtmlLegend($aKeys, $sLangTxtPrefix) : '')
        ;
    }

    // ----------------------------------------------------------------------


    // ----------------------------------------------------------------------
    // PAGE CONTENT
    // ----------------------------------------------------------------------

    /**
     * Wrapper function: get page content as html by including the current 
     * page file.
     * If it requires a login, it will return the login form.
     * 
     * @return string
     */
    public function getContent(): string
    {
        if (!$this->checkAuth()) {
            return $this->_getLoginForm();
        }
        return include $this->_sPageFile;
    }

    /**
     * Wrapper function: get page content of a public page as html
     * 
     * @return string
     */
    public function getPublicContent(): string
    {
        $sPagefile = 'backend/pages/public_' . $this->_sPage . '.php';
        return include $sPagefile;
    }

    /**
     * Wrapper function: get update info text and link to update page.
     * It returns an empty string...
     * - on a public page
     * - in the backend if a user is logged in
     * - if no update is available
     * 
     * @return string The HTML code for an update
     */
    public function getUpdateInfobox(): string
    {
        global $oRenderer;
        if ($this->_bIsPublic) {
            return '';
        }
        return $this->checkAuth() && $this->oUpdate->hasUpdate()
            ? $oRenderer->renderMessagebox(
                sprintf($this->lB('update.available-yes'), $this->oUpdate->getLatestVersion())
                . ' '
                . '<a href="?page=update">' . $this->lB('nav.update.label') . '</a>'
                ,
                'warning'
            )
            : '';
    }

    /**
     * Get html code to show screenshot of the current profile
     * @return string The HTML code
     */
    public function getProfileImage(): string
    {
        return (isset($this->aProfileSaved['profileimagedata']) && $this->aProfileSaved['profileimagedata']
            ? '<img src="' . $this->aProfileSaved['profileimagedata'] . '" class="profile" title="' . $this->aProfileSaved['label'] . '" alt="' . $this->aProfileSaved['label'] . '">'
            : ''
        );
    }

    /**
     * return html + js code to draw a chart (pie or bar)
     * 
     * @staticvar int $iChartCount  number of the chart in the current page
     * 
     * @param array $aOptions
     *              valid keys are
     *              type   {string}  one of pie|bar
     *              data   {array}   data of values ... each item has the keys
     *                                 label  {string}  label text for tooltip and 
     *                                 value  {float}   value
     *                                 color  {string}  color value; can be a js function
     *              datasets  {array}  array of multiple data arrays 
     *                                 label {string}  label for legend of this dataset
     *                                 data  {array}   data items (see param data)
     *              limit  {float}   render a limit value (for bars)
     *              avg    {float}   render an average value (for bars)
     *              legend_display  {bool}  show legend for datarows
     * @return string The HTML code
     */
    private function _getChart(array $aOptions): string
    {

        static $iChartCount;
        if (!isset($iChartCount)) {
            $iChartCount = 0;
        }
        $iChartCount++;

        $sDomIdDiv = 'chart-div-' . $iChartCount;
        $sDomIdCanvas = 'chart-canvas-' . $iChartCount;
        $sVarChart = 'chartConfig' . $iChartCount;
        $sVarCtx = 'chartCtx' . $iChartCount;

        $bShowLegend = $aOptions['type'] !== 'bar';
        $bShowLegend = isset($aOptions['legend_display']) ? $aOptions['legend_display'] : $bShowLegend;

        $bShowRaster = $aOptions['type'] === 'bar';

        $sDatasets = '';
        $sLimit = '';
        $sAvg = '';
        if (isset($aOptions['datasets'])) {
            $aDatasets = $aOptions['datasets'];
        }
        if (isset($aOptions['data'])) {
            $aDatasets[]['data'] = $aOptions['data'];
        }

        if (count($aDatasets)) {

            $dsLabels = [];
            foreach ($aDatasets as $aDataset) {
                $dsData = [
                    'values' => [],
                    'colors' => [],
                ];
                foreach ($aDataset['data'] as $aItem) {
                    if (!$sDatasets) {
                        $dsLabels[] = $aItem['label'];
                    }
                    $dsData['values'][] = $aItem['value'];
                    $dsData['colors'][] = $aItem['color'];
                    if (isset($aOptions['limit']) && $aOptions['limit']) {
                        $sLimit .= ($sLimit ? ', ' : '') . $aOptions['limit'];
                    }
                    if (isset($aOptions['avg']) && $aOptions['avg']) {
                        $sAvg .= ($sAvg ? ', ' : '') . $aOptions['avg'];
                    }

                }
                /*
                 *                             {
                                data: '.json_encode($aOptions['values']).',
                                backgroundColor: '. str_replace('"', '', json_encode($aOptions['colors'])).',
                                borderWidth: 1,
                                fill: false
                            }

                 */
                $sDatasets .= ($sDatasets ? ', ' : '')
                    . '{
                        label: \'' . (isset($aDataset['label']) ? $aDataset['label'] : '') . '\',
                        data: ' . json_encode($dsData['values']) . ',
                        backgroundColor: ' . str_replace('"', '', json_encode($dsData['colors'])) . ',
                        borderWidth: 0,
                        fill: false                    
                    }';
            }
        }
        return '
            <div id="' . $sDomIdDiv . '" class="piechart piechart-' . $aOptions['type'] . '">
		        <canvas id="' . $sDomIdCanvas . '"></canvas>
            </div>
            <script>
                var ' . $sVarChart . ' = {
                    type: \'' . $aOptions['type'] . '\',
                    data: {
                        datasets: [
                            ' . $sDatasets . '
                            ' . ($sLimit
                ? ', {
                                    type: \'line\',
                                    data: JSON.parse(\'[' . $sLimit . ']\'),
                                    backgroundColor: \'#c00\',
                                    borderColor: \'#c00\',
                                    borderWidth: 1,
                                    fill: false,
                                    radius: 0
                                }'
                : ''
            ) . '
                            ' . ($sAvg
                ? ', {
                                    type: \'line\',
                                    data: JSON.parse(\'[' . $sAvg . ']\'),
                                    backgroundColor: \'#56a\',
                                    borderColor: \'#56a\',
                                    borderWidth: 1,
                                    borderDash: [3, 3],
                                    fill: false,
                                    radius: 0
                                }'
                : ''
            ) . '
                        ],
                        labels: ' . json_encode($dsLabels) . '
                    },
                    options: {
                        animation: {
                            duration: 500
                        },
                        plugins: {
                            legend: {
                                display: ' . ($bShowLegend ? 'true' : 'false') . '
                            }
                        },
                        responsive: true,
                        scales: {
                          x: {
                            display: ' . ($bShowRaster ? 'true' : 'false') . ',
                            stacked: true,
                          },
                          y: {
                            display: ' . ($bShowRaster ? 'true' : 'false') . ',
                            stacked: ' . (count($aDatasets) > 1 ? "true" : "false") . ',
                            ticks: {
                              // forces step size to be 50 units
                              stepSize: 50
                            }
                          }
                        }                        
                    }
                    
                };

                // window.onload = function() {
                    var ' . $sVarCtx . ' = document.getElementById("' . $sDomIdCanvas . '").getContext("2d");
                    window.myPie = new Chart(' . $sVarCtx . ', ' . $sVarChart . ');
                // };
            </script>
        ';
    }

    /**
     * Get html code for history counter
     * 
     * @param array  $sCounteritem  id or array of ids to render; with 
     *                                     multiple ids its data will be stacked
     * @return string
     */
    private function _getHistoryCounter(array $sCounteritem): string
    {

        $sHtml = '';
        // ----- config 
        $sColorDefault = 'getStyleRuleValue(\'color\', \'.chartcolor-1\')';
        $sColorDefault2 = 'getStyleRuleValue(\'color\', \'.chartcolor-2\')';
        $sColorDefault3 = 'getStyleRuleValue(\'color\', \'.chartcolor-3\')';
        $sColorWarning = 'getStyleRuleValue(\'color\', \'.chartcolor-warning\')';
        $sColorError = 'getStyleRuleValue(\'color\', \'.chartcolor-error\')';
        $sColorOK = 'getStyleRuleValue(\'color\', \'.chartcolor-ok\')';

        $aBarColors = [
            'countCrawlerErrors' => $sColorError,
            'countLargePages' => $sColorWarning,
            'countLongLoad' => $sColorWarning,
            'countShortDescr' => $sColorWarning,
            'countShortKeywords' => $sColorWarning,
            'countShortTitles' => $sColorWarning,

            'pages' => $sColorDefault,
            'ressources' => $sColorDefault2,
            // 'searches'=>$sColorDefault,

            'responseheaderDeprecated' => $sColorWarning,
            'responseheaderKnown' => $sColorOK,
            'responseheaderNonStandard' => $sColorWarning,
            'responseheaderSecurity' => $sColorOK,
            'responseheaderUnknown' => $sColorWarning,
            'responseheaderUnwanted' => $sColorWarning,

            'statusError' => $sColorError,
            'statusOk' => $sColorOK,
            'statusWarning' => $sColorWarning,
            'TotalErrors' => $sColorError,
            'TotalWarnings' => $sColorWarning,

        ];
        require_once 'counter.class.php';
        $oCounter = new counter();
        $oCounter->mysiteid($this->iSiteId);

        // read data
        $bEnough = false;
        $aCounterItems2Fetch = is_array($sCounteritem) ? $sCounteritem : [$sCounteritem];
        foreach ($aCounterItems2Fetch as $sCItem) {
            $aPageHistory[$sCItem] = $oCounter->getCountersHistory($sCItem);
            if (count($aPageHistory[$sCItem]) > 3) {
                $bEnough = true;
            }
        }

        if ($bEnough) {

            $aDatasets = [];
            foreach ($aPageHistory as $sCItem => $aDataset) {

                $aHistoryData = [
                    'label' => $this->lB('chart.' . $sCItem),
                    'data' => [],
                ];
                $sColor = isset($aBarColors[$sCItem]) ? $aBarColors[$sCItem] : $sColorDefault;

                // 'getStyleRuleValue(\'color\', \'.chartcolor-error\')'
                foreach ($aDataset as $aDataitem) {
                    $aHistoryData['data'][] = [
                        'label' => substr($aDataitem['ts'], 0, 10),
                        // 'label'=>$sCItem,
                        // 'label'=>'',
                        'value' => $aDataitem['value'],
                        'color' => $sColor,
                    ];
                }
                $aDatasets[] = $aHistoryData;
            }

            $sHtml .= ''
                . $this->lB('chart.historicalView') . ':<br><br>'
                . '<div class="floatleft">'
                . $this->_getChart([
                    'type' => 'bar',
                    'datasets' => $aDatasets,
                    'label' => array_keys($aPageHistory),
                    'legend_display' => count($aCounterItems2Fetch) > 1,
                ])
                . '</div>'

                // . '<pre>getCountersHistory("'.$sCounteritem.'") returns<br>' . print_r($aPageHistory, 1) . '</pre>'

                . '<div style="clear: left;"></div>'
            ;
        }
        return $sHtml;
    }

    /**
     * Get html code to draw a chart by given sql query and show max N items
     * Used on page html checks
     * 
     * @param string  $sQuery   sql query to fetch data
     * @param string  $sColumn  column to show bars
     * @param int     $iLimit   max value for OK; if higher then bars will be in warning color
     * @return string The HTML code
     */
    private function _getChartOfRange(string $sQuery, string $sColumn, int $iLimit): string
    {
        $aTable = [];
        $aData = [];
        $iMaxItems = 50;
        $iNextStep = 0;
        $i = 0;

        $aTmp = $this->oDB->query($sQuery)->fetchAll(PDO::FETCH_ASSOC);
        if (!$aTmp || !count($aTmp)) {
            return '';
        }
        $iStep = round(count($aTmp) / $iMaxItems);

        $iTotal = 0;

        foreach ($aTmp as $aRow) {
            $i++;
            if ($i > $iNextStep) {
                $iIsOK = $iLimit > $aRow[$sColumn];
                $aData[] = [
                    // 'label'=>$aRow['url'],
                    'label' => '',
                    // 'label'=>($iIsOK ? '< ' : '> ') .$iMaxLoadtime,
                    'value' => $aRow[$sColumn],
                    'color' => 'getStyleRuleValue(\'color\', \'.chartcolor-' . ($iIsOK ? 'ok' : 'warning') . '\')',
                    // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': '.,
                ];
                $iNextStep += $iStep;
            }
            $iTotal += $aRow[$sColumn];

        }
        return $this->_getChart([
            // 'type'=>'line',
            'type' => 'bar',
            'data' => $aData,
            'limit' => $iLimit,
            'avg' => $iTotal / count($aTmp),
        ]);
    }

    /**
     * Get get html code for a stacked bar chart of total, warnings, errors
     * Used on page htmlchecks, headerchecks, sslchecks
     *
     * @param integer  $iTotal      total items
     * @param integer  $iWarnings   count of warnings
     * @param integer  $iErrors     count of errors
     * @return string The HTML code for the chart
     */
    private function _getHtmlchecksChart(int $iTotal, int $iWarnings, int $iErrors = 0): string
    {
        $aData = [];
        if ($iErrors) {
            $aData[] = [
                'label' => $this->lB('htmlchecks.label-errors'),
                'value' => $iErrors,
                'color' => 'getStyleRuleValue(\'color\', \'.chartcolor-error\')',
                // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': '.,
            ];
        }
        if ($iWarnings) {
            $aData[] = [
                'label' => $this->lB('htmlchecks.label-warnings'),
                'value' => $iWarnings,
                'color' => 'getStyleRuleValue(\'color\', \'.chartcolor-warning\')',
                // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': '.,
            ];
        }
        $aData[] = [
            'label' => $this->lB('htmlchecks.label-ok'),
            'value' => ($iTotal - $iWarnings - $iErrors),
            'color' => 'getStyleRuleValue(\'color\', \'.chartcolor-ok\')',
            // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': '.,
        ];
        return $this->_getChart([
            'type' => 'pie',
            'data' => $aData,
        ]);
    }

    /**
     * Get html code for a table of too short elements.
     * sed in htmlcheck
     * 
     * @param string  $sQuery     query to fetch data
     * @param string  $sTableId   table id
     * @return string
     */
    private function _getHtmlchecksTable(string $sQuery, string $sTableId = ''): string
    {
        $oRenderer = new ressourcesrenderer($this->_sTab);
        $aTmp = $this->oDB->query($sQuery)->fetchAll(PDO::FETCH_ASSOC);
        $aTable = [];
        foreach ($aTmp as $aRow) {
            // $aRow['_']=print_r($aRow, 1);
            $sUrl = $aRow['url'];
            $aRow['url'] = '<a 
                href="./?page=searchindexstatus&siteid=' . $this->_sTab . '&id=' . $aRow['id'] . '" 
                >' . str_replace('/', '/&shy;', $sUrl) . '</a>';

            $aRow['actions'] = ''
                . '<a href="' . $sUrl . '" target="_blank" class="pure-button" title="' . $this->lB('ressources.link-to-url') . '">' . $oRenderer->_getIcon('link-to-url') . '</a>';

            unset($aRow['id']);

            $aTable[] = $aRow;
            /*
            $aData[]=[
                    'label'=>$this->lB('htmlchecks.label-warnings'),
                    'value'=>$iWarnings,
                    'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-warning\')',
                    'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-ok\')',
                    // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': '.,
            ];
            */
        }
        // echo "<pre>$sQuery<br>".print_r($aTmp,1).'</pre>';
        if (!isset($aTmp[0])) {
            return '';
        }
        $aKeys = array_keys($aTmp[0]);
        return $this->_getHtmlTable($aTable, "db-pages.", $sTableId)
            . $this->_getHtmlLegend($aKeys, 'db-pages.')
        ;
    }

    /**
     * Get html code to display a legend
     * 
     * @param string|array  $Content  legend text or an array of ids
     * @param string        $sPrefix  for arrays as $Content: a prefix to scan for prefix+id in lang file
     * @return string
     */
    private function _getHtmlLegend(string|array $Content, string $sPrefix = ''): string
    {
        global $oRenderer;
        $sLegend = '';
        if (is_array($Content)) {
            foreach ($Content as $sKey) {
                $sLegend .= ($sLegend ? '<br>' : '')
                    . '<strong>' . $this->_getIcon($sKey) . ' ' . $this->lB($sPrefix . $sKey) . '</strong><br>'
                    . $this->lB($sPrefix . $sKey . '.description') . '<br>'
                ;
            }
        } else {
            $sLegend = $Content;
        }
        return $oRenderer->renderToggledContent($this->lB('label.legend'), $sLegend, true);
    }


    // ----------------------------------------------------------------------
}
