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
 * 
 * 2024-09-06  v0.167  php8 only; add typed variables; use short array syntax
 */
class htmlelements
{

    /**
     * label of the current html tag
     * @var string
     */
    protected string $_sLabel = '';

    /**
     * Hash of html attributes and its values for the current tag
     * @var array
     */
    protected array $_aAttributes = [];

    // ----------------------------------------------------------------------
    // CONSTRUCTOR
    // ----------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct()
    {
        //
    }

    // ----------------------------------------------------------------------
    // 
    // PRIVATE FUNCTIONS 
    // 
    // ----------------------------------------------------------------------

    /**
     * Internal helper: Generate html attibutes with all internal attributes 
     * key -> values
     * 
     * @return string
     */
    private function _addAttributes(): string
    {
        $sReturn = '';
        foreach ($this->_aAttributes as $sAttr => $sValue) {
            $sReturn .= ' ' . $sAttr . '="' . $sValue . '"';
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
    private function _setAttributes(array $aAttributes): bool
    {
        $this->_sLabel = '';
        if (isset($aAttributes['icon']) && $aAttributes['icon']) {
            $this->_sLabel .= $this->getIcon($aAttributes['icon']);
            unset($aAttributes['icon']);
        }
        if (isset($aAttributes['label']) && $aAttributes['label']) {
            $this->_sLabel .= $aAttributes['label'];
            unset($aAttributes['label']);
        }
        $this->_aAttributes = $aAttributes;
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
     * @return string
     */
    public function getTag($sTag, $aAttributes, $bCloseTag = true)
    {
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
     * helper detect prefix of a string add prefix of a framework
     * i.e. value "fa-close" detects font awesome and adds "fa " as prefix
     * 
     * @param string  $sIconclass
     * @return string
     */
    public function getIcon(string $sIconclass = ''): string
    {
        return $sIconclass
            ? "<i class=\"$sIconclass\"></i> "
            : ''
            ;
    }

}
