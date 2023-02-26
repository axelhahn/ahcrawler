<?php

/**
 * page analysis :: Cookies
 */
$oRenderer = new ressourcesrenderer($this->_sTab);
$oHttpheader = new httpheader();


$sReturn = '';
$sReturn .= $this->_getNavi2($this->_getProfiles(), false, '?page=analysis');
$this->setSiteId($this->_sTab);

// ----------------------------------------------------------------------
// Delete cookie file
// ----------------------------------------------------------------------

if (isset($_POST['action'])) {
    // echo '<pre>' . print_r($_POST, 1) . '</pre>';
    if (
            isset($_POST['action']) && $_POST['action'] === "deletecookie" && isset($_POST['profile']) && $_POST['profile'] == $this->_sTab && $this->sCookieFilename && file_exists($this->sCookieFilename)
    ) {
        echo "TODO: remove cookies in " . $this->sCookieFilename . "<br>";
        unlink($this->sCookieFilename);
    }
}

// ----------------------------------------------------------------------
// COOKIES
// ----------------------------------------------------------------------

$aCookies = $oHttpheader->parseCookiefile($this->sCookieFilename);
$iCookieCount = count($aCookies['cookies']);
$sReturn .= ''
        . '<h3>' . sprintf($this->lB('cookies.headline'), $iCookieCount) . '</h3>'
        . '<p>' . $this->lB('cookies.hint') . '</p>'
;

if ($iCookieCount) {
    // $iCookieCount = count($aCookies['cookies']);
    // . $this->renderTile('',            $this->lB('ressources.age-scan'), $this->hrAge(date("U", strtotime($dateLast))), $dateLast, '')

    $sReturn .= ''
        . $oRenderer->renderTileBar(
            $oRenderer->renderTile($iCookieCount ? '' : 'ok', $this->lB('cookies.count'), $iCookieCount, '', '')
            // . $oRenderer->renderTile('', $this->lB('cookies.since'), $oRenderer->hrAge(filectime($this->sCookieFilename)), date('Y-m-d H:i', filectime($this->sCookieFilename)), '')
        )
        .'<div style="clear: both;"></div>'
    ;
    if (count($aCookies['cookies']) > 0) {

        $aTbl = array();
        foreach ($aCookies['cookies'] as $aCookie) {
            $aTbl[] = array(
                'domain' => $aCookie['domain'],
                'path' => $aCookie['path'],
                'name' => $aCookie['name'],
                'value' => '<div style="max-width: 25em;overflow-wrap: break-word; word-wrap: break-word;">' . $aCookie['value'] . '</div>',
                'httponly' => $aCookie['httponly'],
                'secure' => ($aCookie['secure'] === 'TRUE' 
                                ? '<span class="ok">' 
                                // : ($aCookie['secure'] === 'FALSE' ? '<span class="warning">' : '<span>')
                                : '<span class="warning">'
                        )
                        . $aCookie['secure']
                        .'</span>',
                'expiration' => $aCookie['expiration'],
            );
        }
        $sTableId = 'tblSavedCookies';

        $sReturn .= ''
                . $this->_getHtmlTable($aTbl, 'cookies.col-', $sTableId)
                . $this->_getHtmlLegend(array('domain', 'path', 'name', 'value', 'httponly', 'secure', 'expiration'), 'cookies.col-')
                . '<h3>' . $this->lB('cookies.delete') . '</h3>'
                . '<p>' . $this->lB('cookies.delete.hint') . '</p>'
                . '<form class="pure-form pure-form-aligned" method="POST" action="?' . $_SERVER['QUERY_STRING'] . '">'
                . $oRenderer->oHtml->getTag('input', array(
                        'type' => 'hidden',
                        'name' => 'profile',
                        'value' => $this->_sTab,
                    ), 
                    false)
                . $oRenderer->oHtml->getTag('button', array('label' => $this->_getIcon('button.delete') . $this->lB('button.delete'), 'class' => 'pure-button button-error', 'name' => 'action', 'value' => 'deletecookie'))
                . '</form>'
        ;
    }
} else {
    if(isset($aCookies['error']) && $aCookies['error']){
        $sReturn .= $oRenderer->renderMessagebox($this->lB('cookies.file.'.$aCookies['error']), 'error');
    } else {
        $sReturn .= $oRenderer->renderMessagebox($this->lB('cookies.nocookie'), 'ok');
    }
    
}

// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------
return $sReturn;
