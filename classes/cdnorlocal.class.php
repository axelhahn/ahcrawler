<?php

namespace axelhahn;

/**
 * use source from a CDN cdnjs.cloudflare.com or local folder?
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
        
        $this->setVendorUrl('/vendor');
        $this->setVendorDir($_SERVER['DOCUMENT_ROOT'].'/vendor');
        
        if(is_array($aOptions)){
            if(array_key_exists('vendordir', $aOptions)){
                $this->setVendorDir($aOptions['vendordir']);
            }
            if(array_key_exists('vendorurl', $aOptions)){
                $this->setVendorUrl($aOptions['vendorurl']);
            }
            if(array_key_exists('debug', $aOptions)){
                $this->setDebug($aOptions['debug']);
            }
        }
        $this->load(__DIR__.'/'.$this->_libConfig);
        $this->_makeReplace();
    }

    /**
     * write debug output if the flag was set
     * @param string  $sText  message to show
     */    
    private function _wd($sText){
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
    public function setVendorDir($sNewValue){
        $this->_wd(__METHOD__ . "($sNewValue)");
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
    // download
    // 
    // ----------------------------------------------------------------------

    private function _getCdnMeta($sLibrary) {
        $this->_wd(__METHOD__ . "($sLibrary)");
        if (!array_key_exists('_cdn', $this->aLibs[$sLibrary])){
            
            // TODO: add cache with TTL
            
            $sApiUrl=sprintf($this->sUrlApiPackage, $sLibrary);
            $this->_wd(__METHOD__ . " fetch $sApiUrl");
            $aJson=json_decode(file_get_contents($sApiUrl));
            // echo '<pre>'.print_r($aJson, 1).'</pre>';
            $this->aLibs[$sLibrary]['_cdn']=$aJson;
        }
        return $this->aLibs[$sLibrary]['_cdn'];
    }
    
    private function _getVersionsCdn($sLibrary) {
        $this->_getCdnMeta($sLibrary);
        $aReturn=array();
        if ($this->aLibs[$sLibrary]['_cdn']){
            foreach ($this->aLibs[$sLibrary]['_cdn']->assets as $aAsset){
                $aReturn[]=$aAsset->version;
            }
        }
        return $aReturn;
    }
    private function _getAssetsCdn($sLibrary, $sVersion=false) {
        $this->_getCdnMeta($sLibrary);
        if(!$sVersion){
            $sVersion=$this->aLibs[$sLibrary]['version'];
        }
        /*
        if($sVersion==='latest'){
            $sVersion=$this->aLibs[$sLibrary]['_cdn']->assets[0]->version;
        }
         */
        $this->_wd(__METHOD__ . ' version: '. $sVersion);
        if ($this->aLibs[$sLibrary]['_cdn']){
            foreach ($this->aLibs[$sLibrary]['_cdn']->assets as $aAsset){
                if($aAsset->version === $sVersion){
                    return $aAsset->files;
                }
            }
        }
        return false;
    }
    
    /**
     * recursively remove a directory; used in _downloadAssets()
     * @param string  $dir  name of the directory
     */
    private function _rrmdir($dir) {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file))
                rrmdir($file);
            else
                unlink($file);
        }
        rmdir($dir);
    }

    /**
     * download all files of a given library.
     * @param  string  $sLibrary
     * #return boolean
     */
    private function _downloadAssets($sLibrary){
        if(!array_key_exists($sLibrary, $this->aLibs)){
            die(__CLASS__ . ' - ERROR in '.__METHOD__.' - a project named ['.$sLibrary.'] does ot exist.');
        }
        if(is_dir($this->aLibs[$sLibrary]['local'])){
            $this->_wd(__METHOD__ . ' remove '. $this->aLibs[$sLibrary]['local']);
            $this->_rrmdir($this->aLibs[$sLibrary]['local']);
        }
        $sTmpdir=$this->aLibs[$sLibrary]['local'].'_';
        $bAllDownloaded=true;
        foreach($this->_getAssetsCdn($sLibrary) as $sFilename){
            
            $sRemotefile=$this->aLibs[$sLibrary]['cdn'].'/'.$sFilename;
            $sTmpfile=$sTmpdir.'/'.$sFilename;
            $sLocalfile=$this->aLibs[$sLibrary]['local'].'/'.$sFilename;
            
            if(!file_exists($sLocalfile)){
                if(!file_exists($sTmpfile)){
                    $this->_wd(__METHOD__ . ' download '. $sRemotefile);
                    $sData=file_get_contents($sRemotefile);
                    if($sData){
                        if(!is_dir(dirname($sTmpfile))){
                            $this->_wd(__METHOD__ . ' mkdir '. dirname($sTmpfile));
                            mkdir(dirname($sTmpfile), 755, 1);
                        }
                        $this->_wd(__METHOD__ . ' writing '. $sTmpfile . ' ('. strlen($sData).' byte)');
                        file_put_contents($sTmpfile, $sData);
                    } else {
                        $bAllDownloaded=true;                        
                    }
                }
            }
        }
        if($bAllDownloaded){
            $this->_wd(__METHOD__ . ' move '. $sTmpdir.' to '.$this->aLibs[$sLibrary]['local']);
            rename($sTmpdir, $this->aLibs[$sLibrary]['local']);
        }
        
        // TODO cleanup older version if a lib
        
        return true;
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
        if(!array_key_exists($sLibrary, $this->aLibs)){
            die(__CLASS__.' :: ' . __FUNCTION__ . ' - ERROR: file or project ' . $sLibrary .' was not configured (yet).' );
        }
        if(!array_key_exists('use', $this->aLibs[$sLibrary])){
            die(__CLASS__.' :: ' . __FUNCTION__ . ' - ERROR: _use was not found in key [' . $sLibrary .'] - maybe it was not configured correctly' . print_r($this->aLibs[$sLibrary], 1));
        }
        $sKey=$this->aLibs[$sLibrary]['use'];
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
