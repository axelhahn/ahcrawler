<?php
/**
 * page analysis :: ressources
 */

if (!$this->_requiresPermission("viewer", $this->_sTab)){
    return include __DIR__ . '/error403.php';
}

$sReturn = '';
$aCounter = [];
$aFilter=['http_code', 'ressourcetype','type', 'content_type'];
$aFields = ['id', 'url', 'siteid', 'http_code', 'ressourcetype', 'type', 'content_type', 'lasterror'];

$aLegendKeys=$aFields;
unset($aLegendKeys[2]); // remove siteid
unset($aLegendKeys[0]); // remove id

$aUrl=[];

// $sSiteId = $this->_getRequestParam('siteid');
$oRessources=new ressources();
$oRenderer=new ressourcesrenderer($this->_sTab);

// ----------------------------------------------------------------------
// handle POST requests
// ----------------------------------------------------------------------

// add profiles navigation
$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '');

if(isset($_POST) && count($_POST) && isset($_POST['blacklistitem']) ){
    $iProfileId=$this->_getTab();
    $this->setSiteId($iProfileId);
    $aOptions = $this->_loadConfigfile();

    if(!isset($this->aProfileSaved['ressources']['blacklist'])){
        $this->aProfileSaved['ressources']['blacklist']=[];
    } 
    if(!array_search($_POST['blacklistitem'] , $this->aProfileSaved['ressources']['blacklist'])===false){
        $sReturn.=$oRenderer->renderMessagebox(sprintf($this->lB('ressources.denylist.save-skip'), $_POST['blacklistitem'], $this->aProfileSaved['label']), 'warning');
    } else {
        $this->aProfileSaved['ressources']['blacklist'][]=$_POST['blacklistitem'];
        $this->aProfileSaved['ressources']['blacklist'] = array_unique($this->aProfileSaved['ressources']['blacklist']);

        $aOptions['profiles'][$iProfileId]=$this->aProfileSaved;
        if ($this->_saveConfig($aOptions)){
            $sReturn.=$oRenderer->renderMessagebox(sprintf($this->lB('ressources.denylist.save-ok'), $_POST['blacklistitem'], $this->aProfileSaved['label']), 'ok');
        } else {
            $sReturn.=$oRenderer->renderMessagebox(sprintf($this->lB('ressources.denylist.save-ok'), $_POST['blacklistitem'], $this->aProfileSaved['label']), 'error');
        }
    }
}



// $aWhere=['siteid' => $this->_sTab, 'isExternalRedirect'=>0];
$aWhere=['siteid' => $this->_sTab];
$aFilterItems=$this->_getRequestParam('filteritem');
if ($aFilterItems){
    $aFilterValues=$this->_getRequestParam('filtervalue');
    for ($i=0; $i<count($aFilterItems); $i++){
        $value=($aFilterValues[$i]==='') ? null : $aFilterValues[$i];
        if(strstr($value, ",")){
            foreach(explode(',', $value) as $singlevalue){
                $aWhere[$aFilterItems[$i]][]=$singlevalue;
            }
            // $aWhere[$aFilterItems[$i]]=($aFilterValues[$i]==='') ? null : $aFilterValues[$i];
        } else {
            $aWhere[$aFilterItems[$i]]=($aFilterValues[$i]==='') ? null : $aFilterValues[$i];
        }
        $aUrl[]=['filteritem'=>$aFilterItems[$i], 'filtervalue'=>$aFilterValues[$i]];
    }
}

// $sReturn.= '<pre>'.print_r($aWhere, 1).'</pre>';

// -- get list of all data
// total count
$iRessourcesCount=$this->getRecordCount('ressources', ['siteid'=>$this->_sTab]);
if(!$iRessourcesCount){
    return $sReturn.='<h3>'.$this->lB("error.not-enough-data").'</h3>'.$oRenderer->renderMessagebox(sprintf($this->lB('ressources.empty'), $this->_sTab), 'warning');
}

// count with applying the filter
$iResCount = $oRessources->getCount($aWhere);

// -- get list of filter data
$aCounter2=[];
foreach ($aFilter as $sKey){
    $aCounter2[$sKey]=$oRessources->getCountsOfRow('ressources', $sKey, $aWhere);
}

// --- output

