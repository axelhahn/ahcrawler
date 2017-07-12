<?php

/*
 * SIMPLE WEBSERVICE
 * <br>
 * THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE <br>
 * LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR <br>
 * OTHER PARTIES PROVIDE THE PROGRAM ?AS IS? WITHOUT WARRANTY OF ANY KIND, <br>
 * EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED <br>
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE <br>
 * ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. <br>
 * SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY <br>
 * SERVICING, REPAIR OR CORRECTION.<br>
 * <br>
 * 
 * --- HISTORY:<br>
 * 2014-04-30  0.07  added utf8 encoding for json outpt; 
 * 2014-04-30  0.5   gui enhancements: input field for each param of all methods
 * 2014-04-29  0.4   abstract all data before displaying them
 * 2014-04-06  0.3   enable/ disable gui; detect class parameters
 * 2014-04-05  0.2  
 * 2014-04-05  0.1   first public beta
 * 
 * url params
 * - class  - classname
 * - action - method to call [classname] -> [method]
 * - args   - arguments  (as json)
 * - type   - outputtype (default: json)
 * 
 * TODOs:
 * - add param for arguments to initialize the class (detect params of __construct())
 * - show an error: detect if a configured method is not public 
 * - parse parameters from phpdoc - detect required and optional params
 * - parse parameters from phpdoc - show input fields for each parameters
 * - configuration: option area
 * - configuration: examples of a class + of actions
 * 
 * @version 0.07
 * @author Axel Hahn
 * @link http://www.axel-hahn.de/php_sws
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package Axels simple web service
 */

class sws {

    /**
     * config array with known classnames and its supported methods
     * @var string
     */
    private $_aKnownClasses = array();

    /**
     * array of incoming parameters (GET, POST)
     * @var array
     */
    private $_aParams = array();

    /**
     * name of the detected classname in the http request
     * @var string
     */
    private $_sClass = false;
    
    /**
     * name of the detected init arguments for the class in the http request
     * @var string
     */
    private $_aInit = array();

    /**
     * name of the detected method in the http request
     * @var string
     */
    private $_sAction = false;

    /**
     * name of the detected arguments in the http request
     * @var string
     */
    private $_aArgs = array();

    /**
     * name of the found class file (by found class it is fetched from config)
     * @var string
     */
    private $_sClassfile = false;

    /**
     * relative default dir from sws.class.php to class files in the config
     * @var string
     */
    private $_sClassDir = "";
    //private $_sClassDir = "../php/classes/";

    /**
     * output type for the respone to the client
     * @var string
     */
    private $_sOutputType = "json";

    /**
     * version
     * @var string 
     */
    private $_sVersion = "0.07&nbsp;(beta)";

    /**
     * title
     * @var string
     */
    private $_sTitle = "SWS :: simple&nbsp;web&nbsp;service";

    private $_aOptions = array(
        'enableGui'=>true,
        'enableDump'=>false
        );

    /**
     * url for documentation
     * @var string
     */
    private $_sUrlDoc = "";

    /**
     * url of project home
     * @var string
     */
    private $_sUrlHome = "http://www.axel-hahn.de/php_sws";

    // ----------------------------------------------------------------------
    // general functions
    // ----------------------------------------------------------------------

    /**
     * constructor
     * @param array $aKnownClasses  array with known classes and supported methods
     * @return boolean
     */
    public function __construct($aKnownClasses = false) {
        if (is_array($aKnownClasses)) {
            $this->setConfig($aKnownClasses);
        }
        return true;
    }

    // ----------------------------------------------------------------------
    // private functions
    // ----------------------------------------------------------------------

    /**
     * apply a given config
     * @param array $aKnownClasses  array with known classes and supported methods
     * @return bool
     */
    public function setConfig($aKnownClasses) {
        $this->_aKnownClasses = array();
        if (is_array($aKnownClasses)) {
            $this->_aKnownClasses = $aKnownClasses;
            
            if (array_key_exists("options", $aKnownClasses)){
                $this->_aOptions=array_merge($this->_aOptions, $aKnownClasses["options"]);
            }
            return true;
        }
        return false;
    }

