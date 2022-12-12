<?php

/**
 * Generator for HTML Tags
 * 
 * see getTag() ... 
 *    it generates html code for tags 
 *    - with and without label
 *    - with and without closing tag
 * 
 * see _setAttributes($aAttributes) ... 
 *    attribute will be inserted with given key-value array
 *    BUT with special attribute keys:
 *    - label - will be used as label
 *    - icon  - will be added as <i class="[icon value]"></i> to the label
 * 
 * @author Axel
 */
class htmlelements {

    /**
     * set of auto generated icon prefixes
     * @var type 
     */
    var $_aIcons=array(
            'fa-'=>'fa ',
        );
    
    var $_sLabel = '';
    var $_aAttributes = array();
    

    // ----------------------------------------------------------------------
    // CONSTRUCTOR
    // ----------------------------------------------------------------------
    
    public function __construct() {
        return true;
    }

    // ----------------------------------------------------------------------
    // 
    // PRIVATE FUNCTIONS 
    // 
    // ----------------------------------------------------------------------
    
    
    /**
     * generate html attibutes with all internal attributes key -> values
     * @return string
     */
    private function _addAttributes() {
        $sReturn = '';
        foreach ($this->_aAttributes as $sAttr => $sValue) {
            $sReturn .= ' '.$sAttr . '="' . $sValue . '"';
        }
        return $sReturn;
    }
    
    
    /**
     * internal helper: fetch all attributes from key-value hash; 
     * Specialties here:
     * - label will be extracted from key 'label' 
     * - and optional existing key 'icon' will be added at beginning of a label
     * 
     * @param array $aAttributes
     * @return boolean
     */
    private function _setAttributes($aAttributes){
        $this->_sLabel='';
        if(isset($aAttributes['icon']) && $aAttributes['icon']){
            $this->_sLabel.=$this->getIcon($aAttributes['icon']);
            unset($aAttributes['icon']);
        }
        if(isset($aAttributes['label']) && $aAttributes['label']){
            $this->_sLabel .= $aAttributes['label'];
            unset($aAttributes['label']);
        }
        $this->_aAttributes=$aAttributes;
        return true;
    }

    // ----------------------------------------------------------------------
    // 
    // PUBLIC FUNCTIONS
    // HTML GENERIC
    // 
    // ----------------------------------------------------------------------
    
    /**
     * generic function to get html code for a single tag 
     * 
     * @param string   $sTag          tag name
     * @param array    $aAttributes   array with attributes (optional including 'icon' and 'label')
     * @param boolean  $bCloseTag     optional: set false if tag has no closing tag (= ending with "/>")
     * @return type
     */
    public function getTag($sTag, $aAttributes, $bCloseTag=true){
        $sTpl = $bCloseTag ? "<$sTag%s>%s</$sTag>" : "<$sTag %s/>%s";
        $this->_setAttributes($aAttributes);
        return sprintf($sTpl, $this->_addAttributes(), $this->_sLabel);
    }
    
    // ----------------------------------------------------------------------
    // 
    // PUBLIC FUNCTIONS
    // SIMPLE HTML ELEMENTS
    // 
    // ----------------------------------------------------------------------
    
    
    /**
     * get hml code for a button
     * 
     * @param array $aAttributes
     * @return string
    public function getButton($aAttributes) {
        return $this->getTag('button', $aAttributes);
    }
     */

    /**
     * helper detect prefix of a string add prefix of a framework
     * i.e. value "fa-close" detects font awesome and adds "fa " as prefix
     * 
     * @param type $sIconclass
     * @return boolean
     */
    public function getIcon($sIconclass=false){
        if(!$sIconclass){
            return '';
        }
        $sPrefix='';
        foreach ($this->_aIcons as $sPrefix =>$add) {
            if (strpos($sIconclass, $sPrefix)===0){
                $sPrefix=$add;
                continue;
            }
        }
        // do not use this .. it overrides internal attribute vars
        // return $this->getTag('i', array('class'=>$sPrefix.$sIconclass));
        return '<i class="'.$sPrefix.$sIconclass.'"></i> ';
    }
   

    // ----------------------------------------------------------------------
    // 
    // PUBLIC FUNCTIONS
    // HTML COMPONENTS
    // 
    // ----------------------------------------------------------------------

    /**
     * get html code for an input field
     * 
     * @param array $aAttributes  attributes of the select tag
     * @return string
     */
    public function getFormInput($aAttributes){
        $sTpl = '<input %s/>';
        $this->_setAttributes($aAttributes);
        return sprintf($sTpl, $this->_addAttributes());
    }
    /**
     * get html code for an option field in a select drop down
     * 
     * @param array $aAttributes  attributes of the option tag
     * @return string
     */
    public function getFormOption($aAttributes){
        $sTpl = '<option %s>%s</option>';
        $this->_setAttributes($aAttributes);
        return sprintf($sTpl, $this->_addAttributes(), $this->_sLabel);
    }
    /**
     * get html code for a select drop down
     * 
     * @param array $aAttributes  attributes of the select tag
     * @param array $aOptions     array for all option fields
     * @return string
     */
    public function getFormSelect($aAttributes, $aOptions=array()){
        // $sTpl = '<select %s>%s</select>';

        if(!count($aOptions)){
            return false;
        }
        $sOptions='';
        foreach($aOptions as $aOptionAttributes){
            // $sOptions.=$this->getFormOption($aOptionAttributes);
            $sOptions.=$this->getTag('option', $aOptionAttributes);
        }
        $aAttributes['label']=$sOptions;
        return $this->getTag('select', $aAttributes);
        /*
        $this->_setAttributes($aAttributes);
        return sprintf($sTpl, $this->_addAttributes(), $sOptions);
         * 
         */
    }

    public function getTable($aHead, $aBody, $aTableAttributes=array()){
        $sReturn='';
        $sTdata='';
        $sThead='';
        $sTpl = '<table %s>'
                . '<thead><tr>%s</tr></thead>'
                . '<tbody>%s</tbody>'
                . '</table>';
        
        foreach($aHead as $sTh){
            $sThead.='<th>'.$sTh.'</th>';
        }
        foreach($aBody as $aTr){
            $sTdata.='<tr>';
            foreach($aTr as $sTd){
                $sTdata.='<td>'.$sTd.'</td>';
            }
            $sTdata.='</tr>';
        }
        $this->_setAttributes($aTableAttributes);
        return sprintf($sTpl, 
                $this->_addAttributes(), 
                $sThead,
                $sTdata
                );
    }
}