//
// line with set filters
//
$sSelfUrl='?'.$_SERVER["QUERY_STRING"];
$sBaseUrl='?page='.$this->_getRequestParam('page').'&siteid='.$this->_sTab;
$sFilter='';
$sReport = '';

// --- button bar with filter items (for remove by click)
if (is_array($aFilterItems) && is_array($aFilterValues)){

    for ($i=0; $i<count($aFilterItems); $i++){
        $aRemoveUrl=$aUrl;
        unset($aRemoveUrl[$i]);
        $sUrl=$sBaseUrl;
        foreach($aRemoveUrl as $aItem){
            $sUrl.=$sUrl.='&amp;';
            $sUrl.='filteritem[]='.$aItem['filteritem'].'&amp;filtervalue[]='.$aItem['filtervalue'];
        }
        // $sUrl=str_replace($sRemove, '', $sSelfUrl);
        $sFilter.='<a href="'.$sUrl.'"'
                . ' class="pure-button"'
                . '><span class="varname">'.$this->_getIcon($aFilterItems[$i]).$this->lB('db-ressources.'.$aFilterItems[$i]).'</span> = <span class="value">'.$oRenderer->renderValue($aFilterItems[$i], $aFilterValues[$i]).'</span> .. '
                . $this->_getIcon('button.close')
                . '</a> ';
    }
    $sFilter= ' '.$sFilter.' '
            . ($i>1 ? '<a href="'.$sBaseUrl.'"'
                . ' class="pure-button button-error"'
                . '> '
                . $this->_getIcon('button.close')
                . '</a>'
            : '')
            . '<br><br>';
}

// --- what to create: table or report list
$bShowReport=$this->_getRequestParam('showreport');
$iReportCounter=0;
$bIgnoreLimit=$this->_getRequestParam('ignorelimit');
$sFilterArea='';
$aTable = [];


$bShowRessourcetable=($this->_getRequestParam('showtable') || !$bShowReport);
if ($iResCount>$this->iLimitRessourcelist && !$bIgnoreLimit){
    $bShowReport=false;
    $bShowRessourcetable=false;
}