    /**
     * parse parameters (given GET/ POST is in by _aParams @see setParams)
     *     class  - class to initialize
     *     init   - parameters for constructor
     *     action - public method to call
     *     args   - arguments for the method
     * @return bool
     */
    private function _parseParams() {
        $aMinParams = array("class", "action");
        $aMaxParams = array_merge($aMinParams, array("args"));
        $sErrors = '';

        // set defaults
        $this->_sClass = false;
        $this->_aInit = array();
        $this->_sAction = false;
        $this->_aArgs = array();

        if (!$this->_aKnownClasses || !is_array($this->_aKnownClasses)) {
            $this->_quit("ERROR: no configuration was found."
                    . "You can set it with initialisation <br>\n"
                    . "<code>\$o = new " . __CLASS__ . " ( \$aOptions );</code><br>\n"
                    . "or<br>\n"
                    . "<code>\$o = new " . __CLASS__ . " ();<br>\n"
                    . "\$o -> setConfig ( \$aOptions );</code><br>\n"
            );
        }

        if(!count($this->_aParams)){
            echo $this->_wrapAsWebpage(
                    '<h2>Welcome!</h2>'
                    . '<p>'
                    . 'Here is a web GUI to interactively select and input all parameters.<br>'
                    . 'In a production environment you should disable it in the JSON config below options -> enableGui = 0.'
                    . '</p>'.
                    $this->_showClasshelp());
            die();
        }
        // check minimal params
        foreach ($aMinParams as $sKey) {
            $bFound = array_key_exists($sKey, $this->_aParams);
            if (!$bFound) {
                $sErrors.="- <em>" . $sKey . "</em><br>";
            }
        }
        // TODO: checkMaxParams
        
        // check if classname and action exist in configuration
        if (array_key_exists("class", $this->_aParams)) {
            if (!array_key_exists($this->_aParams["class"], $this->_aKnownClasses["classes"])) {
                $this->_quit('ERROR: parameter <em>class = ' . $this->_aParams["class"] . '</em> is invalid. I cannot handle this class.', $this->_showClasshelp());
            } else {
                // set internal vars: classname
                $this->_sClass = $this->_aParams["class"];
                $this->_sClassfile = $this->_aKnownClasses["classes"][$this->_aParams["class"]]["file"];

                // get arguments for the method
                if (array_key_exists("init", $this->_aParams)) {
                    try {
                        $aTmp = json_decode($this->_aParams["init"], 1);
                    } catch (Exception $e) {
                        // nop
                    }
                    if (!is_array($aTmp)) {
                        $this->_quit(
                                'ERROR: wrong request - init value must be a json string<br>'
                                . 'examples:<br>'
                                . '- one arg <code>(...)&init=["my string"]</code><br>'
                                . '- two args <code>(...)&init=["my string", 123]</code> '
                                , $this->_showClasshelp());
                    }
                    $this->_aInit = $aTmp;
                }
            }
            // get method name to call
            if (array_key_exists("action", $this->_aParams)) {
                if (!array_key_exists($this->_aParams["action"], $this->_aKnownClasses["classes"][$this->_aParams["class"]]["actions"])) {
                    $this->_quit('ERROR: class ' . $this->_aParams["class"] . ' exists but it has no <em>action = ' . $this->_aParams["action"] . '</em>.', $this->_showClasshelp());
                } else {
                    // set internal vars
                    $this->_sAction = $this->_aParams["action"];
                }
            }
            // get arguments for the method
            if (array_key_exists("args", $this->_aParams)) {
                try {
                    $aTmp = json_decode($this->_aParams["args"], 1);
                } catch (Exception $e) {
                    // nop
                }
                if (!is_array($aTmp)) {
                    $this->_quit(
                            'ERROR: wrong request - args value must be a json string<br>'
                            . 'examples:<br>'
                            . '- one arg <code>(...)&args=["my string"]</code><br>'
                            . '- two args <code>(...)&args=["my string", 123]</code> '
                            , $this->_showClasshelp());
                }
                $this->_aArgs = $aTmp;
            }
        }


        if (array_key_exists("type", $this->_aParams)) {
            $this->setOutputType($this->_aParams["type"]);
        }
        if ($sErrors) {
            $this->_quit('ERROR: wrong request -  a required parameter was not found:<br>' . $sErrors, $this->_showClasshelp());
        }
        return true;
    }

    /**
     * helper function: parse php doc comment block
     * @param string $sComment
     * @return string
     */
    private function _parseComment($sComment) {
        $sReturn = $sComment;
        if (!$sReturn) {
            return '<div class="warning">WARNING: this object has no PHPDOC comment.</div>';
        }

        // all @-Tags
        $aTags = array(
            "abstract", "access", "author", "category", "copyright", "deprecated", "example",
            "final", "filesource", "global", "ignore", "internal", "license",
            "link", "method", "name", "package", "param", "property", "return",
            "see", "since", "static", "staticvar", "subpackage", "todo", "tutorial",
            "uses", "var", "version"
        );

        $sReturn = preg_replace('@[\ \t][\ \t]*@', ' ', $sReturn); // remove multiple spaces

        $sReturn = preg_replace('@^\/\*\*.*@', '', $sReturn); // remove first comment line
        $sReturn = preg_replace('@.*\*\/@', '', $sReturn);  // remove last comment line
        $sReturn = preg_replace('@ \* @', '', $sReturn);    // remove " * " 
        $sReturn = preg_replace('@ \*@', '', $sReturn);     // remove " *" 
        $sReturn = preg_replace('@(<br>)*$@', '', $sReturn);
        $sReturn = preg_replace('@^\<br\>@', '', $sReturn);


        /*
          foreach(array("param", "return", "var") as $sKey){
          $sReturn=preg_replace('/\@'.$sKey.' ([a-zA-Z\|]*)\ (.*)\<br\>/U', '@'.$sKey.' <span style="font-weight: bold; color:#000;">$1</span> <span style="font-weight: bold;">$2</span><br>', $sReturn);
          }
         */
        $sReturn = preg_replace('/(\@.*)$/s', '<span class="phpdoc">$1</span>', $sReturn);
        foreach ($aTags as $sKey) {
            $sReturn = preg_replace('/\@(' . $sKey . ')/U', '<span class="doctag">@' . $sKey . '</span>', $sReturn);
        }
        if ($sReturn) {
            $sReturn = '<pre>' . $sReturn . '</pre><span class="phpdoctitle">PHPDOC</span>';
        }
        return $sReturn;
    }

