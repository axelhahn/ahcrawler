<?php
/**
 * page analysis
 */
$sReturn='';
$iProfileId=$this->_getTab();
$this->setSiteId($iProfileId);

$sReturn.=''
        // add profiles navigation
        .$this->_getNavi2($this->_getProfiles(), false, '')
        .'<br>'
        
        // child items
        .$this->_renderChildItems($this->_aMenu['analysis'])
        ;

return $sReturn;