<?php

namespace axelhahn;

/**
 * 
 * CDN OR LOCAL
 * use source from a CDN cdnjs.cloudflare.com or local folder?
 * class for projects
 *
 * @author hahn
 */
class cdnorlocal {
    //put your code here

    /**
     * force usage of cdn
     * @param type $param
     */
    var $bForeceCdn=false;
    /**
     * force usage of local files (for development: set true to download)
     * @var type 
     */
    var $bForeceLocal=false;
        
    var $aLibs=array();
    
    var $_libConfig='cdnorlocal.json';
    var $_bDebug=false;
    
    var $sVendorDir=false;
    var $sVendorUrl=false;
    var $sTplCdnUrl='https://cdnjs.cloudflare.com/ajax/libs';
    var $sTplRelUrl='[PKG]/[VERSION]';
    var $sUrlApiPackage='https://api.cdnjs.com/libraries/%s';

    // ----------------------------------------------------------------------
    // 
    // INIT
    // 
    // ----------------------------------------------------------------------
    
    /**
     * constructor
     * @param  array  $aOptions with possible keys vendordir, vendorurl
     */
    public function __construct($aOptions=false) {
        
        if(is_array($aOptions)){
            if(array_key_exists('debug', $aOptions)){
                $this->setDebug($aOptions['debug']);
            }
            if(array_key_exists('vendordir', $aOptions)){
                $this->setVendorDir($aOptions['vendordir']);
            }
            if(array_key_exists('vendorurl', $aOptions)){
                $this->setVendorUrl($aOptions['vendorurl']);
            }
            if(array_key_exists('vendorrelpath', $aOptions)){
                $this->setVendorWithRelpath($aOptions['vendorrelpath']);
            }
        }
        if(!$this->sVendorDir){
            $this->setVendorUrl('/vendor');
            $this->setVendorDir($_SERVER['DOCUMENT_ROOT'].'/vendor');
        }
        $this->load(__DIR__.'/'.$this->_libConfig);
        $this->_makeReplace();
    }

    /**
     * write debug output if the flag was set
     * @param string  $sText  message to show
     */    
    protected function _wd($sText){
        if ($this->_bDebug){
            echo "DEBUG " . __CLASS__ . " - " . $sText . "<br>\n";
        }
    }
    
    /**
     * return array with subkeys local and cdn
     * @param type $sLibrary
     * @param type $sVersion
     * @return type
     */
    private function _getLocations($sLibrary,$sVersion) {
        $aReturn=array();
        
        $aSearch=array();
        $aReplace=array();

        $aSearch[]='[VERSION]';
        $aReplace[]=$sVersion;
        // $aSearch[]='[PKG]';
        // $aReplace[]=$sProject;
        $aReturn['local']=$this->sVendorDir.'/'.str_replace($aSearch, $aReplace, $this->sTplRelUrl);
        $aReturn['cdn']=str_replace($aSearch, $aReplace, $this->sTplCdnUrl.'/'.$this->sTplRelUrl);
        
        return $aReturn;
    }
    
    /**
     * replace placeholders from .json
     * @return boolean
     */
    private function _makeReplace(){
        foreach ($this->aLibs as $sLibrary=>$aItem){
            $aSearch=array();
            $aReplace=array();
            $aSearch[]='[VERSION]';
            $aReplace[]=$aItem['version'];
            $aSearch[]='[PKG]';
            $aReplace[]=$sLibrary;
            
            // $this->aLibs[$sKey]['local']=str_replace($aSearch, $aReplace, $this->aLibs[$sKey]['local']);
            // $this->aLibs[$sKey]['cdn']=str_replace($aSearch, $aReplace, $this->aLibs[$sKey]['cdn']);

            
            // $this->aLibs[$sKey]['fs']=$this->sVendorDir.'/'.str_replace($aSearch, $aReplace, $this->sTplRe);
            
            $this->aLibs[$sLibrary]['fs']=$this->sVendorDir.'/'.str_replace($aSearch, $aReplace, $this->sTplRelUrl);
            $this->aLibs[$sLibrary]['local']=$this->sVendorUrl.'/'.str_replace($aSearch, $aReplace, $this->sTplRelUrl);
            $this->aLibs[$sLibrary]['cdn']=str_replace($aSearch, $aReplace, $this->sTplCdnUrl.'/'.$this->sTplRelUrl);
            
            // $this->aLibs[$sKey]['_use']=$this->_getLocalOrCdn($this->aLibs[$sKey]['local'], $this->aLibs[$sKey]['cdn']);
            $this->aLibs[$sLibrary]['use']=$this->_getKeyLocalOrCdn($sLibrary);
            
            // print_r($this->_getVersionsCdn($sLibrary));
            // print_r($this->_getAssetsCdn($sLibrary));
            // die();
        }
        // echo '<pre>'.print_r($this->aLibs, 1) . '</pre>';die();
        return true;
    }
    
    /**
     * helper function; get local url if a path exists or a CDN
     * @param string  $sLocalDir  local dir below vendor/
     * @param string  $sCdn       alternative CDN url
     * @return string
     */
    private function ___TODO_DELETE_getLocalOrCdn($sLocalDir, $sCdn){
        if ($this->bForeceCdn){
            return $sCdn;
        }
        // $sLocal=$_SERVER['DOCUMENT_ROOT'].$this->sVendorDir . $sLocalDir;
        $sLocal=$this->sVendorDir . $sLocalDir;
        $this->_wd("checking [$sLocal] ...");
        $sReturn = (is_dir($sLocal)|| file_exists($sLocal)) ? $this->sVendorUrl.$sLocalDir : $sCdn;
        $this->_wd("return $sReturn");
        return $sReturn;
    }
    
