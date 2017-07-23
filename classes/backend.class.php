<?php

require_once 'analyzer.html.class.php';
require_once 'crawler-base.class.php';
require_once 'crawler.class.php';
require_once 'ressources.class.php';
require_once 'renderer.class.php';
require_once 'search.class.php';
require_once 'status.class.php';

/**
 * 
 * AXLES CRAWLER :: BACKEND
 * 
 * 
 * */
class backend extends crawler_base {

    private $_aMenu = array(
        'home'=>array(), 
        'setup'=>array(),
        'search'=>array(
            'profiles'=>array(),
            'status'=>array(), 
            'searches'=>array(),
        ),
        'analysis'=>array(
            'warnings'=>array(), 
            'ressources'=>array(),
            'checkurl'=>array(), 
            'ressourcedetail'=>array(), 
        ), 
        'about'=>array()
    );
    private $_sPage = false;
    private $_sTab = false;
    
    private $_aIcons= array(
        'menu'=>array(
            'home'=>'fa fa-home', 
            'setup'=>'fa fa-cog', 
            'search'=>'fa fa-search', 
            'profiles'=>'fa fa-cogs', 
            'crawler'=>'fa fa-flag', 
            'status'=>'fa fa-flag', 
            'searches'=>'fa fa-search', 
            'analysis'=>'fa fa-newspaper-o', 
            'ressources'=>'fa fa-file-code-o', 
            'warnings'=>'fa fa-warning', 
            'checkurl'=>'fa fa-globe', 
            'ressourcedetail'=>'fa fa-map-o', 
            'about'=>'fa fa-info-circle', 
            'project'=>'fa fa-book', 
            
            'logoff'=>'fa fa-info-circle', 
        ),
        'cols'=>array(
            'url'=>'fa fa-link', 
            'title'=>'fa fa-chevron-right', 
            'description'=>'fa fa-chevron-right', 
            'errorcount'=>'fa fa-bolt', 
            'keywords'=>'fa fa-key', 
            'lasterror'=>'fa fa-bolt', 
            'actions'=>'fa fa-check', 
            'searchset'=>'fa fa-cube', 
            'query'=>'fa fa-search', 
            'results'=>'fa fa-bullseye', 
            'count'=>'fa fa-thumbs-o-up', 
            'host'=>'fa fa-laptop', 
            'ua'=>'fa fa-paw', 
            'referrer'=>'fa fa-link', 
            'ts'=>'fa fa-calendar', 
            'ressourcetype'=>'fa fa-cubes', 
            'type'=>'fa fa-cloud', 
            'content_type'=>'fa fa-file-code-o', 
            'http_code'=>'fa fa-retweet', 
            
            'updateisrunning'=>'fa fa-spinner fa-pulse', 
        ),
        'res'=>array(
            
            // ressourcetype
            'audio'=>'fa fa-file-sound-o',
            'css'=>'fa fa-eyedropper',
            'image'=>'fa fa-file-image-o',
            'link'=>'fa fa-link',
            'page'=>'fa fa-sticky-note-o',
            'redirect'=>'fa fa-mail-forward',
            'script'=>'fa fa-file-code-o',
            
            // type
            'internal'=>'fa fa-thumb-tack',
            'external'=>'fa fa-globe',
            
            // content_type/ MIME
            
            // http_code
            'http-code-0'=>'fa fa-spinner',
            'http-code-2xx'=>'fa fa-check',
            'http-code-3xx'=>'fa fa-mail-forward',
            'http-code-4xx'=>'fa fa-bolt',
            'http-code-5xx'=>'fa fa-spinner',
            
            'ressources.showtable'=>'fa fa-table',
            'ressources.showreport'=>'fa fa-file-o',
            'ressources.ignorelimit'=>'fa fa-unlock',
            
        ),
        'button'=>array(
            'button.close' => 'fa fa-close',
            'button.crawl' => 'fa fa-play',
            'button.delete' => 'fa fa-trash',
            'button.help' => 'fa fa-question-circle',
            'button.login' => 'fa fa-check',
            'button.logoff' => 'fa fa-power-off',
            'button.reindex' => 'fa fa-refresh',
            'button.search' => 'fa fa-search',
            'button.truncateindex' => 'fa fa-trash',
            'button.view' => 'fa fa-eye',
        ),
    );
    
    public $iLimitRessourcelist=1000;

    // ----------------------------------------------------------------------
    /**
     * new crawler
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct($iSiteId = false) {
        session_start();
        $this->setSiteId($iSiteId);
        $this->setLangBackend();
        $this->_getPage();
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
    private function _checkAuth() {
        $aOptions = $this->_loadOptions();
        if (
                !array_key_exists('options', $aOptions) || !array_key_exists('auth', $aOptions['options']) || !array_key_exists('user', $aOptions['options']['auth']) || !array_key_exists('password', $aOptions['options']['auth']) || $this->_getUser()
        ) {
            return true;
        }
        if (
                array_key_exists('AUTH_USER', $_POST) && array_key_exists('AUTH_PW', $_POST) && $aOptions['options']['auth']['user'] == $_POST['AUTH_USER'] && $aOptions['options']['auth']['password'] == md5($_POST['AUTH_PW'])
        ) {
            $this->_setUser($_POST['AUTH_USER']);
            return true;
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

        $sReturn = '<h3>' . $this->lB('login.title') . '</h3>'
                . '<p>' . $this->lB('login.infotext') . '</p>'
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
        // return '?'.  str_replace(array('%5B','%5D'), array('[',']'), http_build_query($aQueryParams));
        return '?'.  preg_replace('/%5B[0-9]+%5D/simU', '[]', http_build_query($aQueryParams));

        $s='';
        foreach ($aQueryParams as $var => $value) {
            if ($value){
                $s.="&amp;" . $var . "=" . urlencode($value);
            }
        }
        $s = "?" . $s;
        return $s;
        
    }

    /**
     * find the current page (returns one of the menu items of _aMenu)
     * @return string
     */
    private function _getPage() {
        $this->_sPage = (array_key_exists('page', $_GET) && $_GET['page']) ? $_GET['page'] : '';
        if (!$this->_sPage) {
            $aKeys=array_keys($this->_aMenu);
            $this->_sPage = $aKeys[0];
        }
        $this->_sTab = (array_key_exists('tab', $_GET) && $_GET['tab']) ? $_GET['tab'] : '';
        if ($this->_sTab) {
            setcookie("tab", $this->_sTab, time() + 3600);
        }
        return $this->_sPage;
    }

    /**
     * find the current tab or take the first id
     * @return type
     */
    private function _getTab($aTabs=false) {
        $this->_sTab = (array_key_exists('tab', $_GET) && $_GET['tab']) ? $_GET['tab'] : '';
        if (!$this->_sTab && array_key_exists('tab', $_COOKIE)) {
            $this->_sTab = $_COOKIE['tab'];
        }

        if (!$this->_sTab && is_array($aTabs)) {
            $aTmp = array_keys($aTabs);
            $this->_sTab = $aTmp[0];
        }

        return $this->_sTab;
    }

    private function _getNavItems($aNav){
        $sNavi = '';
        foreach ($aNav as $sItem=>$aSubItems) {
            $sNaviNextLevel='';
            if (count($aSubItems)){
                $sNaviNextLevel.=$this->_getNavItems($aSubItems);
            }
            $bHasActiveSubitem=strpos($sNaviNextLevel, 'pure-menu-link-active');
            $bIsActive=$this->_sPage == $sItem || $bHasActiveSubitem;
            $sClass = $bIsActive ? ' pure-menu-link-active' : '';
            $sUrl = '?page=' . $sItem;
            if ($this->_sTab) {
                $sUrl.='&amp;tab=' . $this->_sTab;
            }
            if(array_key_exists('menu', $this->aOptions)
                    && array_key_exists($sItem, $this->aOptions['menu'])
                    && !$this->aOptions['menu'][$sItem]
            ){
                // hide menu 
            } else {
                // $sNavi.='<li class="pure-menu-item"><a href="?'.$sItem.'" class="pure-menu-link'.$sClass.'">'.$sItem.'</a></li>';
                $sNavi.='<li class="pure-menu-item">'
                    . '<a href="?page=' . $sItem . '" class="pure-menu-link' . $sClass . '"'
                        . ' title="' . $this->lB('nav.' . $sItem . '.hint') . '"'
                        . '><i class="'.$this->_aIcons['menu'][$sItem].'"></i> ' 
                        . $this->lB('nav.' . $sItem . '.label') 
                    . '</a>'
                    . ($bIsActive ? $sNaviNextLevel : '')
                    ;
            
                $sNavi.='</li>';
            }
        }
        if($sNavi){
            $sNavi='<ul class="pure-menu-list">'.$sNavi.'</ul>';
        }
        
        return $sNavi;
    }
    
