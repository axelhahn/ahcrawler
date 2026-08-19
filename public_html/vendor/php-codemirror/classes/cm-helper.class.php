<?php

declare(strict_types=1);

/**
 * ----------------------------------------------------------------------
 * 
 * HELPER CLASS for syntax highlighting with codemirror
 * 
 * @author Axel Hahn
 * @link https://github.com/axelhahn/php-codemirror
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * 
 * ----------------------------------------------------------------------
 * 2025-11-16  v0.1  <axel>  initial version
 * 2026-04-17  v0.2  <axel>  allow multiple instances
 * 2026-06-24  v0.3  <axel>  __lastModified__
 */

class cmhelper {

    /**
     * @var string base url for codemirror files
     */
    protected string $_sCmbaseUrl='';

    /**
     * @var array memory for loaded modes
     */
    protected array $_aModes2Load=[];

    /**
     * @var array memory for loaded themes
     */
    protected array $_aThemes2Load=[];

    /**
     * @var string html tags (css, js) für html head section
     */
    protected string $_sHtmlHead='';

    /**
     * @var string javascript code to iniialize editors
     */
    protected string $_sJS='';

    /**
     * Mapping array for codemirror modes
     * 
     * WORK IN PROGRESS 
     * These are just a few of the supported languages!
     * @see https://codemirror.net/5/mode/index.html
     * 
     * Per language/ file type we define
     * - load {array}  list of javascript files to load
     * - mode {string} mode value when initalizing editor object
     * 
     * @var array
     */
    public array $_aAvailableShModes=[];

    /**
     * @var array available themes
     */
    protected array $_aAvailableThemes=[];

    // ----------------------------------------------------------------------
    // 
    // ----------------------------------------------------------------------

    /**
     * __construct
     */
    public function __construct()
    {
        $aCfg=(array) (include 'cm-helper.config.php' ?: []);
        $this->_aAvailableShModes=(array) (include 'cm-helper.lang.php' ?: []);
        $this->_aAvailableThemes=(array) (include 'cm-helper.themes.php' ?: []);
        $this->setBase((string) ($aCfg['baseurl']??''));
    }

    // ----------------------------------------------------------------------
    //  SETTER
    // ----------------------------------------------------------------------

