<?php
/**
 * page about
 */
$oRenderer=new ressourcesrenderer();
$aSteps=array(
    'welcome',
    'download',
    'extract',
);
$sReturn = '';

require_once __DIR__ . '/../../classes/ahwi-installer.class.php';
$sApproot=dirname(dirname(__DIR__));


$sZipfile = $sApproot.'/tmp/__latest.zip';

$sTargetPath = $sApproot;
// $sTargetPath = $sApproot.'/tmp';

$sLatestUrl=$this->oUpdate->getDownloadUrl();

$oInstaller=new ahwi(array(
    'product'=>'dummy',
    'source'=>$sLatestUrl,
    'installdir'=>$sTargetPath,
    'tmpzip'=>$sZipfile,
    'checks'=>array(
        'phpversion'=>'5.3',
        'phpextensions'=>array('curl', 'zip')
    ),
));



$sStep=isset($_GET['doinstall']) ? $_GET['doinstall'] : $aSteps[0];

$iStep=array_search($sStep, $aSteps);
if($iStep===false){
    $sStep=$aSteps[0];
    $iStep=0;
}
$sNextUrl=$iStep < (count($aSteps)-1)
        ? '?page=update&doinstall='.$aSteps[($iStep+1)]
        : '?page=about'
        ;
$sBtnNext=$this->_getButton(array(
    'href' => $sNextUrl,
    'class' => 'button-secondary',
    'label' => 'button.continue',
    'popup' => false
));


$sOutput='';
$sReturn .= '<h3 id="h3' . md5('update') . '">'. $this->lB('update.'.$sStep.'.label') . '</h3>'
    . '<p>'. $this->lB('update.'.$sStep.'.description') . '</p><hr>'
    ;
switch ($sStep) {
    case 'welcome':
        $sReturn .= '<p>'
            .($this->oUpdate->hasUpdate()
                ?  
                    $this->_getSimpleHtmlTable(
                        array(
                            array('installiert',  $this->oUpdate->getClientVersion()),
                            array('aktuell',      $this->oUpdate->getLatestVersion()),
                        )
                    )
                    . '<br>' . $this->_getMessageBox($oRenderer->renderShortInfo('warn') . sprintf($this->lB('update.welcome.available-yes') , $this->oUpdate->getLatestVersion()), 'warning')
                :  
                    $this->_getMessageBox($oRenderer->renderShortInfo('found'). $this->lB('update.welcome.available-no'), 'ok')
        
             )
            . '</p>'
            . '<p>'
            . $this->lB('update.steps')
            . '</p>'
            .'<ol>'
                . '<li>'.sprintf($this->lB('update.steps.downloadurl'), $this->oUpdate->getDownloadUrl(), $sZipfile) . '</li>'
                . '<li>'.sprintf($this->lB('update.steps.extractto'), $sTargetPath) . '</li>'
            . '</ol>'
            .'<br>'
            . $sBtnNext
            ;
        break;
    case 'download':
        // $aUpdateInfos=getUpdateInfos(true);
            if (file_exists($sZipfile)) {
                unlink($sZipfile);
            }
            
            ob_start();
            $bDownload=$oInstaller->download(false);
            $sOutput.=str_replace("\n", "<br>", ob_get_contents());
            ob_end_clean();
            if($bDownload){
                $sReturn.='<br><strong>'.$this->lB('update.download.done').'</strong><br><br>'
                        . sprintf($this->lB('update.download.extractto'), $oInstaller->getInstalldir())
                        . '<br><br>'
                        . $sBtnNext
                        ;
            } else {
                $sReturn.=$this->lB('update.download.failed');
            }        
            ;
        break;
    case 'extract':
        // $aUpdateInfos=getUpdateInfos(true);
            ob_start();
            $bInstall=$oInstaller->install();
            $sOutput.=str_replace("\n", "<br>", ob_get_contents());
            ob_end_clean();
            
            if ($bInstall){
                $sReturn.=$this->lB('update.extract.ok')
                    . '<br><br>'
                    . $sBtnNext
                    ;
            } else {
                $sReturn.=$this->lB('update.extract.failed');
            }
            ;
        break;


    default:
        break;
}

$sReturn.=$sOutput ? '<br><br><hr><br>'.$this->lB('update.output').':<br><pre class="output">'.$sOutput.'</pre>' : '';

return $sReturn;
