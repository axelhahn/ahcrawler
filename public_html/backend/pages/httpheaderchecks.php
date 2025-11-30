<?php

/**
 * page analysis :: Http header check
 */
$oRenderer = new ressourcesrenderer((int)$this->_sTab);
$oHttpheader = new httpheader();

$sReturn = '';
$bShowResult = false;

// important base variables in this file:
$sUrl = '';           // requested url
$sResponse = '';      // http response header
$bShowForm = false;   // flag: show input form?

    /**
     * Show table row of security header group
     * 
     * @param string $sLabel 1st column - label
     * @param string $sData  2nd column - data 
     * @return string html code of table row
     */
    function add2Cols(string $sLabel, string $sData):string {
        return "<tr><td valign=\"top\" style=\"min-width: 10em;\">
            $sLabel
        </td><td>
            $sData
        </td></tr>"
        ;
    }

    /**
     * Render a single http response header line and get its html code
     * 
     * @param array $aHeaderitem  item for a single http header line
     * @return string html
     */
    function showHeaderitem(array $aHeaderitem): string{
        $o = new backend();
        $bWarnTag=($aHeaderitem['unwanted']??false) || ($aHeaderitem['deprecated']??false);
        $sDescription=$o->lB('httpheader.' . ($aHeaderitem['_tag']??'') . '.description').'<br><br>';
        if(strstr($sDescription, '[backend: httpheader..description]')){
            $sDescription='';
        }
        return ''
            // .'<li>'
                . ($aHeaderitem['_data']['important']??false
                    ?' !!! '
                    :''
                    )
                .'<pre>'
                    .'<span class="linenumber">' . $aHeaderitem['line'] . '</span> '
                    .'<strong>'
                    . ($bWarnTag ? '<span class="error">' . $aHeaderitem['var']  . '</span>'
                        : $aHeaderitem['var'] 
                    )
                    . '</strong>'
                    .': '
                    .(isset($aHeaderitem['regex']['unwantedregex'])
                        ? preg_replace('/(' . $aHeaderitem['regex']['unwantedregex'] . ')/i', '<span class="error">$1</span>', $aHeaderitem['value'])
                        : $aHeaderitem['value']
                    )
                . '</pre>
                '. $sDescription. '
            '. DocsButton($aHeaderitem['_tag']??'').'
            '. DocsButton($aHeaderitem['_data']['alt']??'')
            // . '<pre>'.print_r($aHeaderitem, 1).'</pre>'
           //  .'</li>'
           ;

    }

    /**
     * Render a button to header doc page on developer.mozilla.org
     * called in showHeaderitem()
     * 
     * @param string $sHeader http header var
     * @return string
     */
    function DocsButton(string $sHeader): string {
        $o = new backend();
        return $sHeader 
            ? $o->getButton([
                'href' => "https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/$sHeader",
                // 'class' => 'button-secondary',
                'popup' => false,
                'target' => '_httpheader',
                'title' => "developer.mozilla.org",
                'customlabel' => $o->_getIcon('button.openurl') . ' ' . $sHeader,
                // 'customlabel' => $sHeader,
            ])
            : ''
        ;
    }

    function BtnData(string $sHeader): array {
        // $o = new backend();
        return [
            'href' => "https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/$sHeader",
            // 'class' => 'button-secondary',
            'popup' => false,
            'target' => '_httpheader',
            // 'customlabel' => $o->_getIcon('button.help') . ' ' . $sHeader,
            'customlabel' => $sHeader,
        ];

    }

