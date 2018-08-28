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
            'Content-Security-Policy'=>array(),
            'Expect-CT'=>array(),
            'Feature-Policy'=>array(),
            'Referrer-Policy'=>array(),
            'Strict-Transport-Security'=>array(),
            'X-Content-Type-Options'=>array(),
            'X-Frame-Options'=>array(),
            'X-XSS-Protection'=>array(),
        ),
        'unwanted'=>array(
            'Referrer-Policy'=>array('badregex'=>'unsafe\-url'), // not recommended
            'Server'=>array('badregex'=>'[0-9]\.[0-9]'), // only with a version i.e. Apache/2.4.34 (Unix)
            'X-Powered-By'=>array(),
        ),
        // en: https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
        // de: https://de.wikipedia.org/wiki/Liste_der_HTTP-Headerfelder
        'standard'=>array(
            'Accept-Ranges'=>array(),
            'Age'=>array(),
            'Allow'=>array(),
            'Cache-Control'=>array(),
            'Connection'=>array(),
            'Content-Encoding'=>array(),
            'Content-Language'=>array(),
            'Content-Length'=>array(),
            'Content-Location'=>array(),
            'Content-MD5'=>array(),
            'Content-Disposition'=>array(),
            'Content-Range'=>array(),
            'Content-Security-Policy'=>array(),
            'Content-Type'=>array(),
            'Date'=>array(),
            'ETag'=>array(),
            'Expires'=>array(),
            'Last-Modified'=>array(),
            'Link'=>array(),
            'Location'=>array(),
            'P3P'=>array(),
            'Pragma'=>array(),
            'Proxy-Authenticate'=>array(),
            'Refresh'=>array(),
            'Retry-After'=>array(),
            'Server'=>array(),
            'Set-Cookie'=>array(),
            'Trailer'=>array(),
            'Transfer-Encoding'=>array(),
            'Vary'=>array(),
            'Via'=>array(),
            'Warning'=>array(),
            'WWW-Authenticate'=>array(),
        ),
        'non-standard'=>array(
            'X-UA-Compatible'=>array(),
            'X-Robots-Tag'=>array(),
            'X-Content-Type-Options'=>array(),
            'X-Frame-Options'=>array(),
            'X-XSS-Protection'=>array(),
            'X-Powered-By'=>array(),
            'X-UA-Compatible'=>array(),
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
