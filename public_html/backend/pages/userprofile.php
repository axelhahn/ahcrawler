<?php
/**
 * page userprofile
 */

$oRenderer = new ressourcesrenderer($this->_sTab);
$sReturn = '';

$sGroupTable = '';

$sGlobal = '';

$sGroups = '';
foreach ($this->acl->getGroups() as $sGroup) {
    $sGroups .= '<li>'. $sGroup . '</li>';
}

if(!$this->acl->hasConfig()){
    $sGroups .= $oRenderer->renderMessagebox($this->lB('userprofile.noacl'), 'ok');
}

$sGroups = $sGroups ? 
    "<h3>" . $this->_getIcon('usergroup').$this->lB("userprofile.groups") . "</h3>
        <ul>$sGroups</ul>" 
    : '';

// --- global permissions

$aGlobalPerms = $this->acl->getMyGlobalPermissions();
// echo '<pre>'.print_r($aGlobalPerms, 1).'</pre>';


if (count($aGlobalPerms)) {
    foreach ($this->acl->getPermNames() as $sPerm){
    // foreach ($aGlobalPerms as $sPerm => $bActive) {
        $sGlobal .= $aGlobalPerms[$sPerm]??false
            ? "<button class=\"pure-button button-success\">X</button> <strong>$sPerm</strong> "
            : "<button class=\"pure-button\">-</button> $sPerm "
        ;
    }
    $sGlobal = "<h3>" . $this->lB("userprofile.globalperms") . "</h3>
        ".$this->lB("userprofile.globalperms-hint")."<br>
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

foreach($this->_getProfiles() as $iGroupId => $sApp){
    $aAppPerms=$this->acl->listUsers($iGroupId);
    foreach($aAppPerms as $sUser => $aRoles){
        if($this->_getUser()==$sUser){
          $sGroupTable.='<tr><td><strong>'.$this->_getIcon('project') . $sApp.'<strong></td>';
          foreach($aPerms as $sPerm){
              $sGroupTable.='<td>'.($aRoles[$sPerm]??'' ? '<span class="pure-button button-success"> X </span>' : '-').'</td>';
          }
          $sGroupTable.='</tr>';
        }
    }
  }

$sGroupTable = $sGroupTable
    ? '<h3>' . $this->_getIcon('project') . $this->lB("userprofile.webperms") . '</h3>
        <table class="pure-table pure-table-horizontal datatable dataTable no-footer">
        <thead>' . $sTHead . '</thead>
        <tbody>' . $sGroupTable . '
        </tbody>
        </table>'
    : ''
;

// --- output

$sReturn .= $this->_getButton([
    'href' => 'javascript:history.back();',
    // 'class' => 'button-secondary',
    'popup' => false,
    'label' => 'button.back'
    ])
    . '<h3>' . $this->_getIcon('userprofile') . $this->_getUser() . '</h3>'
    . $sGroups
    . $sGlobal
    . $sGroupTable
    . '<br><br>'
;

$sReturn .= '<br>'
    . $this->_getButton([
        'href' => 'javascript:history.back();',
        // 'class' => 'button-secondary',
        'popup' => false,
        'label' => 'button.back'
        ])
        . ($this->_getUser() !== 'nobody'
        ? ' '.$this->_getButton([
            'href' => './?page=logoff',
            'class' => 'button-error',
            'label' => 'button.logoff',
            'popup' => false
        ])
        : ''
    )
        ;

return $sReturn;
