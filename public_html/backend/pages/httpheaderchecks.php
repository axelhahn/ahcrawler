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
$bShowTiles = false;  // flag: show tiles in warning detail sections


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

$sTiles = $this->_getTilesOfAPage();

// ----------------------------------------------------------------------
// header dump
// ----------------------------------------------------------------------
$sMyDomain = parse_url($sUrl,  PHP_URL_HOST);
$sReturn .= '<h3>' . $this->lB('httpheader.data') . '</h3>'
    . '<p>'
    . $this->_getIcon('checkurl')
    . sprintf($this->lB('httpheader.data.description'), $sUrl) . '<br><br>'
    . '</p>'
    . $oRenderer->renderTileBar($sTiles, '') . '<div style="clear: both;"></div>'
    . $oRenderer->renderToggledContent($this->lB('httpheader.plain'), '<pre>' . htmlentities(print_r($sResponse, 1)) . '</pre>', false)
    . '<br>'
    . $oRenderer->renderHttpheaderAsTable($oHttpheader->parseHeaders());

// ----------------------------------------------------------------------
// warnings
// ----------------------------------------------------------------------
$iWarnings = 0;
$sWarnings = '';
$sTiles = '';

$sLegendeUnknown = '';
$sLegendeWarn = '';

$sHttpVer = $oHttpheader->getHttpVersion();
if ($sHttpVer < '2') {
    $iWarnings += 1;
    $sWarnings .= ''
        . '<h4 id="warnhttpver">' . str_replace('<br>', ' ', $this->lB('httpheader.header.httpversion')) . '</h4>'
        . $oRenderer->renderMessagebox($this->lB('httpheader.header.httpversion.hint'), 'error')
        . ($bShowTiles
            ? $oRenderer->renderTileBar($oRenderer->renderTile($oHttpheader->getHttpVersionStatus($sHttpVer), $this->lB('httpheader.header.httpversion'), $sHttpVer, ''))
            : ''
        )
        . '<div style="clear: both;"></div>'
        . '<p>' . $this->lB('httpheader.header.httpversion.description') . '</p>';
}


// --- unknown header vars
$aUnknownheader = $oHttpheader->getUnknowHeaders();
if (is_array($aUnknownheader) && count($aUnknownheader)) {
    $iWarnings += $iUnkKnown;

    $sWarnings .= ''
        . '<h4 id="warnunknown">' . str_replace('<br>', ' ', $this->lB('httpheader.header.unknown')) . '</h4>'
        . $oRenderer->renderMessagebox($this->lB('httpheader.unknown.description'), 'warning') . '<br>'
        . $this->_getHistoryCounter(['responseheaderUnknown']);
    foreach ($aUnknownheader as $sKey => $aHeaderitem) {
        $sTiles .= $bShowTiles
            ? $oRenderer->renderTile('warning', $this->lB('httpheader.unknown.tile'), $aHeaderitem['var'], $aHeaderitem['value'])
            : ''
            // .'<li><a href="#" onclick="return false;" class="tile"><br><strong>' . $aHeaderitem['var'].'</strong><br>'.$aHeaderitem['value'].'</a></li>'
        ;
        $sLegendeUnknown .= '<li>' . '<pre><span class="linenumber">' . $aHeaderitem['line'] . '</span> ' . $aHeaderitem['var'] . ': ' . $aHeaderitem['value'] . '</pre></li>';
    }
    $sWarnings .= ''
        . $oRenderer->renderTileBar($sTiles)
        . '<div style="clear: both;"></div>'
        . $this->lB('httpheader.unknown.todo')
        . '<ul>' . $sLegendeUnknown . '</ul><br>';
}

