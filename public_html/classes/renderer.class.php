<?php

require_once 'ressources.class.php';
require_once 'httpheader.class.php';
require_once 'htmlelements.class.php';

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
 * ressources-renderer
 *
 * @author hahn
 * 
 * 2024-09-13  v0.167  php8 only; add typed variables; use short array syntax
 * 2024-10-27  v0.172  fix site id for current object
 */
class ressourcesrenderer extends crawler_base
{

    /**
     * crawler base object
     * @var object 
     */
    protected object $oCrawler;

    /**
     * ressource
     * @var object
     */
    protected object $oRes;


    /**
     * icons
     * @var array 
     */
    private array $_aIcons = [
        /*
        'url' => 'fa fa-link',
        'title' => 'fa fa-chevron-right',
        'description' => 'fa fa-chevron-right',
        'errorcount' => 'fa fa-bolt',
        'keywords' => 'fa fa-key',
        'lasterror' => 'fa fa-bolt',
        'actions' => 'fa fa-check',
        'searchset' => 'fa fa-cube',
        'query' => 'fa fa-search',
        'results' => 'fa fa-bullseye',
        'count' => 'fa fa-thumbs-o-up',
        'host' => 'fa fa-laptop',
        'ua' => 'fa fa-paw',
        'referrer' => 'fa fa-link',
        'id' => 'fa fa-hashtag',
        'ts' => 'fa fa-calendar',
        'ressourcetype' => 'fa fa-cubes',
        'type' => 'fa fa-cloud',
        'content_type' => 'fa fa-file-code-o',
        'http_code' => 'fa fa-retweet',
        'size_download' => 'fa fa-download',
        '_size_download' => 'fa fa-download',
        '_meta_total_time' => 'fa fa-clock-o',
         * 
         */

        // ressourcetype
        'audio' => 'fa-solid fa-volume-high',
        'css' => 'fa-solid fa-eye-dropper',
        'image' => 'fa-regular fa-file-image',
        'link' => 'fa-solid fa-link',
        'media' => 'fa-solid fa-photo-video',
        'page' => 'fa-regular fa-sticky-note',
        // 'redirect'=>'fa-solid fa-angle-double-right',
        'script' => 'fa-regular fa-file-code',

        // type
        'external' => 'fa-solid fa-globe-americas',
        'internal' => 'fa-solid fa-thumbtack',
        // content_type/ MIME
        //
        'link-to-url' => 'fa-solid fa-external-link-alt',
        'blacklist' => 'fa-regular fa-eye-slash',

        // http_code
        'http-code-' => 'fa-regular fa-hourglass',
        'http-code-0xx' => 'fa-solid fa-plug',
        'http-code-2xx' => 'fa-regular fa-thumbs-up',
        'http-code-3xx' => 'fa-solid fa-share',
        'http-code-4xx' => 'fa-solid fa-bolt',
        'http-code-5xx' => 'fa-solid fa-spinner',
        'http-code-9xx' => 'fa-solid fa-bolt',

        'switch-search-res' => 'fa-solid fa-retweet',

        /*
        'ressources.showtable' => 'fa fa-table',
        'ressources.showreport' => 'fa-regular fa-file',
        'ressources.ignorelimit' => 'fa fa-unlock',

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
         * 
         */

        'ico.found' => 'fa-solid fa-check',
        'ico.miss' => 'fa-solid fa-ban',
        'ico.close' => 'fa-solid fa-xmark',

        // http response header
        // 'ico.unknown' => 'fa-solid fa-question-circle',
        'ico.http' => 'fa-solid fa-check',
        'ico.non-standard' => 'fa-regular fa-check-circle',
        'ico.security' => 'fa-solid fa-lock',
        'ico.obsolete' => 'fa-solid fa-trash-alt',
        'ico.deprecated' => 'fa-solid fa-thumbs-down',
        'ico.unwanted' => 'fa-solid fa-exclamation-triangle',
        'ico.badvalue' => 'fa-solid fa-exclamation-triangle',

        'ico.tag' => 'fa-solid fa-tag',

        'ico.experimental' => 'fa-solid fa-flask-vial',
        'ico.cache' => 'fa-solid fa-gauge-high',
        'ico.compression' => 'fa-solid fa-file-zipper',
        'ico.feature' => 'fa-regular fa-sun',
        'ico.unknown' => 'fa-solid fa-question',
        'ico.httpstatus' => 'fa-regular fa-lightbulb',
        'ico.httpversion' => 'fa-regular fa-lightbulb',

        'ico.error' => 'fa-solid fa-bolt',
        'ico.ok' => 'fa-solid fa-check',
        'ico.warn' => 'fa-solid fa-exclamation-triangle',
        'ico.warning' => 'fa-solid fa-exclamation-triangle',

        'ico.bookmarklet' => 'fa-solid fa-expand-arrows-alt',
        'ico.redirect' => 'fa-solid fa-share',
        'ico.filter' => 'fa-solid fa-filter',

        'ico.toggle-off' => 'fa-solid fa-toggle-off',
        'ico.toggle-on' => 'fa-solid fa-toggle-on',

        'ico.reindex' => 'fa-solid fa-redo',
    ];
    public $oHtml = false;

    // ----------------------------------------------------------------------
    // construct
    // ----------------------------------------------------------------------

    /**
     * Constructor
     * @param integer $iSiteId  id of the web project
     */
    public function __construct(int|string $iSiteId = 0)
    {
        $this->oHtml = new htmlelements();
        $this->setLangBackend();

        // override fotawsome icons
        // $this->_aIcons = include 'icons_lineawesome.php';
        $this->_aIcons = include 'icons_tabler.php';

        if ($iSiteId) {
            $this->_initRessource($iSiteId);
        }
    }

    /**
     * Get all icons of the renderer object +as key value hash
     * @return array
     */
    public function getIcons(): array
    {
        $aReturn = [];
        foreach ($this->_aIcons as $sKey => $sClass) {
            $aReturn['rendererer --> ' . $sKey] = $sClass;
        }
        return $aReturn;
    }

    // ----------------------------------------------------------------------
    // private functions
    // ----------------------------------------------------------------------

    /**
     * Init resource class and set the site id
     * 
     * @param integer $iSiteId  id of the web project
     * @return boolean
     */
    private function _initRessource(int $iSiteId = 0): bool
    {
        if (!isset($this->oCrawler)) {
            $this->oCrawler = new crawler();
        }
        if (!isset($this->oRes)) {
            $this->oRes = new ressources();
        }
        if ($iSiteId) {
            $this->oRes->setSiteId($iSiteId);
            $this->oCrawler->setSiteId($iSiteId);
            $this->setSiteId($iSiteId);
        }
        return true;
    }

    /**
     * Get html code for an icon or show a placeholder if the icon key does 
     * not exist
     * 
     * @param string   $sKey             
     * @param boolean  $bEmptyIfMissing
     * @param string   $sClass
     * @return string
     */
    public function _getIcon(string $sKey, bool $bEmptyIfMissing = false, string $sClass = ''): string
    {
        if (array_key_exists($sKey, $this->_aIcons)) {
            return '<i class="' . $this->_aIcons[$sKey] . ($sClass ? " $sClass" : '') . '"></i> ';
        }
        return $bEmptyIfMissing ? '' : "<span title=\"missing icon [$sKey]\">[$sKey]</span>";
    }

    /**
     * render a ressource value and add css class with given array key and
     * the array
     * 
     * @param string  $sKey    array key to render
     * @param array   $aArray  array
     * @return string
     */
    public function renderArrayValue(string $sKey, array $aArray): bool|string
    {
        if (array_key_exists($sKey, $aArray)) {
            return $this->renderValue($sKey, $aArray[$sKey]);
        }
        return false;
    }

