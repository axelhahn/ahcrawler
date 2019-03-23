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
        // see https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#tab=Headers
        'security'=>array(
            'Content-Security-Policy'=>array(),
            'Expect-CT'=>array(),
            'Feature-Policy'=>array(),
            'Public-Key-Pins'=>array(),
            'Referrer-Policy'=>array(),
            'Strict-Transport-Security'=>array(),
            'X-Content-Type-Options'=>array(),
            'X-Frame-Options'=>array(),
            'X-Permitted-Cross-Domain-Policies'=>array(),
            'X-XSS-Protection'=>array(),
        ),
        // keys will be handled as lowercase
        'unwanted'=>array(
            'Referrer-Policy'=>array('badregex'=>'unsafe\-url'), // not recommended
            'Server'=>array('badregex'=>'[0-9]\.[0-9]'), // only with a version i.e. Apache/2.4.34 (Unix)
            'X-Powered-By'=>array(),
        ),
        // en: https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
        // de: https://de.wikipedia.org/wiki/Liste_der_HTTP-Headerfelder
        'httpv1'=>array(
            '_status'                   =>array(),
            'A-IM'                      =>array(),
            'Accept'                    =>array(),
            'Accept-Additions'          =>array(),
            'Accept-Charset'            =>array(),
            'Accept-Encoding'           =>array(),
            'Accept-Features'           =>array(),
            'Accept-Language'           =>array(),
            'Accept-Ranges'             =>array(),
            'Age'                       =>array(),
            'Allow'                     =>array(),
            'Alternates'                =>array(),
            'Authentication-Info'       =>array(),
            'Authorization'             =>array(),
            'C-Ext'                     =>array(),
            'C-Man'                     =>array(),
            'C-Opt'                     =>array(),
            'C-PEP'                     =>array(),
            'C-PEP-Info'                =>array(),
            'Cache-Control'             =>array(),
            'Connection'                =>array(),
            'Content-Base'              =>array(),
            'Content-Disposition'       =>array(),
            'Content-Encoding'          =>array(),
            'Content-ID'                =>array(),
            'Content-Language'          =>array(),
            'Content-Length'            =>array(),
            'Content-Location'          =>array(),
            'Content-MD5'               =>array(),
            'Content-Range'             =>array(),
            'Content-Script-Type'       =>array(),
            'Content-Style-Type'        =>array(),
            'Content-Type'              =>array(),
            'Content-Version'           =>array(),
            'Cookie'                    =>array(),
            'Cookie2'                   =>array(),
            'DAV'                       =>array(),
            'Date'                      =>array(),
            'Default-Style'             =>array(),
            'Delta-Base'                =>array(),
            'Depth'                     =>array(),
            'Derived-From'              =>array(),
            'Destination'               =>array(),
            'Differential-ID'           =>array(),
            'Digest'                    =>array(),
            'ETag'                      =>array(),
            'Expect'                    =>array(),
            'Expires'                   =>array(),
            'Ext'                       =>array(),
            'From'                      =>array(),
            'GetProfile'                =>array(),
            'Host'                      =>array(),
            'IM'                        =>array(),
            'If'                        =>array(),
            'If-Match'                  =>array(),
            'If-Modified-Since'         =>array(),
            'If-None-Match'             =>array(),
            'If-Range'                  =>array(),
            'If-Unmodified-Since'       =>array(),
            'Keep-Alive'                =>array(),
            'Label'                     =>array(),
            'Last-Modified'             =>array(),
            'Link'                      =>array(),
            'Location'                  =>array(),
            'Lock-Token'                =>array(),
            'MIME-Version'              =>array(),
            'Man'                       =>array(),
            'Max-Forwards'              =>array(),
            'Meter'                     =>array(),
            'Negotiate'                 =>array(),
            'Opt'                       =>array(),
            'Ordering-Type'             =>array(),
            'Overwrite'                 =>array(),
            'P3P'                       =>array(),
            'PEP'                       =>array(),
            'PICS-Label'                =>array(),
            'Pep-Info'                  =>array(),
            'Position'                  =>array(),
            'Pragma'                    =>array(),
            'ProfileObject'             =>array(),
            'Protocol'                  =>array(),
            'Protocol-Info'             =>array(),
            'Protocol-Query'            =>array(),
            'Protocol-Request'          =>array(),
            'Proxy-Authenticate'        =>array(),
            'Proxy-Authentication-Info' =>array(),
            'Proxy-Authorization'       =>array(),
            'Proxy-Features'            =>array(),
            'Proxy-Instruction'         =>array(),
            'Public'                    =>array(),
            'Range'                     =>array(),
            'Referer'                   =>array(),
            'Retry-After'               =>array(),
            'Safe'                      =>array(),
            'Security-Scheme'           =>array(),
            'Server'                    =>array(),
            'Set-Cookie'                =>array(),
            'Set-Cookie2'               =>array(),
            'SetProfile'                =>array(),
            'SoapAction'                =>array(),
            'Status-URI'                =>array(),
            'Surrogate-Capability'      =>array(),
            'Surrogate-Control'         =>array(),
            'TCN'                       =>array(),
            'TE'                        =>array(),
            'Timeout'                   =>array(),
            'Trailer'                   =>array(),
            'Transfer-Encoding'         =>array(),
            'URI'                       =>array(),
            'Upgrade'                   =>array(),
            'User-Agent'                =>array(),
            'Variant-Vary'              =>array(),
            'Vary'                      =>array(),
            'Via'                       =>array(),
            'WWW-Authenticate'          =>array(),
            'Want-Digest'               =>array(),
            'Warning'                   =>array(),
        ),
        // see  https://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Common_non-standard_response_fields
        'non-standard'=>array(
            'Content-Security-Policy'=>array(),
            'Refresh'=>array(),
            'Status'=>array(),
            'Timing-Allow-Origin'=>array(),
            'X-Content-Duration'=>array(),
            'X-Content-Security-Policy'=>array(),
            'X-Content-Type-Options'=>array(),
            'X-Correlation-ID'=>array(),
            'X-Pingback'=>array(), // http://www.hixie.ch/specs/pingback/pingback#TOC2.1
            'X-Powered-By'=>array(),
            'X-Request-ID'=>array(),
            'X-Robots-Tag'=>array(),
            'X-UA-Compatible'=>array(),
            'X-WebKit-CSP'=>array(),
            'X-XSS-Protection'=>array(),
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
        // echo "DEBUG: " . $sHeader."<br>";
        if($aTmp && is_array($aTmp) && count($aTmp)){
            foreach($aTmp as $sLine){
                if(!$sLine){
                    continue;
                }
                $aTmp=explode(":", $sLine, 2);
                $sVarname=count($aTmp)>1 ? $aTmp[0]:'_status';
                $value=count($aTmp)>1 ? $aTmp[1]:$sLine;
                $this->_aHeader[]=array($sVarname, trim($value));
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
    public function checkHeaders(){
        $aReturn=array();
        $aKnownHeader=array_merge($this->_aHeaderVars['httpv1'], $this->_aHeaderVars['security']);

        $iLine=0;
        foreach($this->_aHeader as $aLine){
            $iLine++;
            list($varname, $val) = $aLine;
            $sFound=false;
            $sBad=false;
            foreach(array('non-standard', 'httpv1', 'security') as $sSection){
                foreach($this->_aHeaderVars[$sSection] as $sVar=>$aChecks){
                    if(strtolower($varname)=== strtolower($sVar)){
                        $sFound=$sSection;
                    }
                }
            }
            $sSection='unwanted';
            foreach($this->_aHeaderVars[$sSection] as $sVar=>$aChecks){

                if(isset($aChecks['badregex'])){
                    preg_match('/('.$sVar.'):\ (.*'.$aChecks['badregex'].'.*)/i', "$varname: $val", $aMatches);
                    if(count($aMatches)){
                        $sBad=$sSection;
                    }
                } else {
                    if(strtolower($varname)=== strtolower($sVar)){
                        $sBad=$sSection;
                    }
                }
            }
            
            // $aReturn[strtolower($varname)]=array(
            $aReturn[]=array(
                'var'=>$varname,
                'value'=>$val,
                'line'=>$iLine,
                'found'=>$sFound ? $sFound : 'unknown',
                'bad'=>$sBad ? $sBad : false,
            );
        }
        return $aReturn;        
    }
    public function checkUnknowHeaders(){
        $aReturn=array();
        foreach($this->checkHeaders() as $aData){
            if($aData['found']==='unknown'){
                $aReturn[]=$aData;
            }
        }
        return $aReturn;
    }
    public function checkUnwantedHeaders(){
        $aReturn=array();
        foreach($this->checkHeaders() as $aData){
            if($aData['bad']){
                $aReturn[]=array('var'=>$aData['var'], 'value'=>$aData['value'], 'line'=>$aData['line']);;
            }
        }
        return $aReturn;
        /*
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
        */
    }
}