if ($iResCount) {



    if ($bShowReport || $bShowRessourcetable){
        $aRessourcelist = $oRessources->getRessources($aFields, $aWhere, ["url"=>"ASC"]);
        //
        // loop for table or report items 
        //
        foreach ($aRessourcelist as $aRow) {

            // --- generate report
            if ($bShowReport){

                $iReportCounter++;
                $sReport.=''
                        .'<div class="counter">'. $iReportCounter.'</div>'
                        // . '<div style="clear: left;"></div>'
                        //  . '<pre>'.print_r($aRow, 1).'</pre>'
                        .$oRenderer->renderReportForRessource($aRow, true, true)
                        ;
            }
            // --- generate table view
            if ($bShowRessourcetable){
                $aRow['url'] = '<a href="?page=ressourcedetail&id=' . $aRow['id'] . '&siteid=' . $this->_sTab.'">'. str_replace('/', '/&shy;', htmlentities($aRow['url'])).'</a>';

                /*
                $aRow['actions'] = $this->_getButton([
                    'href' => 'overlay.php?action=ressourcedetail&id=' . $aRow['id'] . '&siteid=' . $this->_sTab . '',
                    'class' => 'button-secondary',
                    'label' => 'button.view'
                ]);
                 * 
                 */
                $aRow['ressourcetype'] = $oRenderer->renderArrayValue('ressourcetype', $aRow);
                $aRow['type'] = $oRenderer->renderArrayValue('type', $aRow);
                $aRow['http_code'] = $oRenderer->renderArrayValue('http_code', $aRow);

                unset($aRow['id']);
                unset($aRow['siteid']);
                unset($aRow['lasterror']);
                $aTable[] = $aRow;
            }

        }

        if ($bShowReport){
            $sReport='<br>'.$sReport;
            $sReport.='';
        }
        //
        // table array for ressources
        //
        if(count($aRessourcelist)){
            $aTableFilter[]=['<strong>'.$this->lB('ressources.total').'</strong>', '' ,'<strong>'.count($aRessourcelist).'</strong>'];
        }
    }

    /*
    foreach ($aFilter as $sKey){
        $sRessourcelabel=(array_key_exists($sKey, $this->_aIcons['cols']) ? '<i class="'.$this->_aIcons['cols'][$sKey].'"></i> ' : '') . $sKey;
        $aTableFilter[]=['<strong>'.$sRessourcelabel.'</strong>', '', ''];
        foreach ($aCounter2[$sKey] as $aCounterItem){
            $sCounter=$aCounterItem[$sKey];
            $iValue=$aCounterItem['count'];
            $aTableFilter[]=[
                '', 
                (count($aCounter2[$sKey])>1
                    ? '<a href="'.$sSelfUrl.'&amp;filteritem[]='.$sKey.'&amp;filtervalue[]='.$sCounter.'">'
                        .$oRenderer->renderValue($sKey, $sCounter)
                        .'</a>'
                    : $oRenderer->renderValue($sKey, $sCounter)
                )
                , 
                $iValue
            ];
        }
    }
     */
    
    
    foreach ($aFilter as $sKey){
        $sRessourcelabel=$this->_getIcon($sKey).$this->lB('db-ressources.'.$sKey);
        $aTableF=[];
        $aTableF[]=['<strong>'.$sRessourcelabel.'</strong>', ''];
        foreach ($aCounter2[$sKey] as $aCounterItem){
            $sCounter=$aCounterItem[$sKey];
            $iValue=$aCounterItem['count'];
            $sLinkLabel=$oRenderer->renderValue($sKey, $sCounter);
            $sLinkLabel=$sLinkLabel ? $sLinkLabel : '[ ]';
            $sCounter=$sCounter ? $sCounter : ' ';
            $aTableF[]=[
                (count($aCounter2[$sKey])>1
                    ? '<a href="'.$sSelfUrl.'&amp;filteritem[]='.$sKey.'&amp;filtervalue[]='.urlencode($sCounter).'">'
                        .$sLinkLabel
                        .'</a>'
                    : $sLinkLabel
                )
                , 
                $iValue
            ];
        }
        $sFilterArea.='<div style="float: left; margin-right: 1em;">'
                . $this->_getSimpleHtmlTable($aTableF, 1)
                .'</div>';
    }
    $sFilterArea.='<div style="clear: both"></div>';
}

// --- output

$sBtnReport=$this->_getButton([
    'href'=>$this->_getQs([
        'showreport'=>1,
        'showtable'=>0,
        'tab'=>$this->_sTab,
    ]).'#restable',
    'class'=>'button-secondary',
    'label'=>'ressources.showreport',
    'popup' => false
]);
$sBtnTable=$this->_getButton([
    'href'=>$this->_getQs([
        'showreport'=>0,
        'showtable'=>1,
        'tab'=>$this->_sTab,
    ]).'#restable',
    'class'=>'button-secondary',
    'label'=>'ressources.showtable',
    'popup' => false
]);


