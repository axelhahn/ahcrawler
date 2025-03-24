<?php
/**
 * page userprofile
 */

$sReturn = '';

$sGlobalTable = '';
$sGroupTable = '';




$sGroups = '';
foreach ($this->acl->getGroups() as $sGroup) {
    $sGroups .= $this->_getIcon('usergroup') . $sGroup . '<br>';
}

$sGroups = $sGroups ? "<h4>" . $this->lB("userprofile.groups") . "</h4>$sGroups" : '';
// --- global permissions

$aGlobalPerms = $this->acl->getMyGlobalPermissions();
// print_r($aGlobalPerms);

if (count($aGlobalPerms)) {
    $sGlobal = '';
    foreach ($aGlobalPerms as $sPerm => $bActive) {
        $sGlobal .= $bActive
            ? (($sGlobal ? ', ' : "") . "<button class=\"pure-button button-success\">X</button> $sPerm")
            : '-'
        ;
    }
    $sGlobal = "<h4>" . $this->lB("userprofile.globalperms") . "</h4>
        You have the following global permissions on each project:<br>
        <br>
        $sGlobal
        ";

}

// --- app specific permissions

$aPerms = ["admin", "manager", "viewer"];
$sTHead = '<tr><th>Name</th>';
foreach ($aPerms as $sPerm) {
    $sTHead .= '<th>' . $sPerm . '</th>';
}
$sTHead .= '</tr>';

foreach ($this->_getProfiles() as $iGroupId => $sGroupName) {
    if ($this->acl->is($iGroupId)) {
        $sGroupTable .= '<tr><td>' . $this->_getIcon('project') . $sGroupName . '</td>';
        foreach ($aPerms as $sPerm) {
            $sShownPermission = $this->acl->is("{$iGroupId}_{$sPerm}")
                ? "<button class=\"pure-button button-success\">X</button>"
                : "<button class=\"pure-button button-error\">-</button>"
            ;
            $sGroupTable .= '<td>' . $sShownPermission . '</td>';
        }
        $sGroupTable .= '</tr>';
    }
}
$sGroupTable = $sGroupTable
    ? '<h4>' . $this->lB("userprofile.webperms") . '</h4>
        <table class="pure-table pure-table-horizontal datatable dataTable no-footer">
        <thead>' . $sTHead . '</thead>
        <tbody>' . $sGroupTable . '
        </tbody>
        </table><br><br>'
    : ''
;

// --- output

$sReturn .= $this->_getButton([
    'href' => 'javascript:history.back();',
    // 'class' => 'button-secondary',
    'popup' => false,
    'label' => 'button.back'
])
    . ' '
    . $this->_getButton([
        'href' => './?page=logoff',
        'class' => 'button-secondary',
        'label' => 'button.logoff',
        'popup' => false
    ])
    . '<h3>' . $this->_getIcon('userprofile') . $this->_getUser() . '</h3>'
    . $sGroups
    . $sGlobal
    . $sGroupTable
    /*
    .'<pre>Groups:<br>'
    .print_r($this->acl->getGroups(), 1)
    .'<hr>'
    .print_r($this->_getProfiles(), 1)
    .'</pre>'
    */
;

$sReturn .= '<hr>'
    . $this->_getButton([
        'href' => './?page=logoff',
        'class' => 'button-secondary',
        'label' => 'button.logoff',
        'popup' => false
    ]);

return $sReturn;


// ignore everything below

// otpauth://totp/Example:alice@google.com?secret=JBSWY3DPEHPK3PXP&issuer=Example

/*

  MODES:
  (1) no User is authenticated --> abort with message
  (2) user has no TOTP --> offer "add totp"
  (3) totp exists: show details and offer delete




$sInstance='ahCrawler%20'.$_SERVER['SERVER_NAME'];
$sUser=isset($aOptions['options']['auth']['user']) ? $aOptions['options']['auth']['user'] : '[nouser]';
$sSecret="GASWY3DPEHPK3PXP";
$sIssuer="Axel%20Hahn";

$sTotpUrl="otpauth://totp/$sInstance:$sUser?secret=$sSecret=&issuer=$sIssuer";


$sReturn = '
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>


Instance: '.$sInstance.'<br>
user: '.$sUser.'<br>
<br>
url: '.$sTotpUrl.'<br>
<br>


<div id="qrcode"></div>

<script>
window.addEventListener("load", () => {
  // var url="otpauth://totp/Example:alice@google.com?secret=JBSWY3DPEHPK3PXP&issuer=Example";
  var url="'.$sTotpUrl.'";
  var qrc = new QRCode(document.getElementById("qrcode"), url);
});
</script>
';

return $sReturn;

*/