// --- deprecated header vars
if ($iDeprecated) {
    $aDepr = $oHttpheader->getDeprecatedHeaders();
    $iWarnings += $iDeprecated;
    $sWarnings .= ''
        . '<h4 id="warndeprecated">' . $this->lB('httpheader.header.deprecated') . '</h4>'
        . $oRenderer->renderMessagebox($this->lB('httpheader.warnings.deprecated'), 'warning') . '<br>'
        . $this->_getHistoryCounter(['responseheaderDeprecated'])
        . '<ul>';
    foreach ($aDepr as $sKey => $aHeaderitem) {
        $sWarnings .= '<li><pre><span class="linenumber">' . $aHeaderitem['line'] . '</span> ' . $aHeaderitem['var'] . ': ' . $aHeaderitem['value'] . '</pre></li>';
    }
    $sWarnings .= '</ul><br>';
}
// --- experimental header vars
if ($iExperimental) {
    $aExperimental = $oHttpheader->getExperimentalHeaders();
    $iWarnings += $iExperimental;
    $sWarnings .= ''
        . '<h4 id="warnexperimental">' . $this->lB('httpheader.header.experimental') . '</h4>'
        . $oRenderer->renderMessagebox($this->lB('httpheader.warnings.experimental'), 'warning') . '<br>'
        . $this->_getHistoryCounter(['responseheaderExperimental'])
        . '<ul>';
    foreach ($aExperimental as $sKey => $aHeaderitem) {
        $sWarnings .= '<li><pre><span class="linenumber">' . $aHeaderitem['line'] . '</span> ' . $aHeaderitem['var'] . ': ' . $aHeaderitem['value'] . '</pre></li>';
    }
    $sWarnings .= '</ul><br>';
}

// --- unwanted header vars
$aWarnheader = $oHttpheader->getUnwantedHeaders();
if (is_array($aWarnheader) && count($aWarnheader)) {
    // $iWarnings+=count($aWarnheader);
    $iWarnings += $iUnwanted;
    $sWarnings .= ''
        . '<h4 id="warnunwanted">' . str_replace('<br>', ' ', $this->lB('httpheader.header.unwanted')) . '</h4>'
        . $oRenderer->renderMessagebox($this->lB('httpheader.warnings.unwanted'), 'warning') . '<br>'
        . $this->_getHistoryCounter(['responseheaderUnwanted']);
    foreach ($aWarnheader as $sKey => $aHeaderitem) {
        $sWarnings .= $bShowTiles
            ? $oRenderer->renderTileBar(
                $oRenderer->renderTile('warning', $aHeaderitem['var'], $aHeaderitem['value'])
            )
            : '';
        $sLegendeWarn .= '<li>'
            . $this->lB('httpheader.' . strtolower($aHeaderitem['var']) . '.description')
            . (isset($aHeaderitem['regex']['unwantedregex'])
                ? '<pre><span class="linenumber">' . $aHeaderitem['line'] . '</span> ' . $aHeaderitem['var'] . ': '
                . preg_replace('/(' . $aHeaderitem['regex']['unwantedregex'] . ')/i', '<span class="error">$1</span>', $aHeaderitem['value'])
                . '</pre>'
                // .'<code>'.print_r($aHeaderitem['regex']['unwantedregex'], 1).'</code>'
                : '<pre><span class="linenumber">' . $aHeaderitem['line'] . '</span> ' . $aHeaderitem['var'] . ': ' . $aHeaderitem['value'] . '</pre>'
            )
            . '<br>'
            . '</li>';
    }
    $sWarnings .= '</ul>'
        . '<div style="clear: both;"></div>'
        . '<ul>' . $sLegendeWarn . '</ul>';
}
// --- common but non-standard header vars
if ($iNonStandard) {
    $aNonStdHeader = $oHttpheader->getNonStandardHeaders();
    $iWarnings += $iNonStandard;
    $sWarnings .= ''
        . '<h4 id="warnnonstandard">' . $this->lB('httpheader.header.non-standard') . '</h4>'
        . $this->_getHistoryCounter(['responseheaderNonStandard'])
        . $oRenderer->renderMessagebox($this->lB('httpheader.warnings.non-standard'), 'warning')
        . '<ul>';
    foreach ($aNonStdHeader as $sKey => $aHeaderitem) {
        /*
            * TODO: add translation texts for all non-standard header variables
        $sWarnings.='<li>'
                . $this->lB('httpheader.'.strtolower($aHeaderitem['var']).'.description')
                . '<pre>['.$aHeaderitem['line'].'] '.$aHeaderitem['var'].': '.$aHeaderitem['value'].'</pre><br>'
                . '</li>'
                ;
            */
        $sWarnings .= '<li><pre><span class="linenumber">' . $aHeaderitem['line'] . '</span> ' . $aHeaderitem['var'] . ': ' . $aHeaderitem['value'] . '</pre></li>';
    }
    $sWarnings .= '</ul><br>';
}

