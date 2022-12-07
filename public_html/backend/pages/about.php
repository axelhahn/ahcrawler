<?php
/**
 * page about
 */

/*
require_once __DIR__ . '/../../classes/ahwi-installer.class.php';
$oInstaller=new ahwi(array(
    'product'=>$this->aAbout['product'].' v'.$this->aAbout['version'],
    'source'=>'',
    'installdir'=>'',
    'tmpzip'=>'',
    'checks'=>$this->aAbout['requirements'],
));
$aRequirements=$oInstaller->getRequirements();
echo '<pre>'. print_r($aRequirements, 1).'</pre>';
 */
require_once __DIR__ . '/../../classes/ahwi-installer.class.php';

$sReturn = '';

$aIcons=array(
    'testing'=>'fas fa-stethoscope',
    'translation'=>'fas fa-flag',
);

/*
// see view-source:https://allcontributors.org/docs/en/emoji-key
$aIcons=array(
    'testing'=>'⚠️',
    'translation'=>'🌍',
);
 * 
 */


$sPeople='';

$oRenderer=new ressourcesrenderer($this->_sTab);
if(BACKEND==true){
    // ----------------------------------------------------------------------
    // requirements
    // ----------------------------------------------------------------------
    $oInstaller=new ahwi(array(
        'product'=>$this->aAbout['product'].' v'.$this->aAbout['version'],
        'source'=>'',
        'installdir'=>'',
        'tmpzip'=>'',
        'checks'=>$this->aAbout['requirements'],
    ));
    $aErr=$oInstaller->getRequirementErrors();
    $aRequirements=$oInstaller->getRequirements();
    /*
    $aTableReq=array(
        array(
            $this->lB('installer.requirement.test'),
            $this->lB('installer.requirement.result'),
        )
    );
    $aTableReq=[];
     * 
     */
    $aAllMods=get_loaded_extensions(false);
    asort($aAllMods);        
    if(isset($aRequirements['phpversion'])){
        $aTableReq[]=array(
            sprintf($this->lB('installer.requirement.phpversion'), $aRequirements['phpversion']['required']),
            ($aRequirements['phpversion']['result'] 
                ? $oRenderer->renderShortInfo('found'). $this->lB('installer.requirement-ok') .' ('.$aRequirements['phpversion']['value'].')'
                : $oRenderer->renderShortInfo('miss') . $this->lB('installer.requirement-fail') .' ('.$aRequirements['phpversion']['value'].')'
            ),
        );
    }

    foreach ($aAllMods as $sPhpModule){
        $aTableReq[]=array(
            sprintf($this->lB('installer.requirement.phpextension'), $sPhpModule),
            (isset($aRequirements['phpextensions'][$sPhpModule])
                ? ($aRequirements['phpextensions'][$sPhpModule]['result'] 
                    ? $oRenderer->renderShortInfo('found'). $this->lB('installer.requirement-ok')
                    : $oRenderer->renderShortInfo('miss') . $this->lB('installer.requirement-fail')
                    )
                : ''
                )
        );
    }
}
        