    /**
     * get html code for navigation; the current page is highlighted
     * @return string
     */
    public function getNavi() {
        if (!$this->_checkAuth()) {
            return '';
        }
        $sNavi = $this->_getNavItems($this->_aMenu);
        /*
        foreach ($this->_aMenu as $sItem) {
            $sClass = ($this->_sPage == $sItem) ? ' pure-menu-link-active' : '';
            $sUrl = '?page=' . $sItem;
            if (!$this->_sTab) {
                $sUrl.='&amp;tab=' . $this->_sTab;
            }
            // $sNavi.='<li class="pure-menu-item"><a href="?'.$sItem.'" class="pure-menu-link'.$sClass.'">'.$sItem.'</a></li>';
            $sNavi.='<li class="pure-menu-item">'
                    . '<a href="?page=' . $sItem . '" class="pure-menu-link' . $sClass . '"'
                    . ' title="' . $this->lB('nav.' . $sItem . '.hint') . '"'
                    . '><i class="'.$this->_aIcons['menu'][$sItem].'"></i> ' . $this->lB('nav.' . $sItem . '.label') . '</a></li>';
        }
         * 
         */
        return $sNavi;
    }

    /**
     * get html code for horizontal navigation
     * @param array $aTabs  nav items
     * @return string
     */
    private function _getNavi2($aTabs) {
        $sReturn = '';
        if (!$this->_sTab) {
            $this->_getTab($aTabs);
        }
        foreach ($aTabs as $sId => $sLabel) {
            $sUrl = '?page=' . $this->_sPage . '&amp;tab=' . $sId;
            $sClass = ($this->_sTab == $sId) ? ' pure-menu-link-active' : '';
            $sReturn.='<li class="pure-menu-item">'
                    . '<a href="' . $sUrl . '" class="pure-menu-link' . $sClass . '"'
                    . '>' . $this->_getIcon('project') . $sLabel . '</a></li>';
        }
        if ($sReturn) {
            $sReturn = '<div class="pure-menu pure-menu-horizontal">'
                    . '<ul class="pure-menu-list">'
                    . '' . $sReturn . ''
                    . '</ul>'
                    . '</div>';
        }
        return $sReturn;
    }

