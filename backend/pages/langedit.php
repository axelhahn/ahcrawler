<?php
/**
 * page analysis :: compare lang texts
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sReturn = '';
$aTexts=array();
$aFiles=array();
$aLangkeys=array();


// ----------------------------------------------------------------------
// get lang obj to edit
// ----------------------------------------------------------------------

$aObjects=array(
    'frontend',
    'backend',
);
$sLangobject=$this->_getRequestParam('object') ? $this->_getRequestParam('object') : $aObjects[0];
if (array_search($sLangobject, $aObjects)===false){
    $sLangobject=$aObjects[0];
}

// ----------------------------------------------------------------------
// lang object navi
// ----------------------------------------------------------------------
$sObjNavi='';
$aObjOptions=array();
foreach($aObjects as $sLangobjName){
    $aOptionfield=array(
        'value'=>$sLangobjName,
        'label'=>$sLangobjName,
    );
    if($sLangobjName===$sLangobject){
        $aOptionfield['selected']='selected';
    }
    $aObjOptions[]=$aOptionfield;
}


// ----------------------------------------------------------------------
// load all lang files of selected lang object
// ----------------------------------------------------------------------

foreach(glob(dirname(__DIR__).'/../lang/'.$sLangobject.'.*.json') as $sJsonfile){
    $sKey2=str_replace($sLangobject.'.','',basename($sJsonfile));
    $sKey2=str_replace('.json','',$sKey2);

    $aLangfiles[$sKey2]=$sJsonfile;
    $aLangkeys[]=$sKey2;
}

// get lang 1 and lang 2 for columns to show
$aData1=false;
$aData2=false;
if (count($aLangfiles)>0){
    $sLang1=$this->_getRequestParam('lang1') ? $this->_getRequestParam('lang1') : $aLangkeys[0];
    $sLang2=$this->_getRequestParam('lang2') ? $this->_getRequestParam('lang2') : false;
    
    if (array_search($sLang1, $aLangkeys)===false){
        $sLang1=$aLangkeys[0];
    }
    $aData1 = json_decode(file_get_contents($aLangfiles[$sLang1]), true);
    
    // if (count($aLangkeys)>1){
        if (array_search($sLang2, $aLangkeys)===false){
            $sLang2=($sLang1!==$aLangkeys[0]) ? $aLangkeys[0] : $aLangkeys[1];
        }
        $aData2 = json_decode(file_get_contents($aLangfiles[$sLang2]), true);
    // }
}

// 2 option arrays for lang select boxes
$aLangOptions1=array();
$aLangOptions2=array();
foreach($aLangkeys as $sMylang){
    $aOptionfield1=array(
        'value'=>$sMylang,
        'label'=>$sMylang,
    );
    $aOptionfield2=$aOptionfield1;
    if($sMylang===$sLang1){
        $aOptionfield1['selected']='selected';
        if(isset($aData1['id'])){
            $aOptionfield1['label'].=' ('.$aData1['id'].')';
        }
    }
    if($sMylang===$sLang2){
        $aOptionfield2['selected']='selected';
        if(isset($aData2['id'])){
            $aOptionfield2['label'].=' ('.$aData2['id'].')';
        }
    }
    
    $aLangOptions1[]=$aOptionfield1;
    $aLangOptions2[]=$aOptionfield2;
}
    
// echo "DEBUG: lang1 = $sLang1 ".count($aData1)." ... lang2 = $sLang2 ".count($aData2)."<br>";
// echo "DEBUG: ".print_r($aLangOptions1, 1)."<br>";
// echo "DEBUG: ".print_r($aLangOptions2, 1)."<br>";

if($sLang1){
    foreach(json_decode(file_get_contents($aLangfiles[$sLang1]), true) as $sKey=>$sText){
        $aTexts[$sKey][$sLang1]=$sText;
    }
}
if($sLang2){
    foreach(json_decode(file_get_contents($aLangfiles[$sLang2]), true) as $sKey=>$sText){
        $aTexts[$sKey][$sLang2]=$sText;
    }
}


// ksort($aTexts);


// ----------------------------------------------------------------------
// select boxes: object and 2 langs
// ----------------------------------------------------------------------

$sObjNavi.='<div class="pure-control-group">'

    . $oRenderer->oHtml->getTag('label', array('for'=>'sellangobj', 'label'=>$this->lB('langedit.object')))
    . $oRenderer->oHtml->getFormSelect(array(
        'id'=>'sellangobj', 
        'name'=>'object',
        'onchange'=>'submit()',
        ), $aObjOptions)

    . $oRenderer->oHtml->getTag('label', array('for'=>'sellang1', 'label'=>$this->lB('langedit.lang1')))
    . $oRenderer->oHtml->getFormSelect(array(
        'id'=>'sellang1', 
        'name'=>'lang1',
        'onchange'=>'submit()',
        ), $aLangOptions1)

    . $oRenderer->oHtml->getTag('label', array('for'=>'sellang2', 'label'=>$this->lB('langedit.lang2')))
    . $oRenderer->oHtml->getFormSelect(array(
        'id'=>'sellang2', 
        'name'=>'lang2',
        'onchange'=>'submit()',
        ), $aLangOptions2)
. '</div>'
;


// ----------------------------------------------------------------------
// generate table
// ----------------------------------------------------------------------

$aTbl=array();
$sTableId='tblLangtexts';
$sTablePrefix='tblLangtexts.';

$aWarnings=array();

// foreach ($aTexts as $sKey=>$aAllLangTxt){
foreach (array_keys($aTexts) as $sKey){
    $aTr=array();
    $sDivId='key-'.md5($sKey);
    $aTr['id']='<div id="'.$sDivId.'">'.$sKey.'</div>';
    $iLang=1;
    
    foreach(array($sLang1, $sLang2) as $sLang){
        if(isset($aTexts[$sKey][$sLang])){
            $sLabel=htmlentities($aTexts[$sKey][$sLang]);
        } else {
            $sLabel='<div class="message-error">'.sprintf($this->lB('langedit.miss'), $sKey).'</div>';
            $aTr['id']='<div id="'.$sDivId.'" class="message-error">'.$sKey.'</div>';
            $aWarnings[]=$sLang.': <a class="scroll-link" href="#'.$sDivId.'">'.$sKey.'</a>';
        }
        
        $aTr[$iLang++]=$sLabel;
    }

    // check count of "%s"
    $sLangTxt1=isset($aTexts[$sKey][$sLang1]) ? $aTexts[$sKey][$sLang1] : false;
    $sLangTxt2=isset($aTexts[$sKey][$sLang2]) ? $aTexts[$sKey][$sLang2] : false;
    if($sLangTxt1 && $sLangTxt1){
        // count of specifiers - see https://www.php.net/manual/en/function.sprintf.php
        foreach (str_split("bcdeEfFgGosuxX") as $sSpec){
            $iCountLang1=substr_count($sLangTxt1, '%'.$sSpec);
            $iCountLang2=substr_count($sLangTxt2, '%'.$sSpec);
            if($iCountLang1!=$iCountLang2){
                $aWarnings[]='[%'.$sSpec.'] ' . $sLang1.': '.$iCountLang1.'/ '.$sLang2.': '.$iCountLang2.' - <a class="scroll-link" href="#'.$sDivId.'">'.$sKey.'</a>';
                $aTr['id']='<div id="'.$sDivId.'" class="message-error">'.$sKey.'</div>';
            }
        }
    }

    $aTbl[]=$aTr;
}

// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------


// $sReturn .= $this->_getSimpleHtmlTable($aTbl, true)
$sReturn .= ''
    . '<h3>'.$this->lB('langedit.label').'</h3>'
    . '<h4>'.$this->lB('langedit.settings').'</h4>'
    . '<form class="pure-form pure-form-aligned" method="GET" action="?'.$_SERVER['QUERY_STRING'].'">'
        
        // default GET params
        . $oRenderer->oHtml->getTag('input', array(
            'type'=>'hidden',
            'name'=>'page',
            'value'=>$this->_getRequestParam('page'),
            ), false)

        // navigation
        . $sObjNavi

    . '</form>'

    // data
    . '<h4>'.$this->lB('langedit.data').'</h4>'
    . (count($aWarnings) 
            ? '<div class="message message-error">'.$this->lB('langedit.warnings') . '<ol class="error"><li>'.implode('</li><li>', $aWarnings).'</li></ol>'
            : ($sLang1===$sLang2
                ? '<div class="message message-warning">'.$this->lB('langedit.equal-langs')
                : '<div class="message message-ok">'.$this->lB('langedit.keysok')
               )
      )
    . '</div><br>'
    . $this->_getHtmlTable($aTbl, $sTablePrefix, $sTableId)
    // . $this->_getSimpleHtmlTable($aTbl, true, $sTableId)
    . $oRenderer->renderInitDatatable('#' . $sTableId, array('lengthMenu'=>array(array(-1))))
;

return $sReturn;
