<?php
/**
 * page analysis
 */
$iProfileId=$this->_getTab();
$this->setSiteId($iProfileId);
return $this->_getNavi2($this->_getProfiles(), false, '?page=home')
        . '<br>'
        . $this->_renderChildItems($this->_aMenu['analysis'])
        . $this->getProfileImage()
        ;
