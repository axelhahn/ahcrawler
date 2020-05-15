<?php
/**
 * page analysis
 */
return $this->_getNavi2($this->_getProfiles(), false, '?page=home')
        .'<br>'
        .$this->_renderChildItems($this->_aMenu['analysis'])
        ;