    /**
     * quit with error: returns 404 and the given errormessage in the body
     * @param type $sError
     */
    private function _quit($sError, $sMore = "") {
        header("Status: 400 Bad Request");
        echo $this->_wrapAsWebpage(
                $sMore, '<div class="error">' . $sError . '</div>'
        );
        die();
    }

    

    /**
     * read a class and fetch information about its methods and parameters
     */
    private function _collectClassData(){
        // loop classes of the given config
        foreach (array_keys($this->_aKnownClasses["classes"]) as $sMyClass) {

            $RefClass=&$this->_aKnownClasses["classes"][$sMyClass];
            
            require_once($this->_sClassDir . $RefClass["file"]);
            $oRefClass = new ReflectionClass($sMyClass);
            
            $RefClass=&$this->_aKnownClasses["classes"][$sMyClass];
            $RefClass['phpdoc-class']=$this->_parseComment($oRefClass->getDocComment());
            $RefClass['active']=(array_key_exists("class", $this->_aParams) && $sMyClass===$this->_aParams["class"])?true:false;

            // loop configured methods of a class
            // foreach (array_keys($this->_aKnownClasses["classes"][$sMyClass]["actions"]) as $sAction) {
            foreach (array_merge(array("__construct"), array_keys($this->_aKnownClasses["classes"][$sMyClass]["actions"])) as $sAction) {
            // foreach (array_keys($this->_aKnownClasses["classes"][$sMyClass]["actions"]) as $sAction) {
                $oMethod = false;
                try {
                    $oMethod = $oRefClass->getMethod($sAction);
                } catch (Exception $e) {
                    //   nop
                    echo $e->getMessage();
                }
                if ($oMethod) {
                    $bActive=(array_key_exists("action", $this->_aParams) && $sAction===$this->_aParams["action"])?true:false;
                    $sComment = $this->_parseComment($oRefClass->getMethod($sAction)->getDocComment());

                    $sActionKey=($sAction==="__construct")?"init":"args";
                    $aGivenParams=false;
                    if (array_key_exists($sActionKey, $this->_aParams)){
                        $aGivenParams=json_decode($this->_aParams[$sActionKey]);
                    }
                    
                    if (!$aGivenParams){
                        $aGivenParams=array();
                    }

                    $aParams=array();
                    $iRequired=$oMethod->getNumberOfRequiredParameters();
                    
                    // loop params of the method
                    $iCount=0;
                    foreach($oMethod->getParameters() as $oParam){
                        
                        preg_match('@Parameter\ \#.*\[.*(\$.*)\ \]@', $oParam->__toString(), $aTmp);
                        preg_match('@(.*)@', $aTmp[1], $aTmp1);
                        $aParams[$iCount]["orig"]=$oParam->__toString();
                        $sValue=false;
                        if (count($aTmp1)){
                            $sVarname=trim($aTmp1[1]);
                            $sValue=false;
                            $sDefaultValue=false;
                            if(strpos($sVarname, '=')>0){
                                preg_match('@(.*)=\ (.*)@', $aTmp[1], $aTmp1);
                                $sVarname=trim($aTmp1[1]);
                                $sValue=$aTmp1[2];
                                $sDefaultValue=$aTmp1[2];
                            }
                            
                            $sValue=str_replace(array("'", '"'), array('', ''), $sValue);
                            if ($sValue=="Array"){ 
                                $sValue='{ }';
                            }
                            if ($sValue=="false"){ 
                                $sValue='';
                            }
                            
                            $aParams[$iCount]["varname"]=$sVarname;
                            $aParams[$iCount]["defaultvalue"]=$sDefaultValue;
                        }
                        /*
                        $aParams[$iCount]["required"]=false;
                        } else {
                            $aParams[$iCount]["required"]=true;
                        }
                         */
                        $sValue=(array_key_exists($iCount, $aGivenParams) && $aGivenParams[$iCount])?json_encode($aGivenParams[$iCount]):$sValue;
                        $aParams[$iCount]["value"]=$sValue;
                        
                        $aParams[$iCount]["required"]=$iCount<$iRequired ? true:false;
                        $iCount++;
                    }
                    $RefClass["actions"][$sAction]['active']=$bActive;
                    $RefClass["actions"][$sAction]['phpdoc']=$sComment;
                    $RefClass["actions"][$sAction]['params']=$aParams;
                    $RefClass["actions"][$sAction]['value']=($bActive && array_key_exists("args", $this->_aParams))?$this->_aParams["args"]:'';
                    
                    if($sAction==="__construct"){
                        $RefClass["actions"][$sAction]['value']=(array_key_exists("init", $this->_aParams))?$this->_aParams["init"]:'';
                    }
                } else {
                    // '' . $sAction . ' &lt;&lt;&lt; ERROR in configuration: this method does not exist'
                }
            }
            
        }        
    }
    
