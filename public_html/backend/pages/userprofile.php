<?php
/**
 * page userprofile
 */
// otpauth://totp/Example:alice@google.com?secret=JBSWY3DPEHPK3PXP&issuer=Example

/*

  MODES:
  (1) no User is authenticated --> abort with message
  (2) user has no TOTP --> offer "add totp"
  (3) totp exists: show details and offer delete

*/


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