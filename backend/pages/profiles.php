<?php
/**
 * page searchindex :: profiles
 */
$sReturn = '';
$sReturn.=$this->_getNavi2($this->_getProfiles())
        . '<h3>' . $this->lB('profile.vars.searchprofile') . '</h3>'
        // . '<pre>' . print_r($this->aProfile, 1) . '</pre>'
        ;
$this->setSiteId($this->_sTab);
$aTbl = array();
// foreach ($this->_getProfileConfig($this->_sTab) as $sVar => $val) {
foreach ($this->aProfile as $sVar => $val) {

    $sTdVal = '';
    if (is_array($val)){
        foreach($val as $sKey=>$subvalue){
            $sTdVal .= '<span class="key2">'.$sKey.'</span>:<br>'
                    .((is_array($subvalue)) ? ' - <span class="value">' . implode('</span><br> - <span class="value">', $subvalue) : '<span class="value">'.$subvalue.'</span>')
                    .'</span><br><br>'
                    ;                    
        }
    } else {
        $sTdVal .= (is_array($val)) ? '<span class="value">'.implode('</span><br> - <span class="value">', $val).'</span>' : '<span class="value">'.$val.'</span>';
    }

    $aTbl[] = array($this->lB("profile." . $sVar), '<span class="key">'.$sVar.'</span>', $sTdVal);
}
$sReturn.=$this->_getSimpleHtmlTable($aTbl);
/*
$sReturn.='<h3>' . $this->lB('rawdata') . '</h3>'
        . '<pre>' . print_r($this->_getProfileConfig($this->_sTab), 1) . '</pre>';
;
 * 
 */
return $sReturn;
