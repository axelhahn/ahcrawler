<?php

/**
 * page searchindex :: searches 
 */
$oRenderer = new ressourcesrenderer($this->_sTab);
$sReturn = '';
$aFields = array('ts', 'query', 'searchset', 'results', 'host', 'ua', 'referrer');


// ----------------------------------------------------------------------
// basic infos
// ----------------------------------------------------------------------

$sReturn .= $this->_getNavi2($this->_getProfiles(), false, '?page=search')
        . '<h3>' . $this->lB('searches.overview') . '</h3>'
;
$iSearches = $this->oDB->count(
        'searches',
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
        )
);
$sTiles = $oRenderer->renderTile('', $this->lB('searches.total'), $iSearches, '', '');
if (!$iSearches) {
    $sReturn .= $oRenderer->renderTileBar($sTiles) . '<div style="clear: both;"></div>'
            . '<br>' . $this->_getMessageBox($this->lB('searches.empty'), 'warning');
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

$sReturn .= $oRenderer->renderTileBar($sTiles) . '<div style="clear: both;"></div>'
;


// ----------------------------------------------------------------------
// last N search entries
// ----------------------------------------------------------------------
$iLastCount = $this->_getRequestParam('last', false, 'int');
if (!$iLastCount) {
    $iLastCount = 5;
}

$aLastSearches = $this->oDB->select(
        'searches',
        $aFields,
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
            "ORDER" => array("ts" => "DESC"),
            "LIMIT" => $iLastCount
        )
);
if (count($aLastSearches)) {
    $aTable = array();
    $aKeys=false;
    foreach ($aLastSearches as $aRow) {
        $aTmp = unserialize($aRow['searchset']);
        $sSubdir = (isset($aTmp['subdir']) && $aTmp['subdir']) ? $aTmp['subdir'] : '%'
        ;

        foreach ($aRow as $key => $value) {
            $aRow[$key] = htmlentities($value);
        }
        $aKeys=$aKeys ? $aKeys : array_keys($aRow);
        $aRow['actions'] = $this->_getButton(array(
            'href' => '?page=status&action=search&q=' . $aRow['query'] . '&subdir=' . $sSubdir . '&siteid=' . $this->_sTab,
            'popup' => false,
            'class' => 'button-secondary',
            'label' => 'button.search'
        ));

        $aTable[] = $aRow;
    }

    $sOptionsLast = '';
    foreach (array(5, 10, 20, 50, 100) as $iLastvalue) {
        $sOptionsLast .= '<option value="' . $iLastvalue . '"'
                . ($iLastvalue === $iLastCount ? ' selected="selected"' : '')
                . '>' . $iLastvalue . '</option>'
        ;
    }

    $sReturn .= '<h3 id="h3last">' . sprintf($this->lB('searches.last.head'), $iLastCount) . '</h3>'
            . '<div div class="actionbox">'
            . '<form action="#h3last" method="get" class="pure-form">'
            . '<input type="hidden" name="page" value="searches">'
            . '<input type="hidden" name="action" value="search">'
            . '<input type="hidden" name="siteid" value="' . $this->_sTab . '">'
            . '<label for="eLast">' . $this->lB('searches.last.count') . '</label> '
            . '<select name="last" onchange="this.form.submit();" id="eLast">' . $sOptionsLast . '</select> '
            . '<button class="pure-button button-success">' . $this->_getIcon('button.view') . $this->lB('button.view') . '</button> '
            . '</form>'
            . '</div><br>'
            . $this->_getHtmlTable($aTable, "searches.")
            . $this->_getHtmlLegend($aKeys, "searches.")
    ;
}

// ----------------------------------------------------------------------
// top N form
// ----------------------------------------------------------------------

$iCount = $this->_getRequestParam('count', false, 'int');
if (!$iCount) {
    $iCount = 10;
}
$iSinceDays = $this->_getRequestParam('lastdays', false, 'int');

$sOptionsTop = '';
foreach (array(10, 20, 50, 100) as $iTopvalue) {
    $sOptionsTop .= '<option value="' . $iTopvalue . '"'
            . ($iTopvalue === $iCount ? ' selected="selected"' : '')
            . '>' . $iTopvalue . '</option>'
    ;
}

$sOptionsSince = '';
$aTimes=array(
 0 => $this->lB('searches.topn.all'),
 1 => $this->lB('searches.topn.today'),
 3 => sprintf($this->lB('searches.topn.lastdays'), 3),
 7 => sprintf($this->lB('searches.topn.lastdays'), 7),
 14 => sprintf($this->lB('searches.topn.lastdays'), 14),
 30 => sprintf($this->lB('searches.topn.lastdays'), 30),
 90 => sprintf($this->lB('searches.topn.lastdays'), 90),
 365 => sprintf($this->lB('searches.topn.lastdays'), 365),
 365*2 => sprintf($this->lB('searches.topn.lastyears'), 2),
 365*3 => sprintf($this->lB('searches.topn.lastyears'), 3),
 365*4 => sprintf($this->lB('searches.topn.lastyears'), 4),
 365*5 => sprintf($this->lB('searches.topn.lastyears'), 5),
);