if ($this->_bIsPublic || isset($_GET['url'])) {
    // ----------------------------------------------------------------------
    // public: make a request from url in form value
    // ----------------------------------------------------------------------
    $bShowForm = true;
    // $sUrl=( isset($_GET['url']) && $_GET['url'] ) ? $_GET['url'] : '';
    $sUrl = (isset($_GET['urlbase64']) && $_GET['urlbase64']) ? base64_decode($_GET['urlbase64']) : '';
    $sReturn .= ''
        . $oRenderer->renderContextbox(
            $oRenderer->renderBookmarklet('httpheaderchecks'),
            $this->lB('bookmarklet.httpheaderchecks.head')
        )
        . '<h3>' . $this->lB('httpheader.enter-url') . '</h3>'
        . '<p>' . $this->lB('httpheader.enter-url.hint') . '</p>
            <form class="pure-form pure-form-aligned" method="GET" action="?">
                <input type="hidden" name="page" value="httpheaderchecks">
                <input type="hidden" name="lang" value="' . $this->sLang . '">
                <input type="hidden" name="urlbase64" id="urlbase64" value="">
                <nobr>
                <input type="text" size="100" id="e_url" value="' . htmlentities($sUrl) . '" placeholder="https://example.com" pattern="^http[s]*://.*">'
        . ($sUrl
            ? $oRenderer->oHtml->getTag('a', ['label' => $this->_getIcon('button.close'), 'class' => 'pure-button button-error', 'href' => '?page=httpheaderchecks&lang=' . $this->sLang]) . ' '
            : ''
        )
        . '<button class="pure-button button-secondary">' . $this->_getIcon('button.save') . '</button>'
        . '</nobr>'
        . '<div style="clear: both;"></div>';
    if ($sUrl && preg_match('#^http.*#', $sUrl)) {

        // ---------- request the url
        $aResponse = $this->httpGet($sUrl, 1);
        if (!isset($aResponse['error'])) {
            $sResponse=$aResponse['response'];
            $bShowResult = true;

            if (!preg_match('/^HTTP.*\ 200/', $sResponse)) {
                preg_match("#location:(.*)\\r#i", $sResponse, $aLocation);
                if (isset($aLocation[1])) {
                    $sReturn .= $oRenderer->renderMessagebox($this->lB('httpheader.result.redirect'), 'warning');
                    $sTarget = trim($aLocation[1]);
                    // add protocol and domain name on a relative loction url
                    if (!preg_match('#^http[s]*://#', $sTarget)) {
                        $sTarget = preg_replace('#^(http.*//.*)/.*$#U', '$1', $sUrl) . $sTarget;
                    }
                    // $sReturn.='<button class="pure-button" onclick="document.getElementById(\'e_url\').value=\''.$sTarget.'\'; return true;">'.$sTarget.'</button>';
                    $sReturn .= '<button class="pure-button" onclick="document.getElementById(\'e_url\').value=\'' . $sTarget . '\'; document.getElementById(\'urlbase64\').value=\'' . base64_encode($sTarget) . '\'; return true;">' . $sTarget . '</button>';
                } else {
                    $sReturn .= $oRenderer->renderMessagebox($this->lB('httpheader.result.non-ok'), 'warning');
                }
            }
        } else {
            $sReturn .= $oRenderer->renderMessagebox(
                sprintf($this->lB('ressources.no-response'), $aResponse['error'], $aResponse['errorcode'])
                , 'error'
                );
        }
    }
    $sReturn .= '</form>';
} else {
    // ----------------------------------------------------------------------
    // backend: show header of starting page or by given id
    // ----------------------------------------------------------------------
    if (!$this->_requiresPermission("viewer", $this->_sTab)){
        return include __DIR__ . '/error403.php';
    }
    
    // add profiles navigation
    $sReturn.=$this->_getNavi2($this->_getProfiles(), false, '');

    $aCountByStatuscode = $this->_getStatusinfos(['_global']);
    $iRessourcesCount = $aCountByStatuscode['_global']['ressources']['value'];
    $iPagesCount = $aCountByStatuscode['_global']['pages']['value'];
    if (!$iPagesCount) {
        return $sReturn
            . '<h3>' . $this->lB("error.not-enough-data") . '</h3>'
            . $oRenderer->renderMessagebox(
                sprintf($this->lB('status.emptyindex'), $this->_sTab),
                'warning'
            );
    }

    $iResId = $this->_getRequestParam('id', false, 'int');
    $sUrl = $this->_getRequestParam('url');

    if (!$iResId) {

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
    } else {
        if (!$iRessourcesCount) {
            return $sReturn
                . '<h3>' . $this->lB("error.not-enough-data") . '</h3>'
                . $oRenderer->renderMessagebox(sprintf($this->lB('ressources.empty'), $this->_sTab), 'warning');
        }

        // with get param id: get it from ressources (not pages!)
        $aPagedata = $this->oDB->select(
            'ressources',
            ['url', 'header'],
            [
                'AND' => [
                    'siteid' => $this->_sTab,
                    'id' => $iResId,
                ],
                "ORDER" => ["id" => "ASC"],
                "LIMIT" => 1
            ]
        );
        if (count($aPagedata) === 0) {
            $sReturn .= $oRenderer->renderMessagebox(sprintf($this->lB('httpheader.nopage-with-id'), $iResId) . '<br>' . $this->oDB->last(), 'warning');
            return $sReturn;
        }
    }

    $sInfos = $aPagedata[0]['header'];

    $aInfos = json_decode($sInfos, 1);

    $sResponse = strlen($aInfos['_responseheader'][0]) != 1 ? $aInfos['_responseheader'][0] : $aInfos['_responseheader'];
    $sUrl = $aPagedata[0]['url'];

    // _responseheader ?? --> see crawler.class - method processResponse()
}


