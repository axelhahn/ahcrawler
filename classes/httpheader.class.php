<?php

/**
 * httpstatus
 *
 * @author hahn
 */
class httpheader {

/*    
content-security-policy: default-src 'self'; script-src 'self' cdnjs.cloudflare.com; img-src 'self'; style-src 'self' 'unsafe-inline' fonts.googleapis.com cdnjs.cloudflare.com; font-src 'self' fonts.gstatic.com cdnjs.cloudflare.com; form-action 'self'; report-uri https://scotthelme.report-uri.com/r/default/csp/enforce
strict-transport-security: max-age=31536000; includeSubDomains; preload
referrer-policy: strict-origin-when-cross-origin
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block; report=https://scotthelme.report-uri.com/r/d/xss/enforce
x-content-type-options: nosniff
expect-ct: max-age=0, report-uri="https://scotthelme.report-uri.com/r/d/ct/reportOnly"
feature-policy: accelerometer 'none'; camera 'none'; geolocation 'none'; gyroscope 'none'; magnetometer 'none'; microphone 'none'; payment 'none'; usb 'none'
*/
    
    protected $_aHeaderVars=array(
        'security'=>array(
            'content-security-policy'=>array(),
            'expect-ct'=>array(),
            'feature-policy'=>array(),
            'referrer-policy'=>array(),
            'strict-transport-security'=>array(),
            'x-content-type-options'=>array(),
            'x-frame-options'=>array(),
            'x-xss-protection'=>array(),
        ),
        'unwanted'=>array(
            'server'=>array('badregex'=>'[0-9]\.[0-9]'), // only with a version i.e. Apache/2.4.34 (Unix)
            'x-powered-by'=>array(),
        ),
    );
    
    protected $_sHeader='';
    protected $_aHeader=array();


    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------
    public function __construct() {
        return true;
    }
    
    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------
    
    /**
     * set http header to analyze with a JSON string
     * 
     * @param string $sJson
     * @return array
     */
    public function setHeaderAsString($sHeader){
        $this->_aHeader=array();
        $this->_sHeader='';
        $aTmp=explode("\r\n", $sHeader);
        // echo $sHeader."<br>";
        if($aTmp && is_array($aTmp) && count($aTmp)){
            foreach($aTmp as $sLine){
                $aTmp=explode(":", $sLine, 2);
                $sVarname=count($aTmp)>1 ? $aTmp[0]:'_status';
                $value=count($aTmp)>1 ? $aTmp[1]:$aTmp[0];
                $this->_aHeader[$sVarname]=$value;
            }
            // $this->_aHeader=$aTmp;
        }
        $this->_sHeader=$sHeader."\r\n";
        return $this->_aHeader;
    }


    public function dump(){
        return $this->_aHeader;
    }
    
    public function getHeaderstring(){
        return $this->_sHeader;
    }

    // ----------------------------------------------------------------------
    // Security Headers
    // ----------------------------------------------------------------------
    public function checkSecurityHeaders(){
        $aReturn=array();
        foreach(array_keys($this->_aHeaderVars['security']) as $sVar){
            // $aReturn[$sVar]['found']= stristr($this->_sHeader, $sVar);
            preg_match('/('.$sVar.'):\ (.*)\r\n/i', $this->_sHeader, $aMatches);
            $aReturn[$sVar]=count($aMatches) ? array('var'=>$aMatches[1], 'value'=>$aMatches[2]) : false;
        }
        return $aReturn;        
    }
    
    public function getCountBadSecurityHeaders(){
        $iReturn=0;
        foreach ($this->checkSecurityHeaders() as $val){
            $iReturn+=$val ? 0 : 1;
        }
        return $iReturn;
    }
    public function getCountOkSecurityHeaders(){
        $iReturn=0;
        foreach ($this->checkSecurityHeaders() as $val){
            $iReturn+=$val ? 1 : 0;
        }
        return $iReturn;
    }
    // ----------------------------------------------------------------------
    // Bad Headers
    // ----------------------------------------------------------------------
    public function checkUnwantedHeaders(){
        $aReturn=array();
        foreach($this->_aHeaderVars['unwanted'] as $sVar=>$aChecks){
            
            if(isset($aChecks['badregex'])){
                preg_match('/('.$sVar.'):\ (.*'.$aChecks['badregex'].'.*)\r\n/i', $this->_sHeader, $aMatches);
            } else {
                preg_match('/('.$sVar.'):\ (.*)\r\n/i', $this->_sHeader, $aMatches);
            }
            if(count($aMatches)){
                $aReturn[$sVar]=array('var'=>$aMatches[1], 'value'=>$aMatches[2]);
            }
        }
        return $aReturn;        
        
    }
}
