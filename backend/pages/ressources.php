<?php
/**
 * page analysis :: ressources
 */
$sReturn = '';
$aCounter = array();
$aFilter=array('http_code', 'ressourcetype','type', 'content_type');
$aFields = array('id', 'url', 'http_code', 'ressourcetype', 'type', 'content_type');
$sReturn.=$this->_getNavi2($this->_getProfiles());

$aUrl=array();

// $sSiteId = $this->_getRequestParam('siteid');
$oRessources=new ressources();
$oRenderer=new ressourcesrenderer($this->_sTab);

$aWhere=array('siteid' => $this->_sTab, 'isExternalRedirect'=>0);
if (array_key_exists('filteritem', $_GET) && array_key_exists('filtervalue', $_GET)){
    for ($i=0; $i<count($_GET['filteritem']); $i++){
        $aWhere[$_GET['filteritem'][$i]]=($_GET['filtervalue'][$i]==='') ? null : $_GET['filtervalue'][$i];
        $aUrl[]=array('filteritem'=>$_GET['filteritem'][$i], 'filtervalue'=>$_GET['filtervalue'][$i]);
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
$sBaseUrl='?page='.$_GET['page'].'&tab='.$this->_sTab;
$sFilter='';
$sReport = '';

// --- button bar with filter items (for remove by click)
if (array_key_exists('filteritem', $_GET) && array_key_exists('filtervalue', $_GET)){

    for ($i=0; $i<count($_GET['filteritem']); $i++){
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
                . '><span class="varname">'.$this->_getIcon($_GET['filteritem'][$i]).$_GET['filteritem'][$i].'</span> = <span class="value">'.$oRenderer->renderValue($_GET['filteritem'][$i], $_GET['filtervalue'][$i]).'</span> '
                . '<i class="fa fa-close"></i>'
                . '</a> ';
    }
    $sFilter= '<i class="fa fa-filter"></i> '
            . $this->lB('ressources.filter').$sFilter.' '
            . ($i>1 ? '<a href="'.$sBaseUrl.'"'
                . ' class="pure-button button-error"'
                . '> '
                . '<i class="fa fa-close"></i>'
                . '</a>'
            : '')
            . '<br><br>';
}

// --- what to create: table or report list
$bShowReport=(array_key_exists('showreport', $_GET) && $_GET['showreport']);
$iReportCounter=0;
$bIgnoreLimit=(array_key_exists('ignorelimit', $_GET) && $_GET['ignorelimit']);


$bShowRessourcetable=(array_key_exists('showtable', $_GET) && $_GET['showtable'] || !$bShowReport);
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
                        . '<div style="clear: left;"></div>'
                        .$oRenderer->renderReportForRessource($aRow);
            }
            // --- generate table view
            if ($bShowRessourcetable){
                $aRow['url'] = '<a href="?page=ressourcedetail&id=' . $aRow['id'] . '&siteid=' . $this->_sTab.'">'.str_replace('/', '/&shy;', $aRow['url']).'</a>';

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
        . $oRenderer->renderRessourceStatus()
        . '<p>'.$this->lB('ressources.overview.intro').'</p>'
        . $sFilter
        ;


if ($iResCount) {
    $sReturn.=$this->_getSimpleHtmlTable($aTableFilter)
            . '<h3 id="restable">' . $this->lB('ressources.list') . '</h3>' ;

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
    $sReturn.='<br><div class="warning">'.$this->lB('ressources.empty').'</div>';
}

$sReturn.='<script>$(document).ready( function () {$(\'.datatable\').DataTable();} );</script>';


return $sReturn;

