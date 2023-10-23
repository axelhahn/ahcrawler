<?php
/**
 * SELECT COUNTERS AND SHOW HISTORY DATA
 */
require_once __DIR__ . '/../../classes/counter.class.php';

$oRenderer=new ressourcesrenderer($this->_sTab);
$sHtml='';
$sSelector='';
$sGraph='';

$iSizeOfSelectbox=35;

$sSelectedCounter=$this->_getRequestParam('countername');

$iProfileId=$this->_getTab();
$this->setSiteId($iProfileId);

# TODO translate
$sHtml=''
    .$this->_getNavi2($this->_getProfiles(), false, '')
    . ($sSelectedCounter 
        ? '<h3>'.sprintf($this->lB('counter.head.show'), $sSelectedCounter).'</h3>'
        : '<h3>'.$this->lB('counter.head.select').'</h3>'
    )
    .'<p>'.$this->lB('counter.head.hint').'</p>'
;

$oCounter=new counter();
$oCounter->mysiteid($this->iSiteId);


// ---------- create navigation with al existing counters of the current website.

$aList=$oCounter->getCounterItems(true);

if (!$aList){
    // --- no counter was found yet
    # TODO translate
    $sHtml='INFO: no counter yet. They appear, if you start crawling.';
} else {
    foreach($aList as $sLabel){
        $sSelector.='<a href="?page=counters&siteid='.$iProfileId.'&countername='.$sLabel.'" 
            class="pure-button '.($sSelectedCounter == $sLabel ? ' button-secondary' : '').'" 
            style="width: 100%;">'.$sLabel.'</a><br>';
    }
}

// ---------- load data of a selected counter

if($sSelectedCounter){
    $sTable=[];
    foreach($oCounter->getCountersHistory($sSelectedCounter) as $aEntry){
        $aTable[]=[ 'ts'=>$aEntry['ts'], 'value'=>$aEntry['value'] ];
    }

    $sGraph.=$this->_getHistoryCounter([$sSelectedCounter])
        . $this->_getHtmlTable($aTable, 'counter.', 'tblCounterdata')
        // . '<pre>'.print_r($oCounter->getCountersHistory($sSelectedCounter), 1).'</pre>'
    ;    
}

// ---------- complete output

$sHtml.='<table><th><tr><td valign="top">'.$sSelector.'</td><td>&nbsp;&nbsp;&nbsp;</td><td valign=top>'.$sGraph.'</td></tr></th></table>';
return $sHtml;