<?php

require_once 'ahwi-updatecheck.class.php';
require_once 'analyzer.html.class.php';
require_once 'crawler-base.class.php';
require_once 'crawler.class.php';
require_once 'httpheader.class.php';
require_once 'ressources.class.php';
require_once 'renderer.class.php';
require_once 'search.class.php';
require_once 'sslinfo.class.php';
require_once 'status.class.php';
require_once 'cache.class.php';

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
 * 
 * BACKEND
 * 
 */
class backend extends crawler_base {

    private $_aMenu = array(
        'home'=>array(
            'children'=>array(
                'searchindexstatus'=>array('needs'=>array('pages')), 
                'searchindextester'=>array('needs'=>array('pages')), 
                'searches'=>array('needs'=>array('searches')),
                'crawlerlog'=>array(), 
                'profiles'=>array(),
           ),
        ), 
        // 'search'=>array(),
        'analysis'=>array(
            'children'=>array(
                'sslcheck'=>array('needs'=>array('starturl')), 
                'httpheaderchecks'=>array('needs'=>array('pages')), 
                'cookies'=>array(), 
                'htmlchecks'=>array('needs'=>array('pages')), 
                'linkchecker'=>array('needs'=>array('ressources')), 
                'ressources'=>array('needs'=>array('ressources')),
                'checkurl'=>array('needs'=>array('ressources')), 
                'ressourcedetail'=>array('needs'=>array('ressources')), 
            ),
        ), 
        'tools'=>array(
            'children'=>array(
                'bookmarklet'=>array(), 
                'httpstatuscode'=>array(), 
                'langedit'=>array(), 
                'update'=>array(), 
            ),
        ),
        'settings'=>array(
            'children'=>array(
                'setup'=>array(),
                'vendor'=>array(), 
            ),
        ),
        'about'=>array(
        )
    );
    private $_aMenuPublic = array(
        'home'=>array(),
        'httpheaderchecks'=>array(),
        'sslcheck'=>array(),
        'about'=>array(),
    );
    
    private $_bIsPublic = false;
    private $_sPage = false;
    private $_sPageFile = false;
    private $_sTab = false;
    
    private $_aIcons= array(
        'menu'=>array(
            'login'=>'fas fa-user-lock', 
            'home'=>'fas fa-home', 
            'settings'=>'fas fa-cogs', 
            'setup'=>'fas fa-sliders-h', 
            'profiles'=>'fas fa-globe-americas', 
            'crawlerlog'=>'fas fa-file-alt', 
            'vendor'=>'fas fa-box-open', 
            'search'=>'fas fa-database', 
            'crawler'=>'fas fa-flag', 
            'searchindexstatus'=>'fas fa-flag', 
            'searchindextester'=>'fas fa-search', 
            'searches'=>'fas fa-chart-pie', 
            // 'analysis'=>'fa fa-newspaper-o', 
            'analysis'=>'fas fa-chart-line', 
            'sslcheck'=>'fas fa-shield-alt', 
            'ressources'=>'far fa-file-code', 
            'linkchecker'=>'fas fa-chart-pie', 
            'linkchecker'=>'fas fa-link', 
            'htmlchecks'=>'fab fa-html5', 
            'httpheaderchecks'=>'far fa-flag', 
            'cookies'=>'fas fa-cookie-bite', 
            'checkurl'=>'fas fa-globe-americas', 
            'ressourcedetail'=>'fas fa-map-marked', 
            'tools'=>'fas fa-tools', 
            'bookmarklet'=>'fas fa-bookmark', 
            'httpstatuscode'=>'fab fa-font-awesome', 
            'langedit'=>'far fa-comment', 
            'about'=>'fas fa-info-circle', 
            'update'=>'fas fa-cloud-download-alt', 
            'project'=>'fas fa-book', 
            
            'logoff'=>'fas fa-power-off', 
        ),
        'cols'=>array(
            '1'=>'far fa-comment', 
            '2'=>'far fa-comment', 
            'id'=>'fas fa-hashtag', 
            'summary'=>'far fa-comment', 
            'ranking'=>'fas fa-chart-bar', 
            'url'=>'fas fa-link', 
            'title'=>'fas fa-chevron-right', 
            'description'=>'fas fa-chevron-right', 
            'label'=>'fas fa-chevron-right', 
            'icon'=>'far fa-image', 
            'errorcount'=>'fas fa-bolt', 
            'keywords'=>'fas fa-key', 
            'lasterror'=>'fas fa-bolt', 
            'actions'=>'fas fa-check', 
            'searchset'=>'fas fa-cube', 
            'query'=>'fas fa-search', 
            'results'=>'fas fa-bullseye', 
            'count'=>'fas fa-thumbs-up', 
            'host'=>'fas fa-laptop', 
            'ua'=>'fas fa-paw', 
            'referrer'=>'fas fa-link', 
            'status'=>'far fa-flag', 
            'todo'=>'fas fa-magic', 
            'ts'=>'fas fa-calendar', 
            'ressourcetype'=>'fas fa-cubes', 
            'type'=>'fas fa-cloud', 
            'content_type'=>'far fa-file-code', 
            'http_code'=>'fas fa-retweet', 
            'length'=>'fas fa-arrows-alt-h', 
            'size'=>'fa ', 
            'time'=>'far fa-clock', 
            'words'=>'fas fa-arrows-alt-h', 
            
            'updateisrunning'=>'fas fa-spinner fa-spin', 
            
            // cookies
            'domain'=>'fas fa-atlas', 
            'path'=>'fas fa-folder', 
            'name'=>'fas fa-tag ', 
            'value'=>'fas fa-chevron-right', 
            'httponly'=>'far fa-flag', 
            'secure'=>'fas fa-shield-alt', 
            'expiration'=>'far fa-clock', 
            
        ),
        'res'=>array(
            /*
            
            // ressourcetype
            'audio'=>'far fa-file-sound',
            'css'=>'far fa-eyedropper',
            'image'=>'far fa-file-image',
            'link'=>'fas fa-link',
            'page'=>'far fa-sticky-note',
            // 'redirect'=>'fa fa-mail-forward',
            'redirect'=>'fas fa-angle-double-right',
            'script'=>'far fa-file-code',
            
            // type
            'internal'=>'fas fa-thumb-tack',
            'external'=>'fas fa-globe-americas',
            
            // content_type/ MIME
            
            // http_code
            'http-code-0'=>'fas fa-spinner',
            'http-code-2xx'=>'fas fa-check',
            'http-code-3xx'=>'fas fa-angle-double-right',
            'http-code-4xx'=>'far fa-bolt',
            'http-code-5xx'=>'fas fa-spinner',
             */
            
            'filter'=>'fas fa-filter',

            'url'=>'fas fa-globe',
            'docs'=>'fas fa-book',
            'source'=>'fas fa-code',
            
            'ressources.showtable'=>'fas fa-table',
            'ressources.showreport'=>'far fa-file',
            'ressources.ignorelimit'=>'fas fa-unlock',

            'ssl.type-none'=>'fas fa-lock-open',
            'ssl.type-selfsigned'=>'fas fa-user-lock',
            'ssl.type-Business SSL'=>'fas fa-lock',
            'ssl.type-EV'=>'fas fa-shield-alt',
            
        ),
        'button'=>array(
            'button.add' => 'fas fa-plus',
            'button.back' => 'fas fa-chevron-left',
            'button.close' => 'fas fa-times',
            'button.continue' => 'fas fa-chevron-right',
            'button.create' => 'far fa-star',
            'button.delete' => 'fas fa-trash',
            'button.down' => 'fas fa-arrow-down',
            'button.download'=>'fas fa-cloud-download-alt', 
            'button.edit' => 'fas fa-pencil-alt',
            'button.help' => 'fas fa-question-circle',
            'button.home' => 'fas fa-home',
            'button.login' => 'fas fa-check',
            'button.logoff' => 'fas fa-power-off',
            'button.openurl' => 'fas fa-external-link-alt',
            'button.refresh' => 'fas fa-sync',
            'button.save' => 'fas fa-paper-plane',
            'button.search' => 'fas fa-search',
            'button.truncateindex' => 'fas fa-trash',
            'button.up' => 'fas fa-arrow-up',
            'button.updatesinglestep' => 'fas fa-chevron-right',
            'button.view' => 'far fa-eye',
        ),
    );
    
    public $iLimitRessourcelist=1000;
    
    public $oUpdate = false;