    /**
     * 
     * @return string
     */
    private function _renderChildItems($aNav){
        $sReturn='';
        foreach ($aNav as $sItem=>$aSubItems) {
            if ($this->_sPage!==$sItem){
                $sUrl = '?page=' . $sItem;
                if ($this->_sTab) {
                    $sUrl.='&amp;tab=' . $this->_sTab;
                }
                // $sNavi.='<li class="pure-menu-item"><a href="?'.$sItem.'" class="pure-menu-link'.$sClass.'">'.$sItem.'</a></li>';
                if(array_key_exists('menu', $this->aOptions)
                        && array_key_exists($sItem, $this->aOptions['menu'])
                        && !$this->aOptions['menu'][$sItem]
                ){
                    // hide item
                } else {
                    $sReturn.=
                        '<a href="?page=' . $sItem . '" class="childitem"'
                            . ' title="' . $this->lB('nav.' . $sItem . '.hint') . '"'
                            . '><i class="'.$this->_aIcons['menu'][$sItem].'"></i> ' 
                            . '<strong>'.$this->lB('nav.' . $sItem . '.label').'</strong><br>'
                            .$this->lB('nav.' . $sItem . '.hint')
                        . '</a>'
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
        $sH2 = $this->lB('nav.' . $this->_sPage . '.label');
        $sHint = $this->lB('nav.' . $this->_sPage . '.hint');
        if (!$this->_checkAuth()) {
            $sH2 = $this->lB('nav.login.label');
            $sHint = $this->lB('nav.login.access-denied');
        }
        
        $oStatus=new status();
        $aStatus=$oStatus->getStatus();
        $sStatus='';
        if ($aStatus && is_array($aStatus)){
            $sStatus.=''
                    . $this->_getIcon('updateisrunning')
                    . 'Start: '.date("H:i:s", $aStatus['start'])
                    . ' ('. ($aStatus['last']-$aStatus['start']).' s): '
                    . $aStatus['action'] . ' - '
                    . $aStatus['lastmessage'].' <br>'
                    // .'<pre>'.print_r($aStatus, 1).'</pre>'
                    ;
        } else {
            // $sStatus=$this->lB('status.no-action');
        }
        
                
        return ''
                . ($this->_checkAuth() && $this->_getUser()
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
                . '<h2>'
                . '<i class="'.$this->_aIcons['menu'][$this->_sPage].'"></i> ' . $sH2 . '</h2>'
                . '<p class="pageHint">' . $sHint . '</p>'
                . ($sStatus ? '<div id="divStatus">'. $sStatus .'</div>' : '')
        ;
    }

    // ----------------------------------------------------------------------
    // PROFILE/ CONFIG
    // ----------------------------------------------------------------------

    /**
     * get array with search profiles
     * @return array
     */
    private function _getProfiles() {
        $aOptions = $this->_loadOptions();
        $aReturn = array();
        if (array_key_exists('profiles', $aOptions) && count($aOptions['profiles'])) {
            foreach ($aOptions['profiles'] as $sId => $aData) {
                $aReturn[$sId] = $aData['label'];
            }
        }
        return $aReturn;
    }

    /**
     * get array with profile data of an existing config
     * @see _getProfiles()
     * @param string   $sId  id of search profile
     * @return array
     */
    private function _getProfileConfig__UNUSED($sId) {
        $aOptions = $this->_loadOptions();
        if (array_key_exists('profiles', $aOptions) && array_key_exists($sId, $aOptions['profiles'])) {
            return $aOptions['profiles'][$sId];
        }
        return false;
    }

    // ----------------------------------------------------------------------
    // OUTPUT RENDERING
    // ----------------------------------------------------------------------

    /**
     * get html code for a result table
     * @param array  $aResult          result of a select query
     * @param string $sLangTxtPrefix   langtext prefix
     * @return string
     */
    private function _getHtmlTable($aResult, $sLangTxtPrefix = '') {
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
            $sReturn = '<table class="pure-table pure-table-horizontal datatable">'
                    . '<thead><tr>' . $sTh . '</tr></thead>'
                    . '<tbody>' . $sReturn . ''
                    . '</tbody>'
                    . '</table>';
        }
        return $sReturn;
    }

    /**
     * get html code for a simple table without table head
     * @param array  $aResult          result of a select query
     * @return string
     */
    private function _getSimpleHtmlTable($aResult) {
        $sReturn = '';
        foreach ($aResult as $aRow) {
            $sReturn.='<tr>';
            foreach ($aRow as $sField) {
                $sReturn.='<td>' . $sField . '</td>';
            }
            $sReturn.='</tr>';
        }
        if ($sReturn) {
            $sReturn = '<table class="pure-table pure-table-horizontal"><thead></thead>'
                    . '<tbody>' . $sReturn . ''
                    . '</tbody>'
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
        if (!array_key_exists('popup', $aOptions)) {
            $aOptions['popup'] = true;
        }
        $sReturn = '<a '
                . 'class="pure-button ' . $aOptions['class'] . '" '
                . 'href="' . $aOptions['href'] . '" '
                . 'target="' . $aOptions['target'] . '" '
                . 'title="' . $this->lB($aOptions['label'] . '.hint') . '" '
                . ($aOptions['popup'] ? 'onclick="showModal(this.href); return false;"' : '')
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
        $sVal = htmlentities($sVal);
        return (strlen($sVal) > $iMax) ? substr($sVal, 0, $iMax) . '<span class="more"></span>' : $sVal;
    }

    /**
     * get html code for a simple table without table head
     * @param array  $aResult          result of a select query
     * @return string
     */
    private function _getSearchindexTable($aResult, $sLangTxtPrefix = '') {
        $aTable = array();
        foreach ($aResult as $aRow) {
            $sId = $aRow['id'];
            unset($aRow['id']);
            foreach ($aRow as $sKey => $sVal) {
                $aRow[$sKey] = $this->_prettifyString($sVal);
            }
            $aRow['url']=str_replace('/', '/&shy;', $aRow['url']);
            $aRow['actions'] = $this->_getButton(array(
                'href' => 'overlay.php?action=viewindexitem&id=' . $sId,
                'class' => 'button-secondary',
                'label' => 'button.view'
            ));
            $aTable[] = $aRow;
        }
        return $this->_getHtmlTable($aTable, $sLangTxtPrefix);
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
        if (!$this->_checkAuth()) {
            return $this->_getLoginForm();
        }
        $sMethod = "_getContent" . $this->_sPage;
        if (method_exists($this, $sMethod)) {
            return call_user_func(__CLASS__ . '::' . $sMethod, $this);
        }
        return 'notDefinedYet: ' . __CLASS__ . '::' . $sMethod;
    }

    /**
     * page cotent :: home
     */
    private function _getContenthome() {
        $sReturn = '';
        $sReturn.=$this->_renderChildItems($this->_aMenu)
                . '<h3>' . $this->lB('home.welcome') . '</h3>'
                . (!$this->_getUser() && (
                        !array_key_exists('PHP_AUTH_USER', $_SERVER)
                        || !$_SERVER['PHP_AUTH_USER']
                        )
                 ? '<div class="warning">' . $this->lB('home.cfg.unprotected') . '</div><br><br>' 
                : ''
                )
                . '<p>' . $this->lB('home.welcome-introtext') . '</p>'
                
                /*
                . $this->_getButton(array(
                    'href' => 'https://www.axel-hahn.de/docs/',
                    'class' => 'button-secondary',
                    'label' => 'button.help'
                ))

                . '<h3>' . $this->lB('home.cfg') . '</h3>'
                . $this->_getSimpleHtmlTable(
                        array(
                            array($this->lB('home.cfg.cfgfile'), dirname(__DIR__) . '/config/crawler.config.json'),
                            array($this->lB('home.cfg.db-type'), $this->aOptions['database']['database_type']),
                            array($this->lB('home.cfg.lang'), $this->aOptions['lang']),
                        )
                )
                 * 
                 */
                ;
        // $sReturn.='<h3>' . $this->lB('rawdata') . '</h3><pre>' . print_r($this->aOptions, 1) . '</pre>';

        return $sReturn;
    }
    
    private function _getContentsetup() {
        $sCfg=file_get_contents($this->_getOptionsfile());
        $sReturn='
            <!--
                <link rel="stylesheet" href="../vendor/codemirror/lib/codemirror.css">
                <script src="../vendor/codemirror/lib/codemirror.js"></script>
                <script src="../vendor/codemirror/addon/edit/matchbrackets.js"></script>
                <script src="../vendor/codemirror/addon/comment/continuecomment.js"></script>
                <script src="../vendor/codemirror/addon/comment/comment.js"></script>
                <script src="../vendor/codemirror/mode/javascript/javascript.js"></script>
            -->
                <form>
                    <textarea id="taconfig" name="config" cols="120" rows="20">'.$sCfg.'</textarea>

                </form>
            <!--
                <script>
                  var editor = CodeMirror.fromTextArea(document.getElementById("taconfig"), {
                    matchBrackets: true,
                    autoCloseBrackets: true,
                    mode: "application/ld+json",
                    lineNumbers: true,
                    lineWrapping: true
                  });
                </script>
            -->
        ';
        return $sReturn;
    }

    /**
     * page cotent :: logoff
     */
    private function _getContentlogoff() {
        $this->_setUser('');
        return $this->_getLoginForm();
    }

    /**
     * page cotent :: profiles
     */
    private function _getContentprofiles() {
        $sReturn = '';
        $this->_getTab();
        $this->setSiteId($this->_sTab);
        $sReturn.=$this->_getNavi2($this->_getProfiles())
                . '<h3>' . $this->lB('profile.vars.searchprofile') . '</h3>'
                // . '<pre>' . print_r($this->aProfile, 1) . '</pre>'
                ;
        $aTbl = array();
        // foreach ($this->_getProfileConfig($this->_sTab) as $sVar => $val) {
        foreach ($this->aProfile as $sVar => $val) {

            $sTdVal = '';
            if (is_array($val)){
                foreach($val as $sKey=>$subvalue){
                    $sTdVal .= '<span class="key2">'.$sKey.'</span>:<br>'
                            .((is_array($subvalue)) ? ' - <span class="value">' . implode('</span><br> - <span class="value">', $subvalue) : '<span class="value">'.$subvalue.'</span>')
                            .'</span><br><br>'
                            ;                    
                }
            } else {
                $sTdVal .= (is_array($val)) ? '<span class="value">'.implode('</span><br> - <span class="value">', $val).'</span>' : '<span class="value">'.$val.'</span>';
            }

            $aTbl[] = array($this->lB("profile." . $sVar), '<span class="key">'.$sVar.'</span>', $sTdVal);
        }
        $sReturn.=$this->_getSimpleHtmlTable($aTbl);
        /*
        $sReturn.='<h3>' . $this->lB('rawdata') . '</h3>'
                . '<pre>' . print_r($this->_getProfileConfig($this->_sTab), 1) . '</pre>';
        ;
         * 
         */
        return $sReturn;
    }

    /**
     * page cotent :: status
     */
    private function _getContentstatus() {
        $sReturn = '';
        $sReturn.=$this->_getNavi2($this->_getProfiles());

        $aHeaderIndex = array('id', 'ts', 'url', 'title', 'errorcount', 'lasterror');

        $oCrawler=new crawler($this->_sTab);
        
        $iUrls = $oCrawler->getCount();        
        if(!$iUrls){
            $sReturn.='<br><div class="warning">'.$this->lB('status.emptyindex').'</div>';
            return $sReturn;
        }
        
        $sLast = $oCrawler->getLastRecord();
        $sOldest = $this->oDB->min('pages', array('ts'), array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),));

        
        $iUrlsLast24=$oCrawler->getCount(
            array(
                'siteid' => $this->_sTab,
                'ts[>]' => date("Y-m-d H:i:s", (date("U") - (60 * 60 * 24))),
            )
        );
        // echo "\n" . $this->oDB->last_query() . '<br>'; 
        $iUrlsErr = $oCrawler->getCount(array(
            'AND' => array(
                'siteid' => $this->_sTab,
                'errorcount[>]' => 0,
            )));


        $aNewestInIndex = $this->oDB->select(
                'pages', $aHeaderIndex, array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
            "ORDER" => array("ts"=>"DESC"),
            "LIMIT" => 5
                )
        );
        $aOldestInIndex = $this->oDB->select(
                'pages', $aHeaderIndex, array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
            "ORDER" => array("ts"=>"ASC"),
            "LIMIT" => 5
                )
        );
        $aEmpty = $this->oDB->select(
                'pages', $aHeaderIndex, array(
            'AND' => array(
                'siteid' => $this->_sTab,
                'title' => '',
                'content' => '',
            ),
            "ORDER" => array("ts"=>"ASC"),
            "LIMIT" => 5
                )
        );

        // echo "\n" . $this->oDB->last_query() . '<br>'; 
        // print_r($aResult);
        $sReturn.='<h3>' . $this->lB('status.overview') . '</h3>'
            .$this->_getSimpleHtmlTable(
                array(
                    array($this->lB('status.last_updated.label'), $sLast),
                    array($this->lB('status.indexed_urls.label'), $iUrls),
                    array($this->lB('status.indexed_urls24h.label'), $iUrlsLast24),
                    array($this->lB('status.error_urls.label'), $iUrlsErr),
                    array($this->lB('status.oldest_updated.label'), $sOldest),
                )
        );
        $sReturn.='<br>'
                . $this->_getButton(array(
                    'href' => 'overlay.php?action=search&query=&siteid=' . $this->_sTab . '&searchset=none',
                    'class' => 'button-secondary',
                    'label' => 'button.search'
                ))
                /*
                . ' '
                . $this->_getButton(array(
                    'href' => 'overlay.php?action=crawl&siteid=' . $this->_sTab,
                    'class' => 'button-success',
                    'label' => 'button.crawl'
                ))
                . ' '
                . $this->_getButton(array(
                    'href' => 'overlay.php?action=truncate&siteid=' . $this->_sTab,
                    'class' => 'button-error',
                    'label' => 'button.truncateindex'
                ))
                 * 
                 */
                ;
        if (count($aNewestInIndex)) {
            $sReturn.='<h3>' . $this->lB('status.newest_urls_in_index') . '</h3>'
                    . $this->_getSearchindexTable($aNewestInIndex, 'pages.');
        }
        if (count($aOldestInIndex)) {
            $sReturn.='<h3>' . $this->lB('status.oldest_urls_in_index') . '</h3>'
                    . $this->_getSearchindexTable($aOldestInIndex, 'pages.');
        }

        if (count($aEmpty)) {
            $sReturn.='<h3>' . $this->lB('status.empty_data') . '</h3>'
                    . $this->_getSearchindexTable($aEmpty, 'pages.')
            ;
        }
        if ($iUrlsErr) {
            $aErrorUrls = $this->oDB->select(
                    'pages', $aHeaderIndex, array(
                'AND' => array(
                    'siteid' => $this->_sTab,
                    'errorcount[>=]' => 0,
                ),
                "ORDER" => array("ts"=>"ASC"),
                "LIMIT" => 50
                    )
            );
            $sReturn.='<h3>' . $this->lB('status.error_urls') . '</h3>'
                    . $this->_getSearchindexTable($aErrorUrls, 'pages.')
            ;
        }

        return $sReturn;
    }
    