    /**
     * Get html code for a table to show http responseheader
     * 
     * @param array $aHeaderWithChecks  array of header vars; user the return of [httpheader]->checkHeaders();
     *     [expires] => Array
     *        (
     *            [var] => expires
     *            [value] => Wed, 05 Sep 2018 19:24:03 GMT
     *            [found] => http
     *            [bad] => 
     *            [obsolete] => 
     *            [deprecated] => 
     *        )
     *
     * @return string
     */
    public function renderHttpheaderAsTable(array $aHeaderWithChecks): string
    {
        if (!$aHeaderWithChecks || !is_array($aHeaderWithChecks) || !count($aHeaderWithChecks)) {
            return '';
        }
        $sReturn = '';
        $aTags=[];
        foreach ($aHeaderWithChecks as $aEntry) {
            $sIcon = $this->_getIcon('ico.' . $aEntry['found'], false, 'ico-' . $aEntry['found']);
            // foreach (['unwanted', 'badvalue', /*'unknown',*/ 'obsolete'] as $sMyTag) {
            //     $sIcon .= (array_search($sMyTag, $aEntry['tags']) !== false ? $this->_getIcon('ico.' . $sMyTag, false, 'ico-' . $sMyTag) : '');
            // }

            $sComment = '';
            if (count($aEntry['tags'])) {
                foreach ($aEntry['tags'] as $sTag) {
                    if ($sTag !== 'http'){
                        $sComment .= 
                            // $this->_getIcon('ico.tag')
                            $this->_getIcon('ico.'.$sTag)
                            . $this->lB('httpheader.tag.' . $sTag) . ' '
                            ;
                        $aTags[$this->lB('httpheader.tag.' . $sTag)]=$sTag;
                    }
                }
            }
            $sReturn .= '<tr title="' . htmlentities($aEntry['var'] . ': ' . $aEntry['value']) . '" '
                . 'class="' . implode(' ', array_values($aEntry['tags'])) . '"'
                . '>'
                . '<td>' . (strstr($aEntry['var'], '_') ? '' : htmlentities($aEntry['var'])) . '</td>'
                . '<td style="max-width: 30em; overflow: hidden;">' . htmlentities($aEntry['value']) . '</td>'
                // . '<td>' . $sIcon . '</td>'
                . '<td>' . $sComment . '</td>'
                // . '<td>'. print_r(array_values($aEntry['tags']),1) .'</td>'
                . '</tr>'
            ;
        }
        
        ksort($aTags,SORT_FLAG_CASE + SORT_NATURAL);
        $sFilterbar='';
        foreach($aTags as $sLabel => $sKey){
            $sFilterbar.='<a href="#" class="pure-button button-filter" data-tagname="'.$sKey.'">'
                // .$this->_getIcon('ico.tag') 
                .$this->_getIcon('ico.'.$sKey) 
                . $sLabel
                .' <span class="close">'.$this->_getIcon('ico.close').'</span>'
                .'</a> '
                ;
        }
        if($sFilterbar){
            $sFilterbar=''
                .'<div class="filterbarHttpHeader">'
                    .$sFilterbar
                .'</div><br>'
                ;
        }

        return ''
            // . '<pre>'.print_r($aTags, 1).'</pre>'
            . $sFilterbar
            . '<table class="pure-table pure-table-horizontal" id="httpheader-table">'
            . '<tr>'
            . '<th>' . $this->lB('httpheader.thvariable') . '</th>'
            . '<th>' . $this->lB('httpheader.thvalue') . '</th>'
            // . '<th></th>'
            . '<th>' . $this->lB('httpheader.thcomment') . '</th>'
            . '</tr>'
            . $sReturn
            . '</table>';
    }

    /**
     * Get css classes for http status; it returns 2 classnames with
     * 100 block grouping and the exact code
     * 
     * @param integer $iHttpStatus  http status
     * @param boolean $bOnlyFirst   optional flag: use grouped code "http-code-Nxx" without full statuscode; default: false (=show http code as number)
     * @return string
     */
    protected function _getCssClassesForHttpstatus(int $iHttpStatus, bool $bOnlyFirst = false): string
    {
        return 'http-code-' . floor((int) $iHttpStatus / 100) . 'xx' .
            (!$bOnlyFirst ? ' http-code-' . (int) $iHttpStatus : '');
    }

    /**
     * Get html code for a ressource value and add css class
     * 
     * @param string  $sType  string
     * @param string  $value  value
     * @return string
     */
    public function renderValue(string $sType, string $value): string
    {

        $sIcon = $this->_getIcon($value, true);
        switch ($sType) {

            case 'http_code':
                if (!$sIcon) {
                    $sIcon = $this->_getIcon('http-code-' . floor((int) $value / 100) . 'xx', true);
                }
                if (!$sIcon) {
                    $sIcon = $this->_getIcon('http-code-' . $value, true);
                }
                // $shttpStatusLabel=$this->lB('httpcode.'.$iHttp_code.'.label', 'httpcode.???.label');
                $shttpStatusDescr = $value . ': ' . $this->lB('httpcode.' . $value . '.descr', 'httpcode.???.descr')
                    . ($this->lB('httpcode.' . $value . '.todo') ? "&#13;&#13;" . $this->lB('httpcode.todo') . ":&#13;" . $this->lB('httpcode.' . $value . '.todo') : '');
                $sReturn = '<span class="http-code ' . $this->_getCssClassesForHttpstatus((int)$value) . '" '
                    . 'title="' . $shttpStatusDescr . '"'
                    . '>' . $sIcon . $value . '</span>';
                break;

            case 'ressourcetype':
            case 'type':
                $sReturn = '<span class="' . $sType . ' ' . $sType . '-' . $value . '">' . $sIcon . $value . '</span>';
                break;
            case 'url':
                $sReturn = $sIcon . htmlentities($value);
                break;

            default:
                $sReturn = $sIcon . $value;
                break;
        }
        return $sReturn;
    }

    /**
     * Get html code for an value from the array by the given key
     * It returns false if not found
     * 
     * @param string $sKey
     * @param array  $aArray
     * @return string
     */
    private function _renderArrayValue(string $sKey, array $aArray): bool|string
    {
        if (array_key_exists($sKey, $aArray)) {
            return $this->renderValue($sKey, $aArray[$sKey]);
        }
        return false;
    }

    /**
     * Get items from ressource item array as html table
     * 
     * @param array  $aItem       array of a single ressource item
     * @param array  $aArraykeys  optional: array keys to render (default: all)
     * @return string
     */
    private function _renderItemAsTable(array $aItem, array $aArraykeys = []): string
    {
        if (!$aArraykeys) {
            $aArraykeys = array_keys($aItem);
        }
        $sReturn = '';
        foreach ($aArraykeys as $sKey) {
            if (array_key_exists($sKey, $aItem)) {
                $sLangKey=strstr($sKey, '.') ? $sKey : 'db-ressources.' . $sKey;
                $sReturn .= '<tr>'
                    . '<td>' . $this->_getIcon($sKey, true) . ' ' . $this->lB($sLangKey) . '</td>'
                    . '<td>' . $this->renderValue($sKey, $aItem[$sKey]) . '</td>'
                    . '</tr>';
            }
        }
        if ($sReturn) {
            return "<table class=\"pure-table pure-table-horizontal\">$sReturn</table>";
        }
        return '';
    }

    /**
     * Get human readable size by value in byte
     * 
     * @param integer|float  $iValue
     * @return string
     */
    public function hrSize(int|float $iValue): string
    {
        $iOut = $iValue;
        foreach ([$this->lB('hr-size-byte'), $this->lB('hr-size-kb'), $this->lB('hr-size-MB'), $this->lB('hr-size-GB'), $this->lB('hr-size-TB'), $this->lB('hr-size-PB'), ] as $sSuffix) {
            if ($iOut < 3000) {
                return round($iOut, 2) . ' ' . $sSuffix;
            }
            $iOut = $iOut / 1024;
        }

        // if foor loop didn't return anything ... finally return with added ??
        return "$iValue (??)";
    }

