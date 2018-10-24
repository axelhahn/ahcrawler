<?php
/**
 * page searchindex :: searches 
 */
$sReturn = '';
$aFields = array('ts', 'query', 'searchset', 'results', 'host', 'ua', 'referrer');
$sReturn.=$this->_getNavi2($this->_getProfiles());
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

$aDays=array(7,30,90,365);
foreach($aDays as $iDays){
    $sQuery=''
            . 'SELECT query, count(query) as count, results '
            . 'FROM searches '
            . 'WHERE siteid = '.$this->_sTab.' '
            . 'AND ts > \''.date("Y-m-d H:i:s", (date("U") - (60 * 60 * 24 * $iDays))).'\' '
            . 'GROUP BY query '
            . 'ORDER BY count desc, query asc '
            . 'LIMIT 0,10';
    $oResult=$this->oDB->query($sQuery);

    /*
     * TODO: FIX ME
    $oResult = $this->oDB->select(
            'searches', 
            array('ts', 'query', 'count(query) as count', 'results'),
            array(
                'AND' => array(
                    'siteid' => $this->_sTab,
                    '[>]ts' => date("Y-m-d H:i:s", (date("U") - (60 * 60 * 24 * $iDays))),
                ),
                "GROUP" => "query",
                "ORDER" => array("count"=>"DESC", "query"=>"asc"),
                "LIMIT" => 10
            )
    );
     */

    // echo "$sQuery ".($oResult ? "OK" : "fail")."<br>";
    $aSearches[$iDays]=($oResult ? $oResult->fetchAll(PDO::FETCH_ASSOC) : array());
}

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

        $aRow['actions'] = $this->_getButton(array(
            'href' => 'overlay.php?action=search&query=' . $aRow['query'] . '&siteid=' . $this->_sTab . '&subdir=' . $sSubdir,
            'class' => 'button-secondary',
            'label' => 'button.search'
        ));

        $aTable[] = $aRow;
    }
    $sReturn.='<h3>' . $this->lB('profile.searches.last') . '</h3>' 
            . $this->_getHtmlTable($aTable, "searches.");
} else {
    $sReturn.='<br><div class="warning">'.$this->lB('profile.searches.empty').'</div>';
}



foreach($aDays as $iDays){
    if (count($aSearches[$iDays])) {
        $aTable = array();
        $aChartitems=array();
        $iCount=0;
        foreach ($aSearches[$iDays] as $aRow) {
            $iCount++;
            $aTable[] = $aRow;
            $aChartitems[]=array(
                'label'=>$aRow['query'],
                'value'=>$aRow['count'],
                'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-'.($iCount % 5 + 1).'\')',
                // 'legend'=>$iExternal.' x '.$this->lB('linkchecker.found-http-external'),
            );
        }

        $sReturn.= '<h3>' . sprintf($this->lB('profile.searches.top10lastdays'), $iDays) . '</h3>'
                . '<div style="float: right;">' 
                . $this->_getChart(array(
                    'type'=>'pie',
                    'data'=>$aChartitems
                    ))
                . '</div>'
                . $this->_getHtmlTable($aTable, "searches.")
                ;
    }         
}
/*
  // echo $this->oDB->last_query() . '<br>';
  foreach ($aResult as $aRow){
  $sReturn.='<tr>';
  foreach ($aFields as $sField){
  $sReturn.='<td class="td-'.$sField.'">'.$aRow[$sField].'</td>';
  }
  $sReturn.='</tr>';
  }
  if($sReturn){
  $sTh='';
  foreach ($aFields as $sField){
  $sTh.='<th class="th-'.$sField.'">'.$this->lB('searches.'.$sField).'</th>';
  }
  $sReturn='<table class="pure-table pure-table-horizontal pure-table-striped">'
  . '<thead><tr>'.$sTh.'</tr></thead>'
  . '<tbody>'.$sReturn.''
  . '</tbody>'
  . '</table>';
  }
 * 
 */
return $sReturn;
