<?php
/**
 * page searchindex :: searches 
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sReturn = '';
$aFields = array('ts', 'query', 'searchset', 'results', 'host', 'ua', 'referrer');
$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=search')
        .'<h3>' . $this->lB('searches.overview') . '</h3>'
        ;
$iSearches=$this->oDB->count(
        'searches', 
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
        )
);
$sTiles = $oRenderer->renderTile('', $this->lB('searches.total'), $iSearches, '', '');
if(!$iSearches){
    $sReturn.= $oRenderer->renderTileBar($sTiles).'<div style="clear: both;"></div>'
        .'<br>'.$this->_getMessageBox($this->lB('searches.empty'), 'warning');
    return $sReturn;
}


$sOldest = $this->oDB->min('searches', array('ts'), array(
    'AND' => array(
        'siteid' => $this->_sTab,
    ),));
$sYoungest = $this->oDB->max('searches', array('ts'), array(
    'AND' => array(
        'siteid' => $this->_sTab,
    ),));

$sTiles .= $oRenderer->renderTile('', $this->lB('searches.since'), $oRenderer->hrAge(date('U', strtotime($sOldest))), $sOldest, '');
$sTiles .= $oRenderer->renderTile('', $this->lB('searches.last'), $oRenderer->hrAge(date('U', strtotime($sYoungest))), $sYoungest, '');

$sReturn.= $oRenderer->renderTileBar($sTiles).'<div style="clear: both;"></div>'
        ;

$aLastSearches = $this->oDB->select(
        'searches', 
        $aFields, 
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
            "ORDER" => array("ts"=>"DESC"),
            "LIMIT" => 20
        )
);
/*
$aSearches = $this->oDB->select(
        'searches', 
        array('query'), 
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
            "ORDER" => "ts DESC",
            "LIMIT" => 20
        )
);
 * 
 */

// --- output

if (count($aLastSearches)) {
    $aTable = array();
    foreach ($aLastSearches as $aRow) {
        $aTmp=unserialize($aRow['searchset']);
        /*
        $sSubdir=(is_array($aTmp) && array_key_exists('subdir', $aTmp)) 
            ? $aTmp['subdir'] 
            : (is_array($aTmp) && array_key_exists('url', $aTmp))
                ? preg_replace('#//.*[/%]#', '/', $aTmp['url'], 1)
                : '/'
            ;
         */
        $sSubdir=(isset($aTmp['subdir']) && $aTmp['subdir']) 
            ? $aTmp['subdir'] 
            : '%'
            ;
        // $sSubdir=(is_array($aTmp) && array_key_exists('subdir', $aTmp)) ? $aTmp['subdir'] : '';

        // unset($aRow['searchset']);
        // $aRow['searchset']=print_r($aTmp,1);
        // $aRow['searchset']=$sSubdir;

        foreach ($aRow as $key => $value) {
            $aRow[$key]=htmlentities($value);
        }
        $aRow['actions'] = $this->_getButton(array(
            // 'href' => 'overlay.php?action=search&query=' . $aRow['query'] . '&siteid=' . $this->_sTab . '&subdir=' . $sSubdir,
            'href' => '?page=status&action=search&q=' . $aRow['query'] . '&subdir=' . $sSubdir.'&tab=' . $this->_sTab ,
            'popup' => false,
            'class' => 'button-secondary',
            'label' => 'button.search'
        ));

        $aTable[] = $aRow;
    }
    $sReturn.='<h3>' . $this->lB('searches.last') . '</h3>' 
            . $this->_getHtmlTable($aTable, "searches.");
} 

// ----------------------------------------------------------------------
// top N form
// ----------------------------------------------------------------------

$iCount = $this->_getRequestParam('count', false, 'int');
if(!$iCount){
    $iCount=10;
}
$iSinceDays = $this->_getRequestParam('lastdays', false, 'int');
if(!$iSinceDays){
    $iSinceDays=7;
}

$sOptionsTop='';
foreach(array(10,20,50,100) as $iTopvalue){
    $sOptionsTop.='<option value="'.$iTopvalue.'"'
            .($iTopvalue===$iCount ? ' selected="selected"' : '')
            . '>'.$iTopvalue.'</option>'
            ;
    
}
$sOptionsSince='';
foreach(array(
    7,
    30,
    90,
    365,
    730,
    10000,
) as $iDays){
    $sOptionsSince.='<option value="'.$iDays.'"'
            .($iDays===$iSinceDays ? ' selected="selected"' : '')
            . '>'.$iDays.'</option>';
}
        