    /**
     * Set a base url for codemirror resources.
     * Its value cannot be verified - it is just used for html head
     * Check the browser dev tools -> network if the resource can be loaded.
     * 
     * @param string $sNewbase  new ur
     * @return void
     */
    public function setBase(string $sNewbase=''): void
    {
        $this->_sCmbaseUrl=$sNewbase ?? $this->_sCmbaseUrl;
        $this->_sHtmlHead='
            <!-- codemirror -->
            <link rel="stylesheet" href="'.$this->_sCmbaseUrl.'/codemirror.min.css">
            <script src="'.$this->_sCmbaseUrl.'/codemirror.min.js"></script>
            <script src="'.$this->_sCmbaseUrl.'/addon/selection/selection-pointer.min.js"></script>
            <script src="'.$this->_sCmbaseUrl.'/addon/edit/matchbrackets.min.js"></script>

            <!--
            <link rel="stylesheet" href="'.$this->_sCmbaseUrl.'/addon/lint/lint.css">
            <script src="'.$this->_sCmbaseUrl.'/addon/lint/lint.css"></script>
            -->
        ';

         // https://stackoverflow.com/questions/1462138/event-listener-for-when-element-becomes-visible
        $this->_sJS.='
        <script>
        function onVisible(element, callback) {
            new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                if(entry.intersectionRatio > 0) {
                    callback(element);
                    observer.disconnect();
                }
                });
            }).observe(element);
            if(!callback) return new Promise(r => callback=r);
        }
        </script>';
    }

    // ----------------------------------------------------------------------
    // ADD EDITOR
    // ----------------------------------------------------------------------

    /**
     * Add an editor with its own syntax highlighting for a textarea.
     * You can call this method multiple times for several editors with its
     * own options.
     * 
     * As a reminder: you need to call
     * - $o->getHtmlHead()
     * - $o->getJs()
     * ... to apply the editor settings in the html document
     * 
     * @param string $sMode         Mode/ language for syntax highlighting
     * @param string $sFormid       id of the textarea
     * @param array  $aMoreOptions  array of additional options. known subkeys are
     *                              - readOnly        bool    if true the editor is readonly
     *                              - theme           string  thene name
     * 
     *                              - indentUnit      int     indent unit; default: 4
     *                              - tabSize         int     tab size; default: 4
     *                              - lineNumbers     bool    show line numbers; default: false
     *                              - lineWrapping    bool    wrap long lines; default: false
     *                              - matchBrackets   bool    highlight matching brackets; default: true
     *                              - height          string  css value for height
     * @return bool
     */
    public function addEditor(string $sMode, string $sFormid='', array $aMoreOptions=[]):bool
    {

        static $iCmCounter;

        if(!($iCmCounter??false)){
            $iCmCounter=(int) 0;
        }
        $iCmCounter=(int) $iCmCounter;
        $iCmCounter++;


        $sTheme=(string) ($aMoreOptions['theme']??'');

        $this->_addMode($sMode);
        $this->_addTheme($sTheme);

        $iCmCounter++;

        if ($aMoreOptions['readonly'] ?? false) {
            echo "⚠️ ". __METHOD__." - WARNING: Rewrite option with camelcase '<strong>readOnly</strong>' (instead of 'readonly')<br>";

        }
        // see https://codemirror.net/3/doc/manual.html for options
        $sObjName="CodeMorrorEditor$iCmCounter";
        if($aMoreOptions['height'] ?? false){
            $sCss='
            <style>
                #'.$sFormid.' + div {height: '.(string) $aMoreOptions['height'].'; }
            </style>';
            if(!strstr($this->_sHtmlHead, $sCss)){
                $this->_sHtmlHead.=$sCss;
            }
        }
        $this->_sJS.='
            <script>
                onVisible(document.querySelector("#'.$sFormid.'"), 
                    () => '.$sObjName.' = CodeMirror.fromTextArea(
                        document.getElementById("'.$sFormid.'"), {
                            mode: "'.(string) ($this->_aAvailableShModes[$sMode]['mode'] ?? $sMode).'",
                            '.($sTheme ? "theme: \"$sTheme\"," : '').'

                            indentUnit: '.(int) ($aMoreOptions['indentUnit']??4).',
                            tabSize: '.(int) ($aMoreOptions['tabSize']??4).',
                            indentWithTabs: true,
                            lineNumbers: '.($aMoreOptions['lineNumbers']??true ? 'true' : 'false').',
                            lineWrapping: '.($aMoreOptions['lineWrapping']??false ? 'true' : 'false').',
                            matchBrackets: '.($aMoreOptions['matchBrackets']??true ? 'true' : 'false').',
                            '.($aMoreOptions['readOnly']??false ? 'readOnly: true,' : '').'
                    })
                );
            </script>            
        ';

        return true;
    }

    /**
     * Add an editor with its own syntax highlighting for a textarea.
     * You can call this method multiple times for several editors with its
     * own options.
     * 
     * As a reminder: you need to call
     * - $o->getHtmlHead()
     * - $o->getJs()
     * ... to apply the editor settings in the html document
     * 
     * @param array  $aTextarea     array attributes for the textarea. Required subkeys are
     *                              - id       string  id attribute
     *                              - class    string  css classes attribute; one of it must be "highlight-<type>"
     *                              optional subkeys
     *                              - name     string  name of the form variable
     *                              - value    string  initial value inside teaxtarea
     *                              - ... all other textarea attributes
     * @param array  $aMoreOptions  array of additional options. Known subkeys are
     *                              - readOnly        bool    if true the editor is readonly
     *                              - theme           string  thene name
     * 
     *                              - indentUnit      int     indent unit; default: 4
     *                              - tabSize         int     tab size; default: 4
     *                              - lineNumbers     bool    show line numbers; default: false
     *                              - lineWrapping    bool    wrap long lines; default: false
     *                              - matchBrackets   bool    highlight matching brackets; default: true
     *                              - height          string  TODO css value for height
     * @return string html code for textarea
     */
    public function addTextarea(array $aTextarea=[], array $aMoreOptions=[]):string
    {

        static $iCmCounter;

        if(!($iCmCounter??false)){
            $iCmCounter=0;
        }

        if(!($aTextarea['id']??false) || !($aTextarea['class']??false)){
            throw new Exception("First param array must have an 'id' key and 'class' key.");
        }

        preg_match('/highlight-([^ ]*)/', (string) ($aTextarea['class']??''), $aMatches);
        $sMode=$aMatches[1]??false;
        if(!$sMode){
            throw new Exception("First param array must have a key 'class' with a class named 'highlight-<mode>'.");
        }

        $this->addEditor($sMode, (string) ($aTextarea['id']??''), $aMoreOptions);
        $value=(string) ($aTextarea['value']??'');
        unset($aTextarea['value']);

        $sAtttr='';
        foreach($aTextarea as $sKey=>$sVal){
            $sAtttr.="$sKey=\"$sVal\" ";
        }
        $sReturn="<textarea $sAtttr>$value</textarea>";

        return $sReturn;
    }

    /**
     * add a syntax highlight mode to be loaded
     * 
     * @throws Exception
     * 
     * @param string $sMode
     * @return void
     */
    protected function _addMode(string $sMode): void
    {
        if($this->_aAvailableShModes[$sMode]??false){
            $aLoad=(array) ($this->_aAvailableShModes[$sMode]['load']??[]);
            foreach($aLoad as $sJsfile){
                if(!($this->_aModes2Load[$sJsfile]??false)){
                    $this->_aModes2Load[$sJsfile]=true;
                    $this->_sHtmlHead.="<script src=\"$this->_sCmbaseUrl/mode/$sJsfile/$sJsfile.js\"></script>";                        
                }
            }
        } else {
            echo "⚠️ ". __CLASS__." - WARNING: Unknown type 'highlight-<strong>$sMode</strong>'<br>
                Known mode are: "
                . implode(", ", array_keys($this->_aAvailableShModes))
                ."<br>"
                ;
            $sMode="text";
        }
    }

    /**
     * Add a theme to be loaded.
     * 
     * @throws Exception
     * 
     * @param string $sTheme  name of the theme
     * @return void
     */
    protected function _addTheme(string $sTheme): void
    {

        if($sTheme && $sTheme!=="default"){
            if(!in_array($sTheme, $this->_aAvailableThemes, true)){
                echo "⚠️ ". __CLASS__." - WARNING: Unknown theme '<strong>$sTheme</strong>'<br>
                    Known themes are: "
                    . implode(", ", array_map('strval', $this->_aAvailableThemes))
                    ."<br>"
                    ;
                $sTheme=false;
            }
            if($sTheme && !($this->_aThemes2Load[$sTheme] ?? false)){
                $this->_aThemes2Load[$sTheme]=true;
                $this->_sHtmlHead.="<link rel=\"stylesheet\" href=\"$this->_sCmbaseUrl/theme/$sTheme.min.css\">";
            }
        }
        
    }

    // ----------------------------------------------------------------------
    // GETTER
    // ----------------------------------------------------------------------

    /**
     * Get a list of supported syntax highlight modes
     * @return array
     */
    public function getModes($bWithOptions=false): array
    {
        return $bWithOptions 
            ? $this->_aAvailableShModes 
            : array_keys($this->_aAvailableShModes)
        ;
    }

    /**
     * Get a list of known themes
     * @return array
     */
    public function getThemes(): array
    {
        return $this->_aAvailableThemes;
    }

    // ----------------------------------------------------------------------

    /**
     * Get html code for html head lines
     * @return string
     */
    public function getHtmlHead(): string
    {
        return $this->_sHtmlHead;
    }

    /**
     * Get html code for javascript code for bottom of html page
     * @return string
     */
    public function getJs(): string
    {
        return $this->_sJS;
    }

}