// --- no caching?
if (!isset($aFoundTags['cache'])) {
    $iWarnings++;

    $sWarnings .= '<h4 id="warnnocache">' . str_replace('<br>', ' ', $this->lB('httpheader.header.cache')) . '</h4>'
        . $oRenderer->renderMessagebox($this->lB('httpheader.warnings.nocache'), 'warning') . '<br>'
        /*
            . $oRenderer->renderTileBar(
                $oRenderer->renderTile('warning', $this->lB('httpheader.header.cache'), $oRenderer->renderShortInfo('miss'), '', '')
            )
        */
        . '<div style="clear: both;"></div>';
}

// --- no compression?
if (!isset($aFoundTags['compression'])) {
    $iWarnings++;
    $sWarnings .= '<h4 id="warnnocompression">' . str_replace('<br>', ' ', $this->lB('httpheader.header.compression')) . '</h4>'
        . $oRenderer->renderMessagebox($this->lB('httpheader.warnings.nocompression'), 'warning') . '<br>'
        /*
            .$oRenderer->renderTileBar(
                $oRenderer->renderTile('warning', $this->lB('httpheader.header.compression'), $oRenderer->renderShortInfo('miss'), '', '')
        )
        . '<div style="clear: both;"></div>'
        */;
}


// ----------------------------------------------------------------------
// security header
// ----------------------------------------------------------------------

$sLegendeSecOk = '';
$sLegendeSecMiss = '';
$iFoundSecHeader = 0;
$iWarnSecHeader = 0;
$iErrorSecHeader = 0;
foreach ($aSecHeader as $sVar => $aData) {
    if ($aData) {
        $iFoundSecHeader++;
        $bHasBadValue = in_array('badvalue', $aData['tags']);
        $iWarnSecHeader += $bHasBadValue ? 1 : 0;
        // $sSecOk.='<li><a href="#" onclick="return false;" class="tile ok" title="'.$this->lB('httpheader.'.$sVar.'.description').'">' . $aData['var'].'<br>'.$aData['value'].'<br><strong>'.$oRenderer->renderShortInfo('found').'</strong></a></li>';
        $sLegendeSecOk .= ''
            . $oRenderer->renderMessagebox($sVar, $bHasBadValue ? 'warning' : 'ok')
            // . $oRenderer->renderShortInfo($aData ? 'found': 'miss') . ' <strong>' . $sVar. '</strong><br>'
            . $this->lB('httpheader.' . $sVar . '.description') . '<br>'
            . '<pre><span class="linenumber">' . $aData['line'] . '</span> '
            . ($bHasBadValue
                ?  preg_replace('/(' . $aData['regex']['badvalueregex'] . '[a-z0-9]*)/i', '<span class="error">$1</span>', $aData['value'])
                : $aData['var'] . ': ' . $aData['value']
            )
            . '</pre>'
            . '<br>';
    } else {
        // $sSecMiss.='<li><a href="#" onclick="return false;" class="tile"    title="'.$this->lB('httpheader.'.$sVar.'.description').'">' . $sVar.'<br><br><strong>'.$oRenderer->renderShortInfo('miss').'</strong></a></li>';
        $iErrorSecHeader++;
        $sLegendeSecMiss .= ''
            . $oRenderer->renderMessagebox($sVar, 'error')
            // .$oRenderer->renderShortInfo($aData ? 'found': 'miss'). ' <strong>' . $sVar. '</strong><br>'
            . $this->lB('httpheader.' . $sVar . '.description') . '<br><br>';
    }
}


// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------

$sReturn .= '<h3>' . sprintf($this->lB('httpheader.warnings'), $iWarnings) . '</h3>'
    . ($iWarnings
        ? $sWarnings
        : '<ul class="tiles warnings">'
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
    . $this->_getHtmlchecksChart(count($aSecHeader), $iWarnSecHeader, $iErrorSecHeader)
    . $this->_getHistoryCounter(['responseheaderSecurity'])
    . '<div style="clear: both;"></div>'
    . ($sLegendeSecOk ? '<h4>' . $this->lB('httpheader.securityheaders.found') . '</h4>' . $sLegendeSecOk : '')
    . ($sLegendeSecMiss ? '<h4>' . $this->lB('httpheader.securityheaders.notfound') . '</h4>' . $sLegendeSecMiss : '');

// $sStartUrl=$this->aProfile['searchindex']['urls2crawl'][$sUrl][0];^$sReturn.=$sStartUrl.'<br>';

return $sReturn;