// $since=date("Y-m-d 00:00:00", (date("U") - (60 * 60 * 24 * $iDays)));
        
$sReturn.=''
        . '<h3 id="searchtopn">'.$this->lB('searches.topn.headline').'</h3>'
    . '<div div class="actionbox">'
        . '<form action="#searchtopn" method="get" class="pure-form">'
            . '<input type="hidden" name="page" value="searches">'
            . '<input type="hidden" name="action" value="search">'
            . '<input type="hidden" name="siteid" value="' . $this->_sTab . '">'
        
            . '<label for="eCount">'.$this->lB('searches.topn.count').'</label>'
            . ' '
            . '<select name="count" id="eCount">' . $sOptionsTop . '</select>'
            . ' '
        
            . sprintf($this->lB('searches.topn.since'), '<select name="lastdays">' . $sOptionsSince . '</select>')
        
            // . '<br><br>'
            . ' '
            . '<button class="pure-button button-success">' . $this->_getIcon('button.search') . $this->lB('button.search') . '</button> '
        . '</form>'
    . '</div>'
    ;

$sDateFrom=date("Y-m-d 00:00:00", (date("U") - (60 * 60 * 24 * $iSinceDays)));
$sDateTo=date("Y-m-d H:i:s");
$sQuery=''
        . 'SELECT query, count(query) as count, results '
        . 'FROM searches '
        . 'WHERE siteid = '.$this->_sTab.' '
        . 'AND ts >= \''.$sDateFrom.'\' '
        . 'AND ts < \''.$sDateTo.'\' '
        . 'GROUP BY query '
        . 'ORDER BY count desc, query asc '
        . 'LIMIT 0,'.$iCount;
// echo "DEBUG: query 1: $sQuery<br>";
$oResult=$this->oDB->query($sQuery);

/*
 * TODO: FIX ME
 * 
$oResult = $this->oDB->debug()->select(
        'searches', 
        // problem 1: 'count(query) as count' - is not what we expect
        array('query', 'count(query) as count', 'results'),
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
                'ts[>=]' => date("Y-m-d H:i:s", (date("U") - (60 * 60 * 24 * $iDays))),
            ),
            "GROUP" => "query",
            // problem 2: just one ORDER item
            "ORDER" => array("count"=>"DESC", "query"=>"asc"),
            "LIMIT" => 10
        )
);
// echo "$sQuery ".($oResult ? "OK" : "fail")."<br>";
echo "DEBUG: query 2: ".$this->oDB->last()."<br>";
*/

$aSearchterms=($oResult ? $oResult->fetchAll(PDO::FETCH_ASSOC) : array());

    if (count($aSearchterms)) {
        $aTable = array();
        $aChartitems=array();
        $iCount=0;
        foreach ($aSearchterms as $aRow) {
            $iCount++;
            foreach ($aRow as $key => $value) {
                $aRow[$key]=htmlentities($value);
            }
            $aTable[] = $aRow;
            $aChartitems[]=array(
                'label'=>$aRow['query'],
                'value'=>$aRow['count'],
                'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-'.($iCount % 5 + 1).'\')',
                // 'legend'=>$iExternal.' x '.$this->lB('linkchecker.found-http-external'),
            );
        }

        $sReturn.= '<h4>' . sprintf($this->lB('searches.topn.headline2'), $iCount, $iSinceDays) . '</h4>'
                . '<p>'
                    . sprintf($this->lB('searches.topn.from'), $sDateFrom).'<br>'
                    . sprintf($this->lB('searches.topn.to'), $sDateTo).'<br>'
                . '</p>'
                . '<div style="float: right;">' 
                . $this->_getChart(array(
                    'type'=>'pie',
                    'data'=>$aChartitems
                    ))
                . '</div>'
                . $this->_getHtmlTable($aTable, "searches.")
                . '<div style="clear: both;"></div>' 
                ;
    }         


// ----------------------------------------------------------------------

return $sReturn;