    /**
     * Get human readable age by value in unix ts
     * 
     * @param integer  $iUnixTs  unix timestamp
     * @return string
     */
    public function hrAge(int $iUnixTs): string
    {
        if ($iUnixTs < 1) {
            return $this->lB('hr-time-never');
        }
        return $this->hrTimeInSec(date("U") - $iUnixTs);
    }

    /**
     * Get human readable time by value in seconds
     * 
     * @param integer  $iValue  value in seconds
     * @return string
     */
    public function hrTimeInSec(int $iValue): string
    {
        $iOut = $iValue;
        if ($iOut < 180) {
            return $iOut . ' ' . $this->lB('hr-time-sec');
        }
        $iOut = $iOut / 60;
        if ($iOut < 180) {
            return (int) $iOut . ' ' . $this->lB('hr-time-min');
        }

        $iOut = $iOut / 60;
        if ($iOut < 72) {
            return (int) $iOut . ' ' . $this->lB('hr-time-h');
        }
        $iOut = $iOut / 24;
        if ($iOut < 366) {
            return (int) $iOut . ' ' . $this->lB('hr-time-d');
        }
        $iOut = $iOut / 365;
        return (int) $iOut . ' ' . $this->lB('hr-time-y');
    }

    // ----------------------------------------------------------------------
    // public rendering functions
    // ----------------------------------------------------------------------

    /**
     * Get div for a ressource with redirects in ressource report
     * 
     * @param array    $aRessourceItem  ressource item
     * @param integer  $iLevel          level
     * @param string   $sLastUrl        Url pointing to the current ressource
     * 
     * @return string
     */
    private function _renderWithRedirects(array $aRessourceItem, int $iLevel = 1, string $sLastUrl = ''): string
    {
        $iIdRessource = $aRessourceItem['id'];
        static $aUrllist;
        if ($iLevel === 1) {
            $aUrllist = [];
        }
        $sReturn = '';
        /*
        $iIdRessource=array_key_exists('id_ressource', $aRessourceItem)
                ? $aRessourceItem['id_ressource_to']
                : $aRessourceItem['id']
                ;
        */

        if (array_key_exists($iIdRessource, $aUrllist)) {
            return $sReturn .= $this->renderMessagebox(sprintf($this->lB("linkchecker.loop-detected"), $aRessourceItem['url']), 'error');
        }
        // $oStatus = new httpstatus($aRessourceItem['http_code']);
        $bIsRedirect = ($aRessourceItem['http_code'] >= 300 && $aRessourceItem['http_code'] < 400);
        $lastProt = parse_url($sLastUrl, PHP_URL_SCHEME);
        $nowProt = parse_url($aRessourceItem['url'], PHP_URL_SCHEME);
        $sReturn .= ''
            // . ' #'.$iIdRessource.' '.$iLevel.' '
            . ($iLevel === 2 ? '<div class="redirects"><div class="redirectslabel">' . $this->lB('ressources.redirects-to') . '</div>' : '')
            . ($iLevel > 2 ? '<div class="redirects">' : '')
            . ($aRessourceItem['url'] == str_replace('http://', 'https://', $sLastUrl)
                ? $this->renderMessagebox($this->lB("linkchecker.http-to-https"), 'warning')
                : ''
            )
            . ($lastProt == 'https' && $nowProt == 'http'
                ? $this->renderMessagebox($this->lB("linkchecker.https-to-http"), 'warning')
                : ''
            )
            . $this->renderRessourceItemAsLine($aRessourceItem, true, !$bIsRedirect)
            . ($iLevel === 2 ? '</div>' : '')
            . ($iLevel > 2 ? '</div>' : '')
            // . ' ('.$aRessourceItem['http_code']
        ;
        $aUrllist[$iIdRessource] = true;
        if ($bIsRedirect) {
            // echo " scan sub elements of # $iIdRessource ...<br>";
            $aOutItem = $this->oRes->getRessourceDetailsOutgoing($iIdRessource);
            // $sReturn .= count($aOutItem) .  " sub elements ... recursion with <pre>" . print_r($aOutItem, 1) . "</pre><br>";
            if ($aOutItem && count($aOutItem)) {
                $iLevel++;
                // $sReturn .= str_repeat('&nbsp;&nbsp;&nbsp;', $iLevel++) . '&gt; ' . $this->_renderWithRedirects($aOutItem[0], $iLevel++);
                $sReturn .= '<div class="redirects">' . $this->_renderWithRedirects($aOutItem[0], $iLevel++, $aRessourceItem['url']) . '</div>';
            }
        }
        return $sReturn;
    }

    /**
     * Get div for referencing (incoming) ressources report
     * 
     * @param array   $aRessourceItem  ressource item
     * @param boolean $bReinit         flag for deleting the url list (for multiple usage of this method on a page)
     * @return string
     */
    public function _renderIncomingWithRedirects(array $aRessourceItem, bool $bReInit = false): string
    {
        $iIdRessource = $aRessourceItem['id'];
        static $aUrllist;
        if (!$aUrllist || $bReInit) {
            $aUrllist = [];
        }
        $sReturn = '';

        if (array_key_exists($iIdRessource, $aUrllist)) {
            return $sReturn . $this->renderMessagebox(sprintf($this->lB("linkchecker.loop-detected"), $aRessourceItem['url']), 'error');
        }
        $aResIn = $this->oRes->getRessourceDetailsIncoming($aRessourceItem['id']);
        $aUrllist[$iIdRessource] = true;
        if (count($aResIn)) {
            // $sReport.='|   |<br>';
            $sReturn .= '<div class="references">'
                . '<div class="referenceslabel">' . sprintf($this->lB('ressources.referenced-in'), count($aResIn)) . '</div>';
            foreach ($aResIn as $aInItem) {
                $sReturn .= ''
                    // .$aRessourceItem['url'].'<br>'.print_r($aInItem,1)
                    . ($aInItem['url'] == str_replace('https://', 'http://', $aRessourceItem['url'])
                        ? $this->renderMessagebox($this->lB("linkchecker.http-to-https"), 'warning')
                        : ''
                    )
                    . $this->renderRessourceItemAsLine($aInItem, $aInItem['type'] == 'external')
                ;
                if ($aInItem['type'] == 'external') {
                    $sReturn .= $this->_renderIncomingWithRedirects($aInItem);
                }
            }
            $sReturn .= '</div>';

        } else {
            // $sReturn.='<br>';
        }
        return $sReturn;
    }