    /**
     * page cotent :: searches 
     */
    private function _getContentsearch() {
        return $this->_renderChildItems($this->_aMenu['search']);
    }

    /**
     * page cotent :: searches 
     */
    private function _getContentsearches() {
        $sReturn = '';
        $aFields = array('ts', 'searchset', 'query', 'results', 'host', 'ua', 'referrer');
        $sReturn.=$this->_getNavi2($this->_getProfiles());
        $aLastSearches = $this->oDB->select(
                'searches', 
                $aFields, 
                array(
                    'AND' => array(
                        'siteid' => $this->_sTab,
                    ),
                    "ORDER" => array("ts"=>"DESC"),
                    "LIMIT" => 20
                )
        );
        /*
        $aSearches = $this->oDB->select(
                'searches', 
                array('query'), 
                array(
                    'AND' => array(
                        'siteid' => $this->_sTab,
                    ),
                    "ORDER" => "ts DESC",
                    "LIMIT" => 20
                )
        );
         * 
         */
        
        $aDays=array(7,30,90,365);
        foreach($aDays as $iDays){
            $oResult=$this->oDB->query(''
                    . 'SELECT query, count(query) as count, results '
                    . 'FROM "searches" '
                    . 'WHERE "siteid" = "'.$this->_sTab.'" '
                    . 'AND "ts" > "'.date("Y-m-d H:i:s", (date("U") - (60 * 60 * 24 * $iDays))).'" '
                    . 'GROUP BY query '
                    . 'ORDER BY count desc, query asc '
                    . 'LIMIT 0,10'
            );
            
            $aSearches[$iDays]=($oResult ? $oResult->fetchAll(PDO::FETCH_ASSOC) : array());
        }
        
        // --- output
        
        if (count($aLastSearches)) {
            $aTable = array();
            foreach ($aLastSearches as $aRow) {
                $aTmp=  unserialize($aRow['searchset']);
                $sSubdir=(is_array($aTmp) && array_key_exists('subdir', $aTmp)) 
                    ? $aTmp['subdir'] 
                    : (is_array($aTmp) && array_key_exists('url', $aTmp))
                        ? preg_replace('#//.*[/%]#', '/', $aTmp['url'], 1)
                        : '/'
                    ;
                // $sSubdir=(is_array($aTmp) && array_key_exists('subdir', $aTmp)) ? $aTmp['subdir'] : '';
                $aRow['actions'] = $this->_getButton(array(
                    'href' => 'overlay.php?action=search&query=' . $aRow['query'] . '&siteid=' . $this->_sTab . '&subdir=' . $sSubdir,
                    'class' => 'button-secondary',
                    'label' => 'button.search'
                ));

                $aTable[] = $aRow;
            }
            $sReturn.='<h3>' . $this->lB('profile.searches.last') . '</h3>' 
                    . $this->_getHtmlTable($aTable, "searches.");
        } else {
            $sReturn.='<br><div class="warning">'.$this->lB('profile.searches.empty').'</div>';
        }
        
        
        
        foreach($aDays as $iDays){
            if (count($aSearches[$iDays])) {
                $aTable = array();
                foreach ($aSearches[$iDays] as $aRow) {
                    $aTable[] = $aRow;
                }

                $sReturn.= '<h3>' . sprintf($this->lB('profile.searches.top10lastdays'), $iDays) . '</h3>' 
                        . $this->_getHtmlTable($aTable, "searches.");
            }         
        }
        /*
          // echo $this->oDB->last_query() . '<br>';
          foreach ($aResult as $aRow){
          $sReturn.='<tr>';
          foreach ($aFields as $sField){
          $sReturn.='<td class="td-'.$sField.'">'.$aRow[$sField].'</td>';
          }
          $sReturn.='</tr>';
          }
          if($sReturn){
          $sTh='';
          foreach ($aFields as $sField){
          $sTh.='<th class="th-'.$sField.'">'.$this->lB('searches.'.$sField).'</th>';
          }
          $sReturn='<table class="pure-table pure-table-horizontal pure-table-striped">'
          . '<thead><tr>'.$sTh.'</tr></thead>'
          . '<tbody>'.$sReturn.''
          . '</tbody>'
          . '</table>';
          }
         * 
         */
        return $sReturn;
    }

    /**
     * page content :: Ressources 
     */
    private function _getContentanalysis() {
        // $oRenderer=new ressourcesrenderer($this->_sTab);
        return $this->_renderChildItems($this->_aMenu['analysis'])
                //.$oRenderer->renderRessourceStatus()
                ;
    }
    
