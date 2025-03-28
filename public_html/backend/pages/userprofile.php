<?php
/**
 * page userprofile
 */

$sReturn = '';

$sGroupTable = '';

$sGlobal = '';

$sGroups = '';
foreach ($this->acl->getGroups() as $sGroup) {
    $sGroups .= '<li>'. $sGroup . '</li>';
}

$sGroups = $sGroups ? "<h4>" . $this->_getIcon('usergroup').$this->lB("userprofile.groups") . "</h4><ul>$sGroups</ul>" : '';

// --- global permissions

$aGlobalPerms = $this->acl->getMyGlobalPermissions();
// print_r($aGlobalPerms);


if (count($aGlobalPerms)) {
    foreach ($this->acl->getPermNames() as $sPerm){
    // foreach ($aGlobalPerms as $sPerm => $bActive) {
        $sGlobal .= $aGlobalPerms[$sPerm]??false
            ? "<button class=\"pure-button button-success\">X</button> <strong>$sPerm</strong> "
            : "<button class=\"pure-button\">-</button> $sPerm "
        ;
    }
    $sGlobal = "<h4>" . $this->lB("userprofile.globalperms") . "</h4>
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
    ? '<h4>' . $this->_getIcon('project') . $this->lB("userprofile.webperms") . '</h4>
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
        ]);

return $sReturn;
