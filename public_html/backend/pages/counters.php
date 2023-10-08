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
    $aCounterOptions=[];
    foreach($aList as $sLabel){
        $aOptionItem=[
            'label'=>$sLabel,
            'value'=>$sLabel,
        ];
        if ($sSelectedCounter == $sLabel) {
            $aOptionItem['selected']='selected';
        }
        $aCounterOptions[]=$aOptionItem;
    }

    $sSelId='countervalues';
    $sSelector.= $this->lB('counter.select') . ':<br><br><form action="" method="get" class="pure-form">'
        . $oRenderer->oHtml->getTag('input', array(
            'type'=>'hidden',
            'name'=>'page',
            'value'=>'counters',
            ), false)
            . $oRenderer->oHtml->getTag('input', array(
                'type'=>'hidden',
                'name'=>'siteid',
                'value'=>$iProfileId,
                ), false)
        
        
        . '<div class="pure-control-group">'
        // . $oRenderer->oHtml->getTag('label', array('for'=>$sSelId, 'label'=>$this->lB('setup.section.backend.lang')))
        . $oRenderer->oHtml->getFormSelect(array(
            'id'=>$sSelId, 
            'name'=>'countername',
            'size'=>count($aList) > $iSizeOfSelectbox ? $iSizeOfSelectbox : count($aList),
            'onchange'=>'form.submit();'
            ), $aCounterOptions)
        . '</div>'
        . '<noscript><br><button class="pure-button">' . $this->_getIcon('button.view') . $this->lB('button.view') . '</button></noscript>'
        .'</form>'
    ;

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

    // foreach($oCounter->getCountersHistory($sCItem)
    //$this->_getHtmlTable($aTable, $sLangTxtPrefix, $sTableId)
    
}

// ---------- complete output

$sHtml.='<table><th><tr><td valign="top">'.$sSelector.'</td><td>&nbsp;&nbsp;&nbsp;</td><td valign=top>'.$sGraph.'</td></tr></th></table>';
    // $sHtml.=$sSelector.$sGraph;
return $sHtml;