    /**
     * page content :: analyzer
     */
    private function _getContentressources() {
        $sReturn = '';
        $aCounter = array();
        $aFilter=array('http_code', 'ressourcetype','type', 'content_type');
        $aFields = array('id', 'url', 'http_code', 'ressourcetype', 'type', 'content_type');
        $sReturn.=$this->_getNavi2($this->_getProfiles());
        
        $aUrl=array();
        
        // $sSiteId = $this->_getRequestParam('siteid');
        $oRessources=new ressources();
        $oRenderer=new ressourcesrenderer($this->_sTab);
        
        $aWhere=array('siteid' => $this->_sTab);
        if (array_key_exists('filteritem', $_GET) && array_key_exists('filtervalue', $_GET)){
            for ($i=0; $i<count($_GET['filteritem']); $i++){
                $aWhere[$_GET['filteritem'][$i]]=($_GET['filtervalue'][$i]==='') ? null : $_GET['filtervalue'][$i];
                $aUrl[]=array('filteritem'=>$_GET['filteritem'][$i], 'filtervalue'=>$_GET['filtervalue'][$i]);
            }
        }
        // -- get list of all data
        $iResCount = $oRessources->getCount($aWhere);
        
        // -- get list of filter data
        $aCounter2=array();
        foreach ($aFilter as $sKey){
            $aCounter2[$sKey]=$oRessources->getCountsOfRow('ressources', $sKey, $aWhere);
        }
        
        // --- output
        
        //
        // line with set filters
        //
        $sSelfUrl='?'.$_SERVER["QUERY_STRING"];
        $sBaseUrl='?page='.$_GET['page'].'&tab='.$this->_sTab;
        $sFilter='';
        $sReport = '';
        
        // --- button bar with filter items (for remove by click)
        if (array_key_exists('filteritem', $_GET) && array_key_exists('filtervalue', $_GET)){
            
            for ($i=0; $i<count($_GET['filteritem']); $i++){
                $aRemoveUrl=$aUrl;
                unset($aRemoveUrl[$i]);
                $sUrl=$sBaseUrl;
                foreach($aRemoveUrl as $aItem){
                    $sUrl.=$sUrl.='&amp;';
                    $sUrl.='filteritem[]='.$aItem['filteritem'].'&amp;filtervalue[]='.$aItem['filtervalue'];
                }
                // $sUrl=str_replace($sRemove, '', $sSelfUrl);
                $sFilter.='<a href="'.$sUrl.'"'
                        . ' class="pure-button"'
                        . '><span class="varname">'.$this->_getIcon($_GET['filteritem'][$i]).$_GET['filteritem'][$i].'</span> = <span class="value">'.$oRenderer->renderValue($_GET['filteritem'][$i], $_GET['filtervalue'][$i]).'</span> '
                        . '<i class="fa fa-close"></i>'
                        . '</a> ';
            }
            $sFilter= '<i class="fa fa-filter"></i> '
                    . $this->lB('ressources.filter').$sFilter.' '
                    . ($i>1 ? '<a href="'.$sBaseUrl.'"'
                        . ' class="pure-button button-error"'
                        . '> '
                        . '<i class="fa fa-close"></i>'
                        . '</a>'
                    : '')
                    . '<br><br>';
        }

        // --- what to create: table or report list
        $bShowReport=(array_key_exists('showreport', $_GET) && $_GET['showreport']);
        $iReportCounter=0;
        $bIgnoreLimit=(array_key_exists('ignorelimit', $_GET) && $_GET['ignorelimit']);

        
        $bShowRessourcetable=(array_key_exists('showtable', $_GET) && $_GET['showtable'] || !$bShowReport);
        if ($iResCount>$this->iLimitRessourcelist && !$bIgnoreLimit){
            $bShowReport=false;
            $bShowRessourcetable=false;
        }

        if ($iResCount) {
            
            $aTable = array();


            if ($bShowReport || $bShowRessourcetable){
                $aRessourcelist = $oRessources->getRessources($aFields, $aWhere, array("url"=>"ASC"));
                //
                // loop for table or report items 
                //
                foreach ($aRessourcelist as $aRow) {

                    // --- generate report
                    if ($bShowReport){

                        $iReportCounter++;
                        $sReport.=''
                                .'['. $iReportCounter.'] '
                                .$oRenderer->renderReportForRessource($aRow);
                    }
                    // --- generate table view
                    if ($bShowRessourcetable){
                        $aRow['url'] = '<a href="?page=ressourcedetail&id=' . $aRow['id'] . '&siteid=' . $this->_sTab.'">'.str_replace('/', '/&shy;', $aRow['url']).'</a>';

                        /*
                        $aRow['actions'] = $this->_getButton(array(
                            'href' => 'overlay.php?action=ressourcedetail&id=' . $aRow['id'] . '&siteid=' . $this->_sTab . '',
                            'class' => 'button-secondary',
                            'label' => 'button.view'
                        ));
                         * 
                         */
                        $aRow['ressourcetype'] = $oRenderer->renderArrayValue('ressourcetype', $aRow);
                        $aRow['type'] = $oRenderer->renderArrayValue('type', $aRow);
                        $aRow['http_code'] = $oRenderer->renderArrayValue('http_code', $aRow);

                        unset($aRow['id']);
                        $aTable[] = $aRow;
                    }

                }

                if ($bShowReport){
                    $sReport='<br>'.$sReport;
                    $sReport.='';
                }
                //
                // table array for ressources
                //
                if(count($aRessourcelist)){
                    $aTableFilter[]=array('<strong>'.$this->lB('ressources.itemstotal').'</strong>', '' ,'<strong>'.count($aRessourcelist).'</strong>');
                }
            }
            
            foreach ($aFilter as $sKey){
                $sRessourcelabel=(array_key_exists($sKey, $this->_aIcons['cols']) ? '<i class="'.$this->_aIcons['cols'][$sKey].'"></i> ' : '') . $sKey;
                $aTableFilter[]=array('<strong>'.$sRessourcelabel.'</strong>', '', '');
                foreach ($aCounter2[$sKey] as $aCounterItem){
                    $sCounter=$aCounterItem[$sKey];
                    $iValue=$aCounterItem['count'];
                    $aTableFilter[]=array(
                        '', 
                        (count($aCounter2[$sKey])>1
                            ? '<a href="'.$sSelfUrl.'&amp;filteritem[]='.$sKey.'&amp;filtervalue[]='.$sCounter.'">'
                                .$oRenderer->renderValue($sKey, $sCounter)
                                .'</a>'
                            : $oRenderer->renderValue($sKey, $sCounter)
                        )
                        , 
                        $iValue
                    );
                }
            }
        }
        
        // --- output
        
        $sBtnReport=$this->_getButton(array(
            'href'=>$this->_getQs(array(
                'showreport'=>1,
                'showtable'=>0,
                'tab'=>$this->_sTab,
            )).'#restable',
            'class'=>'button-secondary',
            'label'=>'ressources.showreport',
            'popup' => false
        ));
        $sBtnTable=$this->_getButton(array(
            'href'=>$this->_getQs(array(
                'showreport'=>0,
                'showtable'=>1,
                'tab'=>$this->_sTab,
            )).'#restable',
            'class'=>'button-secondary',
            'label'=>'ressources.showtable',
            'popup' => false
        ));
        $sReturn.='<h3>' . $this->lB('ressources.overview') . '</h3>'
                . '<p>'.$this->lB('ressources.overview.intro').'</p>'
                . $oRenderer->renderRessourceStatus()
                . $sFilter
                ;
        

        if ($iResCount) {
            $sReturn.=$this->_getSimpleHtmlTable($aTableFilter)
                    . '<h3 id="restable">' . $this->lB('ressources.list') . '</h3>' ;
            
            if ($bShowRessourcetable){
                $sReturn.='<p>'
                        . $sBtnReport.'<br><br>'
                        . $this->lB('ressources.list.intro')
                        . '</p>'
                    . $this->_getHtmlTable($aTable, "db-ressources.")
                    ;
            } 
            if ($bShowReport){
                $sReturn.='<p>'
                        . $sBtnTable.'<br><br>'
                        . $this->lB('ressources.report.intro')
                        . '</p>'
                        . $sReport
                        ;
            } 
            if($iResCount>$this->iLimitRessourcelist && !$bIgnoreLimit){
                $sReturn.='<p>'.$this->lB('ressources.hint-manyitems')
                . '<br><br>'
                . $this->_getButton(array(
                    'href'=>$this->_getQs(array(
                        'showtable1'=>1,
                        'showreport'=>0,
                        'ignorelimit'=>1,
                    )),
                    'class'=>'button-error',
                    'label'=>'ressources.ignorelimit',
                    'popup' => false
                    ))
                    ;
            } else if (!$bShowReport && !$bShowRessourcetable){
                $sReturn.= $sBtnTable. ' '. $sBtnReport;
            }
            
        } else {
            $sReturn.='<br><div class="warning">'.$this->lB('ressources.empty').'</div>';
        }
                
        $sReturn.='<script>$(document).ready( function () {$(\'.datatable\').DataTable();} );</script>';
        

        return $sReturn;
    }