    /**
     * get html code for input fields of the parameters of a class
     * @param string $sMyClass  name of the class
     * @param string $sAction   name of the method to render
     * @return string
     */
    private function _showMethodInputForm($sMyClass, $sAction, $sIdPrefixBase){
        $sHtml='';
        $aParams=array();

        $aRefMethod=$this->_aKnownClasses['classes'][$sMyClass]['actions'][$sAction];
        // $sHtml.="_showMethodInputForm($sMyClass, $sAction, $sIdPrefixBase)<br>";
        // $sHtml.='<pre>'.print_r($aRefMethod,1 ) . '</pre>';

        $sHtml.=$aRefMethod["phpdoc"];
        $iCount=count($aRefMethod["params"]);
        if ($iCount) {
            
            $sHtml.='<fieldset><legend>Enter each parameter params to generate json: </legend>';
            foreach($aRefMethod["params"] as $sKey=>$aParam){
                $sIdInput=$sIdPrefixBase.$sKey;
                // $sHtml.='<pre>'.print_r($aParam, 1).'</pre>';
                $sFkt='updateMethodVar(\''.$sIdPrefixBase.'\', '.$iCount.')';
                $sChanges='onchange="'.$sFkt.'" '
                        . 'onkeypress="'.$sFkt.'" '
                        . 'onkeyup="'.$sFkt.'" '
                        ;
                    $sHtml.='<label for="'.$sIdInput.'">'.$aParam["varname"].' = </label>'
                    .'<input type="text" id="'.$sIdInput.'" size="30" '.$sChanges.' '
                            . 'value="'.str_replace('"', '&quot;', $aParam["value"]).'" '
                            . ($aParam["required"] ? 'required="required" pattern=".*" ' : '')
                            . '>'
                    ;
                if ($aParam["required"]){
                    $sHtml.=' (*)';
                } else {
                    $sHtml.=' &laquo; optional; default is <span class="defaultvalue">'.$aParam["defaultvalue"].'</span><br>';
                }
            }
            $sHtml.='</fieldset><br>';
        } else {
            $sHtml.='(no parameters)<br>';
        }
        
        return $sHtml;
    }
    
    
    /**
     * show help - return html code for gui of the given method
     * called in _showClasshelp()
     * @param string $sMyClass  name of the class
     * @param string $sAction   name of the method to render
     * @return string
     */
    private function _showActionHelp($sMyClass, $sAction){
        $sReturn = '';
        $RefClass=&$this->_aKnownClasses["classes"][$sMyClass];
        $sStyle = ' style="display: none;"';
        $sBtnValue = '+';
        $sCssClass = '';
        if (array_key_exists('active', $RefClass["actions"][$sAction]) && $RefClass["actions"][$sAction]["active"]) {
            $sCssClass = 'active';
            $sStyle = '';
            $sBtnValue = '-';
        }
        $sValInit=array_key_exists('value', $RefClass['actions']['__construct'])?$RefClass['actions']['__construct']['value']:'';
        $sValInit=str_replace('"', '&quot;', $sValInit);
        if (array_key_exists("params", $RefClass["actions"][$sAction])) {
            $sIdDescription = "help-" . $sMyClass . "-" . $sAction;
            $sValArgs=array_key_exists('value', $RefClass['actions'][$sAction])?$RefClass['actions'][$sAction]['value']:'';
            $sValArgs=str_replace('"', '&quot;', $sValArgs);
            $sIdPrefix=md5($sMyClass.$sAction);
            $sChanges='onchange="update(\''.$sIdPrefix.'\')" onkeypress="update(\''.$sIdPrefix.'\')" onkeyup="update(\''.$sIdPrefix.'\')" ';
            $sReturn .= '<li class="actionname ' . $sCssClass . '">'
                    . '<h4>'
                    . '<button onclick="toggleDesciption(\'' . $sIdDescription . '\', this)">' . $sBtnValue . '</button> '
                    . $sAction
                    . '</h4>'
                    . '<div id="' . $sIdDescription . '"' . $sStyle . '>'
                    
                    . '<input type="hidden" id="'.$sIdPrefix.'class" value="'.$sMyClass.'" />'
                    . '<input type="hidden" id="'.$sIdPrefix.'action" value="'.$sAction.'" />'
                    
                    . '<blockquote>'
                    
                        . '<h5>init parameters of class &quot;'.$sMyClass.'&quot;</h5>'
                        . '<blockquote>'
                            . '<code>$o = new '.$sMyClass.' ( ... )</code><br>'
                            . $this->_showMethodInputForm($sMyClass, "__construct", $sIdPrefix."init")
                    ;
                    /*
                     * all method vars as json
                     */
                    if (count($RefClass['actions']["__construct"]['params'])){
                        $sReturn .='<div style="display: none;">'
                                . 'OR<br><fieldset><legend>json</legend>'
                            . '<label for="'.$sIdPrefix.'init">init=</label>'
                            . '<input type="text" id="'.$sIdPrefix.'init" size="80" class="einit" name="init" value="'.$sValInit.'" '.$sChanges.'/><br>'
                            . '</fieldset></div>';
                    }
                    $sReturn .= '</blockquote>'

                        . '<h5>parameters of method &quot;'.$sAction.'&quot;</h5>'
                        . '<blockquote>'
                            . '<code>$o ->' . $sAction . ' ( ... )</code><br>'
                            . $this->_showMethodInputForm($sMyClass, $sAction, $sIdPrefix."args");
                    /*
                     * all method vars as json
                     */
                    if (count($RefClass['actions'][$sAction]['params'])){
                        $sReturn .='<div style="display: none;">'
                                . 'OR<br><fieldset><legend>json</legend>'
                            . '<label for="'.$sIdPrefix.'args">args=</label>'
                            . '<input type="text" id="'.$sIdPrefix.'args" size="80" name="args" value="'.$sValArgs.'" '.$sChanges.'/><br>'
                            . '</fieldset></div>';
                    }
                    $sReturn .='</blockquote>'
                    
                    . '<h5>select output type</h5>'
                    . '<blockquote>'
                    . '<select id="'.$sIdPrefix.'type" '.$sChanges.'>';
                        foreach (array("json", "raw") as $sType) {
                            $sReturn .= '<option value="'.$sType.'"';
                            if (array_key_exists("type", $this->_aParams) && $sType === $this->_aParams["type"]){ 
                                $sReturn .= ' selected="selected"'; 
                            }
                            $sReturn .= '>'.$sType.'</option>';   
                        }                    
            $sReturn .= '</select>'
                    . '</blockquote>'
                    
                    . '<h5>preview:</h5>'
                    . '<blockquote>'
                    . '<textarea id="'.$sIdPrefix.'url" cols="120" rows="2" disabled="disabled"></textarea><br><br>'
                    . '<div id="'.$sIdPrefix.'code"></div>'
                    . '</blockquote>'
                    . '<button class="btnsubmit" onclick="run(\''.$sIdPrefix.'\');">go</button>'
                    . '</blockquote>'
                    . '</div>'
                    . '</li>'
                    ;
        } else {
            $sReturn .= '<li class="actionname error">'
                    . '' . $sAction . ' &lt;&lt;&lt; ERROR in configuration: this method does not exist'
                    . '</li>';
        }
        return $sReturn;
    }
    
    
    /**
     * show help - return html gui with list of allowed classes
     * @return string
     */
    private function _showClasshelp() {
        $sReturn = '';

        $this->_collectClassData();
        $sReturn .= '<h2>Explore</h2>'
                . '<p>configured, accessible classes are:</p>'
                . '<ul class="classes">';
        foreach (array_keys($this->_aKnownClasses["classes"]) as $sMyClass) {
            $RefClass=&$this->_aKnownClasses["classes"][$sMyClass];

            $sIdDescription = "help-" . $sMyClass . "";
            $sClass = '';
            $sStyle = ' style="display: none;"';
            $sBtnValue = '+';
            if ($RefClass["active"]) {
                $sClass = 'active';
                $sStyle = '';
                $sBtnValue = '-';
            }
            $sReturn .= '<li class="classname ' . $sClass . ' ">'
                    . '<h3>'
                    . '<button onclick="toggleDesciption(\'' . $sIdDescription . '\', this)">' . $sBtnValue . '</button> '
                    . '<a href="?class=' . $sMyClass . '">' . $sMyClass . '</a>'
                    . '</h3>'
                    . '<div id="' . $sIdDescription . '"' . $sStyle . '>'
                    . $RefClass["phpdoc-class"]
                    . '<p>allowed methods:</p><ul>';
                foreach (array_keys($RefClass["actions"]) as $sAction) {
                    if ($sAction=="__construct"){
                        continue;
                    }
                    $sReturn.=$this->_showActionHelp($sMyClass, $sAction);
                }            
            foreach($RefClass["actions"] as $sAction){
                // nop
            }
            $sReturn .= '</div>'
                    . '</li>';
        }
        $sReturn .= '</ul>';
        return $sReturn;
    }