if (isset($this->aAbout['thanks'])){
    foreach ($this->aAbout['thanks'] as $sSection=>$aPeople ){
        if(count($aPeople)){
            $sPeople.='<h4>'
                    .(isset($aIcons[$sSection]) ? '<i class="'.$aIcons[$sSection].'"></i> ': '')
                    // .(isset($aIcons[$sSection]) ? $aIcons[$sSection].' ' : '')
                    .$this->lB('about.contributors.section-'.$sSection)
                    .'</h4>'
                    .'<p>'.$this->lB('about.contributors.section-'.$sSection.'.hint').'</p>'
                    ;
            foreach($aPeople as $aPerson){
                /*
                 * $aPerson looks like that
                array(
                    'label'=>'', 
                    'name'=>'', 
                    'image'=>'', 
                    'url'=>''
                ),
                 */
                $sName=$aPerson['name']?$aPerson['name']:'%';
                $sLinkedName=$aPerson['url']
                        ? $oRenderer->oHtml->getTag('a',array(
                            'href'=>$aPerson['url'],
                            'title'=>$aPerson['url'],
                            'label'=>$sName
                        ))
                        :$sName;
                $sPeople.='<div class="person">'
                        . ($aPerson['image']   ? '<img src="'.$aPerson['image'].'" ><br>' : '')
                        . '<div class="name">'.$sLinkedName.'</div>'
                        . ($aPerson['label']? '<div class="label">'.$aPerson['label'].'</div>':'')
                        . '</div>';
            }
            $sPeople.='<div style="clear: both"></div>';
        }
    }
}
$sReturn.=''
        . '<h3>' . $this->aAbout['product'] . ' ' . $this->aAbout['version'] . ' ('.$this->aAbout['date'].')</h3>'

        // update info
        . '<p>' . $this->lB('about.info') . '</p>'
        . $this->_getLinkAsBox(array(
                'url'=>$this->aAbout['urlDocs'],
                'hint'=>$this->aAbout['urlDocs'],
                'icon'=>$this->_aIcons['res']['docs'],
                'title'=>$this->lB('about.url.docs'),
                'text'=>$this->aAbout['urlDocs'],
            ))
        /*
        . $this->_getLinkAsBox(array(
                'url'=>$this->aAbout['urlHome'],
                'hint'=>$this->aAbout['urlHome'],
                'icon'=>$this->_aIcons['res']['url'],
                'title'=>$this->lB('about.url.project'),
                'text'=>$this->aAbout['urlHome'],
            ))
         */
        . $this->_getLinkAsBox(array(
                'url'=>$this->aAbout['urlSource'],
                'hint'=>$this->aAbout['urlSource'],
                'icon'=>$this->_aIcons['res']['source'],
                'title'=>$this->lB('about.url.source'),
                'text'=>$this->aAbout['urlSource'],
            ))
        .'<div style="clear: both"></div>'
        /*
        . $this->_getSimpleHtmlTable(
                array(
                    array($this->lB('about.url.project'), '<a href="' . $this->aAbout['urlHome'] . '">' . $this->aAbout['urlHome'] . '</a>'),
                    array($this->lB('about.url.docs'), '<a href="' . $this->aAbout['urlDocs'] . '">' . $this->aAbout['urlDocs'] . '</a>'),
                    array($this->lB('about.url.source'), '<a href="' . $this->aAbout['urlSource'] . '">' . $this->aAbout['urlSource'] . '</a>'),
                )
        )
        */

        . ($sPeople 
                ?'<h3>' . $this->lB('about.contributors') . '</h3>' . $sPeople
                : ''
        )
        
        . '<h3>' . $this->lB('about.thanks') . '</h3>'
        . '<p>' . $this->lB('about.thanks-text') . '</p>'
        . $this->_getSimpleHtmlTable(
                array(
                    array($this->lB('about.thanks.chartjs'), '<a href="https://www.chartjs.org/">https://www.chartjs.org/</a>'),
                    array($this->lB('about.thanks.datatables'), '<a href="https://datatables.net/">https://datatables.net/</a>'),
                    array($this->lB('about.thanks.fontawesome'), '<a href="https://fontawesome.com/">https://fontawesome.com/</a>'),
                    array($this->lB('about.thanks.jquery'), '<a href="https://jquery.com/">https://jquery.com/</a>'),
                    array($this->lB('about.thanks.medoo'), '<a href="https://medoo.in/">https://medoo.in/</a>'),
                    array($this->lB('about.thanks.rollingcurl'), '<a href="https://github.com/chuyskywalker/rolling-curl">https://github.com/chuyskywalker/rolling-curl</a>'),
                    array($this->lB('about.thanks.pure'), '<a href="https://purecss.io/">https://purecss.io/</a>'),
                )
        )
        .(isset($aTableReq) && count($aTableReq) 
            ? 
                '<h3>' . $this->lB('about.phpinfo') . '</h3>'
                . '<p>' . $this->lB('about.phpinfo-hint') . '</p>'
                .$this->_getSimpleHtmlTable($aTableReq,1) 
            : ''
         )
        ;
return $sReturn;
