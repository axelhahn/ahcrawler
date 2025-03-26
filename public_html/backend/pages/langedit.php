<?php
/**
 * page analysis :: compare lang texts
 */

if (!$this->_requiresPermission("globaladmin")){
    return include __DIR__ . '/error403.php';
}
$oRenderer=new ressourcesrenderer($this->_sTab);
$sReturn = '';
$aTexts=[];
$aFiles=[];
$aLangkeys=[];


// ----------------------------------------------------------------------
// get lang obj to edit
// ----------------------------------------------------------------------

$aObjects=[
    'backend',
    'frontend',
    'public',
];
$sLangobject=$this->_getRequestParam('object') ? $this->_getRequestParam('object') : $aObjects[0];
if (array_search($sLangobject, $aObjects)===false){
    $sLangobject=$aObjects[0];
}

// ----------------------------------------------------------------------
// lang object navi
// ----------------------------------------------------------------------
$sObjNavi='';
$aObjOptions=[];
foreach($aObjects as $sLangobjName){
    $aOptionfield=[
        'value'=>$sLangobjName,
        'label'=>$sLangobjName,
    ];
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

// ----------------------------------------------------------------------
// handle POST requests
// ----------------------------------------------------------------------
if(isset($_POST) && count($_POST) && isset($_POST['object']) && isset($_POST['key']) ){
    
    $sMyKey=$_POST['key'];
    foreach($_POST['text'] as $sMyLang=>$sNewText){
        $sJsonfile=$aLangfiles[$sMyLang];
        $aLangData = json_decode(file_get_contents($sJsonfile), true);
        
        $sStatus='';
        if(!isset($aLangData[$sMyKey])){
            $sStatus=sprintf($this->lB('langedit.save.create'), $sMyKey, $sMyLang);
        } else if($aLangData[$sMyKey]!=$sNewText){
            $sStatus=sprintf($this->lB('langedit.save.update'), $sMyKey, $sMyLang);
        }
        if($sStatus){
            $sMyVersion=$this->aAbout['version'];
            $sBakfile= str_replace('.json', '.json._orig_'.$sMyVersion.'_.bak', $sJsonfile);
            if(!file_exists($sBakfile)){
                copy($sJsonfile, $sBakfile);
            }
            if(file_exists($sBakfile)){
                $aLangData[$sMyKey]=$sNewText;
                if (file_put_contents($sJsonfile, json_encode($aLangData, JSON_PRETTY_PRINT))){
                    $sReturn.=$oRenderer->renderMessagebox($sStatus, 'ok');
                } else {
                    $sReturn.=$oRenderer->renderMessagebox($sStatus, 'error');
                }
            } else {
                $sReturn.=$oRenderer->renderMessagebox(sprintf($this->lB('langedit.save.no-bakfile'), $sBakfile), 'error');
            }
        } else {
            $sReturn.=$oRenderer->renderMessagebox(sprintf($this->lB('langedit.save.skip'), $sMyKey, $sMyLang), 'warning');
        }
    }
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
$aLangOptions1=[];
$aLangOptions2=[];
foreach($aLangkeys as $sMylang){
    $aOptionfield1=[
        'value'=>$sMylang,
        'label'=>$sMylang,
    ];
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

    . $oRenderer->oHtml->getTag('label', ['for'=>'sellangobj', 'label'=>$this->lB('langedit.object')])
    . $oRenderer->oHtml->getFormSelect([
        'id'=>'sellangobj', 
        'name'=>'object',
        'onchange'=>'submit()',
        ], $aObjOptions)

    . $oRenderer->oHtml->getTag('label', ['for'=>'sellang1', 'label'=>$this->lB('langedit.lang1')])
    . $oRenderer->oHtml->getFormSelect([
        'id'=>'sellang1', 
        'name'=>'lang1',
        'onchange'=>'submit()',
        ], $aLangOptions1)

    . $oRenderer->oHtml->getTag('label', ['for'=>'sellang2', 'label'=>$this->lB('langedit.lang2')])
    . $oRenderer->oHtml->getFormSelect([
        'id'=>'sellang2', 
        'name'=>'lang2',
        'onchange'=>'submit()',
        ], $aLangOptions2)
. '</div>'
;


// ----------------------------------------------------------------------
// generate table
// ----------------------------------------------------------------------

$aTbl=[];
$sTableId='tblLangtexts';
$sTablePrefix='tblLangtexts.';

$aWarnings=[];

$sJsData=[];
// foreach ($aTexts as $sKey=>$aAllLangTxt){
foreach (array_keys($aTexts) as $sKey){
    $aTr=[];
    $sDivId='key-'.md5($sKey);
    $aTr['id']='<div id="'.$sDivId.'" class="editrow" data-key="'.$sKey.'">'.$sKey.'</div>';
    $iLang=1;
    
    foreach([$sLang1, $sLang2] as $sLang){
        if(isset($aTexts[$sKey][$sLang])){
            $sLabel=htmlentities($aTexts[$sKey][$sLang]);
            $sJsData[$sKey][$sLang]=$aTexts[$sKey][$sLang];
        } else {
            $sLabel='<div class="message-error">'.sprintf($this->lB('langedit.miss'), $sKey).'</div>';
            $sLabel=$oRenderer->renderMessagebox(sprintf($this->lB('langedit.miss'), $sKey), 'error');
            // $aTr['id']='<div id="'.$sDivId.'" class="message-error" data-key="'.$sKey.'">'.$sKey.'</div>';
            $aWarnings[]=$sLang.': <a class="scroll-link" href="#'.$sDivId.'">'.$sKey.'</a>';
            $sJsData[$sKey][$sLang]='';
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
                $aWarnings[]=sprintf($this->lB('langedit.count-specifiers'), $sSpec, $sLang1, $iCountLang1, $iCountLang2, $sLang2)
                        . ' - <a class="scroll-link" href="#'.$sDivId.'">'.$sKey.'</a>';
                $aTr['id']='<div id="'.$sDivId.'"  class="editrow message-error" data-key="'.$sKey.'">'.$sKey.'<br>'.sprintf($this->lB('langedit.count-specifiers'), $sSpec, $sLang1, $iCountLang1, $iCountLang2, $sLang2).'</div>';
            }
        }
    }

    $aTbl[]=$aTr;
}
// echo '<pre>script var aLang='. htmlentities(json_encode($sJsData,JSON_PRETTY_PRINT)).';</pre>';

// ----------------------------------------------------------------------
// form
// ----------------------------------------------------------------------

$sForm='<form class="pure-form pure-form-aligned" method="POST" action="?'.$_SERVER['QUERY_STRING'].'">'
    . '<input type="hidden" name="object" value="'.$sLangobject.'">'
    . '<input type="hidden" name="lang1" value="'.$sLang1.'">'
    . '<input type="hidden" name="lang2" value="'.$sLang2.'">'
    . '<input type="hidden" id="lang-key" name="key" value="[key]"><br>'
    . '<div class="pure-control-group">'
        . $oRenderer->oHtml->getTag('label', ['for'=>'text1', 'label'=>$sLang1, 'style'=>'min-width: 0; width: 4em;'])
        . $oRenderer->oHtml->getTag('textarea', [
            'id'=>'lang-text1', 
            'name'=>'text['.$sLang1.']',
            'size'=>'120',
            'cols'=>'120',
            'rows'=>'3',
            'value'=>''
            ])
        . '</div>'
    . '<div class="pure-control-group">'
        . $oRenderer->oHtml->getTag('label', ['for'=>'text2', 'label'=>$sLang2, 'style'=>'min-width: 0; width: 4em;'])
        . $oRenderer->oHtml->getTag('textarea', [
            'id'=>'lang-text2', 
            'name'=>'text['.$sLang2.']',
            'size'=>'120',
            'cols'=>'120',
            'rows'=>'3',
            'value'=>''
            ])
        . '</div>'
    . '<br><hr>'
    . '<div>'
        . '<div style="float: right">'
            . $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.close') . $this->lB('button.close'), 'class'=>'pure-button button-default', 'onclick'=>'return hideModal();'])
            . ' '
            . $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.save') . $this->lB('button.save'), 'class'=>'pure-button button-secondary'])
        . '</div>'
        . $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.up'), 'class'=>'pure-button button-default', 'onclick'=>'hideModal(); return prevRow();'])
        . ' '
        . $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.down'), 'class'=>'pure-button button-default', 'onclick'=>'hideModal(); return nextRow();'])
    . '</div>'
    . '</form>'
;

// ----------------------------------------------------------------------
// page content
// ----------------------------------------------------------------------

// $sReturn .= $this->_getSimpleHtmlTable($aTbl, true)
$sReturn .= ''
    . '<h3>'.$this->lB('langedit.label').'</h3>'
    . '<p>'.$this->lB('langedit.intro').'</p><br>'
    . '<form class="pure-form pure-form-aligned" method="GET" action="?'.$_SERVER['QUERY_STRING'].'">'
        
        // default GET params
        . $oRenderer->oHtml->getTag('input', [
            'type'=>'hidden',
            'name'=>'page',
            'value'=>$this->_getRequestParam('page'),
            ], false)

        // navigation
        . $sObjNavi

    . '</form>'

    // data
    . (count($aWarnings) 
            ? $oRenderer->renderMessagebox('<ol class="error"><li>'.implode('</li><li>', $aWarnings).'</li></ol>', 'error')
            : ($sLang1===$sLang2
                ? $oRenderer->renderMessagebox($this->lB('langedit.equal-langs'), 'warning')
                : $oRenderer->renderMessagebox($this->lB('langedit.keysok'), 'ok')
               )
      )
    . '<br>'
    . $this->_getHtmlTable($aTbl, $sTablePrefix, $sTableId)
    // . $this->_getSimpleHtmlTable($aTbl, true, $sTableId)
       
    .'<div id="form2edittemplate">'
    . '</div>'
    . '
        <script>
            var jsData='.json_encode($sJsData).';
            var sClassEdit="mark";
            
            var myid = false;
            var oRow=false;

            function trClean(){
                $("tr").each( function(){
                    $(this).removeClass(sClassEdit);
                });
            }

            function nextRow(){
                var oNext=$(oRow).next();
                if(oNext){
                    oNext.click();
                }
                return false;
            }
            function prevRow(){
                var oNext=$(oRow).prev();
                if(oNext){
                    oNext.click();
                }
                return false;
            }
            function markByLabel(sLabel){
                trClean();
                $("div[data-key=\'"+sLabel+"\']").parent().parent().addClass(sClassEdit);
            }
            

            $("#'.$sTableId.' tbody").on("click", "tr", function () {

                oRow=$(this);
                trClean();
                $(oRow).addClass(sClassEdit);
                myid = $(".editrow", this).attr("data-key");
                var langTxt1=((jsData[myid] && jsData[myid].'.$sLang1.') ? jsData[myid].'.$sLang1.' : "");
                var langTxt2=((jsData[myid] && jsData[myid].'.$sLang2.') ? jsData[myid].'.$sLang2.' : "");

                // ensure that the form and ids exist
                modalDlg_setContent(\''.$sForm.'\');
                modalDlg_setTitle(myid);

                $("#lang-key").val(myid);
                $("#lang-text1").val(langTxt1);
                $("#lang-text2").val(langTxt2);

                showModalWindow();
                return false;
            });

            '.(isset($_POST['key'])
                ? 'markByLabel("'.$_POST['key'].'");'
                : ''
            ).'
        </script>
    '
;

return $sReturn;
