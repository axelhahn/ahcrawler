<?php
/**
 * page :: test icons
 */

$sReturn='';


/*

see https://fontawesome.com/docs/web/setup/upgrade/

fas --> fa-solid
far --> fa-regular

*/
$aFaMappings=[
    'v5value'=>'v6value',
    'far fa-file-sound' => 'fa-solid fa-volume-high',
];

$aIcons=[
    // on page "about"
    'about --> testing'=>'fa-solid fa-stethoscope',
    'about --> translation'=>'fa-solid fa-flag',
];

$oRenderer=new ressourcesrenderer($this->_sTab);

$aAllIcons=[];
$aAllIcons=array_merge($aAllIcons, $this->getIcons());
$aAllIcons=array_merge($aAllIcons, $oRenderer->getIcons());
$aAllIcons=array_merge($aAllIcons, $aIcons);


$sReturn.='<h3>Icons Fontawesome v5 -> v6</h3>';

$aTable=[];
$aRows=[];
$aRows[]=['Key', 'class', 'icon'];
foreach($aAllIcons as $sKey => $sCssClass){
    $aRows[]=[
        $sKey,
        $sCssClass,
        '<i class="'.$sCssClass.'"></i>',
    ];
}
$aTable=$aRows;

$sReturn.=$this->_getSimpleHtmlTable($aTable, 1);


return $sReturn;