    /**
     * Get html code to render a bookmarklet
     * 
     * @param string $sId  id for bookmarklet; one of 'details', 'httpheaderchecks', 'sslcheck'
     * @return string
     */
    public function renderBookmarklet(string $sId): string
    {
        $aItems = [
            'details' => [
                'query' => "backend/?page=checkurl&siteid=all&redirect=1&query='+encodeURI(document.location.href);"
            ],
            'httpheaderchecks' => [
                // 'query'=>"?page=httpheaderchecks&url='+encodeURI(document.location.href);"
                'query' => "?page=httpheaderchecks&urlbase64='+btoa(document.location.href);"
            ],
            'sslcheck' => [
                'query' => "?page=sslcheck&host='+(document.location.hostname)+'&port='+(document.location.port ? document.location.port : (document.location.protocol==='http:' ? 80 : (document.location.protocol==='https:' ? 443 : 0 )));"
            ],
        ];
        if (!isset($aItems[$sId])) {
            // TODO: translate text
            return "INTERNAL ERROR: this page integrated bookmarklet of non existing id [$sId]<br>";
        }
        $sBaseUrl = preg_replace('/(\/backend|\?.*)/', '', $_SERVER["REQUEST_URI"]);
        $sMyUrl = 'http'
            . ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]) ? 's' : '')
            . '://'
            . $_SERVER["HTTP_HOST"]
            . ':' . $_SERVER["SERVER_PORT"]
            . $sBaseUrl
            . $aItems[$sId]['query']
            // . (isset($_GET['lang']) ? '&lang='.$_GET['lang'] : '')
        ;
        $sMyUrl = isset($_GET['lang']) ? str_replace('?page=', '?lang=' . $_GET['lang'] . '&amp;page=', $sMyUrl) : $sMyUrl;
        return $this->lB('bookmarklet.hint') . ':<br><br>'
            . $this->oHtml->getTag('a', [
                'class' => 'pure-button',
                'href' => 'javascript:document.location.href=\'' . $sMyUrl,
                'onclick' => 'alert(\'' . $this->lB('bookmarklet.hint') . '\'); return false;',
                'title' => $this->lB('bookmarklet.hint'),
                'label' => $this->_getIcon('ico.bookmarklet') . $this->lB('bookmarklet.' . $sId . '.label'),
            ])

            . '<br><br>'
            . $this->lB('bookmarklet.' . $sId . '.posthint')
        ;
    }

    /**
     * Get html code for a context box on the side; with title in the header 
     * or without.
     * 
     * @param string  $sContent  content of the box
     * @param string  $sTitle    optional title
     * @return string
     */
    public function renderContextbox(string $sContent, string $sTitle = ''): string
    {
        return '<div class="contextbox">'
            . ($sTitle ? "<div class=\"head\">$sTitle</div>" : '')
            . "<div class=\"content\">$sContent</div>"
            . '</div>';
    }

    /**
     * Get html code opening and closing button for showing extended settings/ content
     * @param string  $sContent  content of the box
     * @param string  $sTitle    optional title
     * @return string
     */
    public function renderExtendedView(): string
    {
        return $this->oHtml->getTag('a', [
            'href' => '#',
            'id' => 'btn-extend-on',
            'class' => 'pure-button btn-extend btn-extend-on',
            'onclick' => 'toggleExtendedView(); return false',
            'label' => $this->_getIcon('ico.toggle-on') . $this->lB('button.extended-on'),
        ])
        . $this->oHtml->getTag('a', [
            'href' => '#',
            'id' => 'btn-extend-off',
            'class' => 'pure-button btn-extend btn-extend-off',
            'onclick' => 'toggleExtendedView(); return false',
            'label' => $this->_getIcon('ico.toggle-off') . $this->lB('button.extended-off'),
        ]);
    }

    /**
     * Get html code for a search index button
     * @param string  $sAction  action
     * @param string  $sWhat    what
     * @param string  $sSiteId  site id
     * @return string
     */
    public function renderIndexButton(string $sAction, string $sWhat, string $sSiteId): string
    {
        return $this->oHtml->getTag(
            'a',
            [
                'href' => "./get.php?action=$sAction-$sWhat&siteid=$sSiteId",
                'class' => 'pure-button button-secondary trigger_action_reindex',
                'target' => 'selfiframe',
                'label' => $this->_getIcon('ico.reindex') . $this->lB('button.' . $sAction),
            ]
        );
    }

    /**
     * Get html code for a box showing the status of the search index
     * It writes divs for each status that will be shown by css
     * 
     * @param string  $sAction  Name of the action; one of 'reindex'
     * @param string  $sWhat    What to index
     * @param integer $sSiteId  Site id of the web
     * @return string
     */
    public function renderIndexActions(string $sAction, string $sWhat, string $sSiteId): string
    {
        return '<div class="actions-crawler">'
            . '<div class="running">'
            . $this->renderMessagebox($this->lB('status.indexer_is_running'), 'warning')
            . '</div>'
            . '<div class="stopped">'
            . $this->renderIndexButton($sAction, $sWhat, $sSiteId)
            . '</div>'
            . '</div>';
    }

    /**
     * Get html code for a button that points to a known page (anywhere) in the 
     * menu
     * 
     * @param string  $sMenuItem         target menu id (page=...)
     * @param string  $sIcon             optional: icon to show (html code)
     * @param integer $iSiteId           optional: site id
     * @param string  $sMoreUrlParams    optional: more url params for href target
     * @return string
     */
    public function renderLink2Page(string $sMenuItem, string $sIcon = '', int|string $iSiteId = 0, string $sMoreUrlParams = ''): string
    {
        return $this->oHtml->getTag('a', [
            'class' => 'pure-button',
            'href' => '?page=' . $sMenuItem . ($iSiteId ? '&siteid=' . $iSiteId : '') . ($sMoreUrlParams ? $sMoreUrlParams : ''),
            'title' => $this->lB('nav.' . $sMenuItem . '.hint'),
            'label' => ($sIcon ? $sIcon . ' ' : '') . $this->lB('nav.' . $sMenuItem . '.label'),
        ]);
    }

    /**
     * Get html code for an infobox 
     * @param string  $sMessage  message text
     * @param string  $sType     one of ok|warning|error; default: ''
     * @return string
     */
    public function renderMessagebox(string $sMessage, string $sType = ''): string
    {
        return '<div class="message message-' . $sType . '">'
            . $this->renderShortInfo($sType)
            . $sMessage
            . '</div>';
    }

    public function renderNetworkTimer(string $sCurlheader): string
    {
        // --- timings
        // see https://ops.tips/gists/measuring-http-response-times-curl/
        //
        $iMaxLoadTime=$this->aOptions['analysis']['MaxLoadtime'];

        $aCurlHeader=json_decode($sCurlheader, 1);
        if(!$aCurlHeader){
            return '-';
        }
        // print_r($aCurlHeader);
        $iTimeMultiplicator=1/1000;
        $aTimers=[
            'namelookup' => $aCurlHeader['namelookup_time_us'] * $iTimeMultiplicator, 
            'connect' => $aCurlHeader['connect_time_us'] * $iTimeMultiplicator,
            'appconnect' => $aCurlHeader['appconnect_time_us'] * $iTimeMultiplicator, // The time, in seconds, it took from the start until the SSL/SSH/etc connect/handshake to the remote host was completed.
            'pretransfer' => $aCurlHeader['pretransfer_time_us'] * $iTimeMultiplicator, 
            'redirect' => $aCurlHeader['redirect_time_us'] * $iTimeMultiplicator, 
            'starttransfer' => $aCurlHeader['starttransfer_time_us'] * $iTimeMultiplicator, 
            'total' => $aCurlHeader['total_time_us'] * $iTimeMultiplicator, 
        ];
        $aTimers['_handshake']=$aTimers['appconnect'] - $aTimers['connect'] - $aTimers['namelookup'];
        $aTimers['_onserver']=$aTimers['starttransfer'] - $aTimers['pretransfer'];
        $aTimers['_transfer']=$aTimers['total'] - $aTimers['starttransfer'];

        // print_r($aCurlHeader);
        $sIntro=''
            .($aCurlHeader['effective_method'] ? '<strong>'.$aCurlHeader['effective_method'].'</strong>' : '?').' '
            .($aCurlHeader['url'] ?? '?').' '
            .($aCurlHeader['http_code'] ?  $this->renderValue('http_code', $aCurlHeader['http_code']) : '').' '
            ;
        $sIntro.= $sIntro ? '<br>' : '';

        return ''
            . $sIntro
            .($aTimers['total']>$iMaxLoadTime 
                ? $this->renderMessagebox($this->lB('counter.countLongLoad.label'), 'warning')
                : ''
            )
            . $this->lB("curl.timer.total").': <strong>'.$aTimers['total'].'</strong> ms'
                .'<br>'
            . $this->lB("curl.timer.onserver").': <strong>'.$aTimers['_onserver'].'</strong> ms ('.round($aTimers['_onserver']*100/$aTimers['total']).'%)<br>'

            .'<div class="request-time">'
                // . '<pre>'.print_r($aTimers, 1).'</pre>'
                . ( $iMaxLoadTime ? '<div class="maxloadtime" style="margin-left:'.$iMaxLoadTime.'px" title="'.$iMaxLoadTime.' ms"></div>' : '' )
                .'<div class="maxloadtime2" style="width:'.$iMaxLoadTime.'px" ></div><br>'
                .'<div class="total" style="width:'.$aTimers['total'].'px" title="'.$this->lB("curl.timer.total").': '.$aTimers['total'].' ms"></div>'
                .'<br><br>'
                .'<div class="namelookup" style="width:'.$aTimers['namelookup'].'px" title="'.$this->lB("curl.timer.lookup").': '.$aTimers['namelookup'].' ms"></div>'
                .'<div class="connect" style="width:'.$aTimers['connect'].'px" title="'.$this->lB("curl.timer.connect").': '.$aTimers['connect'].' ms"></div>'
                .'<div class="handshake" style="width:'.$aTimers['_handshake'].'px" title="'.$this->lB("curl.timer.handshake").': '.$aTimers['_handshake'].' ms"></div>'
                //.'<div class="appconnect" style="width:'.$aTimers['appconnect'].'px"></div>'
                .'<br><br>'
                .'<span style="margin-left:'.$aTimers['pretransfer'].'px"></span>'
                .'<div class="onserver" style="width:'.$aTimers['_onserver'].'px" title="'.$this->lB("curl.timer.onserver").': '.$aTimers['_onserver'].' ms"></div>'
                .'<br><br>'
                .'<span style="margin-left:'.$aTimers['starttransfer'].'px"></span>'
                .'<div class="transfer" style="width:'.$aTimers['_transfer'].'px" title="'.$this->lB("curl.timer.transfer").': '.$aTimers['_transfer'].' ms"></div>'
                .'<br><br>'

            .'</div>'
            ;

    }
    /**
     * Get html code for report item with redirects and and its references
     * 
     * @param array    $iRessourceItem  array of the ressource
     * @param boolean  $bShowIncoming   optional flag: show ressources that use the current ressource? default: true (=yes)
     * @param boolean  $bShowRedirects  optional flag: show redrirects? default: true (=yes)
     * @return string
     */
    public function renderReportForRessource(array $aRessourceItem, bool $bShowIncoming = true, bool $bShowRedirects = true): string
    {
        $sReturn = '';
        $this->_initRessource();

        $sCssStatus = isset($aRessourceItem['http_code']) ? ' ' . $this->_getCssClassesForHttpstatus($aRessourceItem['http_code']) : '';

        $sReturn .= $bShowRedirects
            ? $this->_renderWithRedirects($aRessourceItem)
            : $this->renderRessourceItemAsLine($aRessourceItem, true)
        ;
        if ($bShowIncoming) {
            $sReturn .= $this->_renderIncomingWithRedirects($aRessourceItem, $bShowIncoming, $bShowRedirects);
        }
        // return $sReturn;
        return "<div class=\"divRessourceReport $sCssStatus\">$sReturn</div>";
    }

    /**
     * Get html code for infobox with a single ressource given by id
     * 
     * @param integer  $iRessourceId  id of the ressource
     * @return string
     */
    public function renderRessourceId(int $iRessourceId): string
    {
        $iId = (int) $iRessourceId;
        if (!$iId) {
            return '';
        }
        $this->_initRessource();
        $aResourceItem = $this->oRes->getRessourceDetails($iId);
        return $this->renderRessourceItemAsBox($aResourceItem);
    }

    /**
     * Extend properties of given resource item.
     * - human readable values:
     *    - _size_download - download size 
     *    - _dlspeed - download speed
     * - parse curl metadata in header and add it as value to the item
     *    - _meta_total_time
     *    - _meta_namelookup_time
     *    - _meta_connect_time
     *    - _meta_pretransfer_time
     *    - _meta_starttransfer_time
     *    - _meta_redirect_time
     * 
     * @param array  $aRessourceItem
     * @return array
     */
    private function _extendRessourceItem(array $aRessourceItem): array
    {

        if (array_key_exists('size_download', $aRessourceItem)) {
            $aRessourceItem['_size_download'] = $aRessourceItem['size_download']
                ? $this->hrSize($aRessourceItem['size_download'])
                : $this->lB('ressources.size-is-zero');
        }
        if (array_key_exists('total_time', $aRessourceItem) && $aRessourceItem['total_time']) {
            $aRessourceItem['_dlspeed'] = $this->hrSize($aRessourceItem['size_download'] / $aRessourceItem['total_time']) . '/ sec';
        }

        // add head metadata
        $aCurlHeader = json_decode($aRessourceItem['header'], 1);
        foreach (['total_time', 'namelookup_time', 'connect_time', 'pretransfer_time', 'starttransfer_time', 'redirect_time'] as $sKey) {
            if ($aCurlHeader && is_array($aCurlHeader) && array_key_exists($sKey, $aCurlHeader)) {
                $aRessourceItem['_meta_' . $sKey] = $aCurlHeader[$sKey];
            }
        }
        return $aRessourceItem;
    }

    /**
     * Get html code for infobox with a single ressource given by arraydata
     * 
     * @param array  $aRessourceItem  array of the ressource item
     * @return string
     */ 
    public function renderRessourceItemAsBox(array $aRessourceItem): string
    {
        $sReturn = '';
        if (!is_array($aRessourceItem) || !count($aRessourceItem) || !array_key_exists('ressourcetype', $aRessourceItem)) {
            return '';
        }
        $aRessourceItem = $this->_extendRessourceItem($aRessourceItem);

        $unixTS = date("U", strtotime($aRessourceItem['ts']));
        $iPageId = $this->getIdByUrl($aRessourceItem['url'], 'pages');

        $sLink2Searchindex = $aRessourceItem['isSource'] ? '?page=searchindexstatus&id=' . $iPageId . '&siteid=' . $aRessourceItem['siteid'] : false;

        $sReturn .= '<div class="divRessource">'
            . '<div class="divRessourceHead">'
            /*
            . '<span style="float: right;">'
                . '<a href="' . $aRessourceItem['url'] . '" target="_blank" class="pure-button button-secondary" title="'.$this->lB('ressources.link-to-url').'">'
                    . $this->_getIcon('link-to-url')
                . '</a>'
            . '</span>'
            . $this->_renderArrayValue('type', $aRessourceItem)
            . ' '
            . $this->_renderArrayValue('ressourcetype', $aRessourceItem)
            . '<br>'
            */
            . '<br><strong>' . str_replace('&', '&shy;&', htmlentities($this->_renderArrayValue('url', $aRessourceItem))) . '</strong>'
            . ' '
            . ($sLink2Searchindex
                ? '&nbsp; <a href="' . $sLink2Searchindex . '" class="pure-button"'
                . ' title="' . $this->lB('ressources.link-to-searchindex') . '"'
                . '>'
                . $this->_getIcon('switch-search-res')
                . '</a>'
                : ''
            )
            . ' <a href="' . $aRessourceItem['url'] . '" target="_blank" class="pure-button" title="' . $this->lB('ressources.link-to-url') . '">'
            . $this->_getIcon('link-to-url')
            . '</a>'
            . '<br><br>'
            . '</div>'
            . '<div class="divRessourceContent">'
            . $this->lB('ressources.age-scan') . ': ' . $this->hrAge($unixTS) . '<br><br>'

        ;

        // $oRenderer->renderNetworkTimer($aItem[0]['header'], $aOptions['analysis']['MaxLoadtime'])
        // $aRessourceItem['curl.timers'] = $this->renderNetworkTimer($aRessourceItem['header']);

        $aRessourceItem['timers'] = $this->renderNetworkTimer($aRessourceItem['header']);

        $sReturn .= $this->_renderItemAsTable($aRessourceItem, [
            // 'id',
            'http_code',
            'type',
            'ressourcetype',
            'content_type',
            '_size_download',
            'ts',
            'timers'
            // '_meta_total_time',
            // 'curl.timers',
            // 'errorcount', 
        ])

        // . $this->renderNetworkTimer($aRessourceItem['header'])
        // .'<pre>'.print_r($aRessourceItem, 1).'</pre>'
        ;

        /*
        if ($aRessourceItem['errorcount']) {
            $aJson = json_decode($aRessourceItem['lasterror'], true);
            $sReturn.=$this->lB('error')
                    . '<pre>' . print_r($aJson, 1) . '</pre>'
                    ;
        }
        */

        $sReturn .= '</div>'
            . '</div>'
        ;

        // $sReturn.='<pre>ressource id #'.$aRessourceItem['id'].'<br>'.print_r($aRessourceItem, 1).'</pre>';

        return $sReturn;
    }

    /**
     * Render a ressource as a line (for reporting)
     * 
     * @param array    $aResourceItem    array of ressurce item
     * @param boolean  $bShowHttpstatus  flasg: show http code? default: false (=no)
     * @param boolean  $bUseLast         add css class "last" to highlight it? default; flase (=no)
     * @return string
     */
    public function renderRessourceItemAsLine(array $aResourceItem, bool $bShowHttpstatus = false, bool $bUseLast = false): string
    {
        if (!is_array($aResourceItem) || !count($aResourceItem) || !array_key_exists('ressourcetype', $aResourceItem)) {
            return '';
        }
        $sButtons = '';
        if ($bShowHttpstatus && (!$aResourceItem['http_code'] || $aResourceItem['http_code'] > 299)) {
            $sButtons .= '<a href="#" class="pure-button blacklist" data-url="' . $aResourceItem['url'] . '" title="' . $this->lB('ressources.denylist.add') . '">'
                . $this->_getIcon('blacklist')
                . '</a> '
            ;
        }
        $sCurlError = '';
        if (isset($aResourceItem['lasterror'])) {
            $aErrData = json_decode($aResourceItem['lasterror'], 1);
            if (isset($aErrData['_curlerror']) && $aErrData['_curlerror']) {
                $sCurlError .= $this->renderMessagebox(sprintf($this->lB("ressources.no-response"), $aErrData['_curlerror'], $aErrData['_curlerrorcode']), 'error');
            }
        }
        return '<div class="divRessourceAsLine' . ($bUseLast ? ' last last-' . $this->_getCssClassesForHttpstatus($aResourceItem['http_code'], true) : '') . '">'
            . ' <span style="float: right; font-size: 70%;">'
            . $sButtons
            . '<a href="' . $aResourceItem['url'] . '" class="pure-button" title="' . $this->lB('ressources.link-to-url') . '" target="_blank">'
            . $this->_getIcon('link-to-url')
            . '</a>'
            . '</span>'
            . ($bShowHttpstatus ? ' ' . $this->_renderArrayValue('http_code', $aResourceItem) : '')
            . ' ' . $this->_renderArrayValue('type', $aResourceItem)
            . ' ' . $this->_renderArrayValue('ressourcetype', $aResourceItem)
            . ' <a href="?page=ressourcedetail&id=' . $aResourceItem['id'] . '&siteid=' . $aResourceItem['siteid'] . '" class="url" title="' . $this->lB('ressources.link-to-details') . '">' . htmlentities($aResourceItem['url']) . '</a>'
            . ($aResourceItem['http_code'] == -1 && $this->oRes->isInDenyList($aResourceItem['url'])
                ? $this->renderMessagebox(sprintf($this->lB("linkchecker.found-in-deny-list"), $this->oRes->isInDenyList($aResourceItem['url'])), 'ok')
                : ''
            )
            . $sCurlError

            /*
            . (isset($aResourceItem['isExternalRedirect']) && $aResourceItem['isExternalRedirect'] 
                    ? ' <span class="redirect"><nobr>' . $this->_getIcon('ico.redirect') . $this->lB('ressources.link-is-external-redirect') . '</nobr></span>' 
                    : '')
             * 
             */
            . '<div style="clear: both;"></div>'
            . '</div>'
            // . print_r($aResourceItem, 1)
        ;
    }

    /**
     * Helper function for vis js:
     * Get a node as array
     * 
     * @param array  $aItem    ressource item
     * @param string $sNodeId  optional id for the node (default is id in ressource item)
     * @return array
     */
    private function _getVisNode(array $aItem, string $sNodeId = ''): array
    {
        $sNodeLabel = $aItem['url'] . "\n(" . $aItem['type'] . ' ' . $aItem['ressourcetype'] . '; ' . $aItem['http_code'] . ')';
        $sNodeId = $sNodeId ? $sNodeId : $aItem['id'];
        return [
            'id' => $sNodeId,
            // 'label'=>$this->renderRessourceItemAsLine($aItem),
            'label' => $sNodeLabel,
            'group' => $aItem['ressourcetype'],
            'title' => $sNodeLabel,
        ];
    }

    /**
     * helper function for vis js
     * Get an edge as array to connect 2 nodes
     * 
     * @param array  $aOptions   Array with options; subkeys are:
     *                              - from   id for starting node 
     *                              - to     id for ending node
     *                              - color  color of the edge
     *                              - arrows arrow direction
     *                              - title  title of the edge
     * @param string $sNodeId  optional id for the node (default is id in ressource item)
     * @return array
     */
    private function _getVisEdge(array $aOptions): array
    {
        $aColors = [
            'in' => '#99bb99',
            'out' => '#9999bb',
        ];
        foreach (['from', 'to'] as $sMustKey) {
            if (!array_key_exists($sMustKey, $aOptions)) {
                echo __METHOD__ . ' WARNING: no ' . $sMustKey . ' in option array<br>';
                return [];
            }
        }
        $aReturn = [
            'from' => $aOptions['from'],
            'to' => $aOptions['to'],
        ];

        if (array_key_exists('color', $aOptions) && array_key_exists($aOptions['color'], $aColors)) {
            $aOptions['color'] = $aColors[$aOptions['color']];
        }
        foreach (['arrows', 'title', 'color'] as $sKey) {
            if (array_key_exists($sKey, $aOptions)) {
                $aReturn[$sKey] = $aOptions[$sKey];
            }
        }
        return $aReturn;
    }


    /**
     * Get html code for network visualization with visjs
     * @param array  $aNodes
     * @param array  $aEdges
     * @return string The HTML code
     */
    private function _renderNetwork(array $aNodes, array $aEdges): string
    {
        $sIdDiv = 'visarea';
        $sVisual = ''
            . '<!-- for header -->'
            . '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.20.1/vis.min.js"></script>'
            . '<link href="https://cdnjs.cloudflare.com/ajax/libs/vis/4.20.1/vis.min.css" rel="stylesheet" type="text/css" />'
            . '  <style>
                    #' . $sIdDiv . '{
                      height: 500px;
                      width: 60%;
                      border:1px solid lightgrey;
                    }
                </style>'
            . '<!-- for body -->'
            . '<div id="' . $sIdDiv . '"></div>'
            . '<script language="JavaScript">
                    var iIconSize=120;
                    var optionsFA = {
                      groups: {
                        css: {
                          shape: \'icon\',
                          icon: {
                            face: \'FontAwesome\',
                            code: \'\uf15c\',
                            size: iIconSize,
                            color: \'#cccccc\'
                          }
                        },
                        image: {
                          shape: \'icon\',
                          icon: {
                            face: \'FontAwesome\',
                            code: \'\uf1c5\',
                            size: iIconSize,
                            color: \'#eecc22\'
                          }
                        },
                        link: {
                          shape: \'icon\',
                          icon: {
                            face: \'FontAwesome\',
                            code: \'\uf0c1\',
                            size: iIconSize,
                            color: \'#888888\'
                          }
                        },
                        page: {
                          shape: \'box\',
                          shape: \'icon\',
                          color: {background:\'pink\', border:\'purple\'},
                          icon: {
                            face: \'FontAwesome\',
                            code: \'\uf15b\',
                            size: iIconSize,
                            shape: \'box\'
                          }
                        },
                        script: {
                          shape: \'icon\',
                          icon: {
                            face: \'FontAwesome\',
                            code: \'\uf1c9\',
                            size: iIconSize,
                            color: \'#88cccc\'
                          }
                        }
                      },
                      layout: {
                          hierarchical: {
                              direction: "LR",
                              sortMethod: "directed",

                              levelSeparation: 500,
                              nodeSpacing: 200,

                          }
                      },
                      interaction: {dragNodes :false},
                      physics: {
                          enabled: false
                      },
                      configure1: {
                        showButton:false,
                        filter: function (option, path) {
                            if (path.indexOf(\'hierarchical\') !== -1) {
                                return true;
                            }
                            return false;
                        }
                      }
                    };

                    // create a network
                    var containerFA = document.getElementById(\'' . $sIdDiv . '\');
                    var dataFA = {
                      nodes: ' . json_encode($aNodes) . ',
                      edges: ' . json_encode($aEdges) . '
                    };

                    var networkFA = new vis.Network(containerFA, dataFA, optionsFA);

                    networkFA.on("click", function (params) {
                        params.event = "[original event]";
                        // console.log(\'click event, getNodeAt returns: \' + this.getNodeAt(params.pointer.DOM));
                        console.log(\'click event - params: \' + params);
                    });      
                </script>';
        return $sVisual;
    }

    /**
     * Get html code for a list of ressource items and an added http-status group filter 
     * used in renderRessourceItemFull()
     * 
     * @staticvar int $iListcounter
     * 
     * @param array    $aItemlist       array of ressource items to display
     * @param boolean  $bShowIncoming   optional flag: show ressources that use the current ressource? default: true (=yes)
     * @param boolean  $bShowRedirects  optional flag: show redrirects? default: true (=yes)
     * @return string
     */
    protected function _renderRessourceListWithGroups(array $aItemlist, bool $bShowIncoming = true, bool $bShowRedirects = true): string
    {
        $sReturn = $this->lB('ressources.total') . ': <strong>' . count($aItemlist) . '</strong>';
        if (!count($aItemlist)) {
            return $sReturn;
        }
        static $iListcounter;
        if (!isset($iListcounter)) {
            $iListcounter = 0;
        }
        $iListcounter++;

        $oHttp = new httpstatus();
        $sDivClass = 'resitemout' . $iListcounter;
        $iReportCounter = 1;
        $sFilter = '';
        $sOut = '';
        $aHttpStatus = [];
        $aTypes = [];
        foreach ($aItemlist as $aTmpItem) {
            $oHttp->setHttpcode($aTmpItem['http_code']);
            $sHttpStatusgroup = $oHttp->getStatus();
            $sRestype = $aTmpItem['ressourcetype'];
            $aHttpStatus[$sHttpStatusgroup] = (isset($aHttpStatus[$sHttpStatusgroup])) ? $aHttpStatus[$sHttpStatusgroup] + 1 : 1;
            $aTypes[$sRestype] = (isset($aTypes[$sRestype])) ? $aTypes[$sRestype] + 1 : 1;

            $sOut .= '<div class="' . $sDivClass . ' group-' . $sHttpStatusgroup . ' restype-' . $sRestype . '">'
                . '<div class="counter">' . $iReportCounter++ . '</div>' . $this->renderReportForRessource($aTmpItem, $bShowIncoming, $bShowRedirects)
                . '</div>'
            ;
        }
        if (count($aHttpStatus) > 0) {
            ksort($aHttpStatus);
            foreach ($aHttpStatus as $sHttpStatusgroup => $iStatusCount) {
                $sCss = 'text-on-markedelement http-code-' . implode(' http-code-', explode('-', $sHttpStatusgroup));
                $sFilter .= ''
                    . '<a href="#" class="pure-button ' . $sCss . '" '
                    . 'onclick="$(this).toggleClass(\'' . $sCss . '\'); $(\'div.' . $sDivClass . '.group-' . $sHttpStatusgroup . '\').toggle(); return false;"'
                    . '><strong>' . $iStatusCount . '</strong> x ' . $this->lB('http-status-group-' . $sHttpStatusgroup) . '</a>'
                    . ' '
                ;
            }
            /*
             * only useful with a tab control
             * 
            $sFilter.=$sFilter ? ' ... ' : '';
            foreach($aTypes as $sTypegroup=>$iStatusCount){
                $sCss='restype-'.implode(' restype-',explode('-', $sTypegroup));
                $sFilter.=''
                        . '<a href="#" class="pure-button button-secondary '.$sCss.'" '
                        . 'onclick="$(this).toggleClass(\'button-secondary\'); $(\'div.'.$sDivClass.'.restype-'.$sTypegroup.'\').toggle(); return false;"'
                        . '><strong>'.$iStatusCount . '</strong> x ' .$sTypegroup.'</a>'
                        . ' '
                        ;
            }
             * 
             */
            $sFilter = '<div class="actionbox">'
                . $this->_getIcon('ico.filter')
                . $this->lB('ressources.filter') . '<br>'
                . $this->lB('ressources.filter-httpgroups') . '<br>'
                . '<br>'
                . $sFilter
                . '</div><br>';
        }
        return "$sReturn<br><br>$sFilter$sOut";
    }


    /**
     * Get html code for curl meta infos
     * 
     * @param array $aItem  ressource item
     * @return string
     */
    public function renderCurlMetadata(array $aItem=[]): string
    {
        if(!isset($aItem['header'])){
            return '-';
        }
        $aHeader=json_decode($aItem['header'], 1);
        $sRemoveKey='_responseheader';

        if(isset($aHeader[$sRemoveKey])){
            unset($aHeader[$sRemoveKey]);
        }
        
        return '<pre>' 
            . print_r($aHeader, 1) 
            . '</pre>'
            ;
    }

    /**
     * Get html code for full detail of a ressource with properties, in and outs
     * 
     * @param array $aItem  ressource item
     * @return string
     */
    public function renderRessourceItemFull(array $aItem): string
    {
        $sReturn = '';
        $iId = $aItem['id']??0;
        $aIn = $this->oRes->getRessourceDetailsIncoming($iId);
        $aOut = $this->oRes->getRessourceDetailsOutgoing($iId);

        /*
         
        // data mapping into JS to visualize a map
         
        $aNodes=[];
        $aEdges=[];
        
        $sNodeLabel=$aItem['url']."\n(".$aItem['type'].' '.$aItem['ressourcetype'].'; '.$aItem['http_code'].')';
        $aNodes[]=$this->_getVisNode($aItem);
        if (count($aIn)){
            foreach ($aIn as $aTmpItem) {
                $sNodeId=$aTmpItem['id'].'IN';
                $aNodes[]=$this->_getVisNode($aTmpItem,$sNodeId);
                $aEdges[]=$this->_getVisEdge([
                    'from'=>$sNodeId,
                    'to'=>$aNodes[0]['id'], 
                    'arrows'=>'to',
                    'color' => 'in',
                ]);
            }
        }
        if (count($aOut)){
            foreach ($aOut as $aTmpItem) {
                $sNodeId=$aTmpItem['id'].'OUT';
                $sNodeLabel=$aTmpItem['url']."\n(".$aTmpItem['type'].' '.$aTmpItem['ressourcetype'].')';
                $aNodes[]=$this->_getVisNode($aTmpItem,$sNodeId);
                $aEdges[]=$this->_getVisEdge([
                    'from'=>$aNodes[0]['id'],
                    'to'=>$sNodeId, 
                    'arrows'=>'to',
                    'color' => 'out',
                ]);
            }
        }
        */

        // --------------------------------------------------
        // table on top
        // --------------------------------------------------

        /*
        $sReturn.=''
                . '<table><tr>'
                    . '<td style="vertical-align: top; text-align: center; padding: 0 1em;">'
                        . $this->lB('ressources.references-in') . '<br>'
                        . '<span class="ressourcecounter"><a href="#listIn">' . count($aIn) . '<br><i class="fa fa-arrow-right"></i></a></span>'
                    . '</td>'
                    . '<td>'
                        . $this->renderRessourceItemAsBox($aItem)
                    . '</td>'
                        . '<td style="vertical-align: top; text-align: center; padding: 0 1em;">'
                        . $this->lB('ressources.references-out') . '<br>'
                        . '<span class="ressourcecounter"><a href="#listOut">' . count($aOut) . '<br><i class="fa fa-arrow-right"></i></a></span>'
                    . '</td>'
                . '</tr></table>'
                // . $this->_renderNetwork($aNodes, $aEdges)
                ;
        */
        $sReturn .= $this->renderRessourceItemAsBox($aItem) . '<br>';

        // --------------------------------------------------
        // http header
        // --------------------------------------------------
        $sHeader = $aItem['header'] ?: $aItem['lasterror'];
        $aHeaderJson = json_decode($sHeader, 1);
        if(is_array($aHeaderJson)){
            // $sReturn.='<pre>'.print_r($aHeaderJson,1).'</pre>';
            if (isset($aHeaderJson['_curlerror']) && $aHeaderJson['_curlerror']) {
                $sReturn .= $this->renderMessagebox(sprintf($this->lB("ressources.no-response"), $aHeaderJson['_curlerror'], $aHeaderJson['_curlerrorcode']), 'error') . '<br>';
            } else {
                $sReposneHeaderAsString = strlen($aHeaderJson['_responseheader'][0]??'') != 1 
                    ? $aHeaderJson['_responseheader'][0] 
                    : $aHeaderJson['_responseheader']
                    ;

                $oHttpheader = new httpheader();
                $oHttpheader->setHeaderAsString($sReposneHeaderAsString);
                // $aHeader=$oHttpheader->setHeaderAsString(is_array($aHeaderJson['_responseheader']) ? $aHeaderJson['_responseheader'][0] : $aHeaderJson['_responseheader']);
                // . $oRenderer->renderHttpheaderAsTable($oHttpheader->checkHeaders());

                $sReturn .= ''
                    . $this->renderToggledContent(
                        $this->lB('httpheader.data'),
                        $this->renderHttpheaderAsTable($oHttpheader->parseHeaders())
                        . (isset($this->aOptions['menu-public']['httpheaderchecks']) && $this->aOptions['menu-public']['httpheaderchecks']
                            ? '<br><a href="../?page=httpheaderchecks&urlbase64=' . base64_encode($aItem['url']) . '" class="pure-button" target="_blank">' . $this->_getIcon('link-to-url') . $this->lB('ressources.httpheader-live') . '</a>'
                            : ''
                        )
                        ,
                        false
                    );
            }
        }

        // --------------------------------------------------
        // curl metadata
        // --------------------------------------------------
        $sCurl=isset($aItem['header'])
            ? '<pre>' . print_r(json_decode($aItem['header'], 1), 1) . '</pre>'
            : '-'
        ;
        $sCurl = $this->renderCurlMetadata($aItem);

        $sReturn .= $this->renderToggledContent(
            $this->lB('ressources.curl-metadata-h3'),
            $sCurl,
            false
        );

        // --------------------------------------------------
        // where it is linked
        // --------------------------------------------------
        $sReturn .= $this->renderToggledContent(
            sprintf($this->lB('ressources.references-h3-in'), count($aIn)),
            $this->_renderRessourceListWithGroups($aIn, false, true),
            false
        );

        /*
        $sReturn.='<h3 id="listIn">' . sprintf($this->lB('ressources.references-h3-in'), count($aIn)) . '</h3>'
                . $this->_renderRessourceListWithGroups($aIn, false, false)
        ;
         */
        // --------------------------------------------------
        // outgoing links / redirects
        // --------------------------------------------------
        $sReturn .= $this->renderToggledContent(
            sprintf($this->lB('ressources.references-h3-out'), count($aOut)),
            $this->_renderRessourceListWithGroups($aOut, false, true),
            false
        );
        /*
        $sReturn.='<h3 id="listOut">' . sprintf($this->lB('ressources.references-h3-out'), count($aOut)) . '</h3>'
                . $this->_renderRessourceListWithGroups($aOut,false, true)
        ;
         */
        return $sReturn;
    }

    /**
     * Get html code for tiles of ressource status total
     * 
     * @return string
     */
    public function renderRessourceStatus(): string
    {
        // $iRessourcesCount=$this->oDB->count('ressources',['siteid'=>$this->iSiteId]);
        $this->_initRessource();
        $iRessourcesCount = $this->oRes->getCount();
        $iExternal = $this->oRes->getCount(['siteid' => $this->oRes->iSiteId, 'isExternalRedirect' => '1']);

        $dateLast = $this->oRes->getLastRecord();
        $sTiles = ''
            . $this->renderTile('', $this->lB('ressources.age-scan'), $this->hrAge(date("U", strtotime($dateLast))), $dateLast, '')
            . $this->renderTile('', $this->lB('ressources.itemstotal'), $iRessourcesCount, '', '')
            . $this->renderTile('', $this->lB('linkchecker.found-http-external'), $iExternal, '', '')
        ;

        return $this->renderTileBar($sTiles);
    }

    /**
     * render an icon and a prefix
     * 
     * @param string $sType
     * @return string
     */
    public function renderShortInfo(string $sType): string
    {
        return $this->_getIcon("ico.$sType", false, "ico-$sType");
    }


    /**
     * Get html code to draw a tile
     * 
     * @param string $sType       type to select a color; one of '' |'ok'|'error'
     * @param string $sIntro      top text
     * @param string $sCount      counter value
     * @param string $sFoot       optional: footer text
     * @param string $sTargetUrl  optional: linked url if it acts as a link
     * @return string
     */
    public function renderTile(string $sType, string $sIntro, string $sCount, string $sFoot = '', string $sTargetUrl = ''): string
    {
        return '<li>'
            . $this->oHtml->getTag('a', [
                'href' => ($sTargetUrl ?: '#" onclick="return false;'),
                'class' => 'tile ' . $sType . ' scroll-link ' . ($sTargetUrl ? '' : 'nonclickable'),
                'label' => $sIntro
                    . (strstr($sIntro, '<br>') ? '' : '<br>')
                    . '<br>'
                    . '<strong>' . $sCount . '</strong><br>'
                    . $sFoot
            ])
            . '</li>';
    }

    /**
     * Get html code to wrap all tiles 
     * @see $this->renderTile() to generate the necessary items.
     * 
     * @param string $sTiles      html code with tiles (list items)
     * @param string $sType       default type of all tiles; one of '' |'ok'|'error'|'warn'
     * @return string
     */
    public function renderTileBar(string $sTiles, string $sType = ''): string
    {
        return "<ul class=\"tiles $sType\">$sTiles</ul>";
    }

    /**
     * Get html code to show a toggable content box
     * 
     * @staticvar int $iToggleCounter  counter of toggled box on a page
     * 
     * @param string   $sHeader    clickable header text
     * @param string   $sContent   content
     * @param boolean  $bIsOpen    flag: open box by default? default: false (=closed content)
     * @return string
     */
    public function renderToggledContent(string $sHeader, string $sContent, bool $bIsOpen = false): string
    {
        static $iToggleCounter;
        if (!isset($iToggleCounter)) {
            $iToggleCounter = 0;
        }
        $iToggleCounter++;
        $sDivIdHead = 'div-toggle-head-' . $iToggleCounter;
        $sDivId = 'div-toggle-' . $iToggleCounter;
        return ''
            . '<div class="div-toggle-head" id="' . $sDivIdHead . '">'
            . $this->oHtml->getTag('a', [
                'href' => '#',
                'class' => ($bIsOpen ? 'open' : ''),
                // 'onclick'=>'$(\'#'.$sDivId.'\').slideToggle(); $(this).toggleClass(\'open\'); return false;',
                'label' => $sHeader,
            ])
            . '</div>'
            . '<div' . ($bIsOpen ? '' : ' style="display:none;"') . ' id="' . $sDivId . '" class="div-toggle">'
            . $sContent
            . '</div>'
        ;

    }
}