    /**
     * helper function; what url to use: "cdn" or "local" (if local dir exists)
     * @param string  $sLibrary
     * @return string
     */
    private function _getKeyLocalOrCdn($sLibrary){
        $this->_wd(__METHOD__ . " - checking " . $this->aLibs[$sLibrary]['fs']);
        if ($this->bForeceCdn){
            return 'cdn';
        }
        if($this->bForeceLocal && !is_dir($this->aLibs[$sLibrary]['fs'])){
            $this->_wd(__METHOD__ . " - need to download ");
            $this->_downloadAssets($sLibrary);
        }
        $sReturn = (is_dir($this->aLibs[$sLibrary]['fs'])) ? 'local' : 'cdn';
        $this->_wd(__METHOD__ . " - return $sReturn");
        return $sReturn;
    }

    /**
     * load a config for libraries
     * @param type $filename
     * @return type
     */
    public function load($filename){
        if(!file_exists($filename)){
            die(__CLASS__.' :: ' . __FUNCTION__ . ' - ERROR: file does not exist ' . $filename .'.' );
        }
        $aCfg=json_decode(file_get_contents($filename), 1);
        if (!is_array($aCfg)){
            die(__CLASS__.' :: ' . __FUNCTION__ . ' - ERROR: file  ' . $filename .' exists but is not a valid json.' );
        }
        $this->_wd("config was loaded: $filename");
        return $this->aLibs=$aCfg;
    }
    
    
    // ----------------------------------------------------------------------
    // 
    // getter and setter
    // 
    // ----------------------------------------------------------------------

    /**
     * get current flag for forcing CDN
     * @return boolean
     */
    public function getCdnForce(){
        return $this->bForeceCdn;
    }

    /**
     * set flag for forcing CDN
     * @return boolean
     */
    public function setCdnForce($bNewflag){
        $this->bForeceCdn=!!$bNewflag;
        $this->_makeReplace();
        return true;
    }
    
    /**
     * set a vendor url to use as link for libraries
     * @param string  $sNewValue  new url
     * @return string
     */
    public function setDebug($sNewValue){
        $this->_wd(__METHOD__ . "($sNewValue)");
        return $this->_bDebug=$sNewValue;
    }
    /**
     * set a vendor dir to scan libraries
     * @param string  $sNewValue  new local dir
     * @return string
     */
    public function setVendorWithRelpath($sRelpath){
        $this->_wd(__METHOD__ . "($sRelpath)");
        $this->setVendorDir(__DIR__ . '/'.$sRelpath);
        $this->setVendorUrl($sRelpath);
        return true;
    }
    /**
     * set a vendor dir to scan libraries
     * @param string  $sNewValue   new local dir
     * @param boolean $bMustExist  optional flag: ensure that the directory exists
     * @return string
     */
    
    public function setVendorDir($sNewValue, $bMustExist=false){
        $this->_wd(__METHOD__ . "($sNewValue)");
        if(!file_exists($sNewValue) && $bMustExist){
            die(__CLASS__ . ' ' . __METHOD__ . ' - ERROR: directory ['.$sNewValue.'] does not exist.');
        }
        return $this->sVendorDir=$sNewValue;
    }

    /**
     * set a vendor url to use as link for libraries
     * @param string  $sNewValue  new url
     * @return string
     */
    public function setVendorUrl($sNewValue){
        $this->_wd(__METHOD__ . "($sNewValue)");
        return $this->sVendorUrl=$sNewValue;
    }
    
    
    // ----------------------------------------------------------------------
    // 
    // rendering
    // 
    // ----------------------------------------------------------------------

    /**
     * helper function 
     * @param string  $sLibrary  name of the library
     * @param string  $sFile     optional file inside a library path (without beginning /)
     * @return string
     */
    private function _getWhatToUse($sLibrary, $sFile){
        $this->_wd(__METHOD__ . "($sLibrary, $sFile)");
        if(!array_key_exists($sLibrary, $this->aLibs)){
            die(__CLASS__.' :: ' . __FUNCTION__ . ' - ERROR: file or project ' . $sLibrary .' was not configured (yet).' );
        }
        if(!array_key_exists('use', $this->aLibs[$sLibrary])){
            die(__CLASS__.' :: ' . __FUNCTION__ . ' - ERROR: _use was not found in key [' . $sLibrary .'] - maybe it was not configured correctly' . print_r($this->aLibs[$sLibrary], 1));
        }
        $sKey=$this->aLibs[$sLibrary]['use'];
        $this->_wd(__METHOD__ . " -> $sKey");
        return  $this->aLibs[$sLibrary][$sKey] . ($sFile ? '/'.$sFile : '');
        
    }
    
    /**
     * get html code to link a css file
     * @param string  $sLibrary  name of the library
     * @param string  $sFile     optional file inside a library path (without beginning /)
     * @return string
     */
    function getCss($sLibrary, $sFile=''){
        $sUrl=$this->_getWhatToUse($sLibrary, $sFile);
        return '<link rel="stylesheet" type="text/css" href="'.$sUrl.'">';
    }
    
    /**
     * get html code to link a javascript
     * @param string  $sLibrary  name of the library
     * @param string  $sFile     optional file inside a library path (without beginning /)
     * @return string
     */
    function getJs($sLibrary, $sFile=''){
        $sUrl=$this->_getWhatToUse($sLibrary, $sFile);
        return '<script src="'.$sUrl.'"></script>';
    }
    
}
