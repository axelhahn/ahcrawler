<?php
/**
 * page analysis :: ressources
 */
$sReturn = '';
$aCounter = array();
$aFilter=array('http_code', 'ressourcetype','type', 'content_type');
$aFields = array('id', 'url', 'siteid', 'http_code', 'ressourcetype', 'type', 'content_type');
$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');

$aUrl=array();

// $sSiteId = $this->_getRequestParam('siteid');
$oRessources=new ressources();
$oRenderer=new ressourcesrenderer($this->_sTab);

$aWhere=array('siteid' => $this->_sTab, 'isExternalRedirect'=>0);
$aFilterItems=$this->_getRequestParam('filteritem');
if ($aFilterItems){
    $aFilterValues=$this->_getRequestParam('filtervalue');
    for ($i=0; $i<count($aFilterItems); $i++){
        $aWhere[$aFilterItems[$i]]=($aFilterValues[$i]==='') ? null : $aFilterValues[$i];
        $aUrl[]=array('filteritem'=>$aFilterItems[$i], 'filtervalue'=>$aFilterValues[$i]);
    }
}
// -- get list of all data
$iResCount = $oRessources->getCount($aWhere);

// -- get list of filter data
$aCounter2=array();
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


$bShowRessourcetable=($this->_getRequestParam('showtable') || !$bShowReport);
if ($iResCount>$this->iLimitRessourcelist && !$bIgnoreLimit){
    $bShowReport=false;
    $bShowRessourcetable=false;
}

if ($iResCount) {

    $aTable = array();


    if ($bShowReport || $bShowRessourcetable){
        $aRessourcelist = $oRessources->getRessources($aFields, $aWhere, array("url"=>"ASC"));
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
                $aRow['actions'] = $this->_getButton(array(
                    'href' => 'overlay.php?action=ressourcedetail&id=' . $aRow['id'] . '&siteid=' . $this->_sTab . '',
                    'class' => 'button-secondary',
                    'label' => 'button.view'
                ));
                 * 
                 */
                $aRow['ressourcetype'] = $oRenderer->renderArrayValue('ressourcetype', $aRow);
                $aRow['type'] = $oRenderer->renderArrayValue('type', $aRow);
                $aRow['http_code'] = $oRenderer->renderArrayValue('http_code', $aRow);

                unset($aRow['id']);
                unset($aRow['siteid']);
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
            $aTableFilter[]=array('<strong>'.$this->lB('ressources.total').'</strong>', '' ,'<strong>'.count($aRessourcelist).'</strong>');
        }
    }

    /*
    foreach ($aFilter as $sKey){
        $sRessourcelabel=(array_key_exists($sKey, $this->_aIcons['cols']) ? '<i class="'.$this->_aIcons['cols'][$sKey].'"></i> ' : '') . $sKey;
        $aTableFilter[]=array('<strong>'.$sRessourcelabel.'</strong>', '', '');
        foreach ($aCounter2[$sKey] as $aCounterItem){
            $sCounter=$aCounterItem[$sKey];
            $iValue=$aCounterItem['count'];
            $aTableFilter[]=array(
                '', 
                (count($aCounter2[$sKey])>1
                    ? '<a href="'.$sSelfUrl.'&amp;filteritem[]='.$sKey.'&amp;filtervalue[]='.$sCounter.'">'
                        .$oRenderer->renderValue($sKey, $sCounter)
                        .'</a>'
                    : $oRenderer->renderValue($sKey, $sCounter)
                )
                , 
                $iValue
            );
        }
    }
     */
    
    $sFilterArea='';
    
    foreach ($aFilter as $sKey){
        $sRessourcelabel=$this->_getIcon($sKey).$this->lB('db-ressources.'.$sKey);
        $aTableF=array();
        $aTableF[]=array('<strong>'.$sRessourcelabel.'</strong>', '');
        foreach ($aCounter2[$sKey] as $aCounterItem){
            $sCounter=$aCounterItem[$sKey];
            $iValue=$aCounterItem['count'];
            $aTableF[]=array(
                (count($aCounter2[$sKey])>1
                    ? '<a href="'.$sSelfUrl.'&amp;filteritem[]='.$sKey.'&amp;filtervalue[]='.$sCounter.'">'
                        .$oRenderer->renderValue($sKey, $sCounter)
                        .'</a>'
                    : $oRenderer->renderValue($sKey, $sCounter)
                )
                , 
                $iValue
            );
        }
        $sFilterArea.='<div style="float: left; margin-right: 1em;">'
                . $this->_getSimpleHtmlTable($aTableF, 1)
                .'</div>';
    }
    $sFilterArea.='<div style="clear: both"></div>';
}

// --- output

$sBtnReport=$this->_getButton(array(
    'href'=>$this->_getQs(array(
        'showreport'=>1,
        'showtable'=>0,
        'tab'=>$this->_sTab,
    )).'#restable',
    'class'=>'button-secondary',
    'label'=>'ressources.showreport',
    'popup' => false
));
$sBtnTable=$this->_getButton(array(
    'href'=>$this->_getQs(array(
        'showreport'=>0,
        'showtable'=>1,
        'tab'=>$this->_sTab,
    )).'#restable',
    'class'=>'button-secondary',
    'label'=>'ressources.showtable',
    'popup' => false
));
$sReturn.='<h3>' . $this->lB('ressources.overview') . '</h3>'
        . $oRenderer->renderRessourceStatus(). '<div style="clear: both;"></div>'
        . '<p>'.$this->lB('ressources.overview.intro').'</p>'
        ;


if ($iResCount) {
    $sReturn.=''
            // .$this->_getSimpleHtmlTable($aTableFilter)
            . '<div class="actionbox">'
                . $this->_getIcon('filter')
                . $this->lB('ressources.filter')
                . ($sFilter ? $sFilter : '<br><br>')
                . $sFilterArea
            . '</div>'
            . '<h3 id="restable">' . $this->lB('ressources.list') . '</h3>'
            ;

    if ($bShowRessourcetable){
        $sReturn.='<p>'
                . $sBtnReport.'<br><br>'
                . $this->lB('ressources.list.intro')
                . '</p>'
            . $this->_getHtmlTable($aTable, "db-ressources.")
            ;
    } 
    if ($bShowReport){
        $sReturn.='<p>'
                . $sBtnTable.'<br><br>'
                . $this->lB('ressources.report.intro')
                . '</p>'
                . $sReport
                ;
    } 
    if($iResCount>$this->iLimitRessourcelist && !$bIgnoreLimit){
        $sReturn.='<p>'.$this->lB('ressources.hint-manyitems')
        . '<br><br>'
        . $this->_getButton(array(
            'href'=>$this->_getQs(array(
                'showtable1'=>1,
                'showreport'=>0,
                'ignorelimit'=>1,
            )),
            'class'=>'button-error',
            'label'=>'ressources.ignorelimit',
            'popup' => false
            ))
            ;
    } else if (!$bShowReport && !$bShowRessourcetable){
        $sReturn.= $sBtnTable. ' '. $sBtnReport;
    }

} else {
    $sReturn.='<br>'.$this->_getMessageBox(sprintf($this->lB('ressources.empty'), $this->_sTab), 'warning');
}

$sReturn.= $oRenderer->renderInitDatatable('.datatable', array('lengthMenu'=>array(array(20, 50, 100, -1))));

return $sReturn;