    private function _utf8ize($d) {
        if (is_array($d))
            foreach ($d as $k => $v)
                $d[$k] = $this->_utf8ize($v);

        else if (is_object($d))
            foreach ($d as $k => $v)
                $d->$k = $this->_utf8ize($v);
        else
            return utf8_encode($d);

        return $d;
    }



    /**
     * render output as html page
     * @param string $sBody   html body
     * @param string $sError  error message
     * @return string
     */
    private function _wrapAsWebpage($sBody = "", $sError = "") {

        $sClassSelect = '<span class="urlvalue">[class]</span>';
        $sClassInit = '<span class="urlvalue">[initparams]</span>';
        $sActionSelect = '<span class="urlvalue">[action]</span>';
        $sParamSelect = '<span class="urlvalue">[parameters]</span>';
        $sTypeSelect = '<span class="urlvalue">[type: raw|json]</span>';

        $sSyntax = sprintf(
                '<pre>?'
                . '<span class="urlvar">class</span>=%s'
                . '&<span class="urlvar">init</span>=%s'
                . '&<span class="urlvar">action</span>=%s'
                . '&<span class="urlvar">args</span>=%s'
                . '&<span class="urlvar">type</span>=%s'
                . '</pre>', $sClassSelect, $sClassInit, $sActionSelect, $sParamSelect, $sTypeSelect
        );

        /*
          if ($this->_sClass){
          $sClassSelect='<strong>'.$this->_sClass.'</strong>';
          }
          if ($this->_sAction){
          $sActionSelect='<strong>'.$this->_sAction.'</strong>';
          }
          if ($this->_aArgs){
          $sActionSelect='<strong>'.$this->_aParams["args"].'</strong>';
          }

          $sSyntax2=sprintf(
          '<pre>?<span class="urlvar">class</span>=%s&<span class="urlvar">action</span>=%s&<span class="urlvar">args</span>=%s&<span class="urlvar">type</span>=%s</pre>',
          $sClassSelect,
          $sActionSelect,
          $sParamSelect,
          $sTypeSelect
          );
          if ($sSyntax!=$sSyntax2){
          $sSyntax.='<br><br>change it here:<br>'.$sSyntax2;
          }
          $sSyntax='general syntax:<br>'.$sSyntax;
         */
        $sReturn = '<!DOCTYPE html>' . "\n"
                . '<html>'
                . '<head>'
                . '<title>' . $this->_sTitle . '</title>'
                . '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />'
                . '<style>'
                    . 'body{background:#aaa; color: #ddd; font-family: verdana,arial; margin: 0;}'
                    . '#content{padding: 2em; color: #444; background:#f8f8f8;}'
                    . '#footer{padding: 0 2em; border-top: 0.5em solid #999;}'
                    . 'a,a:visited{text-decoration: none; color: #000;}'
                    . 'a.button{border: 2px solid #888; background:#aaa; padding: 0.2em; color:#eee; border-radius: 0.3em;}'
                    . 'a.try{background:#080; border-color: #0c0;}'
                    . 'a.try:hover{background:#0b0; border-color: #0c0;}'
                    . 'button{ width: 2em; height: 2em; background: #44a; border: 3px solid #338; border-radius: 0.3em; color:#eee; font-size: 110%;}'
                    . 'button .btnsubmit{ background: #4a4 !important; border: 3px solid #383;  }'
                    . 'h1{background:#222; color:#ccc; padding: 0.3em; margin: 0; border-bottom: 0.2em solid #999;}'
                    . 'h1 a, h1 a:visited{color:#888;}'
                    . 'h1 a span{color:#666; font-size: 45%;}'
                    . 'h2{margin: 3em 0 1em;  }'
                    . 'h3,h4{margin: 0;}'
                    . 'fieldset{border: 1px solid rgba(0,0,0,0.2); background: rgba(0,0,0,0.05); }'
                    . 'input {border: 0px solid #ccc; padding: 0.1em; border-radius: 0.2em; color: #348; font-size: 100%;}' 
                    . 'label{width: 10em; float: left; text-align: right; color:#008;}'
                    . 'ul{padding-left: 0; margin: 0; border-radius: 0.5em;}'
                    . 'ul ul{margin: 1em;}'
                    . '.classes li{list-style: none; border-radius: 0.3em; padding: 0.3em; margin-bottom: 0.3em;}'
                    . 'li{border-left: 5px solid #aaa; }'
                    . 'li.active{border-left: 5px solid #aaa; }'
                    . 'li li.active{border-left: 5px solid #c00; background: #fcc;}'
                    . 'pre{margin: 0; border: #ddd solid 1px; border-radius: 0.5em; padding: 0.5em; background: #fff; opacity: 0.7;}'
                    . 'pre .urlvar{color: #480; font-weight: bold;}'
                    . 'pre .urlvalue{color: #606;}'
                    . 'pre pre{display: inline; padding: 0; border: 0;}'
                    . 'select {border: 2px solid #ddd; padding: 0.3em; border-radius: 0.5em; color: #348; font-size: 100%;}' 
                    . 'td{color:#444;}'
                    . '.classname{ background:#eee; }'
                    . '.actionname{background:#ddd; background: rgba(0,0,0,0.1);}'
                    . '.phpdoctitle{display: none; background:#ccc; color:#fff;float: right; margin-right: 0.5em; padding: 0 1em; border-radius: 0.5em; border-top-left-radius: 0;border-top-right-radius: 0;}'
                    . '.phpdoc{background:#f0f4f8;}'
                    . '.doctag{ color:#080; }'
                    . '.error{ color:#800; background:#fcc; padding: 0.5em; margin-bottom: 2em; border-left: 4px solid;}'
                    . '.warning{ color:#a96; background:#fc8; padding: 0.5em; margin-bottom: 2em; border-left: 4px solid;}'
                    . '.defaultvalue{color: #33c;}'
                . '</style>'
                . '<script>'
                . 'function toggleDesciption(sId, a){'
                . 'var o=document.getElementById(sId);'
                . 'if (o) {'
                . 'o.style.display=(o.style.display=="")?"none":"";'
                . '}'
                . 'console.log(a);'
                . 'if (a) {'
                . 'a.innerHTML=(a.innerHTML=="+")?"-":"+";'
                . '}'
                . '}'
                .'
                    function updateMethodVar(sIdPrefix, iCount){
                        var sOut=\'\';
                        var sVal=false;
                        var aParams=[];
                        if (!document.getElementById(sIdPrefix)){
                            return false;
                        }
                        for (var i=0; i<iCount; i++) {
                            sVal=document.getElementById(sIdPrefix+""+i).value;
                            if(!sVal){
                                sVal="";
                            } else {
                                if (sVal % 1 == 0){
                                    sVal=sVal/1;
                                } else {
                                    if( sVal === "true" || sVal === "false" ){
                                        sVal=sVal || sVal;
                                    } else {
                                        if (
                                            sVal.indexOf(\'"\')<0
                                            && sVal.indexOf("\'")<0
                                            && sVal.indexOf("\[")<0
                                            && sVal.indexOf("\]")<0
                                            && sVal.indexOf("\{")<0
                                            && sVal.indexOf("\}")<0
                                        ){
                                            sVal=\'"\'+sVal+\'"\';
                                        }
                                    }
                                }
                            }
                            if (sOut){
                                sOut+=", ";
                            }
                            sOut+=sVal;
                            aParams[i]=sVal;
                        }
                        document.getElementById(sIdPrefix).value="[" + sOut + "]";
                        // document.getElementById(sIdPrefix).value=JSON.stringify(aParams);
                        document.getElementById(sIdPrefix).onchange();
                    }
                    
                    function update(sIdPrefix){
                        if (!document.getElementById(sIdPrefix+"class")){
                            return false;
                        }
                        var sUrl="?";
                        var arguments=["class", "init", "action", "args", "type"];
                        for (var i=0; i<arguments.length; i++){
                            if (document.getElementById(sIdPrefix+arguments[i])
                            && document.getElementById(sIdPrefix+arguments[i]).value
                            ){
                                sUrl+="&"+arguments[i]+"="+document.getElementById(sIdPrefix+arguments[i]).value;
                            }
                        }
                        document.getElementById(sIdPrefix+"url").value=sUrl;
                        return true;
                    }
                    
                    function run(sIdPrefix){
                        if (update(sIdPrefix)) {
                            location.href=document.getElementById(sIdPrefix+"url").value;
                        }
                        return true;
                    }
                    
                  '
                . '</script>'
                . '</head>'
                . '<body>';

        if($this->_aOptions["enableGui"]){
            $sReturn.= 
                  '<h1><a href="?">' . $this->_sTitle . ' <span>'.$this->_sVersion.'</span></a></h1>'
                . '<div id="content">'
                . $sError;
            if($this->_aOptions["enableDump"]){
                $sReturn.= '<h2>Debug</h2>'
                        . '_aParams:<pre>'.print_r($this->_aParams, true).'</pre>'
                        . '_aKnownClasses:<pre>'.print_r($this->_aKnownClasses, true).'</pre>'
                    ;
            }
            $sReturn.= $sBody
                . '<h2>Syntax</h2>'
                . $sSyntax
                . '</div><div id="footer">'
                . '<h2>About</h2>'
                . '<p>'
                . 'SWS is a free wrapper for other php classes and makes their public '
                . 'functions (or a subset of them) available as a webservice.<br>'
                . 'It offers a webinterface to explore available classes and methods. '
                . 'The shown information is parsed by reading phpdoc.'
                . '</p>'
                . '<p>'
                . 'GNU GPL 3.0;<br>';
            if ($this->_sUrlDoc) {
                $sReturn.='<a href="' . $this->_sUrlDoc . '">' . $this->_sUrlDoc . '</a> ';
            }
            if ($this->_sUrlHome) {
                $sReturn.='<a href="' . $this->_sUrlHome . '">' . $this->_sUrlHome . '</a> ';
            }
            $sReturn.='</p></div>';
        } else {
                  
            $sReturn.=
                    '<div id="content" style="height: 1000px; overflow: hidden;">'
                    .$sError .''. $this->_sTitle
                    .'</div>'
                    ;
        }
        $sReturn.='</body></html>';
        return $sReturn;
    }

    // ----------------------------------------------------------------------
    // setter
    // ----------------------------------------------------------------------

    /**
     * set ouput type of response
     * @param string $sOutputType  one of json|raw
     * @return bool
     */
    public function setOutputType($sOutputType) {
        $this->_sOutputType = $sOutputType;
        return true;
    }

    /**
     * put query parameter
     * @param array $aParams  parameters; default false and params will be taken from $_GET and $_POST
     * @return array
     */
    public function setParams($aParams = false) {

        $this->_aParams = array();
        if (is_array($aParams)) {
            $this->_aParams = $aParams;
        } else {
            if (isset($_GET) && count($_GET)) {
                foreach ($_GET as $key => $value)
                    $this->_aParams[$key] = $value;
            }
            if (isset($_POST) && count($_POST)) {
                foreach ($_POST as $key => $value)
                    $this->_aParams[$key] = $value;
            }
        }
        $this->_parseParams();
        return $this->_aParams;
    }

    // ----------------------------------------------------------------------
    // actions
    // ----------------------------------------------------------------------

    /**
     * execute function
     * @param type $sOutputType
     */
    public function run($sOutputType = false) {
        if (!count($this->_aParams)){
            $this->setParams();
        }
        $this->_parseParams();
        require_once($this->_sClassDir . $this->_sClassfile);

        if (!class_exists($this->_sClass)) {
            $this->_quit("ERROR: class does not exist: " . $this->_sClass);
        }
        
        // ------------------------------------------------------------
        // init object
        // ------------------------------------------------------------
        switch (count($this->_aInit)) {
            case 0: $o = new $this->_sClass;
                break;
            case 1: $o = new $this->_sClass($this->_aInit[0]);
                break;
            case 2: $o = new $this->_sClass($this->_aInit[0], $this->_aInit[1]);
                break;
            case 3: $o = new $this->_sClass($this->_aInit[0], $this->_aInit[1], $this->_aInit[2]);
                break;
            case 4: $o = new $this->_sClass($this->_aInit[0], $this->_aInit[1], $this->_aInit[2], $this->_aInit[3]);
                break;
            default: die("internal ERROR in run(): need to set a new case with up to " . count($this->_aInit) . " arguments in " . __FILE__);
        }   
        
        if (!method_exists($o, $this->_sAction)) {
            $this->_quit("ERROR: method does not exist: " . $this->_sClass . " -> " . $this->_sAction . "()");
        }

        // ------------------------------------------------------------
        // call function
        // ------------------------------------------------------------
        switch (count($this->_aArgs)) {
            case 0: $return = call_user_func(array($o, $this->_sAction));
                break;
            case 1: $return = call_user_func(array($o, $this->_sAction), $this->_aArgs[0]);
                break;
            case 2: $return = call_user_func(array($o, $this->_sAction), $this->_aArgs[0], $this->_aArgs[1]);
                break;
            case 3: $return = call_user_func(array($o, $this->_sAction), $this->_aArgs[0], $this->_aArgs[1], $this->_aArgs[2]);
                break;
            case 4: $return = call_user_func(array($o, $this->_sAction), $this->_aArgs[0], $this->_aArgs[1], $this->_aArgs[2], $this->_aArgs[3]);
                break;
            default: die("internal ERROR in run(): need to set a new case with up to " . count($this->_aArgs) . " arguments in " . __FILE__);
        }
        /*
        if (!$return) {
            $this->_quit("ERROR: no output");
        }
         * 
         */

        // ------------------------------------------------------------
        // output
        // ------------------------------------------------------------
        switch ($this->_sOutputType) {
            case "json":
                header('Content-Type: application/json');
                echo json_encode($this->_utf8ize($return));
                break;
            case "raw":
                if (is_array($return)) {
                    $this->_quit("ERROR: Wrong output type for that method. Your selection was raw. But the return of " . $this->_sClass . '->' . $this->_sAction . "() is an array", $this->_showClasshelp());
                }
                echo $return;
                break;
            default:
                $this->_quit("ERROR in " . __FUNCTION__ . ": outputtype </em>" . $this->_sOutputType . "</em> is unknown", $this->_showClasshelp());
        }
    }

}

// ----------------------------------------------------------------------