    // ----------------------------------------------------------------------
    /**
     * new crawler
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct($iSiteId = false, $bIsPublic=false) {
        $this->_oLog=new logger();
        if($bIsPublic){
            $this->_bIsPublic=true;
            $this->_aMenu=$this->_aMenuPublic;
        }
        if (!isset($_SESSION)) {
            session_start();
        }
        
        // for settings: create a default array with all available menu items
        foreach($this->_aMenuPublic as $sKey=>$aItem){
            $this->aDefaultOptions['menu-public'][$sKey]=false;
        }
        
        if($bIsPublic){
            $this->setSiteId(false);
            $this->aOptions['menu']=$this->aOptions['menu-public'];
            $this->setLangPublic();
            $this->logAdd(__METHOD__.' public lang was set');
        } else {

            // for settings: create a default array with all available menu items
            foreach($this->_aMenu as $sKey=>$aItem){
                $this->aDefaultOptions['menu'][$sKey]=true;
                if (isset($aItem['children'])){
                    foreach(array_keys($aItem['children']) as $sKey2){
                        $this->aDefaultOptions['menu'][$sKey2]=true;
                    }
                }
            }
            $iSiteId=$iSiteId ? $iSiteId : $this->_getRequestParam('siteid', false, 'int');
            $this->logAdd(__METHOD__.' iSiteId detected as '.$iSiteId);
            $this->setSiteId($iSiteId);
            $this->logAdd(__METHOD__.' site id was set to '.$this->iSiteId);
            $this->setLangBackend();
            $this->logAdd(__METHOD__.' backend lang was set');
            /*
             * 
             */
            $this->oUpdate=new ahwiupdatecheck(array(
                    'product'=>$this->aAbout['product'],
                    'version'=>$this->aAbout['version'],
                    'baseurl'=>$this->aOptions['updater']['baseurl'],
                    'tmpdir'=>($this->aOptions['updater']['tmpdir'] ? $this->aOptions['updater']['tmpdir'] : __DIR__.'/../tmp/'),
                    'ttl'=>$this->aOptions['updater']['ttl'],
            ));
            // echo "getUpdateInfos : </pre>" . print_r($this->oUpdate->getUpdateInfos(), 1).'</pre>';
        }
        
        $this->_getPage();
        $this->logAdd(__METHOD__.' getPage was finished');
        
        return true;
    }
    // ----------------------------------------------------------------------
    // LOGIN
    // ----------------------------------------------------------------------

    /**
     * check authentication if a user and password were configured
     * @global array  $aUserCfg  config from ./config/config_user.php
     * @return boolean
     */
    public function checkAuth() {
        if($this->_bIsPublic){
            return true;
        }
        $aOptions = $this->_loadConfigfile();
        if (!isset($aOptions['options']['auth']['user']) || $this->_getUser()
        ) {
            return true;
        }
        if (
                array_key_exists('AUTH_USER', $_POST) && array_key_exists('AUTH_PW', $_POST) && $aOptions['options']['auth']['user'] == $_POST['AUTH_USER'] && password_verify($_POST['AUTH_PW'], $aOptions['options']['auth']['password'])
        ) {
            $this->_setUser($_POST['AUTH_USER']);
            return true;
        }
        
        if (
                array_key_exists('AUTH_USER', $_POST) && array_key_exists('AUTH_PW', $_POST) && $aOptions['options']['auth']['user'] == $_POST['AUTH_USER'] && $aOptions['options']['auth']['password'] == md5($_POST['AUTH_PW'])
        ) {
            die('SORRY, the password handler function was exchanged by a stronger variant.<br>'
                    . '<br>'
                    . 'In config/crawler.config.json ...<br>'
                    . '<br>'
                    . 'remove the entry options -> auth -> user.<br>'
                    . 'Then reload and go to the settings to set the user and password again.<br>'
                    . '<br>'
                    . 'OR<br>'
                    . '<br>'
                    . 'Get a new password hash on commandline by<br>'
                    . '<code> php -r "echo password_hash(\'mypassword\', PASSWORD_DEFAULT);"</code><br>'
                    . 'and enter the output into options -> auth -> password');
        }
        return false;
    }

    /**
     * get the username of the current user
     * @return boolean
     */
    private function _getUser() {
        if (!array_key_exists('AUTH_USER', $_SESSION)) {
            return false;
        }
        return $_SESSION['AUTH_USER'];
    }

    /**
     * set an authenticated user user
     * @param string  $sNewUser
     * @return boolean
     */
    private function _setUser($sNewUser) {
        if (!$sNewUser) {
            // ... means: logoff
            unset($_SESSION['AUTH_USER']);
            return false;
        }
        $_SESSION['AUTH_USER'] = $sNewUser;
        return $_SESSION['AUTH_USER'];
    }

    /**
     * get html code of a login form
     * @return string
     */
    private function _getLoginForm() {
        $sReturn = '';

        header('HTTP/1.0 401 Unauthorized');
        $aTable = array();
        $aTable[] = array(
            '<label for="euser">' . $this->lB('login.username') . '</label>',
            '<input type="text" id="euser" name="AUTH_USER" value="" required="required" placeholder="' . $this->lB('login.username') . '">'
        );
        $aTable[] = array(
            '<label for="epw">' . $this->lB('login.password') . '</label>',
            '<input type="password" id="epw" name="AUTH_PW" value="" required="required" placeholder="' . $this->lB('login.password') . '">'
        );

        $sHref = '?' . str_replace('page=logoff', '', $_SERVER['QUERY_STRING']);

        $sReturn = ''
                /*
                . '<h3>' . $this->lB('login.title') . '</h3>'
                 */
                . '<div style="background:#fff; margin-top: 12em; padding: 1em;">'
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
                        . '<button type="submit" class="pure-button button-secondary">' .$this->_getIcon('button.login'). $this->lB('button.login') . '</button>'
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
     * get new querystring - create the new querystring by existing query string
     * of current request and given new parameters
     * @param array $aQueryParams
     * @return string
     */
    private function _getQs($aQueryParams) {
        if ($_GET) {
            $aQueryParams = array_merge($_GET, $aQueryParams);
        }
        return '?'.  preg_replace('/%5B[0-9]+%5D/simU', '[]', http_build_query($aQueryParams));
    }

    /**
     * find the current page (returns one of the menu items of _aMenu)
     * @return string
     */
    private function _getPage() {
        // $sPage = $this->_getRequestParam('page','/^[a-z]*$/');
        $sPage = $this->_getRequestParam('page');
        if (!$sPage) {
            $aKeys=array_keys($this->_aMenu);
            $sPage = $aKeys[0];
        }
        $sFilename=dirname(__DIR__).'/backend/pages/'.($this->_bIsPublic ? 'public_' : '').$sPage.'.php';
        if(!file_exists($sFilename)){
            $sPage='error404';
            $sFilename=dirname(__DIR__).'/backend/pages/'.$sPage.'.php';
            header("HTTP/1.0 404 Not Found");
        }
        $this->_sPage=$sPage;
        $this->_sPageFile=$sFilename;

        return $this->_sPage;
    }

    /**
     * find the current tab from url param siteid=... 
     * or take the first id of given array (of profiles)
     * It returns 0..N (id of profile) or a string (of allowed GET param)
     * 
     * @return string|integer
     */
    private function _getTab($bAllowSpecialSiteids=false) {
        $sAdd = $bAllowSpecialSiteids ? $this->_getRequestParam('siteid', '/add/') : '';
        $sAll = $bAllowSpecialSiteids ? $this->_getRequestParam('siteid', '/all/') : '';
        $this->_sTab = $sAdd.$sAll ? $sAdd.$sAll : $this->_getRequestParam('siteid', false, 'int');
        if ($this->_sTab && $this->_sTab!=='add') {
            /*
            setcookie(
                "tab", 
                $this->_sTab, 
                array(
                    'expires'=>time() + 60*60*8,
                    'path'=>'/',
                    'domain'=>$_SERVER["HTTP_HOST"],
                    'secure'=>isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] ? true : false,
                    'httponly'=>true,
                    'samesite'=>'Strict',
                )
            );
             * 
             */
            $_SESSION['siteid']=$this->_sTab;
        }
        /*
        if (!$this->_sTab && array_key_exists('tab', $_COOKIE)) {
            // header('location: '.$_SERVER['REQUEST_URI'].'&siteid='.$_COOKIE['tab']);
            // $this->_sTab = $_COOKIE['tab'];
            $this->_sTab = $_SESSION['siteid'];
        }
         * 
         */

        if (!$this->_sTab) {
            $aTmp = array_keys($this->_getProfiles());
            $this->_sTab = count($aTmp) ? $aTmp[0] : false;
        }

        return $this->_sTab;
    }

    /**
     * helper for navigation: is a menu item hidden by user config?
     * @param type $sItem
     * @return boolean
     */
    public function isNavitemHidden($sItem=false){
        if(!$sItem){
            $sItem=$this->_sPage;
        }
        return array_key_exists('menu', $this->aOptions)
                && array_key_exists($sItem, $this->aOptions['menu'])
                && !$this->aOptions['menu'][$sItem]
        ;
        
    }
    protected function _getNavAttrIsEnabled($aItem){
        if(!isset($aItem['needs']) || !is_array($aItem['needs']) || !count($aItem['needs']) ){
            return true;
        }
        foreach($aItem['needs'] as $sTable){
            if ($this->hasDataInDb($sTable)){
                return true;
            }
            if($sTable=='starturl'){
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
    
    private function _getNavItems($aNav){
        $sNavi = '';
        
        // $aProfiles=$this->getProfileIds();
        // $bHasProfile=($aProfiles && count($aProfiles));
        
        // for first runs after setup:
        // disable nav items if there no profile was set so far
        /*
        $aDisabled=!$bHasProfile 
                ? array('search','analysis')
                : array()
                ;
        
         * 
         */
        // echo '<pre>'.print_r($aDisabled,1).'</pre>';
        // echo '<pre>'.print_r($aNav,1).'</pre>'; die();
        foreach ($aNav as $sItem=>$aSubItems) {
            $sNaviNextLevel='';
            // echo $this->_sPage . '<pre>'.print_r($aNav, 1).'</pre>'; die();
            if (isset($aSubItems['children']) && count($aSubItems['children'])){
                $sNaviNextLevel.=$this->_getNavItems($aSubItems['children']);
            }
            // check options->menu: is this item hidden?
            /*
            if(array_key_exists('menu', $this->aOptions)
                    && array_key_exists($sItem, $this->aOptions['menu'])
                    && !$this->aOptions['menu'][$sItem]
            ){
             */
            if($this->isNavitemHidden($sItem)){
                // do nothing means: hide menu item
                
            } else {
                $bHasActiveSubitem=strpos($sNaviNextLevel, 'pure-menu-link-active');
                $bIsActive=$this->_sPage == $sItem || $bHasActiveSubitem;
                $sClass = $bIsActive ? ' pure-menu-link-active' : '';
                $sUrl = '?page=' . $sItem.($this->_bIsPublic ? '&amp;lang='.$this->sLang : '');
                if ($this->_sTab) {
                    $sUrl.='&amp;siteid=' . $this->_sTab;
                }
                
                if (!$this->_getNavAttrIsEnabled($aSubItems)){
                    $sClass .= ' pure-menu-disabled';
                    $sUrl='#';
                }

                // $sNavi.='<li class="pure-menu-item"><a href="?'.$sItem.'" class="pure-menu-link'.$sClass.'">'.$sItem.'</a></li>';
                $sNavi.='<li class="pure-menu-item">'
                    . '<a href="' . $sUrl . '" class="pure-menu-link' . $sClass . '"'
                        . ' title="' . $this->lB('nav.' . $sItem . '.hint') . '"'
                        . '><i class="'.$this->_aIcons['menu'][$sItem].'"></i>'
                        . '<span> ' . $this->lB('nav.' . $sItem . '.label') . '</span>' 
                    . '</a>'
                    . ($bIsActive ? $sNaviNextLevel : '')
                    ;
                
                $sNavi.='</li>';
            }
        }
        if($sNavi || true){
            $sNavi='<ul class="pure-menu-list">'.$sNavi.'</ul>';
        }
        
        return $sNavi;
    }
    
    /**
     * get html code for navigation; the current page is highlighted
     * @return string
     */
    public function getNavi() {
        if (!$this->checkAuth()) {
            return '';
        }
        if (!$this->installationWasDone()){
            return '';
        }
        
        $sNavi = $this->_getNavItems($this->_aMenu);
        return $sNavi;
    }
    
    /**
     * get languages
     * @param string $sLangobject
     * @return type
     */
    public function getLangs($sLangobject=false) {
        $aLangfiles=array();
        $aLangkeys=array();
        
        // automatic set of object if not given
        $sLangobject=$sLangobject 
                ? $sLangobject 
                : ($this->_bIsPublic ? 'frontend' : 'backend')
                ;
        foreach(glob(dirname(__DIR__).'/lang/'.$sLangobject.'.*.json') as $sJsonfile){
            $sKey2=str_replace($sLangobject.'.','',basename($sJsonfile));
            $sKey2=str_replace('.json','',$sKey2);

            $aLangfiles[$sKey2]=$sJsonfile;
            $aLangkeys[]=$sKey2;
        }
        
        return count($aLangkeys) 
            ? array(
                'keys'=>$aLangkeys,
                'files'=>$aLangfiles,
            )
            : false;
    }

    public function getLangNavi(){
        global $oRenderer;
        $sReturn='';
        $aLangs=$this->getLangs();
        if(!$aLangs){
            return false;
        }
        $aLangOptions=array();
        foreach($aLangs['keys'] as $sLang){
            $sClass='pure-menu-link' . ($sLang == $this->sLang ? ' pure-menu-link-active' : '');
            $sReturn.='<li class="'.$sClass.'">'.$sLang.'</li>';
            $aOption=array(
                'label'=>$sLang,
            );
            if($sLang == $this->sLang) {
                $aOption['selected']='selected';
            }
            $aLangOptions[]=$aOption;
        }
        return '<div class="langnav">'
            . '<form class="pure-form pure-form-aligned" method="GET" action="?">'
                . '<input type="hidden" name="page" value="'.(isset($_GET['page']) ? $_GET['page'] : '').'">'
                .$oRenderer->oHtml->getFormSelect(array(
                'id'=>'sellang', 
                'name'=>'lang',
                'onchange'=>'submit();',
                ), $aLangOptions
            ).'</form>'
        . '</div>';

        return '<ul class="pure-menu-list">'.$sReturn.'</ul><br>';
    }
    /**
     * get html code for project selection
     * 
     * @param array    $aTabs        nav items
     * @param boolean  $bAddButton   flag for add button; default false; set true on profile setup
     * @param string   $sUpUrl       url for "up" tab in front of other tabs
     * 
     * @return string
     */
    private function _getNavi2($aTabs=array(), $bAddButton=false, $sUpUrl=false) {
        $sReturn = '';
        $sMore = '';
        if (!$this->_sTab) {            
            $this->_getTab();
        }
        if($bAddButton){
            $aTabs['add']=$this->_getIcon('button.add').$this->lB('profile.new');
            if($this->_getTab($bAddButton)!=='add'){
                $sUrl = '?page=' . $this->_sPage . '&amp;siteid=add';
                $sMore = ' <a href="'.$sUrl.'" class="pure-button button-success">'.$this->_getIcon('button.add').$this->lB('profile.new').'</a>';
            }
        }
        if($sUpUrl){
            $sReturn.='<li class="pure-menu-item">'
                    . '<a href="' . $sUpUrl . '" class="pure-menu-link"'
                    . '>' . $this->_getIcon('button.up') . '</a></li>';
        }
        $sOptions='';
        if(count($aTabs)){
            foreach ($aTabs as $sId => $sLabel) {
                $sUrl = '?page=' . $this->_sPage . '&amp;siteid=' . $sId;
                $sOptions.='<option'
                            . ' value="' . $sUrl . '"'
                            .(($this->_sTab == $sId) ? ' selected="selected"' : '') 
                        . '>'
                        . $this->_getIcon('project') . $sLabel . '</option>';
            }
            if ($sOptions) {
                $sOptions=''
                        . '<span>'
                        . $this->_getIcon('project') 
                        . $this->lB('home.select-project').' '
                        . '</span>'
                        . '<select>'
                            . $sOptions
                        . '</select>'
                        ;
            }
        }
        $sReturn = ''
                // . '<div class="pure-menu pure-menu-horizontal">'
                . '<form class="pure-form pure-form-aligned">'
                    . '<div id="selectProject" class="pure-control-group">'
                    . $sOptions
                    . $sMore
                    . '</div>'
                . '</form>';
        /*
        foreach ($aTabs as $sId => $sLabel) {
            $sUrl = '?page=' . $this->_sPage . '&amp;siteid=' . $sId;
            $sClass = ($this->_sTab == $sId) ? ' pure-menu-link-active' : '';
            $sReturn.='<li class="pure-menu-item">'
                    . '<a href="' . $sUrl . '" class="pure-menu-link' . $sClass . '"'
                    . '>' . $this->_getIcon('project') . $sLabel . '</a></li>';
        }
        if ($sReturn) {
            $sReturn = ''
                    // . '<div class="pure-menu pure-menu-horizontal">'
                    . '<div id="nav2" class="pure-menu custom-restricted-width">'
                    . '<ul class="pure-menu-list">'
                    . '' . $sReturn . ''
                    . '</ul>'
                    . '</div>';
        }
         * 
         */
        return $sReturn;
    }
    /**
     * get html code for a link in a box 
     * used for child items
     * 
     * @param array  $aLink  array with link params
     *                       url
     *                       class  optional css class - "pure-menu-disabled"
     *                       hint
     *                       icon
     *                       title
     *                       text
     * @return string
     */
    protected function _getLinkAsBox($aLink){
        
        return
            '<a href="' . $aLink['url'] . '" '
                . 'class="childitem'
                . (isset($aLink['class']) ? ' ' . $aLink['class'] : '')
                . '"'
                . (isset($aLink['hint']) ? ' title="' . $aLink['hint'].'"' : '')
                . '>'
                . (isset($aLink['icon']) ? '<i class="'.$aLink['icon'].'"></i> ' : '')
                . '<strong>'.$aLink['title'].'</strong>'
                . (isset($aLink['text']) ? $aLink['text'] : '')
            . '</a>'
            ;
    }
    /**
     * get html code for a link in a box 
     * used for child items
     * 
     * @param array  $aLink  array with link params
     *                       url
     *                       hint
     *                       icon
     *                       title
     *                       text
     * @return string
     */
    protected function _getLink2Navitem($sNavid){
        global $oRenderer;
        return $oRenderer->renderLink2Page($sNavid, $this->_getIcon($sNavid), $this->_sTab);
    }

    /**
     * get html code for a message box 
     * @deprecated since 0.105 - use $oRenderer->renderMessagebox($sMessage, $sLevel)
     * @param type $sMessage  message text
     * @param type $sLevel    level ok|warning|error
     * @return string
     */
    protected function _getMessageBox($sMessage, $sLevel='warning'){
        
        return '<div class="message message-'.$sLevel.'">'
                . 'DEPRECATED $oBackend->_getMessageBox()!!!<br>'
                // . $oRenderer->renderShortInfo($sLevel)
                . $sMessage
                . '</div>'
                ;
    }
    /**
     * 
     * @return string
     */
    private function _renderChildItems($aNav){
        $sReturn='';
        if(!isset($aNav['children']) || !is_array($aNav['children']) ||!count($aNav['children'])){
            return '';
        }
        foreach ($aNav['children'] as $sItem=>$aSubItems) {
            if ($this->_sPage!==$sItem){
                $sUrl = '?page=' . $sItem;
                $sClass='';
                if ($this->_sTab) {
                    $sUrl.='&amp;siteid=' . $this->_sTab;
                }
                // $sNavi.='<li class="pure-menu-item"><a href="?'.$sItem.'" class="pure-menu-link'.$sClass.'">'.$sItem.'</a></li>';
                if(array_key_exists('menu', $this->aOptions)
                        && array_key_exists($sItem, $this->aOptions['menu'])
                        && !$this->aOptions['menu'][$sItem]
                ){
                    // hide item
                } else {
                    
                    if (!$this->_getNavAttrIsEnabled($aSubItems)){
                        $sClass .= ' pure-menu-disabled';
                        $sUrl='#';
                    }
                    
                    $sReturn.=$this->_getLinkAsBox(array(
                            'url'=>$sUrl,
                            'class'=>$sClass,
                            'hint'=>$this->lB('nav.' . $sItem . '.hint'),
                            'icon'=>$this->_aIcons['menu'][$sItem],
                            'title'=>$this->lB('nav.' . $sItem . '.label'),
                            'text'=>$this->lB('nav.' . $sItem . '.hint'),
                        ))
                        ;
                }
            }
        }
        $sReturn.='<div style="clear: both"></div>';
        return $sReturn;
    }
    
    /**
     * get html code for document header: headline and hint
     * @return string
     */
    public function getHead() {
        $sReturn='';
        $this->logAdd(__METHOD__ . '() start; page = "' . $this->_sPage . '"');
        if (!$this->checkAuth()) {
            $this->_sPage='login';
        }
        $sH2 = $this->lB('nav.' . $this->_sPage . '.label');
        $sHint = $this->lB('nav.' . $this->_sPage . '.hint');
                
                
        $this->logAdd(__METHOD__ . ' H2 = "'.$sH2.'"');
        return ''
                . (!$this->_bIsPublic && $this->checkAuth() && $this->_getUser()
                    ? '<span style="z-index: 100000; position: fixed; right: 1em; top: 1em;">'
                        . $this->_getButton(array(
                            'href' => './?page=logoff',
                            'class' => 'button-secondary',
                            'label' => 'button.logoff',
                            'popup' => false
                        ))
                        . '</span>'
                    : ''
                )
                . (isset($sH2) && $sH2 ? '<h2>' : '')
                . (isset($this->_aIcons['menu'][$this->_sPage]) 
                    ? '<i class="'.$this->_aIcons['menu'][$this->_sPage].'"></i> '
                    : ''
                    )
                . (isset($sH2) && $sH2 ? $sH2 . '</h2><p class="pageHint">' . $sHint . '</p>' : '')
        ;
    }
    /**
     * get custom html code for document footer/ statistic tracking
     * @return string
     */
    public function getCustomFooter() {
        $sReturn='';
        $this->logAdd(__METHOD__ . '() start;');
        return implode("\n", $this->aOptions['output']['customfooter']);
    }
    
    /**
     * find page specific javascript to be loaded optional on footer of html document
     * It returns relative url to js file
     * @return string
     */
    public function getMoreJS(){
        $sUrlJs='javascript/functions-'.$this->_sPage.'.js';
        $sPageJs=dirname(__DIR__).'/backend/'.$sUrlJs;
        return file_exists($sPageJs) ? $sUrlJs : '';
    }
    
    public function getStatus() {
        $oStatus=new status();
        $aStatus=$oStatus->getStatus();
        $sStatus='';
        if ($aStatus && is_array($aStatus)){
            $sStatus.=''
                    . $this->_getIcon('updateisrunning')
                    . 'Start: '.date("H:i:s", $aStatus['start'])
                    . ' ('. ($aStatus['last']-$aStatus['start']).' s): '
                    . (isset($aStatus['action'])      ? $aStatus['action']      : '[unknown action]')
                    . ' - '
                    . (isset($aStatus['lastmessage']) ? $aStatus['lastmessage'] : '[unknown message]') 
                    .'<br>'
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
     * get array with search profiles
     * @return array
     */
    private function _getProfiles() {
        $aOptions = $this->_loadConfigfile();
        $aReturn = array();
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
    
    public function hasDataInDb($sTableitem){
        /* $aData=$this->_getStatusinfos(array('_global'));
        return isset($aData['_global'][$sTableitem]['value']) 
            ? $aData['_global'][$sTableitem]['value']
            : false
            ;
         */
        $aData=$this->getStatusCounters('_global');
        return isset($aData[$sTableitem]) 
            ? $aData[$sTableitem]
            : false
            ;
    }
    /**
     * get percent value with 2 digits after "."; it returns an empty string on zero
     * @param float $floatValue
     * @return string
     */
    protected function _getPercent($floatValue){
        return $floatValue ? 
                (
                    $floatValue==1 ? '100' : sprintf("%01.2f", (100*$floatValue))
                ).'%' : '';
    }
    
    protected function _getCacheModule(){
        return 'project-'.$this->_sTab.'-backend';
    }
    protected function _getCacheId($sMethod){
        $sReturn='';
        $sReturn.=$sMethod;
        return $sReturn;
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
    public function _getStatusinfos($aPages=false, $bIgnoreCache=false){
        global $oRenderer;
        static $aStatusinfo;

        if($aPages===false){
            $aPages=array_merge(array('_global'), array_keys($this->_aIcons['menu']));
        }
        if(!isset($aStatusinfo)){
            $aStatusinfo=array();
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
        $this->logAdd(__METHOD__.' reading source data ... aPages = '.print_r($aPages, 1).' ... ['.$this->sLogFilename.']');
        // $aOptions = $this->getEffectiveOptions();
        $iCounter=0;
        
        /*
        $iPagesCount=$this->getRecordCount('pages', array('siteid'=>$this->iSiteId));
        $iRessourcesCount=$this->getRecordCount('ressources', array('siteid'=>$this->iSiteId));
        $iSearchesCount=$this->getRecordCount('searches', array('siteid'=>$this->iSiteId));
         * 
         */
        $aMyGlobalCounters=$this->getStatusCounters('_global');
        $iPagesCount=$aMyGlobalCounters['pages'];
        $iRessourcesCount=$aMyGlobalCounters['ressources'];
        $iSearchesCount=$aMyGlobalCounters['searches'];
        foreach ($aPages as $sPage){
            if(!isset($aStatusinfo[$sPage])){
                $aMyCounters=$this->getStatusCounters($sPage); // crawler-base.class.php
                // echo 'DEBUG '.__METHOD__.': $sPage = '.$sPage.'<pre>$aMyCounters = '.print_r($aMyCounters, 1).'</pre>';
                $aMsg=array();
                switch ($sPage){
                    case '_global':
                        $aMsg['pages']=array(
                            'counter'=>$iCounter++,
                            'status'=>$iPagesCount ? 'info' : 'error', 
                            'value'=>$iPagesCount, 
                            'message'=>$iPagesCount ? false : sprintf($this->lB('status.emptyindex'), $this->_sTab),
                            'thead'=>$this->lB('nav.search.label'),
                            'tfoot'=>$this->getLastTsRecord('pages', array('siteid'=>$this->_sTab)).'<br>'
                            . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('pages', array('siteid'=>$this->_sTab))))),
                            'page'=>'searchindexstatus',
                        );
                        $aMsg['ressources']=array(
                            'counter'=>$iCounter++,
                            'status'=>$iRessourcesCount ? ($iRessourcesCount>1 ? 'info' : 'warning') : 'error', 
                            'value'=>$iRessourcesCount, 
                            'message'=>$iRessourcesCount ? false : sprintf($this->lB('ressources.empty'), $this->_sTab),
                            'thead'=>$this->lB('nav.ressources.label'),
                            'tfoot'=>$this->getLastTsRecord('ressources', array('siteid'=>$this->_sTab)).'<br>'
                            . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('ressources', array('siteid'=>$this->_sTab))))),
                            'page'=>'ressources',
                        );
                        $aMsg['searches']=array(
                            'counter'=>$iCounter++,
                            'status'=>'info', 
                            'value'=>$iSearchesCount, 
                            'message'=>$iSearchesCount ? false : $this->lB('searches.empty'),
                            'thead'=>$this->lB('nav.searches.label'),
                            'tfoot'=>$this->getLastTsRecord('searches', array('siteid'=>$this->_sTab)).'<br>'
                            . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('searches', array('siteid'=>$this->_sTab))))),
                            'page'=>'searches',
                        );
                        break;

                    // Analysis --> HTML checks
                    case 'htmlchecks':

                        $aOptions = $this->getEffectiveOptions();
                        /*
                        $oCrawler=new crawler($this->_sTab);
                        $aCounter=array();
                        $aCounter['countCrawlerErrors']=$oCrawler->getCount(array(
                            'AND' => array(
                                'siteid' => $this->_sTab,
                                'errorcount[>]' => 0,
                            )));

                        $aCounter['countShortTitles']   = $this->_getHtmlchecksCount('title',       $aOptions['analysis']['MinTitleLength']);
                        $aCounter['countShortDescr']    = $this->_getHtmlchecksCount('description', $aOptions['analysis']['MinDescriptionLength']);
                        $aCounter['countShortKeywords'] = $this->_getHtmlchecksCount('keywords',    $aOptions['analysis']['MinKeywordsLength']);
                        $aCounter['countLargePages']    = $this->_getHtmlchecksLarger('size',       $aOptions['analysis']['MaxPagesize']);
                        $aCounter['countLongLoad']      = $this->_getHtmlchecksLarger('time',       $aOptions['analysis']['MaxLoadtime']);
                         * 
                         */
                        // (floor($iCountCrawlererrors/$iRessourcesCount*1000)/10).'%';
                        // sprintf("%01.2f", $money)
                        $aMsg['countCrawlerErrors']=array(
                            'counter'=>$iCounter++,
                            'status'=>$aMyCounters['countCrawlerErrors']?'error':'ok', 
                            'value'=>$aMyCounters['countCrawlerErrors'],
                            'message'=>false,
                            'thead'=>$this->lB('htmlchecks.tile-crawlererrors'),
                            'tfoot'=>$iPagesCount ? $this->_getPercent($aMyCounters['countCrawlerErrors']/$iPagesCount) : '',
                            'thash'=>$aMyCounters['countCrawlerErrors'] ? '#tblcrawlererrors' : '',
                        );
                        $aMsg['countShortTitles']=array(
                            'counter'=>$iCounter++,
                            'status'=>$aMyCounters['countShortTitles']?'warning':'ok', 
                            'value'=>$aMyCounters['countShortTitles'], 
                            'message'=>false,
                            'thead'=>sprintf($this->lB('htmlchecks.tile-check-short-title'), $aOptions['analysis']['MinTitleLength']),
                            'tfoot'=>$iPagesCount ? $this->_getPercent($aMyCounters['countShortTitles']/$iPagesCount) : '',
                            'thash'=>$aMyCounters['countShortTitles'] ? '#tblshorttitle' : '',
                        );
                        $aMsg['countShortDescr']=array(
                            'counter'=>$iCounter++,
                            'status'=>$aMyCounters['countShortDescr']?'warning':'ok', 
                            'value'=>$aMyCounters['countShortDescr'], 
                            'message'=>false,
                            'thead'=>sprintf($this->lB('htmlchecks.tile-check-short-description'), $aOptions['analysis']['MinDescriptionLength']),
                            'tfoot'=>$iPagesCount ? $this->_getPercent($aMyCounters['countShortDescr']/$iPagesCount) : '',
                            'thash'=>$aMyCounters['countShortDescr'] ? '#tblshortdescription' : '',
                        );
                        $aMsg['countShortKeywords']=array(
                            'counter'=>$iCounter++,
                            'status'=>$aMyCounters['countShortKeywords']?'warning':'ok', 
                            'value'=>$aMyCounters['countShortKeywords'], 
                            'message'=>false,
                            'thead'=>sprintf($this->lB('htmlchecks.tile-check-short-keywords'), $aOptions['analysis']['MinKeywordsLength']),
                            'tfoot'=>$iPagesCount ? $this->_getPercent($aMyCounters['countShortKeywords']/$iPagesCount) : '',
                            'thash'=>$aMyCounters['countShortKeywords'] ? '#tblshortkeywords' : '',
                        );
                        $aMsg['countLongLoad']=array(
                            'counter'=>$iCounter++,
                            'status'=>$aMyCounters['countLongLoad']?'warning':'ok', 
                            'value'=>$aMyCounters['countLongLoad'], 
                            'message'=>false,
                            'thead'=>sprintf($this->lB('htmlchecks.tile-check-loadtime-of-pages'), $aOptions['analysis']['MaxLoadtime']),
                            'tfoot'=>$iPagesCount ? $this->_getPercent($aMyCounters['countLongLoad']/$iPagesCount) : '',
                            'thash'=>'#tblloadtimepages',
                        );
                        $aMsg['countLargePages']=array(
                            'counter'=>$iCounter++,
                            'status'=>$aMyCounters['countLargePages']?'warning':'ok', 
                            'value'=>$aMyCounters['countLargePages'], 
                            'message'=>false,
                            'thead'=>sprintf($this->lB('htmlchecks.tile-check-large-pages'), $aOptions['analysis']['MaxPagesize']),
                            'tfoot'=>$iPagesCount ? $this->_getPercent($aMyCounters['countLargePages']/$iPagesCount) : '',
                            'thash'=>'#tbllargepages',
                        );
                        
                        break;

                    // Analysis --> HTTP header checks
                    case 'httpheaderchecks':

                        // default: detect first url in pages table
                        $aPagedata = $this->oDB->select(
                            'pages', 
                            array('url', 'header'), 
                            array(
                                'AND' => array(
                                    'siteid' => $this->_sTab,
                                ),
                                "ORDER" => array("id"=>"ASC"),
                                "LIMIT" => 1
                            )
                        );
                        if (count($aPagedata)){
                            /*
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
                             
                            */
                            $iTotalHeaders=$aMyCounters['responseheaderCount'];
                            $iKnown=$aMyCounters['responseheaderKnown'];
                            $iUnkKnown=$aMyCounters['responseheaderUnknown'];
                            $iUnwanted=$aMyCounters['responseheaderUnwanted'];
                            $iDeprecated=$aMyCounters['responseheaderDeprecated'];
                            $iNonStandard=$aMyCounters['responseheaderNonStandard'];
                            $iCacheInfos=$aMyCounters['responseheaderCache'];
                            $iCompressionInfos=$aMyCounters['responseheaderCompression'];
                            $iSecHeader=$aMyCounters['responseheaderSecurity'];

                            // $aSecHeader=$oHttpheader->getSecurityHeaders();
                            $aMsg['total']=array(
                                'counter'=>$iCounter++,
                                'status'=>'info', 
                                'value'=>$iTotalHeaders, 
                                'message'=>false,
                                'thead'=>$this->lB('httpheader.header.total'),
                                'tfoot'=>'',
                            );
                            $aMsg['http']=array(
                                'counter'=>$iCounter++,
                                'status'=>($iKnown+$iSecHeader===$iTotalHeaders ? 'ok' : ($iKnown > 0 ? 'info' : 'error') ),
                                'value'=>$iKnown, 
                                'message'=>false,
                                'thead'=>$this->lB('httpheader.header.http'),
                                'tfoot'=>'',
                                'thash'=>'',
                            );
                            $aMsg['unknown']=array(
                                'counter'=>$iCounter++,
                                'status'=>($iUnkKnown ? 'warning' : 'ok'),
                                'value'=>$iUnkKnown, 
                                'message'=>false,
                                'thead'=>$this->lB('httpheader.header.unknown'),
                                'tfoot'=>$iTotalHeaders ? $this->_getPercent($iUnkKnown/$iTotalHeaders) : '',
                                'thash'=>($iUnkKnown ? '#warnunknown' : ''),
                            );
                            $aMsg['deprecated']=array(
                                'counter'=>$iCounter++,
                                'status'=>($iDeprecated ? 'warning' : 'ok'),
                                'value'=>$iDeprecated, 
                                'message'=>false,
                                'thead'=>$this->lB('httpheader.header.deprecated'),
                                'tfoot'=>$iTotalHeaders ? $this->_getPercent($iDeprecated/$iTotalHeaders) : '',
                                'thash'=>($iDeprecated ? '#warndeprecated' : ''),
                            );
                            $aMsg['unwanted']=array(
                                'counter'=>$iCounter++,
                                'status'=>($iUnwanted ? 'warning' : 'ok'),
                                'value'=>$iUnwanted, 
                                'message'=>false,
                                'thead'=>$this->lB('httpheader.header.unwanted'),
                                'tfoot'=>$this->_getPercent($iUnwanted/$iTotalHeaders),
                                'thash'=>($iUnwanted ? '#warnunwanted' : ''),
                            );
                            $aMsg['nonstandard']=array(
                                'counter'=>$iCounter++,
                                'status'=>($iNonStandard ? 'warning' : 'ok'),
                                'value'=>$iNonStandard, 
                                'message'=>false,
                                'thead'=>$this->lB('httpheader.header.non-standard'),
                                'tfoot'=>$this->_getPercent($iNonStandard/$iTotalHeaders),
                                'thash'=>($iNonStandard ? '#warnnonstandard' : ''),
                            );
                            $aMsg['httpversion']=array(
                                'counter'=>$iCounter++,
                                'status'=>$aMyCounters['responseheaderVersionStatus'],
                                'value'=>$aMyCounters['responseheaderVersion'], 
                                'message'=>false,
                                'thead'=>$this->lB('httpheader.header.httpversion'),
                                'tfoot'=>'',
                                'thash'=>$aMyCounters['responseheaderVersionStatus'] == 'ok' ? '' : '#warnhttpver',
                            );
                            $aMsg['cacheinfos']=array(
                                'counter'=>$iCounter++,
                                'status'=>($iCacheInfos ? 'ok' : 'warning'),
                                'value'=>$iCacheInfos ? $iCacheInfos : $oRenderer->renderShortInfo('miss'), 
                                'message'=>false,
                                'thead'=>$this->lB('httpheader.header.cache'),
                                'tfoot'=>'',
                                'thash'=>($iCacheInfos ? '' : '#warnnocache'),
                            );
                            $aMsg['compression']=array(
                                'counter'=>$iCounter++,
                                'status'=>($iCompressionInfos ? 'ok' : 'warning'),
                                'value'=>$iCompressionInfos ? $iCompressionInfos : $oRenderer->renderShortInfo('miss'), 
                                'message'=>false,
                                'thead'=>$this->lB('httpheader.header.compression'),
                                'tfoot'=>'',
                                'thash'=>($iCompressionInfos ? '' : '#warnnocompression'),
                            );
                            $aMsg['security']=array(
                                'counter'=>$iCounter++,
                                'status'=>($iSecHeader ? 'ok' : 'warning'),
                                'value'=>$iSecHeader ? $iSecHeader : $oRenderer->renderShortInfo('miss'), 
                                'message'=>false,
                                'thead'=>$this->lB('httpheader.header.security'),
                                'tfoot'=>'',
                                'thash'=>'#securityheaders',
                            );
                            
                        }
                        
                        break;
                    
                    // Analysis --> SSL check
                    case 'sslcheck':
                        $sFirstUrl=isset($this->aProfileSaved['searchindex']['urls2crawl'][0]) ? $this->aProfileSaved['searchindex']['urls2crawl'][0] : false;
                        
                        if(!$sFirstUrl){
                            // $sReturn.='<br>'.$this->_getMessageBox($this->lB('sslcheck.nostarturl'), 'warning');
                            $aMsg['certstatus']=array(
                                'counter'=>$iCounter++,
                                'status'=>'error', 
                                'value'=>'', 
                                'message'=>$this->lB('sslcheck.nostarturl'),
                                'thead'=>'',
                                'tfoot'=>'',
                            );
                        } else if(strstr($sFirstUrl, 'http://')){
                            
                            $aMsg['certstatus']=array(
                                'counter'=>$iCounter++,
                                'status'=>'error', 
                                'value'=>$this->lB('sslcheck.httponly.description'), 
                                'message'=>$this->lB('sslcheck.httponly').' '.$this->lB('sslcheck.httponly.description').' '.$this->lB('sslcheck.httponly.hint'),
                                'thead'=>$this->lB('sslcheck.httponly'),
                                'tfoot'=>$this->lB('sslcheck.httponly.hint'),
                            );
                        } else {
                            // TODO: cache infos ... for 1 h
                            $oSsl=new sslinfo();
                            $aSslInfos=$oSsl->getSimpleInfosFromUrl($sFirstUrl);
                            if(isset($aSslInfos['CN'])){
                                $sStatus=$oSsl->getStatus();
                                $aSslInfosAll=$oSsl->getCertinfos($url=false);
                                $iDaysleft = round((date("U", strtotime($aSslInfos['validto'])) - date('U')) / 60 / 60 / 24);
                                $aMsg['certstatus']=array(
                                    'counter'=>$iCounter++,
                                    'status'=>$sStatus, 
                                    'data'=>$aSslInfos, 
                                    'value'=>$aSslInfos['issuer'], 
                                    'message'=>$aSslInfos['issuer'].': '.$aSslInfos['CN'].'; '.$aSslInfos['validto'].' ('.$iDaysleft.' d) '
                                        . ($aSslInfos['chaining'] ? '': $this->lB('sslcheck.chaining.fail')),
                                    'thead'=>$aSslInfos['CN'],
                                    'tfoot'=>$aSslInfos['validto'].' ('.$iDaysleft.' d)',
                                );
                            }
                        }
                        break;
                    case 'linkchecker':
                        if($iRessourcesCount){
                            $oRessources=new ressources($this->_sTab);

                            $aCountByStatuscode=$oRessources->getCountsOfRow(
                                'ressources', 'http_code', 
                                array(
                                    'siteid'=> $this->_sTab,
                                    'isExternalRedirect'=>'0',
                                )
                            );
                            if (!count($aCountByStatuscode)){
                                /*
                                 * TODO: leave a messgae that scan is not finished
                                 * -a update -d ressources -p [N]
                                 * 
                                $aMsg['ressources-unfinished']=array(
                                    'counter'=>$iCounter++,
                                    'status'=>'error', 
                                    'value'=>0, 
                                    'message'=>$iRessourcesCount ? false : sprintf($this->lB('ressources.not-finished'), $this->_sTab),
                                    'thead'=>$this->lB('nav.ressources.label'),
                                    'tfoot'=>$this->getLastTsRecord('ressources', array('siteid'=>$this->_sTab)).'<br>'
                                    . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('ressources', array('siteid'=>$this->_sTab))))),
                                    'page'=>'linkchecker',
                                );
                                 */
                            } else {
                                $aTmpItm=array('status'=>array(), 'total'=>0);
                                
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
                                $aBoxes=array('Todo'=>$aTmpItm, 'Error'=>$aTmpItm,'Warning'=>$aTmpItm, 'Ok'=>$aTmpItm);
                                foreach (array_keys($aBoxes) as $sSection){
                                    $aHttpStatus=array();
                                    foreach($aMyCounters as $sKey=>$ivalue){
                                        if(strstr($sKey, $sSection.'[')){
                                            $iStatuscode=preg_replace('/.*\[(.*)\]/i','\1', $sKey);
                                            $aHttpStatus[$iStatuscode]=$ivalue;
                                        }
                                    }
                                    $iBoxvalue=$aMyCounters['status'.$sSection];
                                    $sStatus=(!$iBoxvalue || $sSection==='ok' ? 'ok' : strtolower($sSection) );
                                    $aMsg[strtolower($sSection)]=array(
                                        'counter'=>$iCounter++,
                                        '_data'=>$aHttpStatus, 
                                        'status'=>$sStatus, 
                                        'value'=>$aMyCounters['status'.$sSection], 
                                        'message'=>false,
                                        'thead'=>$this->lB('linkchecker.found-http-'.strtolower($sSection)),
                                        'tfoot'=>$this->_getPercent($iBoxvalue/$iRessourcesCount),
                                        'thash'=>($iBoxvalue ? '#h3-'.strtolower($sSection) : ''),
                                    );      
                                    
                                }
                                // echo '<pre>'.print_r( $aMsg,1).'</pre>';
                            }
                        }
                        break;
                }
                if(count($aMsg)){
                    foreach($aMsg as $skey=>$aItem){                        
                        if(!isset($aItem['message']) || !$aItem['message']){
                            $aMsg[$skey]['message']=str_replace('<br>', ' ', '<strong>'.$aItem['value'].'</strong> '.$aItem['thead'].($aItem['tfoot'] ? ' ('.$aItem['tfoot'].')' : ''));
                        }
                    }
                    $aStatusinfo[$sPage]=$aMsg;
                }
            }
        }
        // $oCache->write($aStatusinfo, 10);
        return $aStatusinfo;
    }
    
    /**
     * get hash of analytics messages based on level
     * @see _getStatusinfos()
     * @param string  $sLevel  level; one or error|warning|ok|info
     * @return array
     */
    protected function _getStatusInfoByLevel($sLevel=false) {
        $aReturn=array();
        foreach ($this->_getStatusinfos() as $sTarget=>$aInfos){
            foreach($aInfos as $sCountername=>$aData){
                if($aData['status']===$sLevel){
                    $aReturn[$aData['counter']]=$aData;
                    $aReturn[$aData['counter']]['target']=$sTarget;
                }
            }
        }
        ksort($aReturn);
        return $aReturn;
    }
    
    protected function _getTilesOfAPage(){
        global $oRenderer;
        $sReturn='';
        $sPage=$this->_getPage();
        if(!$sPage){
            return '';
        }
        $aTileData=$this->_getStatusinfos(array($sPage));
        if(!isset($aTileData[$sPage])){
            return '';
        }
        // echo '<pre>'.print_r($aTileData[$sPage], 1).'</pre>';
        foreach($aTileData[$sPage] as $sKey=>$aItem){
            $sReturn.=$oRenderer->renderTile($aItem['status'], $aItem['thead'], $aItem['value'], $aItem['tfoot'], (isset($aItem['thash']) && $aItem['thash'] ? $aItem['thash'] : ''));
        }
        return $sReturn;
    }

    // ----------------------------------------------------------------------
    // OUTPUT RENDERING
    // ----------------------------------------------------------------------

    /**
     * get html code for a result table
     * @param array  $aResult          result of a select query
     * @param string $sLangTxtPrefix   langtext prefix
     * @param string $sTableId         value of id attribute for the table
     * @return string
     */
    private function _getHtmlTable($aResult, $sLangTxtPrefix = '', $sTableId=false) {
        $sReturn = '';
        $aFields = false;
        if (!is_array($aResult) || !count($aResult)) {
            return false;
        }
        foreach ($aResult as $aRow) {
            if (!$aFields) {
                $aFields = array_keys($aRow);
            }
            $sReturn.='<tr>';
            foreach ($aFields as $sField) {
                $sReturn.='<td class="td-' . $sField . '">' . $aRow[$sField] . '</td>';
            }
            $sReturn.='</tr>';
        }
        if ($sReturn) {
            $sTh = '';
            foreach ($aFields as $sField) {
                $sIcon=(array_key_exists($sField, $this->_aIcons['cols']) ? '<i class="'.$this->_aIcons['cols'][$sField].'"></i> ' : '['.$sField.']');

                $sTh.='<th class="th-' . $sField . '">' . $sIcon . $this->lB($sLangTxtPrefix . $sField) . '</th>';
            }
            // $sReturn = '<table class="pure-table pure-table-horizontal pure-table-striped datatable">'
            $sReturn = '<table'.($sTableId ? ' id="'.$sTableId.'"' : '').' class="pure-table pure-table-horizontal datatable">'
                    . '<thead><tr>' . $sTh . '</tr></thead>'
                    . '<tbody>' . $sReturn . '</tbody>'
                . '</table>'
                ;
        }
        return $sReturn;
    }

    /**
     * get html code for a simple table without table head
     * @param array  $aResult          result of a select query
     * @param array  $bFirstIsHeader   flag: first record is header line; default is false
     * @return string
     */
    private function _getSimpleHtmlTable($aResult, $bFirstIsHeader=false, $sTableId=false) {
        $sReturn = '';
        $bIsFirst=true;
        $sTHeader='';
        foreach ($aResult as $aRow) {
            $sReturn.='<tr>';
            foreach ($aRow as $sField) {
                if($bFirstIsHeader && $bIsFirst){
                    $sTHeader.='<th>' . $sField . '</th>';
                } else {
                    $sReturn.= '<td>' . $sField . '</td>';
                }
            }
            $sReturn.='</tr>';
            $bIsFirst=false;
        }
        if ($sReturn) {
            $sReturn = '<table'.($sTableId ? ' id="'.$sTableId.'"' : '').' class="pure-table pure-table-horizontal datatable">'
                . '<thead>' . ($sTHeader ? '<tr>'.$sTHeader.'</tr>' : '' ). '</thead>'
                . '<tbody>' . $sReturn  . '</tbody>'
            . '</table>';
        }
        return $sReturn;
    }

    private function _getButton($aOptions = array()) {
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
                . 'title="' . $this->lB($aOptions['label'] . '.hint') . '" '
                . (isset($aOptions['onclick']) 
                    ? 'onclick="' . $aOptions['onclick'] . '" '
                    : ''
                  )
                // . ($aOptions['popup'] ? 'onclick="showModal(this.href); return false;"' : '')
                . '>' . $this->_getIcon($aOptions['label']).$this->lB($aOptions['label']) . '</a>';
        return $sReturn;
    }

    private function _getIcon($sKey, $bEmptyIfMissing=false){
        foreach(array_keys($this->_aIcons)as $sIconsection){
            if (array_key_exists($sKey, $this->_aIcons[$sIconsection])){
                return '<i class="'.$this->_aIcons[$sIconsection][$sKey].'"></i> ';
            }
        }
        return $bEmptyIfMissing ? '' : '<span title="missing icon ['.$sKey.']">['.$sKey.']</span>';
    }
    
    /**
     * prettify table output: limit a string to a mximum and insert space
     * @param string  $sVal   string
     * @param int     $iMax   max length
     * @return string
     */
    private function _prettifyString($sVal, $iMax = 500) {
        $sVal = str_replace(',', ', ', $sVal);
        $sVal = str_replace(',  ', ', ', $sVal);
        // $sVal = htmlentities($sVal);
        // return (strlen($sVal) > $iMax) ? substr($sVal, 0, $iMax) . '<span class="more"></span>' : $sVal;
        return (strlen($sVal) > $iMax) ? '<textarea class="pure-input" cols="100" rows="10">'.$sVal . '</textarea><br>' : htmlentities($sVal);
    }

    /**
     * get html code for a search index table
     * @param array  $aResult          result of a select query
     * @param string $sLangTxtPrefix   langtext prefix
     * @param string $sTableId         value of id attribute for the table
     * @return string
     */
    private function _getSearchindexTable($aResult, $sLangTxtPrefix = '', $sTableId=false, $bShowLegend=true) {
        $aTable = array();
        foreach ($aResult as $aRow) {
            $sId = $aRow['id'];
            unset($aRow['id']);
            foreach ($aRow as $sKey => $sVal) {
                $aRow[$sKey] = $this->_prettifyString($sVal);
            }
            $aRow['url']=str_replace('/', '/&shy;', $aRow['url']);
            $aRow['actions'] = $this->_getButton(array(
                // 'href' => 'overlay.php?action=viewindexitem&id=' . $sId,
                'href' => './?'.$_SERVER['QUERY_STRING'].'&id='.$sId,
                'popup' => false,
                'class' => 'pure-button',
                'label' => 'button.view'
            ));
            $aTable[] = $aRow;
        }
        $aKeys=array_keys($aResult[0]);
        if($aKeys[0]==='id'){
            unset($aKeys[0]);
        }
        return $this->_getHtmlTable($aTable, $sLangTxtPrefix, $sTableId)
                .($bShowLegend ? $this->_getHtmlLegend($aKeys, $sLangTxtPrefix) : '')
                ;
    }

    // ----------------------------------------------------------------------
    
    
    // ----------------------------------------------------------------------
    // PAGE CONTENT
    // ----------------------------------------------------------------------
    
    /**
     * wrapper function: get page content as html
     * @return string
     */
    public function getContent() {
        if (!$this->checkAuth()) {
            return $this->_getLoginForm();
        }
        return include $this->_sPageFile;
    }
    /**
     * wrapper function: get page content as html
     * @return string
     */
    public function getPublicContent() {
        $sPagefile='backend/pages/public_'.$this->_sPage.'.php';
        return include $sPagefile;
    }
    
    /**
     * wrapper function: get update infobox
     * @return string
     */
    public function getUpdateInfobox() {
        global $oRenderer;
        if($this->_bIsPublic){
            return '';
        }
        return $this->checkAuth() && $this->oUpdate->hasUpdate()
            ? $oRenderer->renderMessagebox(
                    sprintf($this->lB('update.available-yes') , $this->oUpdate->getLatestVersion()) 
                    .' '
                    . '<a href="?page=update">'.$this->lB('nav.update.label').'</a>'
              , 
                    'warning'
              )
            : '';
    }

    public function getProfileImage(){
        return (isset($this->aProfileSaved['profileimagedata']) && $this->aProfileSaved['profileimagedata']
                ? '<img src="'.$this->aProfileSaved['profileimagedata'].'" class="profile" title="'.$this->aProfileSaved['label'].'" alt="'.$this->aProfileSaved['label'].'">'
                : ''
        );
    }
        
    /**
     * return html + js code to draw a chart (pie or bar)
     * 
     * @staticvar int $iChartCount
     * 
     * @param type $aOptions
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
     * @return type
     */
    private function _getChart($aOptions){
        $sReturn='';
        
        static $iChartCount;
        if(!isset($iChartCount)){
            $iChartCount=0;
        }
        $iChartCount++;
        
        $sDomIdDiv='chart-div-'.$iChartCount;
        $sDomIdCanvas='chart-canvas-'.$iChartCount;
        $sVarChart='chartConfig'.$iChartCount;
        $sVarCtx='chartCtx'.$iChartCount;
        
        $bShowLegend=$aOptions['type'] !== 'bar';
        $bShowLegend=isset($aOptions['legend_display']) ? $aOptions['legend_display'] : $bShowLegend;

        $bShowRaster=$aOptions['type'] === 'bar';
        
        $sDatasets='';
        $sLimit='';
        $sAvg='';
        if(isset($aOptions['datasets'])){
            $aDatasets=$aOptions['datasets'];
        }
        if(isset($aOptions['data'])){
            $aDatasets[]['data']=$aOptions['data'];
        }

        if(count($aDatasets)){

            $dsLabels=[];
            foreach ($aDatasets as $aDataset){
                $dsData=[
                    'values'=>[],
                    'colors'=>[],
                ];                
                foreach($aDataset['data'] as $aItem){
                    if(!$sDatasets){
                        $dsLabels[]=$aItem['label'];
                    }
                    $dsData['values'][]=$aItem['value'];
                    $dsData['colors'][]=$aItem['color'];
                    if(isset($aOptions['limit']) && $aOptions['limit']){
                        $sLimit .= ($sLimit ? ', ' : '') . $aOptions['limit'];
                    }
                    if(isset($aOptions['avg']) && $aOptions['avg']){
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
                $sDatasets.=($sDatasets ? ', ' : '')
                    .'{
                        label: \''.(isset($aDataset['label']) ? $aDataset['label'] : '').'\',
                        data: '.json_encode($dsData['values']).',
                        backgroundColor: '. str_replace('"', '', json_encode($dsData['colors'])).',
                        borderWidth: 1,
                        fill: false                    
                    }';
            }
        }
        return '
            <div id="'.$sDomIdDiv.'" class="piechart piechart-'.$aOptions['type'].'">
		<canvas id="'.$sDomIdCanvas.'"></canvas>
            </div>
            <script>
                var '.$sVarChart.' = {
                    type: \''.$aOptions['type'].'\',
                    data: {
                        datasets: [
                            '.$sDatasets.'
                            '.($sLimit
                                ? ', {
                                    type: \'line\',
                                    data: JSON.parse(\'['.$sLimit.']\'),
                                    backgroundColor: \'#c00\',
                                    borderColor: \'#c00\',
                                    borderWidth: 1,
                                    fill: false,
                                    radius: 0
                                }'
                                : ''
                            ).'
                            '.($sAvg
                                ? ', {
                                    type: \'line\',
                                    data: JSON.parse(\'['.$sAvg.']\'),
                                    backgroundColor: \'#56a\',
                                    borderColor: \'#56a\',
                                    borderWidth: 1,
                                    borderDash: [3, 3],
                                    fill: false,
                                    radius: 0
                                }'
                                : ''
                            ).'
                        ],
                        labels: '.json_encode($dsLabels).'
                    },
                    options: {
                        animation: {
                            duration: 500
                        },
                        plugins: {
                            legend: {
                                display: '.($bShowLegend ? 'true' : 'false').'
                            }
                        },
                        responsive: true,
                        scales: {
                          x: {
                            display: '.($bShowRaster ? 'true' : 'false').',
                            stacked: true,
                          },
                          y: {
                            display: '.($bShowRaster ? 'true' : 'false').',
                            stacked: '.(count($aDatasets)>1 ? "true" : "false").',
                            ticks: {
                              // forces step size to be 50 units
                              stepSize: 50
                            }
                          }
                        }                        
                    }
                    
                };

                // window.onload = function() {
                    var '.$sVarCtx.' = document.getElementById("'.$sDomIdCanvas.'").getContext("2d");
                    window.myPie = new Chart('.$sVarCtx.', '.$sVarChart.');
                // };
            </script>
        ';
    }

    /**
     * 
     * @param string|array  $sCounteritem  id or array of ids to render; with 
     *                                     multiple ids its data will be stacked
     * @return string
     */
    private function _getHistoryCounter($sCounteritem){

        $sHtml='';
        // ----- config 
        $sColorDefault='getStyleRuleValue(\'color\', \'.chartcolor-1\')';
        $sColorDefault2='getStyleRuleValue(\'color\', \'.chartcolor-2\')';
        $sColorDefault3='getStyleRuleValue(\'color\', \'.chartcolor-3\')';
        $sColorWarning='getStyleRuleValue(\'color\', \'.chartcolor-warning\')';
        $sColorError='getStyleRuleValue(\'color\', \'.chartcolor-error\')';
        $sColorOK='getStyleRuleValue(\'color\', \'.chartcolor-ok\')';

        $aBarColors=array(
            'countCrawlerErrors'=>$sColorError,
            'countLargePages'=>$sColorWarning,
            'countLongLoad'=>$sColorWarning,
            'countShortDescr'=>$sColorWarning,
            'countShortKeywords'=>$sColorWarning,
            'countShortTitles'=>$sColorWarning,

            'pages'=>$sColorDefault,
            'ressources'=>$sColorDefault2,
            // 'searches'=>$sColorDefault,

            'responseheaderDeprecated'=>$sColorWarning,
            'responseheaderKnown'=>$sColorOK,
            'responseheaderNonStandard'=>$sColorWarning,
            'responseheaderSecurity'=>$sColorOK,
            'responseheaderUnknown'=>$sColorWarning,
            'responseheaderUnwanted'=>$sColorWarning,
            
            'statusError'=>$sColorError,
            'statusOk'=>$sColorOK,
            'statusWarning'=>$sColorWarning,
            'TotalErrors'=>$sColorError,
            'TotalWarnings'=>$sColorWarning,

        );
        require_once 'counter.class.php';
        $oCounter=new counter();
        $oCounter->mysiteid($this->iSiteId);
        
        // read data
        $bEnough=false;
        $aCounterItems2Fetch=is_array($sCounteritem) ? $sCounteritem : [$sCounteritem];
        foreach ($aCounterItems2Fetch as $sCItem){
            $aPageHistory[$sCItem]=$oCounter->getCountersHistory($sCItem);
            if(count($aPageHistory[$sCItem]) > 3){
                $bEnough=true;
            }
        }
        
        if($bEnough){
            
            $aDatasets=[];
            foreach($aPageHistory as $sCItem=>$aDataset){
                
                $aHistoryData=[
                    'label'=>$this->lB('chart.'.$sCItem),
                    'data'=>[],
                ];
                $sColor=isset($aBarColors[$sCItem]) ? $aBarColors[$sCItem] : $sColorDefault;

                // 'getStyleRuleValue(\'color\', \'.chartcolor-error\')'
                foreach($aDataset as $aDataitem){
                    $aHistoryData['data'][]=array(
                        'label'=> substr($aDataitem['ts'],0,10),
                        // 'label'=>$sCItem,
                        // 'label'=>'',
                        'value'=>$aDataitem['value'],
                        'color'=>$sColor,  
                    );
                }
                $aDatasets[]=$aHistoryData;
            }
            
            $sHtml.=''
                . $this->lB('chart.historicalView').'<br><br>'
                . '<div class="floatleft">'
                    . $this->_getChart(array(
                        'type'=>'bar',
                        'datasets'=>$aDatasets,
                        'label'=> array_keys($aPageHistory),
                        'legend_display'=>count($aCounterItems2Fetch)>1,
                    ))
                . '</div>'

                // . '<pre>getCountersHistory("'.$sCounteritem.'") returns<br>' . print_r($aPageHistory, 1) . '</pre>'

                . '<div style="clear: left;"></div>'        
                ; 
        }
        return $sHtml;
    }    
    private function _getChartOfRange($sQuery, $sColumn, $iLimit) {
        $aTable = array();
        $aData = array();
        $iMaxItems=50;
        $iNextStep=0;
        $i=0;
        
        $aTmp = $this->oDB->query($sQuery)->fetchAll(PDO::FETCH_ASSOC);
        if(!$aTmp ||!count($aTmp)){
            return false;
        }
        $iStep=round(count($aTmp)/$iMaxItems);
        
        $iTotal=0;
        
        foreach ($aTmp as $aRow) {
            $i++;
            if($i>$iNextStep){
                $iIsOK=$iLimit>$aRow[$sColumn];
                $aData[]=array(
                        // 'label'=>$aRow['url'],
                        'label'=>'',
                        // 'label'=>($iIsOK ? '< ' : '> ') .$iMaxLoadtime,
                        'value'=>$aRow[$sColumn],
                        'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-'.($iIsOK ? 'ok':'warning' ) .'\')',
                        // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': '.,
                    );
                $iNextStep+=$iStep;
            }
            $iTotal+=$aRow[$sColumn];

        }        
        return $this->_getChart(array(
                // 'type'=>'line',
                'type'=>'bar',
                'data'=>$aData,
                'limit'=>$iLimit,
                'avg'=>$iTotal/count($aTmp),
            ));
    }

        /**
         * html check - get get html code for a chart of too short elements
         * @param string   $sQuery      query to fetch data
         * @param integer  $iMinLength  minimal length
         * @return string
         */
        private function _getHtmlchecksChart($iTotal, $iWarnings, $iErrors=0){
            $aData=array();
            if($iErrors){
                $aData[]=array(
                        'label'=>$this->lB('htmlchecks.label-errors'),
                        'value'=>$iErrors,
                        'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-error\')',
                        // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': '.,
                    );
            }
            if($iWarnings){
                $aData[]=array(
                        'label'=>$this->lB('htmlchecks.label-warnings'),
                        'value'=>$iWarnings,
                        'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-warning\')',
                        // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': '.,
                    );
            }
            $aData[]=array(
                        'label'=>$this->lB('htmlchecks.label-ok'),
                        'value'=>($iTotal-$iWarnings-$iErrors),
                        'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-ok\')',
                        // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': '.,
                    );
            return $this->_getChart(array(
                'type'=>'pie',
                'data'=>$aData,
            ));
        }
        /**
         * html check - get get html code for a table of too short elements
         * @param string|array   $sQuery      query to fetch data
         * @param string         $iMinLength  table id
         * @return string
         */
        private function _getHtmlchecksTable($sQuery, $sTableId=false){
            if(is_array($sQuery)){
                $aTmp = $this->oDB->debug()->select(
                        $sQuery[0], // table
                        $sQuery[1], // what to select
                        $sQuery[2]  // params
                        );
                echo '<pre>'.print_r($sQuery, 1).'</pre>';
                echo '<pre>'.print_r($aTmp, 1).'</pre>';
                
            } else {
                $aTmp = $this->oDB->query($sQuery)->fetchAll(PDO::FETCH_ASSOC);
            }
            $aTable = array();
            $aData = array();
            foreach ($aTmp as $aRow) {
                $aTable[] = $aRow;
                /*
                $aData[]=array(
                        'label'=>$this->lB('htmlchecks.label-warnings'),
                        'value'=>$iWarnings,
                        'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-warning\')',
                        'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-ok\')',
                        // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': '.,
                    );
                */
            }
            // echo "<pre>$sQuery<br>".print_r($aTmp,1).'</pre>';
            if(!isset($aTmp[0])){
                return '';
            }
            $aKeys=array_keys($aTmp[0]);
            return $this->_getHtmlTable($aTable, "db-pages.", $sTableId)
                . $this->_getHtmlLegend($aKeys, 'db-pages.')
                ;
        }
        
        /**
         * get html code to display a legend
         * 
         * @param string|array  $Content  legend text or an array of ids
         * @param string        $sPrefix  for arrays as $Content: a prefix to scan for prefix+id in lang file
         * @return string
         */
        private function _getHtmlLegend($Content, $sPrefix=''){
            global $oRenderer;
            $sLegend='';
            if(is_array($Content)){
                foreach ($Content as $sKey) {
                    $sLegend .= ($sLegend ? '<br>' : '')
                        . '<strong>' . $this->_getIcon($sKey) . ' ' . $this->lB($sPrefix . $sKey) . '</strong><br>'
                        . $this->lB($sPrefix . $sKey . '.description') . '<br>'
                        ;
                }
            } else {
                $sLegend=$Content;
            }
            return $oRenderer->renderToggledContent($this->lB('label.legend'),$sLegend, true);
            /*
            return ''
            . '<div class="legendlabel div-toggle-head"><strong>'.$this->lB('label.legend').':</strong></div>'
            . '<div class="legend">'
                .$sLegend
            .'</div>'
            ;
             * 
             */
        }


    private function _getRessourceSummary($aRessourcelist, $bLinkRessource=false){
        $sReturn='';
        // $aFilter=array('ressourcetype','type', 'content_type', 'http_code');
        $aFilter=array('type', 'content_type', 'http_code');
        $aCounter=array();
        $aTable = array();
        if (count($aRessourcelist)) {
            
            foreach ($aRessourcelist as $aRow) {
                foreach ($aFilter as $sKey){
                    if (!array_key_exists($sKey, $aCounter)){
                        $aCounter[$sKey]=array();
                    }
                    if (!array_key_exists($aRow[$sKey], $aCounter[$sKey])){
                        $aCounter[$sKey][$aRow[$sKey]]=0;
                    }
                    $aCounter[$sKey][$aRow[$sKey]]++;
                    ksort($aCounter[$sKey]);
                }
                /*
                    $aRow['actions'] = $this->_getButton(array(
                        'href' => 'overlay.php?action=ressourcedetail&id=' . $aRow['id'] . '&siteid=' . $this->_sTab . '',
                        'class' => 'button-secondary',
                        'label' => 'button.view'
                    ));
                 * 
                 */
                    $sUrl=str_replace('/', '/&shy;', ($bLinkRessource
                            ?'<a href="?action=ressourcedetail&id='.$aRow[$bLinkRessource].'&siteid='.$this->_getRequestParam('siteid', false, 'int').'">'.$aRow['url'].'</a>'
                            :$aRow['url']
                    ));
                    
                    $aRow['type'] = $oRenderer->renderArrayValue('type', $aRow);
                    $aRow['http_code'] = $oRenderer->renderArrayValue('http_code', $aRow);
                    // unset($aRow['id']);
                    $aTable[] = array(
                        $sUrl,

                        $aRow['type'],
                        $aRow['content_type'],
                        $aRow['http_code'],
                    );

            }
        } else {
            $sReturn.=' :-/ ';
        }
        $sReturn.=$this->_getHtmlTable($aTable, "db-ressources.");
        return $sReturn;
    }


    // ----------------------------------------------------------------------
}
