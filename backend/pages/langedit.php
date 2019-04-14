<?php
/**
 * page analysis :: compare lang texts
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sReturn = '';
$aTexts=array();
$aFiles=array();
$aLangkeys=array();

$sPrefix='backend';


// ----------------------------------------------------------------------
// load all lang files
// ----------------------------------------------------------------------

foreach(glob(dirname(__DIR__).'/../lang/'.$sPrefix.'.*.json') as $sJsonfile){
    $aData = json_decode(file_get_contents($sJsonfile), true);
    // $sReturn = '<pre>'.print_r($aData, 1).'</pre>';
    $sKey2=str_replace($sPrefix.'.','',basename($sJsonfile));
    $sKey2=str_replace('.json','',$sKey2);
    $aFiles[]=basename($sJsonfile);
    $aLangkeys[]=$sKey2;
    if($aData){
        foreach($aData as $sKey=>$sText){
            $aTexts[$sKey][$sKey2]=$sText;
        }
    }
}
// ksort($aTexts);

// ----------------------------------------------------------------------
// render table
// ----------------------------------------------------------------------

$aTbl=array(
    array_merge(array('#'), $aLangkeys)
);
$aTbl=array();
foreach ($aTexts as $sKey=>$aAllLangTxt){
    $aTr=array();
    $aTr['#']=$sKey;
    foreach($aLangkeys as $sLang){
        $aTr[$sLang]=isset($aAllLangTxt[$sLang]) 
                ? htmlentities($aAllLangTxt[$sLang])
                : '<div style="background:#fcc;">MISS</div>'
                ;
    }
    $aTbl[]=$aTr;
}

$sTableId='tblLangtexts';
// $sReturn .= $this->_getSimpleHtmlTable($aTbl, true)
$sReturn .= $this->_getHtmlTable($aTbl, '', $sTableId)
    . $oRenderer->renderInitDatatable('#' . $sTableId, array('lengthMenu'=>array(array(-1))))
;

return $sReturn;
