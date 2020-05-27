<?php
/**
 * page about
 */
$oRenderer=new ressourcesrenderer();
$aSteps=array(
    'welcome',
    'info',
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
    'product'=>$this->aAbout['product'].' v'.$this->aAbout['version'],
    'source'=>$sLatestUrl,
    'installdir'=>$sTargetPath,
    'tmpzip'=>$sZipfile,
    'checks'=>$this->aAbout['requirements'],
));



$sStepName=$this->_getRequestParam('doinstall') ? $this->_getRequestParam('doinstall') : $aSteps[0];
$bAutoContinue=$this->_getRequestParam('docontinue') ? (int)$this->_getRequestParam('docontinue') : false;

$iStep=array_search($sStepName, $aSteps);
if($iStep===false){
    $sStepName=$aSteps[0];
    $iStep=0;
}
$sBackUrl=$iStep > 1
        ? '?page=update&doinstall='.$aSteps[($iStep-1)]
        : '?page=update'
        ;
$sNextUrl=$iStep < (count($aSteps)-1)
        ? '?page=update&doinstall='.$aSteps[($iStep+1)]
        : '?page=update&ts='.date('U')
        ;
$sBtnBack=$this->_getButton(array(
    'href' => $sBackUrl,
    'class' => 'pure-button',
    'label' => 'button.back',
    'popup' => false
)).' ';
$sBtnNext=$this->_getButton(array(
    'href' => $sNextUrl,
    'class' => 'button-secondary',
    'label' => 'button.continue',
    'popup' => false
)).' ';
$sScriptContinue=$bAutoContinue ? 
        '<br><br>'
            .$oRenderer->renderMessagebox($this->lB("update.singlestepupdate"), 'warning')
            . '<script>location.href="'.$sNextUrl.'&docontinue=1";</script>' 
        : ''
    ;


$sOutput='';
$sReturn .= '<h3>'. $this->lB('update.'.$sStepName.'.label') . '</h3>'
    . '<p>'. $this->lB('update.'.$sStepName.'.description') . '</p><hr>'
    ;
switch ($sStepName) {
    case 'welcome':
        // force update check to refresh the locally cached version infos
        $this->oUpdate->getUpdateInfos(true);
        
        global $oCdn;
        $iCountUnused=count($oCdn->getFilteredLibs(array('islocal'=>1,'isunused'=>1)));
        $bHasUpdate=$this->oUpdate->hasUpdate();
        
        $sCssBtnHome=$bHasUpdate ? 'pure-button' : 'button-secondary';
        $sCssBtnContinue=!$bHasUpdate ? 'pure-button' : 'button-secondary';

        $sReturn .= '<p>'
            .($bHasUpdate || 0
                ?  
                    $this->_getSimpleHtmlTable(
                        array(
                            array($this->lB('update.welcome.version-on-client'),  $this->oUpdate->getClientVersion()),
                            array($this->lB('update.welcome.version-latest'),     $this->oUpdate->getLatestVersion()),
                        )
                    )
                    . '<br>' . $this->_getMessageBox($oRenderer->renderShortInfo('warn') . sprintf($this->lB('update.welcome.available-yes') , $this->oUpdate->getLatestVersion()), 'warning')
                    . '<br>'
                    . '<div>'
                :  
                    $this->_getMessageBox($oRenderer->renderShortInfo('found'). $this->lB('update.welcome.available-no'), 'ok')
                    .'<br>'
                    .'<br>'
                
                    .($iCountUnused 
                        ? sprintf($this->lB('update.welcome.unusedLibs'), $iCountUnused).'<br><br>' 
                            .$oRenderer->oHtml->getTag('a', array(
                                'href' => '?page=vendor',
                                'class' => 'pure-button',
                                'title' => $this->lB('nav.vendor.hint'),
                                'label' => $this->_getIcon('vendor'). $this->lB('nav.vendor.label') ,
                            )).'<br><br><br><br><br>'
                        : ''
                    )
                    . '<div>'
        
             )
            
            // --- buttons 
            . $this->_getButton(array(
                'href' => '?',
                'class' => $sCssBtnHome,
                'label' => 'button.home',
                'popup' => false
            ))
            . ' '
            . $this->_getButton(array(
                'href' => $sNextUrl,
                'class' => $sCssBtnContinue,
                'label' => 'button.continue',
                'popup' => false
            ))
            . ' '
            .$this->_getButton(array(
                'href' => $sNextUrl.'&docontinue=1',
                'class' => $sCssBtnContinue,
                'label' => 'button.updatesinglestep',
                'popup' => false
            ))
            . '</div>'
            . '</p>'
            ;
        break;
    case 'info':
        $sReturn .= ''
            . '<p>'
            . $this->lB('update.steps')
            . '</p>'
            .'<ol>'
                . '<li>'.sprintf($this->lB('update.steps.downloadurl'), $this->oUpdate->getDownloadUrl(), $sZipfile) . '</li>'
                . '<li>'.sprintf($this->lB('update.steps.extractto'), $sTargetPath) . '</li>'
            . '</ol>'
            .'<br>'
            . $sBtnBack.$sBtnNext.$sScriptContinue
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
                        . $sBtnBack.$sBtnNext.$sScriptContinue
                        ;
            } else {
                $sReturn.=$sBtnBack.$this->lB('update.download.failed');
            }        
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
                    . $sBtnBack.$sBtnNext.$sScriptContinue
                    ;
            } else {
                $sReturn.=$sBtnBack.$this->lB('update.extract.failed');
            }
        break;


    default:
        break;
}

$sReturn.=$sOutput ? '<br><br><hr><br>'.$this->lB('update.output').':<br><pre class="output">'.$sOutput.'</pre>' : '';

return $sReturn;
