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
    .'<script>$(document).ready( function () {$(\'#'.$sTableId.'\').DataTable( {\'lengthMenu\': [[-1], [\'All\']] } );} );</script>'
    ;
// $sReturn .= '<pre>'.print_r($aTexts, 1).'</pre>';

/*
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAuth.'username', 'label'=>$this->lB('setup.section.auth.user')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixAuth.'user', 
                    'name'=>'options[auth][user]',
                    'value'=>isset($aOptions['options']['auth']['user']) ? $aOptions['options']['auth']['user'] : '',
                    ), false)
                . '</div>'

*/


// ----------------------------------------------------------------------
// javascript: define datatables
// ----------------------------------------------------------------------
$sReturn.='<script>$(document).ready(function () {'
        . '$(\'#tableCrawlerErrors\').DataTable({"aaSorting":[[1,"asc"]]});'
        . '$(\'#tableShortTitles\').DataTable({"aaSorting":[[1,"asc"]]});'
        . '$(\'#tableShortDescr\').DataTable({"aaSorting":[[1,"asc"]]});'
        . '$(\'#tableShortKeywords\').DataTable({"aaSorting":[[1,"asc"]]});'
        . '$(\'#tableLongLoad\').DataTable({"aaSorting":[[1,"desc"]]});'
        . '$(\'#tableLargePages\').DataTable({"aaSorting":[[1,"desc"]]});'
        . '} );'
        . '</script>';

return $sReturn;
