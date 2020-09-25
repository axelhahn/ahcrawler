<?php
/**
 * CRAWLER LOG
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$iLinesPerPageDefault=1000;
$sHtml='';

$iProfileId=$this->_getTab();
$this->setSiteId($iProfileId);


$iLines2Show=$this->_getRequestParam('lines', false, 'int');
$iLines2Show=$iLines2Show ? $iLines2Show : $iLinesPerPageDefault;
$iPage=$this->_getRequestParam('logpage', false, 'int');
$iPage=$iPage ? $iPage : 1;

if($this->_getRequestParam('full')){
    $iLines2Show=0;
}


$sLogs=$this->logfileToHtml($iLines2Show, $iPage);

$sHtml.=''        
    .$this->_getNavi2($this->_getProfiles(), false, '')
    .'<h3>'.$this->lB('crawlerlog.head').'</h3>'
    .'<p>'.$this->lB('crawlerlog.description').'</p>'
    .($sLogs ? $sLogs : $oRenderer->renderMessagebox($this->lB('crawlerlog.no-logs'), 'error'))
    ;
            
return $sHtml;