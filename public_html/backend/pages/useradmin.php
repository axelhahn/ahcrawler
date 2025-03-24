<?php
/**
 * page userprofile
 */

$sReturn = '';




$aPerms=["admin","manager","viewer"];


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
          ? '<td colspan="'.(count($aPerms)+2).'"><strong>@global<strong></td></tr><tr><td></td><td>'.$this->_getIcon('userprofile') . $sUser.'</td>'
          : '<td></td><td>'.$this->_getIcon('userprofile') . $sUser.'</td>'
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
            ? '<td colspan="'.(count($aPerms)+2).'"><strong>'.$this->_getIcon('project') . $sApp.'<strong></td></tr><tr><td></td><td>'.$this->_getIcon('userprofile') . $sUser.'</td>'
            : '<td></td><td>'.$this->_getIcon('userprofile') . $sUser.'</td>'
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
