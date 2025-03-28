<?php
/**
 * SELECT COUNTERS AND SHOW HISTORY DATA
 */
if (!$this->_requiresPermission("viewer", $this->_sTab)){
    return include __DIR__ . '/error403.php';
}
require_once __DIR__ . '/../../classes/counter.class.php';

$oRenderer=new ressourcesrenderer($this->_sTab);
$sHtml='';
$sSelector='';
$sDetails='';

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
    $sHtml.=$oRenderer->renderMessagebox($this->lB('counter.notEnoughData'), 'warning');
} else {
    foreach($aList as $sLabel){
        $sSelector.='<a href="?page=counters&siteid='.$iProfileId.'&countername='.$sLabel.'" 
            class="pure-button button-small '.($sSelectedCounter == $sLabel ? ' button-secondary' : '').'" 
            style="width: 100%;">'.$sLabel.'</a><br>';
            // echo '    "counter.'.$sLabel.'.label" :"'.$sLabel.'",<br>';
            // echo '    "counter.'.$sLabel.'.description" :"'.$sLabel.'",<br>';
    }
    // exit;
}

// ---------- load data of a selected counter

if($sSelectedCounter){
    $sTable=[];
    foreach($oCounter->getCountersHistory($sSelectedCounter) as $aEntry){
        $aTable[]=[ 'ts'=>$aEntry['ts'], 'value'=>$aEntry['value'] ];
    }

    $iHttpcode=false;
    $sHttpCodeHelp='';
    $sLangIdx=$sSelectedCounter;
    if (strstr($sSelectedCounter, '[')){
        $sLangIdx=preg_replace('/\[.*\]/','',$sSelectedCounter).'[]';
        preg_match('/\[(.*)\]/',$sSelectedCounter,$aMatches);
        $iHttpcode=isset($aMatches[1]) ? $aMatches[1] : false;
        $sHttpCodeHelp='<br><br>'.$this->lB('httpcode.'.$iHttpcode.'.label').'<br>'
            .$this->lB('httpcode.'.$iHttpcode.'.descr')
        ;
    }
    $sLabel=sprintf($this->lB('counter.'.$sLangIdx.'.label'), $iHttpcode);
    $sLabel=($sLabel!=$sLangIdx) ? $sLabel : '';
    $sDescription=sprintf($this->lB('counter.'.$sLangIdx.'.description'), $iHttpcode, $sHttpCodeHelp);
    $sDescription=($sDescription!=$sLangIdx) ? $sDescription : '';


    $sDetails.= ''
        . ($sLabel 
            ? '<strong>'.$sLabel.'</strong><br>' 
                . ( $sDescription ? $sDescription.'<br><br>' : '<br>' )
            : '')
        . $this->_getHistoryCounter([$sSelectedCounter])
        . $this->_getHtmlTable($aTable, 'counter.', 'tblCounterdata')
        // . '<pre>'.print_r($oCounter->getCountersHistory($sSelectedCounter), 1).'</pre>'
    ;    
}

// ---------- complete output

$sHtml.='<table><th><tr><td valign="top">'.$sSelector.'</td><td>&nbsp;&nbsp;&nbsp;</td><td valign=top>'.$sDetails.'</td></tr></th></table>';
return $sHtml;