if (!$sResponse) {
    return $sReturn;
}

$oHttpheader->setHeaderAsString($sResponse);
$aSecHeader = $oHttpheader->getSecurityHeaders();

// ----------------------------------------------------------------------
// tiles
// ----------------------------------------------------------------------
$aFoundTags = $oHttpheader->getExistingTags();
// print_r($aFoundTags);
$iTotalHeaders = count($oHttpheader->getHeaderAsArray());
$iSecHeader = isset($aFoundTags['security'])  ? $aFoundTags['security']  : 0;
$iUnkKnown = isset($aFoundTags['unknown'])  ? $aFoundTags['unknown']  : 0;
$iUnwanted = isset($aFoundTags['unwanted']) ? $aFoundTags['unwanted'] : 0;
$iDeprecated = isset($aFoundTags['deprecated']) ? $aFoundTags['deprecated'] : 0;
$iExperimental = $aFoundTags['experimental'] ?? 0;
$iNonStandard = isset($aFoundTags['non-standard']) ? $aFoundTags['non-standard'] : 0;

// $sTiles = $this->_getTilesOfAPage();

// ----------------------------------------------------------------------
// header dump
// ----------------------------------------------------------------------
$sMyDomain = parse_url($sUrl,  PHP_URL_HOST);
$sReturn .= '<h3>' . $this->lB('httpheader.data') . '</h3>'
    . '<p>'
    . $this->_getIcon('checkurl')
    . sprintf($this->lB('httpheader.data.description'), $sUrl) . '<br><br>'
    . '</p>'
    // . $oRenderer->renderTileBar($sTiles, '') . '<div style="clear: both;"></div>'
    . $oRenderer->renderToggledContent($this->lB('httpheader.plain'), '<pre>' . htmlentities(print_r($sResponse, 1)) . '</pre>', false)
    . '<br>'
    . $oRenderer->renderHttpheaderAsTable($oHttpheader->parseHeaders());

// ----------------------------------------------------------------------
// warnings
// ----------------------------------------------------------------------
$iWarnings = 0;
$sWarnings = '';

$sLegendeUnknown = '';
$sLegendeWarn = '';

// ----------------------------------------------------------------------
// http version
$sHttpVer = $oHttpheader->getHttpVersion();
if ($sHttpVer < 2) {
    $iWarnings += 1;
    $sWarnings=add2Cols(
        '<strong id="warnhttpver">' . str_replace('<br>', ' ', $this->lB('httpheader.header.httpversion')) . '</strong>',
        $oRenderer->renderMessagebox($this->lB('httpheader.header.httpversion.hint'), 'error')
        . '<p>' . $this->lB('httpheader.header.httpversion.description') . '</p>'

    );
}