    /**
     * page cotent :: Warnings 
     */
    private function _getContentwarnings() {
        $sReturn = '';
        $sReturn.=$this->_getNavi2($this->_getProfiles());

        $iMinTitleLength=10;
        $iMinDescriptionLength=40;
        $iMinKeywordsLength=10;
        
        $aPageFields=array('id', 'url', 'description', 'description');
        $aWhere=array('siteid' => $this->_sTab);
        $oRessources=new ressources($this->_sTab);
        $oRenderer=new ressourcesrenderer($this->_sTab);
        
        $iSearchindexCount=$this->oDB->count('pages',array('siteid'=>$this->_sTab));        
        if (!$iSearchindexCount) {
            return $sReturn.'<br><div class="warning">'.$this->lB('status.emptyindex').'</div>';
        }
        $iRessourcesCount=$this->oDB->count('ressources',array('siteid'=>$this->_sTab));
        
        if (!$iRessourcesCount) {
            return $sReturn.'<br><div class="warning">'.$this->lB('ressources.empty').'</div>';
        }
        
        
        
        if ($iRessourcesCount){
            
            $aCountByStatuscode=$oRessources->getCountsOfRow('ressources', 'http_code', array('siteid'=> $this->_sTab));
            
            
            $aBoxes=array('todo'=>array(), 'errors'=>array(),'warnings'=>array(), 'ok'=>array());
            // echo '<pre>$aCountByStatuscode = '.print_r($aCountByStatuscode,1).'</pre>';
            // --- http errors
            foreach ($aCountByStatuscode as $aStatusItem){
                $iHttp_code=$aStatusItem['http_code'];
                $iCount=$aStatusItem['count'];
                $oHttp=new httpstatus();
                $oHttp->setHttpcode($iHttp_code);
                
                if ($oHttp->isError()){
                   $aBoxes['errors'][$iHttp_code] = $iCount;
                }
                if ($oHttp->isRedirect()){
                   $aBoxes['warnings'][$iHttp_code] = $iCount;
                }
                if ($oHttp->isOperationOK()){
                   $aBoxes['ok'][$iHttp_code] = $iCount;
                }
                if ($oHttp->isTodo()){
                   $aBoxes['todo'][$iHttp_code] = $iCount;
                }
            }
            // echo '<pre>$aBoxes = '.print_r($aBoxes,1).'</pre>';
            $sBar='';
            $sResResult='';
            foreach (array_keys($aBoxes) as $sSection){
                $sLegende='';
                if (array_key_exists($sSection, $aBoxes)){
                    if (count($aBoxes[$sSection])){
                        $sResResult.=''
                                . '<h4>'.$this->lB('warnings.found-http-'.$sSection).'</h4>'
                                . '<p>'.$this->lB('warnings.found-http-'.$sSection.'-hint').'</p>'
                                . '<ul class="tiles '.$sSection.'">';
                        foreach ($aBoxes[$sSection] as $iHttp_code=>$iCount){
                            $shttpStatusLabel=$this->lB('httpcode.'.$iHttp_code.'.label', 'httpcode.???.label');
                            $shttpStatusDescr=$this->lB('httpcode.'.$iHttp_code.'.descr', 'httpcode.???.descr');
                            
                            $sBar.='<div class="bar-'.$sSection.'" style="width: '.($iCount/$iRessourcesCount*100 - 3).'%; float: left;" '
                                    . 'title="'.$iCount.' x '.$this->lB('db-ressources.http_code').' '.$iHttp_code.'">'.$iCount.'</div>';
                            $sResResult.='<li>'
                                    .'<a href="?page=ressources&showreport=1&showtable=0&filteritem[]=http_code&filtervalue[]='.$iHttp_code.'#restable" class="tile"'
                                    . 'title="'.$shttpStatusDescr.'">'
                                    . $this->lB('db-ressources.http_code').' '
                                    . $oRenderer->renderValue('http_code', $iHttp_code).'<br><br>'
                                    . '<strong>'
                                        .$iCount
                                    .'</strong><br>'
                                    . $shttpStatusLabel.'<br>'
                                    . '</a>'
                                . '</li>';
                            
                            $sLegende.='<li>'
                                    . '<strong>'.$iHttp_code.'</strong> '
                                    . $shttpStatusDescr
                                    ;
                        }
                    }
                    $sResResult.='</ul>'
                        . '<div style="clear: both;"></div>'
                        . ($sLegende ? '<p>'.$this->lB('warnings.legend').'</p><ul>'.$sLegende.'</ul>' : '')
                        ;
                }
            }
            $sReturn.='<h3>'.$this->lB("warnings.check-links").'</h3>'
                    . $oRenderer->renderRessourceStatus()
                    . '<div class="bar">'.$sBar.'&nbsp;</div><br><br><br><br><br>'
                    . $sResResult
                    ;
            
        }
        
        // --- Warnings from searchindex
        
        $sReturn.=''
                . '<h3>' . $this->lB('warnings.check-content') . '</h3>'
                . '<p>'.$this->lB('status.indexed_urls.label').': <strong>'.$iSearchindexCount.'</strong></p>'
                ;
        if ($iSearchindexCount){
            // HINT: length is avialable in SQL for sqlite, mysql
            $aCountShortTitles = $this->oDB->query('
                        select count(*) count from pages 
                        where siteid='.$this->_sTab.' and length(title)<'.$iMinTitleLength
                )->fetchAll(PDO::FETCH_ASSOC);
            $aCountShortDescr = $this->oDB->query('
                        select count(*) count from pages 
                        where siteid='.$this->_sTab.' and length(description)<'.$iMinDescriptionLength
                    )->fetchAll(PDO::FETCH_ASSOC);
            
            $aCountShortKeywords = $this->oDB->query('
                        select count(*) count from pages 
                        where siteid='.$this->_sTab.' and length(keywords)<'.$iMinKeywordsLength
                    )->fetchAll(PDO::FETCH_ASSOC);
            /*
            $aShortTitles = $this->oDB->query(
                    '
                        select length(title), title, url
                        from pages 
                        where 
                            siteid='.$this->_sTab.'
                            and length(title)<'.$iMinTitleLength.'
                        order by length(title)
                    '
                    )->fetchAll(PDO::FETCH_ASSOC);
            $aShortDescr = $this->oDB->query(
                    '
                        select length(description), description, title, url
                        from pages 
                        where 
                            siteid='.$this->_sTab.'
                            and length(description)<'.$iMinDescriptionLength.'
                        order by length(description)
                    '
                    )->fetchAll(PDO::FETCH_ASSOC);
            $aShortKeywords = $this->oDB->query(
                    '
                        select length(keywords), keywords, title, url
                        from pages 
                        where 
                            siteid='.$this->_sTab.'
                            and length(keywords)<'.$iMinKeywordsLength.'
                        order by length(keywords)
                    '
                    )->fetchAll(PDO::FETCH_ASSOC);
            */
            
            // TODO translate
            $sReturn.='<ul class="tiles warnings">'
                . ($aCountShortTitles[0]['count']
                    ? '<li><a href="#tblshorttitle" class="tile">'.sprintf($this->lB('status.tile-check-short-title'), $iMinTitleLength).':<br><strong>'.$aCountShortTitles[0]['count'].'</strong></a></li>'
                    : '<li><a href="#" class="tile ok">'.sprintf($this->lB('status.tile-check-short-title'), $iMinTitleLength).':<br><strong>'.$aCountShortTitles[0]['count'].'</strong></a></li>'
                )
                . ($aCountShortDescr[0]['count']
                    ? '<li><a href="#tblshortdescription" class="tile">'.sprintf($this->lB('status.tile-check-short-description'), $iMinTitleLength).':<br><strong>'.$aCountShortDescr[0]['count'].'</strong></a></li>'
                    : '<li><a href="#" class="tile ok">'.sprintf($this->lB('status.tile-check-short-description'), $iMinTitleLength).':<br><strong>'.$aCountShortDescr[0]['count'].'</strong></a></li>'
                )
                . ($aCountShortKeywords[0]['count']
                    ? '<li><a href="#tblshortkeywords" class="tile">'.sprintf($this->lB('status.tile-check-short-keywords'), $iMinKeywordsLength).':<br><strong>'.$aCountShortKeywords[0]['count'].'</strong></a></li>'
                    : '<li><a href="#" class="tile ok">'.sprintf($this->lB('status.tile-check-short-keywords'), $iMinKeywordsLength).':<br><strong>'.$aCountShortKeywords[0]['count'].'</strong></a></li>'
                )
                /*
                . '<li><a href="#tblshorttitle" class="tile">zu kurze Titel<br>(kleiner '.$iMinTitleLength.' Zeichen):<br><strong>'.count($aShortTitles).'</strong></a></li>'
                . '<li><a href="#tblshortdescription" class="tile">zu kurze Beschreibung<br>(kleiner '.$iMinDescriptionLength.' Zeichen):<br><strong>'.count($aShortDescr).'</strong></a></li>'
                . '<li><a href="#tblshortkeywords" class="tile">zu kurze Keywords<br>(kleiner '.$iMinKeywordsLength.' Zeichen):<br><strong>'.count($aShortKeywords).'</strong></a></li>'
                 */
                . '</ul>'
                . '<div style="clear: both;"></div>'
                ;
        
            /*
            if (count($aShortTitles)) {
                    $aTableT = array();
                    foreach ($aShortTitles as $aRow) {
                        $aTableT[] = $aRow;
                    }
                    $sReturn.= '<h3 id="tblshorttitle">' . $this->lB('warnings.tableShortTitles') . '</h3>'
                            .$this->_getHtmlTable($aTableT, "db-pages.");
            }
            if (count($aShortDescr)) {
                    $aTableD = array();
                    foreach ($aShortDescr as $aRow) {
                        $aTableD[] = $aRow;
                    }
                    $sReturn.= '<h3 id="tblshortdescription">' . $this->lB('warnings.tableShortDescription') . '</h3>'
                            .$this->_getHtmlTable($aTableD, "db-pages.");
            }
            if (count($aShortKeywords)) {
                    // echo '<pre>'.print_r($aShortKeywords,1).'</pre>';
                    $aTableK = array();
                    foreach ($aShortKeywords as $aRow) {
                        $aTableK[] = $aRow;
                    }
                    $sReturn.= '<h3 id="tblshortkeywords">' . $this->lB('warnings.tableShortKeywords') . '</h3>'
                            .$this->_getHtmlTable($aTableK, "db-pages.");
            }

            $sReturn.='<script>$(document).ready( function () {$(\'.datatable\').DataTable();} );</script>';
             */
        }

        return $sReturn;
    }
    
    /**
     * page cotent :: check a single url
     * @return string
     */
    private function _getContentcheckurl() {
        $sReturn='';
        $sReturn.=$this->_getNavi2($this->_getProfiles()).'<br>';
        $sQuery = $this->_getRequestParam('query');
        
        $sReturn.= '<p>' . $this->lB('ressources.searchurl-hint') . '</p>'
                .'<form action="" method="get" class="pure-form">'
                . '<input type="hidden" name="page" value="checkurl">'
                . '<input type="hidden" name="siteid" value="' . $this->_sTab . '">'
                . '<label>' . $this->lB('ressources.searchurl') . '</label>'
                . '<input type="text" name="query" value="' . $sQuery . '" required="required" size="80" placeholder="https://www...">'
                . ' '
                // . $sSelect
                . '<button class="pure-button button-success">' . $this->_getIcon('button.search') . $this->lB('button.search') . '</button>'
                . '</form><br><br>';
        
        if ($sQuery){
            $oRessources=new ressources($this->_sTab);
            $oRenderer=new ressourcesrenderer($this->_sTab);
            
            // $aData=$oRessources->getRessources('*', array('url'=>$sQuery), array('url'=>'ASC'));
            $aData=$oRessources->getRessourceDetailsByUrl($sQuery);
            
            if ($aData && count($aData)){
                $sReturn.='<h3>exact results '.count($aData).' </h3>'
                        . $this->lB('ressources.itemstotal')
                        . ': <strong>' . count($aData) . '</strong><br><br>'
                        ;
                foreach($aData as $aItem){
                    $sReturn.=$oRenderer->renderRessourceItemAsLine($aItem, true).'<br>';
                }
            } else {
                
                // search again ... but use "like" now
                $aDataLazy=$oRessources->getRessourceDetailsByUrl($sQuery, true);
                if ($aDataLazy && count($aDataLazy)){
                    $sReturn.='<h3>lazy results</h3>'
                        . $this->lB('ressources.itemstotal')
                        . ': <strong>' . count($aDataLazy) . '</strong><br><br>'
                        ;
                    foreach($aDataLazy as $aItem){
                        $sReturn.=$oRenderer->renderRessourceItemAsLine($aItem, true).'<br>';
                    }
                } else {
                    $sReturn.='<p>'.$this->lB('ressources.itemsnone').'</p>';                    
                }
            }
            

        }
        return $sReturn;
    }
    