$sReturn.='<h3>' . $this->lB('ressources.overview') . '</h3>'
        . $oRenderer->renderRessourceStatus(). '<div style="clear: both;"></div>'
        . '<p>'.$this->lB('ressources.overview.intro').'</p>'
        .($iRessourcesCount==1
                ? $oRenderer->renderMessagebox($this->lB('ressources.only-one'), 'warning').'<br>'
                : ''
        )
        
        ;


    $sReturn.=''
            // .$this->_getSimpleHtmlTable($aTableFilter)
            . '<div class="actionbox">'
                . $this->_getIcon('filter')
                . $this->lB('ressources.filter')
                . ($sFilter ? $sFilter : '<br><br>')
                . $sFilterArea
            . '</div>'
            .($iResCount
                ? $this->_getHtmlLegend($aFilter, 'db-ressources.')
                : ':-/'
             )
            
            
            ;

    if($iResCount){
        $sReturn.='<h3 id="restable">' . $this->lB('ressources.list') . '</h3>';
        if ($bShowRessourcetable){
            $sReturn.='<p>'
                    . $sBtnReport.'<br><br>'
                    . $this->lB('ressources.list.intro')
                    . '</p>'
                . $this->_getHtmlTable($aTable, "db-ressources.", 'tableResData')
                . $this->_getHtmlLegend($aLegendKeys, "db-ressources.")
                ;
        } 
        if ($bShowReport){
            $sForm='<form class="pure-form pure-form-aligned" method="POST" action="?'.$_SERVER['QUERY_STRING'].'">'
                        . '<p>'.$this->lB('ressources.denylist.intro').'</p>'
                        . '<div class="pure-control-group">'
                            // . $oRenderer->oHtml->getTag('label', ['for'=>'input-url', 'label'=>'TODO', 'style'=>'min-width: 0; width: 4em;'])
                            . $oRenderer->oHtml->getTag('input', [
                                'id'=>'input-url', 
                                'name'=>'blacklistitem',
                                'size'=>'100',
                                'style'=>'width:100%',
                                'value'=>''
                                ])
                            . '</div>'
                        . '<ul>'
                            . '<li>'.$this->lB('ressources.denylist.hint-scan').'</li>'
                            . '<li>'.$this->lB('ressources.denylist.hint-start').'</li>'
                            . '<li>'.$this->lB('ressources.denylist.hint-protocol').'</li>'
                            . '<li>'.$this->lB('ressources.denylist.hint-end').'</li>'
                            . '<li>'.$this->lB('ressources.denylist.hint-profile').'</li>'
                        . '</ul>'
                        . '<hr>'
                        . '<div>'
                            . '<div style="float: right">'
                                . $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.close') . $this->lB('button.close'), 'class'=>'pure-button button-default', 'onclick'=>'return hideModal();'])
                                . ' '
                                . $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.save') . $this->lB('button.save'), 'class'=>'pure-button button-secondary'])
                            . '</div>'
                            . $oRenderer->oHtml->getTag('button', ['label'=>'^http[s].*', 'id'=>'btnswitch', 'class'=>'pure-button', 'onclick'=>'return switchProto();'])
                        . '</div><br><br>'
                . '</form>'                    
                ;
            $sReturn.='<p>'
                    . $sBtnTable.'<br><br>'
                    . $this->lB('ressources.report.intro')
                    . '</p>'
                    . $sReport
                    . '<script>
                        
                        var sUrl = false;
                        var sProto = false;
                        var sBoth="http[s]*://";
                        var sLabelButton = false;
                        
                        $(".divRessourceAsLine .blacklist").on("click", function () {

                            sUrl = $(this).attr("data-url");
                            sUrl = "^"+sUrl+"$";
                            
                            sProto=sUrl.indexOf("http\:\/\/")>=0
                                ? "http" 
                                : sUrl.indexOf("https\:\/\/")>=0 
                                    ? "https"
                                    : ""
                            ;

                            // ensure that the form and ids exist
                            modalDlg_setContent(\''.$sForm.'\');
                            modalDlg_setTitle("'.$this->lB('ressources.denylist.title').'");

                            $("#input-url").val(sUrl);
                            $("#btnswitch").html(sProto + " >> " + sBoth);
                            showModalWindow();
                            return false;
                        });
                        function switchProto(){
                            var sUrlInput = $("#input-url").val();
                            var sUrlNew = false;
                            var sLabelNew = false;
                            
                            if (sUrlInput.indexOf(sBoth)>=0){
                                sUrlNew = sUrl.replace("#http[s]*://#", sProto);
                                sLabelNew = sProto + " >> " + sBoth;
                            } else {
                                sUrlNew = sUrlInput.replace("https:\/\/", sBoth);
                                sUrlNew = sUrlNew.replace("http:\/\/", sBoth);
                                sLabelNew = sProto + " << " + sBoth;
                            }
                            $("#input-url").val(sUrlNew);
                            $("#btnswitch").html(sLabelNew);
                            return false;
                        }
                    </script>'
                    ;
        } 
    }
    if($iResCount>$this->iLimitRessourcelist && !$bIgnoreLimit){
        $sReturn.='<p>'.$this->lB('ressources.hint-manyitems')
        . '<br><br>'
        . $this->_getButton([
            'href'=>$this->_getQs([
                'showtable1'=>1,
                'showreport'=>0,
                'ignorelimit'=>1,
            ]),
            'class'=>'button-error',
            'label'=>'ressources.ignorelimit',
            'popup' => false
            ])
            ;
    } else if (!$bShowReport && !$bShowRessourcetable){
        $sReturn.= $sBtnTable. ' '. $sBtnReport;
    }


return $sReturn;

