<?php
/**
 * page about
 */
$oRenderer=new ressourcesrenderer();

$sReturn = '';


require_once __DIR__ . '/../../vendor/ahwebinstall/ahwi-installer.class.php';

$aDirs=[
    'download'=>[
        'root'=>dirname(dirname(__DIR__)),
        'zip' =>dirname(dirname(__DIR__)).'/tmp/__latest.zip',
    ],
    'git'=>[
        'root'=>dirname(dirname(dirname(__DIR__))),
        'zip' =>false,
    ]
];

$oInstaller=new ahwi([
    'product'=>$this->aAbout['product'].' v'.$this->aAbout['version'],
    'source'=>$this->oUpdate->getDownloadUrl(),
    'md5'=>$this->oUpdate->getChecksumUrl(),
    'installdir'=>$aDirs['git']['root'],
    'tmpzip'=>$aDirs['git']['zip'],
    'checks'=>$this->aAbout['requirements'],
]);

// to get all known vcs:
// $aTmpVcs=$oInstaller->vcsDetect();

$bIsGit=$oInstaller->vcsDetect('git');
if($bIsGit){
    $aSteps=[
        'welcome',
        'gitinfo',
        'gitpull',
    ];
} else {
    $oInstaller=new ahwi([
        'product'=>$this->aAbout['product'].' v'.$this->aAbout['version'],
        'source'=>$this->oUpdate->getDownloadUrl(),
        'md5'=>$this->oUpdate->getChecksumUrl(),
        'installdir'=>$aDirs['download']['root'],
        'tmpzip'=>$aDirs['download']['zip'],
        'checks'=>$this->aAbout['requirements'],
    ]);
    $sZipfile=$aDirs['download']['zip'];
    $sTargetPath=$aDirs['download']['root'];
    $aSteps=[
        'welcome',
        'info',
        'download',
        'extract',
    ];
}

// $sReturn.= '<pre>'.$oInstaller->dumpCfg().'</pre>';

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
$sBtnBack=$this->_getButton([
    'href' => $sBackUrl,
    'class' => 'pure-button',
    'label' => 'button.back',
    'popup' => false
]).' ';
$sBtnNext=$this->_getButton([
    'href' => $sNextUrl,
    'class' => 'button-secondary',
    'label' => 'button.continue',
    'popup' => false
]).' ';
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
        $iCountUnused=count($oCdn->getFilteredLibs(['islocal'=>1,'isunused'=>1]));

        $sFoundError=$this->oUpdate->getError();
        $bHasUpdate=$this->oUpdate->hasUpdate();
        
        $sCssBtnHome=$bHasUpdate ? 'pure-button' : 'button-secondary';
        $sCssBtnContinue=!$bHasUpdate ? 'pure-button' : 'button-secondary';

        $sReturn .= '<p>'
            .($sFoundError
                ? $oRenderer->renderMessagebox($sFoundError, 'error').'<br>'
                    . $this->lB('update.welcome.available-check').'<br></br><br>'
                :
                    ($bHasUpdate || 0
                        ?  
                            $this->_getSimpleHtmlTable(
                                [
                                    [$this->lB('update.welcome.version-on-client'),  $this->oUpdate->getClientVersion()],
                                    [$this->lB('update.welcome.version-latest'),     $this->oUpdate->getLatestVersion()],
                                ]
                            )
                            . '<br>' . $oRenderer->renderMessagebox(sprintf($this->lB('update.welcome.available-yes') , $this->oUpdate->getLatestVersion()), 'warning')
                            . '<br>'
                        :  
                            $oRenderer->renderMessagebox($this->lB('update.welcome.available-no'), 'ok')
                            .'<br>'
                            .'<br>'
                        
                            .($iCountUnused 
                                ? sprintf($this->lB('update.welcome.unusedLibs'), $iCountUnused).'<br><br>' 
                                    .$oRenderer->oHtml->getTag('a', [
                                        'href' => '?page=vendor',
                                        'class' => 'pure-button',
                                        'title' => $this->lB('nav.vendor.hint'),
                                        'label' => $this->_getIcon('vendor'). $this->lB('nav.vendor.label') ,
                                    ]).'<br><br><br><br><br>'
                                : ''
                            )        
                    )
            )
             . '<div>'
             
                // --- buttons 
                . $this->_getButton([
                    'href' => '?',
                    'class' => $sCssBtnHome,
                    'label' => 'button.home',
                    'popup' => false
                ])
                . ' '
                . $this->_getButton([
                    'href' => $sNextUrl,
                    'class' => $sCssBtnContinue,
                    'label' => 'button.continue',
                    'popup' => false
                ])
                . ' '
                .$this->_getButton([
                    'href' => $sNextUrl.'&docontinue=1',
                    'class' => $sCssBtnContinue,
                    'label' => 'button.updatesinglestep',
                    'popup' => false
                ])
            
            
            . '</div>'
            . '</p>'
            ;
        break;

    // ---------- GIT
    case 'gitinfo':
            $sReturn .= ''
                . '<p>'
                . $this->lB('update.gitinfo.steps')
                . '</p>'
                .'<br>'
                . $sBtnBack.$sBtnNext.$sScriptContinue
                ;
            break;
    case 'gitpull':
        // $aUpdateInfos=getUpdateInfos(true);
            $aResult=$oInstaller->vcsUpdate('git');
            if($aResult===false){
                // wrong vcs or git not detected locally
                $sReturn.=$sBtnBack.$this->lB('update.gitpull.no-git-cli');

            } else {
                $iRc=$aResult[0];
                $sOutput=implode('<br>', $aResult[1]);
                if ($iRc==0){
                    $sReturn.=$this->lB('update.gitpull.ok').'<br>'
                        . $this->lB('update.refresh-info').'<br>'
                        . '<br>'
                        . $sBtnBack.$sBtnNext.$sScriptContinue
                        ;
                } else {
                    $sReturn.=$oRenderer->renderMessagebox($this->lB('update.gitpull.failed'), 'error')
                        .$sBtnBack
                        ;
                }
            }
        break;
        
    // ---------- DOWNLOAD ZIP AND EXTRACT
    case 'info':
        $sReturn .= ''
            . '<p>'
            . $this->lB('update.steps')
            . '</p>'
            .'<ol>'
                . '<li>'.sprintf($this->lB('update.steps.downloadurl'), ($this->oUpdate->getDownloadUrl() ?: '❌'), $sZipfile) . '</li>'
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
                $sReturn.=$oRenderer->renderMessagebox($this->lB('update.download.failed'), 'error')
                    .$sBtnBack
                    ;
            }        
        break;
    case 'extract':
        // $aUpdateInfos=getUpdateInfos(true);
            ob_start();
            $bInstall=$oInstaller->install();
            $sOutput.=str_replace("\n", "<br>", ob_get_contents());
            ob_end_clean();
            
            if ($bInstall){
                $sReturn.=$this->lB('update.extract.ok').'<br>'
                    . $this->lB('update.refresh-info').'<br>'
                    . '<br>'
                    . $sBtnBack.$sBtnNext.$sScriptContinue
                    ;
            } else {
                $sReturn.=$oRenderer->renderMessagebox($this->lB('update.extract.failed'), 'error')
                    . $sBtnBack
                    ;
            }
        break;

    default:
        $sReturn.='UNKNOWN STEP: ['.htmlentities($sStepName).']<br>';
    break;
}

$sReturn.=$sOutput ? '<br><br><hr><br>'.$this->lB('update.output').':<br><pre class="output">'.$sOutput.'</pre>' : '';

return $sReturn;
