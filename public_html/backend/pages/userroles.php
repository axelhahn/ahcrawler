<?php
/**
 * page userprofile
 */

$sReturn = '';

// if (!$this->_requiresPermission("manager")){
//   return include __DIR__ . '/error403.php';
// }

// ------------------------------------------------------------
// if a linked user id was clicked -> show profile of selected user

if ($this->_getRequestParam('user')) {

  $this->_requiresPermission('globaladmin');

  if($this->acl->setUser($this->_getRequestParam('user'))){
    include(__DIR__ . '/userprofile.php');
    return $sReturn;
  } else {
    // return include(__DIR__ . '/error403.php');
  }
}

// ------------------------------------------------------------
// FUNCTIONS
// ------------------------------------------------------------

function userlink($sUser, $bLink=false){
  
  return $bLink 
    ? '<a href="'.$_SERVER['REQUEST_URI'].'&user='.$sUser.'">'.$sUser.'</a>'
    : $sUser
    ;
}


// ------------------------------------------------------------
// PAGE
// ------------------------------------------------------------

$aPerms=$this->acl->getPermNames();


$sTHead='<tr><th>Name</th><th>User</th>';
foreach($aPerms as $sPerm){
    $sTHead.='<th>'.$sPerm.'</th>';
}
$sTHead.='</tr>';

$sAdminTable='';
$sGroupTable='';

$aAppPerms=$this->acl->listUsers("global");
$iCountUser=0;
foreach($aAppPerms as $sUser => $aRoles){
  $iCountUser++;
      $sAdminTable.='<tr>'
        .($iCountUser==1
          ? '<td colspan="'.(count($aPerms)+2).'"><strong>@global<strong></td></tr><tr><td></td><td>'.$this->_getIcon('userprofile') . userlink($sUser, $this->acl->isGlobalAdmin() ).'</td>'
          : '<td></td><td>'.$this->_getIcon('userprofile') . userlink($sUser, $this->acl->isGlobalAdmin()).'</td>'
        )
        ;
      foreach($aPerms as $sPerm){
          $sAdminTable.='<td>'.($aRoles[$sPerm]??'' ? '<span class="pure-button button-success"> X </span>' : '-').'</td>';
      }
      $sAdminTable.='</tr>';
}
$sAdminTable=$sAdminTable ? '<table class="pure-table pure-table-horizontal datatable dataTable no-footer">
        <thead>'.$sTHead.'</thead>
        <tbody>'.$sAdminTable.'
        </tbody>
        </table><br><br>' 
    : 'NONE'
    ;


foreach($this->_getProfiles() as $iGroupId => $sApp){
  $aAppPerms=$this->acl->listUsers($iGroupId);
  if($this->_requiresPermission("viewer", $iGroupId)){
    
    $iCountUser=0;
    foreach($aAppPerms as $sUser => $aRoles){
      $iCountUser++;
          $sGroupTable.='<tr>'
            .($iCountUser==1
              ? '<td colspan="'.(count($aPerms)+2).'"><strong>'.$this->_getIcon('project') . $sApp.'<strong></td></tr><tr><td></td><td>'.$this->_getIcon('userprofile') . userlink($sUser, $this->acl->isGlobalAdmin()).'</td>'
              : '<td></td><td>'.$this->_getIcon('userprofile') . userlink($sUser, $this->acl->isGlobalAdmin()).'</td>'
            )
            ;
          foreach($aPerms as $sPerm){
              $sGroupTable.='<td>'.($aRoles[$sPerm]??'' ? '<span class="pure-button button-success"> X </span>' : '-').'</td>';
          }
          $sGroupTable.='</tr>';
    }
  }
  if(count($aAppPerms)==0 && $this->_requiresPermission("viewer", $iGroupId)){
    $sGroupTable.='<tr><td colspan="'.(count($aPerms)+2).'">'.$this->_getIcon('project') . $sApp.'</td></tr>';

  }
}


$sGroupTable=$sGroupTable ? '<table class="pure-table pure-table-horizontal datatable dataTable no-footer">
        <thead>'.$sTHead.'</thead>
        <tbody>'.$sGroupTable.'
        </tbody>
        </table><br><br>' 
    : ''
    ;

// output
$sReturn.=''
  .'<h3>'.$this->lB("userprofile.globalperms").'</h3>'
  .$sAdminTable
  .($sGroupTable 
    ? '<h3>'.$this->_getIcon('project') .$this->lB("userprofile.webperms").'</h3>'
      .$sGroupTable
    : ''
  )
  ;


return $sReturn;
