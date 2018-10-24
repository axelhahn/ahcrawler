<?php
/**
 * page searchindex :: status
 */
$sReturn = '';
$sReturn.=$this->_getNavi2($this->_getProfiles());

$aHeaderIndex = array('id', 'ts', 'url', 'title', 'errorcount', 'lasterror');

$oCrawler=new crawler($this->_sTab);

$iUrls = $oCrawler->getCount();        
if(!$iUrls){
    $sReturn.='<br><div class="warning">'.$this->lB('status.emptyindex').'</div>';
    return $sReturn;
}

$sLast = $oCrawler->getLastRecord();
$sOldest = $this->oDB->min('pages', array('ts'), array(
    'AND' => array(
        'siteid' => $this->_sTab,
    ),));


$iUrlsLast24=$oCrawler->getCount(
    array(
        'siteid' => $this->_sTab,
        'ts[>]' => date("Y-m-d H:i:s", (date("U") - (60 * 60 * 24))),
    )
);
// echo "\n" . $this->oDB->last_query() . '<br>'; 
$iUrlsErr = $oCrawler->getCount(array(
    'AND' => array(
        'siteid' => $this->_sTab,
        'errorcount[>]' => 0,
    )));


$aNewestInIndex = $this->oDB->select(
        'pages', $aHeaderIndex, array(
    'AND' => array(
        'siteid' => $this->_sTab,
    ),
    "ORDER" => array("ts"=>"DESC"),
    "LIMIT" => 5
        )
);
$aOldestInIndex = $this->oDB->select(
        'pages', $aHeaderIndex, array(
    'AND' => array(
        'siteid' => $this->_sTab,
    ),
    "ORDER" => array("ts"=>"ASC"),
    "LIMIT" => 5
        )
);
$aEmpty = $this->oDB->select(
        'pages', $aHeaderIndex, array(
    'AND' => array(
        'siteid' => $this->_sTab,
        'title' => '',
        'content' => '',
    ),
    "ORDER" => array("ts"=>"ASC"),
    "LIMIT" => 5
        )
);

// echo "\n" . $this->oDB->last_query() . '<br>'; 
// print_r($aResult);
$sReturn.='<h3>' . $this->lB('status.overview') . '</h3>'
    .$this->_getSimpleHtmlTable(
        array(
            array($this->lB('status.last_updated.label'), $sLast),
            array($this->lB('status.indexed_urls.label'), $iUrls),
            array($this->lB('status.indexed_urls24h.label'), $iUrlsLast24),
            array($this->lB('status.error_urls.label'), $iUrlsErr),
            array($this->lB('status.oldest_updated.label'), $sOldest),
        )
);
$sReturn.='<br>'
        . $this->_getButton(array(
            'href' => 'overlay.php?action=search&query=&siteid=' . $this->_sTab . '&searchset=none',
            'class' => 'button-secondary',
            'label' => 'button.search'
        ))
        /*
        . ' '
        . $this->_getButton(array(
            'href' => 'overlay.php?action=crawl&siteid=' . $this->_sTab,
            'class' => 'button-success',
            'label' => 'button.crawl'
        ))
        . ' '
        . $this->_getButton(array(
            'href' => 'overlay.php?action=truncate&siteid=' . $this->_sTab,
            'class' => 'button-error',
            'label' => 'button.truncateindex'
        ))
         * 
         */
        ;
if (count($aNewestInIndex)) {
    $sReturn.='<h3>' . $this->lB('status.newest_urls_in_index') . '</h3>'
            . $this->_getSearchindexTable($aNewestInIndex, 'db-pages.');
}
if (count($aOldestInIndex)) {
    $sReturn.='<h3>' . $this->lB('status.oldest_urls_in_index') . '</h3>'
            . $this->_getSearchindexTable($aOldestInIndex, 'db-pages.');
}

if (count($aEmpty)) {
    $sReturn.='<h3>' . $this->lB('status.empty_data') . '</h3>'
            . $this->_getSearchindexTable($aEmpty, 'db-pages.')
    ;
}
if ($iUrlsErr) {
    $aErrorUrls = $this->oDB->select(
            'pages', $aHeaderIndex, array(
        'AND' => array(
            'siteid' => $this->_sTab,
            'errorcount[>=]' => 0,
        ),
        "ORDER" => array("ts"=>"ASC"),
        "LIMIT" => 50
            )
    );
    $sReturn.='<h3>' . $this->lB('status.error_urls') . '</h3>'
            . $this->_getSearchindexTable($aErrorUrls, 'pages.')
    ;
}

return $sReturn;
