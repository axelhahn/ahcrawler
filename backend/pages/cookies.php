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
    echo '<pre>' . print_r($_POST, 1) . '</pre>';
    if (
            isset($_POST['action']) && $_POST['action'] === "deletecookie" && isset($_POST['profile']) && $_POST['profile'] == $this->_sTab && $this->sCcookieFilename && file_exists($this->sCcookieFilename)
    ) {
        echo "TODO: remove cookies in " . $this->sCcookieFilename . "<br>";
        unlink($this->sCcookieFilename);
    }
}

// ----------------------------------------------------------------------
// COOKIES
// ----------------------------------------------------------------------

$aCookies = $oHttpheader->parseCookiefile($this->sCcookieFilename);
$sReturn .= ''
        . '<h3>' . sprintf($this->lB('cookies.headline'), count($aCookies['cookies'])) . '</h3>'
        . '<p>' . $this->lB('cookies.hint') . '</p>'
;

if (file_exists($this->sCcookieFilename)) {
    $iCookieCount = count($aCookies['cookies']);
    // . $this->renderTile('',            $this->lB('ressources.age-scan'), $this->hrAge(date("U", strtotime($dateLast))), $dateLast, '')

    $sReturn .= ''
        . $oRenderer->renderTileBar(
            $oRenderer->renderTile($iCookieCount ? '' : 'ok', $this->lB('cookies.count'), $iCookieCount, '', '')
            // . $oRenderer->renderTile('', $this->lB('cookies.since'), $oRenderer->hrAge(filectime($this->sCcookieFilename)), date('Y-m-d H:i', filectime($this->sCcookieFilename)), '')
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
                                : '<span>'
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
                . $oRenderer->renderInitDatatable('#' . $sTableId, array('lengthMenu'=>array(array(50, -1))))
        ;
    } else {
        $sReturn .= $this->_getMessageBox($this->lB('cookies.nocookie'), 'ok');
    }
} else {
    $sReturn .= $this->_getMessageBox($this->lB('cookies.nofile'), 'warning');
}

// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------
return $sReturn;
