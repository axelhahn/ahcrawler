<?php
/**
 * page userprofile
 */

$sReturn = '';

if ($this->_getRequestParam('user')) {
  if($this->acl->setUser($this->_getRequestParam('user'))){
    include(__DIR__ . '/userprofile.php');
    return $sReturn;
  }
}

function userlink($sUser){
  return '<a href="'.$_SERVER['REQUEST_URI'].'&user='.$sUser.'">'.$sUser.'</a>';
}



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
          ? '<td colspan="'.(count($aPerms)+2).'"><strong>@global<strong></td></tr><tr><td></td><td>'.$this->_getIcon('userprofile') . userlink($sUser).'</td>'
          : '<td></td><td>'.$this->_getIcon('userprofile') . userlink($sUser).'</td>'
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
  $iCountUser=0;
  foreach($aAppPerms as $sUser => $aRoles){
    $iCountUser++;
        $sGroupTable.='<tr>'
          .($iCountUser==1
            ? '<td colspan="'.(count($aPerms)+2).'"><strong>'.$this->_getIcon('project') . $sApp.'<strong></td></tr><tr><td></td><td>'.$this->_getIcon('userprofile') . userlink($sUser).'</td>'
            : '<td></td><td>'.$this->_getIcon('userprofile') . userlink($sUser).'</td>'
          )
          ;
        foreach($aPerms as $sPerm){
            $sGroupTable.='<td>'.($aRoles[$sPerm]??'' ? '<span class="pure-button button-success"> X </span>' : '-').'</td>';
        }
        $sGroupTable.='</tr>';
  }
  if(count($aAppPerms)==0){
    $sGroupTable.='<tr><td colspan="'.(count($aPerms)+2).'">'.$this->_getIcon('project') . $sApp.'</td></tr>';

  }
}


$sGroupTable=$sGroupTable ? '<table class="pure-table pure-table-horizontal datatable dataTable no-footer">
        <thead>'.$sTHead.'</thead>
        <tbody>'.$sGroupTable.'
        </tbody>
        </table><br><br>' 
    : 'NONE'
    ;

// output
$sReturn.=''
  .'<h3>'.$this->lB("userprofile.globalperms").'</h3>'
  .$sAdminTable
  .'<h3>'.$this->lB("userprofile.webperms").'</h3>'
    .$sGroupTable
  ;


return $sReturn;