    private function _getContentressourcedetail(){
        $sReturn='';
        $sReturn.=$this->_getNavi2($this->_getProfiles()).'<br>';
        $iRessourceId = (int)$this->_getRequestParam('id');
        
        $oRenderer=new ressourcesrenderer($this->_sTab);

        // $aData=$oRessources->getRessources('*', array('url'=>$sQuery), array('url'=>'ASC'));
        $oRessources=new ressources($this->_sTab);
        $aData=$oRessources->getRessourceDetails($iRessourceId);

        // echo '<pre>'.print_r($aData, 1).'</pre>' . count($aData);
        if (count($aData)){
            foreach($aData as $aItem){
                $sReturn.=$oRenderer->renderRessourceItemFull($aItem);
                /*
                if ((int)$aItem['http_code']===200 && strpos($aItem['content_type'], 'html')>0){
                    $oHtml=new analyzerHtml();
                    $oHtml->fetchUrl($aItem['url']);
                    $sReturn.='<h3>Live response of html analyzer</h3>'
                            . '<pre>'.print_r($oHtml->getReport(), 1).'</pre>';
                } else {
                    // $sReturn.='skip live parsing<br>'.$aItem['http_code'] . ' - ' . $aItem['content_type'] . ' - ' . strpos($aItem['content_type'], 'html').'<br>';
                }
                 * 
                 */
            }
        } else {
            $sReturn.= '<p>' . $this->lB('ressources.searchressourceid-hint') . '</p>'
                    
                    . '<a href="?page=warnings" class="pure-menu-linkwarnings"'
                        . ' title="' . $this->lB('nav.warnings.hint') . '"'
                        . '><i class="'.$this->_aIcons['menu']['warnings'].'"></i> ' 
                        . $this->lB('nav.warnings.label') 
                        .'</a>'
                    .' | '
                    . '<a href="?page=ressources" class="pure-menu-linkressources"'
                        . ' title="' . $this->lB('nav.ressources.hint') . '"'
                        . '><i class="'.$this->_aIcons['menu']['ressources'].'"></i> ' 
                        . $this->lB('nav.ressources.label') 
                        .'</a>'
                    .'<br><br><hr><br>'

                    .'<form action="" method="get" class="pure-form">'
                    . '<input type="hidden" name="page" value="ressourcedetail">'
                    . '<input type="hidden" name="siteid" value="' . $this->_sTab . '">'
                    // . '<label>' . $this->lB('searches.url') . '</label> '
                    . '<label>' . $this->lB('ressources.searchressourceid') . '</label>'
                    . '<input type="text" name="id" value="' . $iRessourceId . '" required="required" size="5" placeholder="ID">'
                    . ' '
                    // . $sSelect
                    . '<button class="pure-button button-success">' . $this->_getIcon('button.search') . $this->lB('button.search') . '</button>'
                    . '</form><br><br>';
            if ($iRessourceId){
                $sReturn.='<p>'.$this->lB('ressources.itemsnone').'</p>';
            }
        }
        return $sReturn;
    }
    
    /**
     * page cotent :: about
     */
    private function _getContentabout() {
        $sReturn = '';
        
        $sReturn.='<h3>' . $this->aAbout['product'] . ' ' . $this->aAbout['version'] . '</h3>'
                . '<p>' . $this->lB('about.info') . '</p>'
                . $this->_getSimpleHtmlTable(
                        array(
                            array($this->lB('about.url.project'), '<a href="' . $this->aAbout['urlHome'] . '">' . $this->aAbout['urlHome'] . '</a>'),
                            array($this->lB('about.url.docs'), '<a href="' . $this->aAbout['urlDocs'] . '">' . $this->aAbout['urlDocs'] . '</a>'),
                        )
                )
                . '<h3>' . $this->lB('about.thanks') . '</h3>'
                . '<p>' . $this->lB('about.thanks-text') . '</p>'
                . $this->_getSimpleHtmlTable(
                        array(
                            array($this->lB('about.thanks.datatables'), '<a href="https://datatables.net/">https://datatables.net/</a>'),
                            array($this->lB('about.thanks.fontawesome'), '<a href="http://fontawesome.io/">http://fontawesome.io/</a>'),
                            array($this->lB('about.thanks.jquery'), '<a href="http://jquery.com/">http://jquery.com/</a>'),
                            array($this->lB('about.thanks.medoo'), '<a href="https://medoo.in/">https://medoo.in/</a>'),
                            array($this->lB('about.thanks.rollingcurl'), '<a href="https://github.com/chuyskywalker/rolling-curl">https://github.com/chuyskywalker/rolling-curl</a>'),
                            array($this->lB('about.thanks.pure'), '<a href="https://purecss.io/">https://purecss.io/</a>'),
                        )
                );
        return $sReturn;
    }

    // ----------------------------------------------------------------------
    // OVERLAY CONTENT
    // ----------------------------------------------------------------------

    private function _getRequestParam($sParam) {
        return (array_key_exists($sParam, $_GET) && $_GET[$sParam]) ? $_GET[$sParam] : false;
    }

    /**
     * wrapper function: get page content as html
     * @return string
     */
    public function getOverlayContent() {
        if (!$this->_checkAuth()) {
            // TODO: go to login form
            // return $this->lB('nav.login.access-denied');
            return $this->_getLoginForm();
        }
        $sAction = $this->_getRequestParam('action');
        $sMethod = "_getOverlayContent" . $sAction;
        if (method_exists($this, $sMethod)) {
            return call_user_func(__CLASS__ . '::' . $sMethod, $this);
        }
        return 'unknown method: ' . __CLASS__ . '::' . $sMethod;
    }

    /**
     * overlay: view a search index item
     * @return string
     */
    private function _getOverlayContentviewindexitem() {
        $sReturn = '<h1>' . $this->lB('overlay.viewIndexItem') . '</h1>';
        $sId = $this->_getRequestParam('id');
        if (!$sId) {
            return $sReturn;
        }
        $aItem = $this->oDB->select(
                'pages', '*', array(
            'AND' => array(
                'id' => $sId,
            )
                )
        );
        if (count($aItem)) {
            $aTable = array();
            foreach ($aItem[0] as $sKey => $sVal) {
                $aTable[] = array(
                    $sKey,
                    $this->_prettifyString($sVal)
                );
            }
            return $sReturn . $this->_getSimpleHtmlTable($aTable)
                    . '<br>'
                    . $this->_getButton(array(
                        'href' => './?page=status',
                        'target' => '_top',
                        'class' => 'button-secondary',
                        'label' => 'button.close'
                    ))
                    . ' '
                    . $this->_getButton(array(
                        'href' => 'overlay.php?action=updateindexitem&url=' . $aItem[0]['url'] . '&siteid=' . $aItem[0]['siteid'],
                        'class' => 'button-success',
                        'label' => 'button.reindex'
                    ))
                    . ' '
                    . $this->_getButton(array(
                        'href' => 'overlay.php?action=deleteindexitem&id=' . $sId . '&siteid=' . $aItem[0]['siteid'],
                        'class' => 'button-error',
                        'label' => 'button.delete'
                    ))
            ;
        }
        return $sReturn;
    }

    /**
     * overlay: delete a search index item
     * @return string
     */
    private function _getOverlayContentdeleteindexitem() {
        $sReturn = '<h1>' . $this->lB('overlay.deleteIndexItem') . '</h1>';
        $sSiteId = $this->_getRequestParam('siteid');
        $sId = $this->_getRequestParam('id');

        $sReturn.='siteid=' . $sSiteId . ' id=' . $sId . '<br>';
        $o = new crawler($sSiteId);
        $sReturn.=$o->deleteFromIndex($sId);
        $sReturn.=$this->_getButton(array(
            'href' => './?page=status',
            'class' => 'button-secondary',
            'target' => '_top',
            'label' => 'button.close'
        ));
        return $sReturn;
    }