// ----------------------------------------------------------------------
// unknown header vars
$aUnknownheader = $oHttpheader->getUnknowHeaders();
if (is_array($aUnknownheader) && count($aUnknownheader)) {
    $iWarnings += $iUnkKnown;
    $sHeaders='';

    foreach ($aUnknownheader as $sKey => $aHeaderitem) {
        $sHeaders .= showHeaderitem($aHeaderitem);
    }

    $sWarnings .= add2Cols(
        '<strong id="warnunknown">' . str_replace('<br>', ' ', $this->lB('httpheader.header.unknown')) . '</strong>',
        $oRenderer->renderMessagebox($this->lB('httpheader.unknown.description'), 'warning') . '<br>'
            . $this->_getHistoryCounter(['responseheaderUnknown'])
            . $this->lB('httpheader.unknown.todo')
            . '<blockquote>' . $sHeaders . '</blockquote>'

    );
}

// ----------------------------------------------------------------------
// deprecated header vars
if ($iDeprecated) {
    $aDepr = $oHttpheader->getDeprecatedHeaders();
    $sHeaders='';

    // echo '<pre>' . print_r($aDepr, 1). '</pre>';
    $iWarnings += $iDeprecated;
    foreach ($aDepr as $aHeaderitem) {
        $sHeaders.=showHeaderitem($aHeaderitem);
    }
    $sWarnings .= add2Cols(
        '<strong id="warndeprecated">' . $this->lB('httpheader.header.deprecated') . '</strong>',
        $oRenderer->renderMessagebox($this->lB('httpheader.warnings.deprecated'), 'warning') . '<br>'
        . $this->_getHistoryCounter(['responseheaderDeprecated'])
        . '<blockquote>' . $sHeaders . '</blockquote>'
    );
}
// --- experimental header vars
if ($iExperimental) {
    $aExperimental = $oHttpheader->getExperimentalHeaders();
    $iWarnings += $iExperimental;
    $sHeaders='';

    foreach ($aExperimental as $sKey => $aHeaderitem) {
        $sHeaders.=showHeaderitem($aHeaderitem);
    }

    $sWarnings .= add2Cols(
        '<strong id="warnexperimental">' . $this->lB('httpheader.header.experimental') . '</strong>',
          $oRenderer->renderMessagebox($this->lB('httpheader.warnings.experimental'), 'warning') . '<br>'
        . $this->_getHistoryCounter(['responseheaderExperimental'])
        . '<blockquote>' . $sHeaders . '</blockquote>'
    );
}

// ----------------------------------------------------------------------
// unwanted header vars
$aWarnheader = $oHttpheader->getUnwantedHeaders();
if (is_array($aWarnheader) && count($aWarnheader)) {
    // $iWarnings+=count($aWarnheader);
    $iWarnings += $iUnwanted;
    $sHeaders='';

    foreach ($aWarnheader as $sKey => $aHeaderitem) {
        $sHeaders.=showHeaderitem($aHeaderitem);
    }

    $sWarnings .= add2Cols(
        '<strong id="warnunwanted">' . str_replace('<br>', ' ', $this->lB('httpheader.header.unwanted')) . '</strong>',
            $oRenderer->renderMessagebox($this->lB('httpheader.warnings.unwanted'), 'warning') . '<br>'
            . $this->_getHistoryCounter(['responseheaderUnwanted'])
            . '<blockquote>' . $sHeaders . '</blockquote>'
    );

}

// ----------------------------------------------------------------------
// common but non-standard header vars
if ($iNonStandard) {
    $aNonStdHeader = $oHttpheader->getNonStandardHeaders();
    $iWarnings += $iNonStandard;
    $sHeaders='';
    foreach ($aNonStdHeader as $sKey => $aHeaderitem) {
        $sHeaders.=showHeaderitem($aHeaderitem);
    }

    $sWarnings .= add2Cols(
        '<strong id="warnnonstandard">' . $this->lB('httpheader.header.non-standard') . '</strong>',
         $this->_getHistoryCounter(['responseheaderNonStandard'])
        . $oRenderer->renderMessagebox($this->lB('httpheader.warnings.non-standard'), 'warning')
            . '<blockquote>' . $sHeaders . '</blockquote>'
    );

}

