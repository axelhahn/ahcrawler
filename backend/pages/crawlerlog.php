<?php
/**
 * CRAWLER LOG
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sHtml='';

$iProfileId=$this->_getTab();
$this->setSiteId($iProfileId);

$sLogs=$this->logfileToHtml();

$sHtml.=''        
    .$this->_getNavi2($this->_getProfiles(), false, '')
    .'<h3>'.$this->lB('crawlerlog.head').'</h3>'
    .'<p>'.$this->lB('crawlerlog.description').'</p>'
    .($sLogs ? $sLogs : $oRenderer->renderMessagebox($this->lB('crawlerlog.no-logs'), 'error'))
    ;
            
return $sHtml;