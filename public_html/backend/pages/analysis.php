<?php
/**
 * page analysis
 */
$iProfileId=$this->_getTab();
$this->setSiteId($iProfileId);
return '<br><br><br><br>'.$this->_renderChildItems($this->_aMenu['analysis'])
        // . $this->getProfileImage()
        ;