// ----------------------------------------------------------------------
// no caching?
if (!isset($aFoundTags['cache'])) {
    $iWarnings++;

    $sWarnings .= add2Cols(
        '<strong id="warnnocache">' . str_replace('<br>', ' ', $this->lB('httpheader.header.cache')) . '</strong>',
         $this->_getHistoryCounter(['responseheaderNonStandard'])
        . $oRenderer->renderMessagebox($this->lB('httpheader.warnings.nocache'), 'warning') . '<br>'
    );

}

// ----------------------------------------------------------------------
// no compression?
if (!isset($aFoundTags['compression'])) {
    $iWarnings++;
    $sWarnings .= add2Cols(
        '<strong id="warnnocompression">' . str_replace('<br>', ' ', $this->lB('httpheader.header.compression')) . '</strong>',
         $this->_getHistoryCounter(['responseheaderNonStandard'])
        . $oRenderer->renderMessagebox($this->lB('httpheader.warnings.nocompression'), 'warning') . '<br>'
    );
}


// ----------------------------------------------------------------------
// security header
// ----------------------------------------------------------------------
// echo '<pre>' . htmlentities(print_r($aSecHeader,1)) . '</pre>'; die();

$sLegendeSecOk = '';
$sLegendeSecMiss = '';
$sLegendeSecOther = '';
$iFoundSecHeader = 0;
$iWarnSecHeader = 0;
$iErrorSecHeader = 0;
foreach ($aSecHeader as $sVar => $aData) {
    $sDescription=$this->lB("httpheader.$sVar.description").'<br>';
    if(strstr($sDescription, '[backend: httpheader..description]')){
        $sDescription='';
    }
    $bImportant=$aData['important']??false;
    switch($aData['state']){
        case "found":
            $iFoundSecHeader++;
            $bDeprecated = in_array('deprecated', $aData['tags']);
            $sHeaders='';

            $sLegendeSecOk .= ''
                . ($bImportant 
                    ? $oRenderer->renderMessagebox($sVar, 'ok')
                    : "<strong>$sVar</strong><br>" 
                )
                
                . $sDescription
                . ($bDeprecated 
                    ? $oRenderer->renderMessagebox($this->lB('httpheader.header.deprecated').'<br>' .$this->lB('httpheader.warnings.deprecated'), 'warning') 
                    : ""
                    )
                . DocsButton($sVar??'').' '
                . DocsButton($aData['alt']??'').' '
                .'<br>'
                // . $oRenderer->renderMessagebox(($bImportant ? ' 🏅 ' : '' ) . $sVar . ' ', ($bHasBadValue || $bDeprecated) ? 'warning' : 'ok')
                // . $oRenderer->renderShortInfo($aData ? 'found': 'miss') . ' <strong>' . $sVar. '</strong><br>'
                . '<blockquote>';

            foreach($aData['headers'] as $aHeader){
                $bHasBadValue = in_array('badvalue', $aHeader['tags']);
                $iWarnSecHeader += ($bHasBadValue || $bDeprecated) ? 1 : 0;

                // $sSecOk.='<li><a href="#" onclick="return false;" class="tile ok" title="'.$this->lB('httpheader.'.$sVar.'.description').'">' . $aData['var'].'<br>'.$aData['value'].'<br><strong>'.$oRenderer->renderShortInfo('found').'</strong></a></li>';
                $sLegendeSecOk .= ''
                        . '<pre>'
                        .'<span class="linenumber">' . $aHeader['line'] . '</span> '
                        . "<strong>$aHeader[var]</strong>: "
                        . ($bHasBadValue
                            ?  preg_replace('/(' . $aHeader['regex']['badvalueregex'] . '[a-z0-9]*)/i', '<span class="error">$1</span>', $aHeader['value'])
                            : $aHeader['value']
                        )
                        . '</pre>'
                        . ($bHasBadValue
                            ? '<blockquote>'.$oRenderer->renderMessagebox($this->lB('httpheader.tag.badvalue'), "warning").'</blockquote>' 
                            : ""
                            )
                    ;
            }
            $sLegendeSecOk .= ''
                    . '</blockquote>'
                    . '<br><br>'
                    ;
            break;
        case "miss":
            $iErrorSecHeader++;
            $sLegendeSecMiss .= $oRenderer->renderMessagebox($sVar, 'error')
                . $sDescription
                . DocsButton($sVar)
                .'<br><br>'
                ;
            break;
        case "other":
            $sLegendeSecOther.= "<strong>$sVar</strong><br>"
                . $sDescription
                . DocsButton($sVar)
                .'<br><br>'
                ;
            break;
        default:
            throw new Exception("not 'state' was set");
    }
}


// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------

$sReturn .= '<h3>' . sprintf($this->lB('httpheader.warnings'), $iWarnings) . '</h3>'
    . ($iWarnings
        ? "<table class=\"pure-table-horizontal no-footer\">$sWarnings</table>"
        : '<ul class="tiles">'
            . '<li><a href="#" onclick="return false;" class="tile ok">' . $this->lB('httpheader.warnings.ok-label') . '<br><strong>' . $this->lB('httpheader.warnings.ok') . '</strong></a></li>'
            . '</ul>'
            . '<div style="clear: both;"></div>'
    )

    . '<h3 id="securityheaders">' . sprintf($this->lB('httpheader.securityheaders'), $iFoundSecHeader, count($aSecHeader)) . '</h3>'
    . $oRenderer->renderContextbox(
        '<p>' . $this->lB('httpheader.context.securityheaders-links') . '</p>'
            . '<ul>'
            . '<li><a href="https://observatory.mozilla.org/analyze/' . $sMyDomain . '?third-party=false" target="_blank">observatory.mozilla.org</a></li>'
            . '<li><a href="https://securityheaders.com/?q=' . $sUrl . '&hide=on" target="_blank">securityheaders.com</a></li>'
            . '</ul>',
        $this->lB('context.links')
    )
    . '<p>'
    . $this->lB('httpheader.securityheaders.description') . '<br>'
    . '</p>'

    // . $this->_getHtmlchecksChart(($iFoundSecHeader+$iWarnSecHeader+$iErrorSecHeader), $iWarnSecHeader, $iErrorSecHeader)
    // . $this->_getHistoryCounter(['responseheaderSecurity'])
    . '<div style="clear: both;"></div>'

    // . "<pre>" . print_r($oHttpheader->getSecurityHeaders(), 1) . "</pre>"

    . "<table class=\"pure-table-horizontal no-footer\">"
    . ($sLegendeSecMiss 
        ? add2Cols(
            $oRenderer->renderMessagebox('<strong>' . $this->lB('httpheader.securityheaders.notfound') . '</strong>', 'error'),
            $sLegendeSecMiss.'<br><br>'
        )
        :''
    )
    . ($sLegendeSecOk 
        ? add2Cols(
            $oRenderer->renderMessagebox('<strong>' . $this->lB('httpheader.securityheaders.found') . '</strong>', 'ok'),
            $sLegendeSecOk.'<br><br>'
            // .'<pre>'.print_r($oHttpheader->getFoundSecurityHeaders(), 1).'</pre>'
        )
        : ''
    )
    . ($sLegendeSecOther
        ? add2Cols(
            '<strong>' . $this->lB('httpheader.securityheaders.other') . '</strong>',
            $sLegendeSecOther
        )
        :''
    )
    . "</table>"
    ;

// $sStartUrl=$this->aProfile['searchindex']['urls2crawl'][$sUrl][0];^$sReturn.=$sStartUrl.'<br>';

return $sReturn;