foreach ($aTimes as $iDays => $sLabeltext) {
    $sDateFrom = date("Y-m-d 00:00:00", (date("U") - (60 * 60 * 24 * $iDays)));
    if(!$iDays || ($sDateFrom>$sOldest && $sYoungest>$sDateFrom)){
        $sOptionsSince .= '<option value="' . $iDays . '"'
            . ($iDays === $iSinceDays ? ' selected="selected"' : '')
            . '>' . $sLabeltext . '</option>';
    }
}

// $since=date("Y-m-d 00:00:00", (date("U") - (60 * 60 * 24 * $iDays)));

$sReturn .= ''
        . '<h3 id="searchtopn">' . sprintf($this->lB('searches.topn.headline'), $iCount) . '</h3>'
        . '<div div class="actionbox">'
        . '<form action="#searchtopn" method="get" class="pure-form">'
        . '<input type="hidden" name="page" value="searches">'
        . '<input type="hidden" name="action" value="search">'
        . '<input type="hidden" name="siteid" value="' . $this->_sTab . '">'
        . '<label for="eCount">' . $this->lB('searches.topn.count') . '</label> '
        . '<select name="count" id="eCount" onchange="this.form.submit();">' . $sOptionsTop . '</select> '
        . '<label for="eLastDays">' . $this->lB('searches.topn.since') . '</label> '
        . '<select name="lastdays" id="eLastDays" onchange="this.form.submit();">' . $sOptionsSince . '</select>'

        // . '<br><br>'
        . ' '
        . '<button class="pure-button button-success">' . $this->_getIcon('button.view') . $this->lB('button.view') . '</button> '
        . '</form>'
        . '</div>'
;

$sDateFrom = $iSinceDays 
        ? date("Y-m-d 00:00:00", (date("U") - (60 * 60 * 24 * $iSinceDays)))
        : $sOldest
        ;
$sDateTo = $sYoungest;
$sQuery = ''
        . 'SELECT query, count(query) as count, results '
        . 'FROM searches '
        . 'WHERE siteid = ' . $this->_sTab . ' '
        . ( $iSinceDays ? 'AND ts >= \'' . $sDateFrom . '\' '.'AND ts < \'' . $sDateTo . '\' '  : '')
        . 'GROUP BY query '
        . 'ORDER BY count desc, query asc '
        . 'LIMIT 0,' . $iCount;
$oResult = $this->oDB->query($sQuery);

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

$aSearchterms = ($oResult ? $oResult->fetchAll(PDO::FETCH_ASSOC) : array());

if (count($aSearchterms)) {
    $aTable = array();
    $aChartitems = array();
    $iCount = 0;
    $aKeys=false;
    foreach ($aSearchterms as $aRow) {
        $iCount++;
        $aKeys=$aKeys ? $aKeys : array_keys($aRow);
        foreach ($aRow as $key => $value) {
            $aRow[$key] = htmlentities($value);
            $aRow['actions'] = $this->_getButton(array(
                'href' => '?page=status&action=search&q=' . $aRow['query'] . '&subdir=&siteid=' . $this->_sTab,
                'popup' => false,
                'class' => 'button-secondary',
                'label' => 'button.search'
            ));
        }
        
        $aTable[] = $aRow;
        $aChartitems[] = array(
            'label' => $aRow['query'],
            'value' => $aRow['count'],
            'color' => 'getStyleRuleValue(\'color\', \'.chartcolor-' . ($iCount % 5 + 1) . '\')',
                // 'legend'=>$iExternal.' x '.$this->lB('linkchecker.found-http-external'),
        );
    }

    $sReturn .= 
            '<br>'
            . $this->_getSimpleHtmlTable(array(
                array($this->lB('searches.topn.from'), $sDateFrom),
                array($this->lB('searches.topn.to'), $sDateTo)
            ))
            . '<br>'
            . '<div style="float: right;">'
            . $this->_getChart(array(
                'type' => 'pie',
                'data' => $aChartitems
            ))
            . '</div>'
            . $this->_getHtmlTable($aTable, "searches.")
            . $this->_getHtmlLegend($aKeys, "searches.")
            . '<div style="clear: both;"></div>'
    ;
}


// ----------------------------------------------------------------------

return $sReturn;