    /**
     * overlay: update a single url in search index
     * @return string
     */
    private function _getOverlayContentupdateindexitem() {
        $sReturn = '<h1>' . $this->lB('overlay.updateIndexItem') . '</h1>';
        $sSiteId = $this->_getRequestParam('siteid');
        $sUrl = $this->_getRequestParam('url');
        $sReturn.='siteid=' . $sSiteId . ' url=' . $sUrl . '<br>';
        ob_start();
        $o = new crawler($sSiteId);
        $o->updateSingleUrl($sUrl);
        $sReturn.='<pre>' . ob_get_contents() . '</pre>';
        ob_end_clean();

        $sReturn.=$this->_getButton(array(
            'href' => './?page=status',
            'class' => 'button-secondary',
            'target' => '_top',
            'label' => 'button.close'
        ));
        return $sReturn;
    }

    private function _getOverlayContentcrawl() {
        $sReturn = '<h1>' . $this->lB('overlay.crawl') . '</h1>';
        $sSiteId = $this->_getRequestParam('siteid');
        $sReturn.='siteid=' . $sSiteId . '<br>';
        ob_start();
        // echo "..."; ob_flush();flush();
        $o = new crawler($sSiteId);
        $o->run();
        $sReturn.='<pre>' . ob_get_contents() . '</pre>';
        ob_end_clean();

        $sReturn.=$this->_getButton(array(
            'href' => './?page=status',
            'class' => 'button-secondary',
            'target' => '_top',
            'label' => 'button.close'
        ));
        return $sReturn;
    }

    private function _getOverlayContentsearch() {
        $sSiteId = $this->_getRequestParam('siteid');
        $sQuery = $this->_getRequestParam('query');
        $sSubdir = $this->_getRequestParam('subdir');
        $o = new ahsearch($sSiteId);
        $aResult = $o->search($sQuery, array('subdir'=>$sSubdir));
        
        $sSelect='';
        $aCat=$o->getSearchcategories();
        if ($aCat){
            foreach ($aCat as $sLabel=>$sUrl){
                $sSelect.='<option value="'.$sUrl.'" '.($sSubdir==$sUrl?'selected="selected"':'').' >'.$sLabel.'</option>';
            }
            $sSelect=' <select name="subdir" class="form-control">'.$sSelect.'</select> ';
        }

        $sForm = '<form action="" method="get" class="pure-form">'
                . '<input type="hidden" name="action" value="search">'
                . '<input type="hidden" name="siteid" value="' . $sSiteId . '">'
                // . '<input type="hidden" name="subdir" value="' . $sSubdir . '">'
                . '<label>' . $this->lB('searches.query') . '</label> '
                . '<input type="text" name="query" value="' . $sQuery . '" required="required">'
                . ' '
                . $sSelect
                . '<button class="pure-button button-success">' . $this->_getIcon('button.search') . $o->lF('btn.search.label') . '</button>'
                . '</form>';

        $iResults = $o->getCountOfSearchresults($aResult);
        $sReturn = '<h1>' . $this->lB('overlay.search') . '</h1>'
                . $sForm
                . ($sQuery ? '<p>' . $this->lB('searches.results') . ': ' . $iResults . '<p>' : '');

        $aTable = array();

        $iCounter = 0;
        $iMaxRanking = false;

        if ($sQuery && $iResults) {
            foreach ($aResult as $iRanking => $aDataItems) {
                if (!$iMaxRanking) {
                    $iMaxRanking = $iRanking;
                }
                $aRow = array();
                foreach ($aDataItems as $aItem) {
                    // unset($aItem['content']);
                    // echo '<pre>'.print_r($aItem, 1); die();
                    $iCounter ++;
                    $sResult = '';
                    foreach ($aItem['results'] as $sWord => $aMatchTypes) {
                        $sResult.='<strong>' . $sWord . '</strong><br>';
                        foreach ($aMatchTypes as $sType => $aHits) {
                            $sMatches = '';
                            foreach ($aHits as $sWhere => $iHits) {
                                if ($iHits) {
                                    $sMatches.='...... ' . $sWhere . ': ' . $iHits . '<br>';
                                }
                            }
                            if ($sMatches) {
                                $sResult.='.. ' . $sType . '<br>' . $sMatches;
                            }
                        }
                    }
                    $aTable[] = array(
                        'search.#' => $iCounter,
                        'search.summary' => '<strong><a href="' . $aItem['url'] . '" target="_blank">' . $aItem['title'] . '</a></strong><br>'
                        . 'description: <em>' . $aItem['description'] . '</em><br>'
                        . 'keywords: <em>' . $aItem['keywords'] . '</em><br>'
                        . 'content: <em>' . $this->_prettifyString($aItem['content'], 200) . '</em><br>'
                        ,
                        'search.ranking' => '<a href="#" class="hoverinfos">' . $iRanking . '<span>' . $sResult . '<!-- <pre>' . print_r($aItem['results'], 1) . '</pre>--></span></a>',
                    );
                }
            }
        }
        $sReturn.=$this->_getHtmlTable($aTable)
                . (($iResults > 3) ? '<br>' . $sForm : '')
                . '<br>' . $this->_getButton(array(
                    'href' => './?page=searches',
                    'class' => 'button-secondary',
                    'target' => '_top',
                    'label' => 'button.close'
        ));

        return $sReturn;
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
                            ?'<a href="?action=ressourcedetail&id='.$aRow[$bLinkRessource].'&siteid='.$_GET['siteid'].'">'.$aRow['url'].'</a>'
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
            /*
            $aTableFilter[]=array('<strong>'.$this->lB('ressources.itemstotal').'</strong>', '' ,'<strong>'.count($aRessourcelist).'</strong>');
            foreach ($aFilter as $sKey){
                $sRessourcelabel=(array_key_exists($sKey, $this->_aIcons['cols']) ? '<i class="'.$this->_aIcons['cols'][$sKey].'"></i> ' : '') . $sKey;
                $aTableFilter[]=array('<strong>'.$sRessourcelabel.'</strong>', '', '');
                foreach ($aCounter[$sKey] as $sCounter=>$iValue){
                    $aTableFilter[]=array(
                        '', 
                        (count($aCounter[$sKey])>1
                            ? '<a href="?'.$sSelfUrl.'&amp;filteritem[]='.$sKey.'&amp;filtervalue[]='.$sCounter.'">'.$sCounter.'</a>'
                            : $sCounter
                        )
                        , 
                        $iValue
                    );
                }
            }
             * 
             */
        } else {
            $sReturn.=' :-/ ';
        }
        $sReturn.=$this->_getHtmlTable($aTable, "db-ressources.");
        return $sReturn;
    }

        
    private function _getOverlayContentressourcedetail() {
        $sSiteId = $this->_getRequestParam('siteid');
        $sId = $this->_getRequestParam('id');
        $aRessource = $this->oDB->select(
                'ressources', 
                '*', 
                array(
                    'AND' => array(
                        'siteid' => $sSiteId,
                        'id' => $sId,
                    ),
                )
        );
        $aFrom = $this->oDB->select(
                'ressources', 
                array(
                    '[>]ressources_rel' => array('id'=>'id_ressource')
                ),
                '*', 
                array(
                    'AND' => array(
                        'ressources_rel.siteid' => $sSiteId,
                        'ressources_rel.id_ressource_to' => $sId,
                    ),
                )
        );
        $aTo = $this->oDB->select(
                'ressources', 
                array(
                    '[>]ressources_rel' => array('id'=>'id_ressource_to')
                ),
                '*', 
                array(
                    'AND' => array(
                        'ressources_rel.siteid' => $sSiteId,
                        'ressources_rel.id_ressource' => $sId,
                    ),
                )
        );
        // echo $this->oDB->last().'<br>';
        $sReturn='';
        
        $sReturn.='<h1>'.$aRessource[0]['url'].'</h1>'
                .'<table>'
                . '<tbody>'
                . '<tr>'
                    . '<td valign="top">'
                        .'FROM: '
                        . $this->_getRessourceSummary($aFrom, 'id_ressource')
                        // . '<pre>'.print_r($aFrom, 1).'</pre>'
                    . '</td>'
                
                    . '<td valign="top">'
                        . '&gt;'
                    . '</td>'
                
                    . '<td valign="top">'
                        . $this->_getRessourceSummary($aRessource)
                        // .'<pre>'.print_r($aRessource, 1).'</pre>'
                    . '</td>'
                
                    . '<td valign="top">'
                        . '&gt;'
                    . '</td>'
                
                    . '<td valign="top">'
                        .'TO: '
                        . $this->_getRessourceSummary($aTo, 'id_ressource_to')
                        // .'To: <pre>'.print_r($aTo, 1).'</pre>'
                    . '</td>'
                . '</tr>'
                . '</tbody>'
                . '</table>'
                // .'<pre>'.print_r($aRessource, 1).'</pre>'
                // .'FROM: <pre>'.print_r($aFrom, 1).'</pre>'
                // .'To: <pre>'.print_r($aTo, 1).'</pre>'
                ;
        return $sReturn;
    }
    // ----------------------------------------------------------------------
}
