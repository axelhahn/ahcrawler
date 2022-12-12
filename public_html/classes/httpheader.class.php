<?php

/**
 * ____________________________________________________________________________
 *          __    ______                    __             
 *   ____ _/ /_  / ____/________ __      __/ /__  _____    
 *  / __ `/ __ \/ /   / ___/ __ `/ | /| / / / _ \/ ___/    
 * / /_/ / / / / /___/ /  / /_/ /| |/ |/ / /  __/ /        
 * \__,_/_/ /_/\____/_/   \__,_/ |__/|__/_/\___/_/         
 * ____________________________________________________________________________ 
 * Free software and OpenSource * GNU GPL 3
 * DOCS https://www.axel-hahn.de/docs/ahcrawler/index.htm
 * 
 * THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE <br>
 * LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR <br>
 * OTHER PARTIES PROVIDE THE PROGRAM ?AS IS? WITHOUT WARRANTY OF ANY KIND, <br>
 * EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED <br>
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE <br>
 * ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. <br>
 * SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY <br>
 * SERVICING, REPAIR OR CORRECTION.<br>
 * 
 * ----------------------------------------------------------------------------
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

    protected $_aHeaderVars = array(
        // en: https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
        // de: https://de.wikipedia.org/wiki/Liste_der_HTTP-Headerfelder
        // ... plus https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
        'http' => array(
            '_status' => array(),
            'Accept' => array('client'=>true),
            'Accept-Additions' => array(),
            'Accept-CH' => array('client'=>true),
            'Accept-Charset' => array('client'=>true),
            'Accept-CH-Lifetime' => array('client'=>true),
            'Accept-Encoding' => array('client'=>true),
            'Accept-Features' => array(),
            'Accept-Language' => array('client'=>true),
            'Accept-Patch' => array('response'=>true),
            'Accept-Ranges' => array('response'=>true),
            'Access-Control-Allow-Credentials' => array('response'=>true),
            'Access-Control-Allow-Headers' => array('response'=>true),
            'Access-Control-Allow-Methods' => array('response'=>true),
            'Access-Control-Allow-Origin' => array('response'=>true),
            'Access-Control-Expose-Headers' => array('response'=>true),
            'Access-Control-Max-Age' => array('response'=>true),
            'Access-Control-Request-Headers' => array(),
            'Access-Control-Request-Method' => array(),
            'Age' => array('tags' => array('cache')),
            'A-IM' => array(),
            'Allow' => array(),
            'Alternates' => array(),
            'Alt-Svc' => array(),
            'Authentication-Info' => array(),
            'Authorization' => array(),
            'Cache-Control' => array('tags' => array('cache')),
            'C-Ext' => array(),
            'Clear-Site-Data' => array(),
            'C-Man' => array(),
            'Connection' => array('response'=>true),
            'Content-Base' => array(),
            'Content-Disposition' => array('response'=>true),
            'Content-Encoding' => array('response'=>true, 'tags' => array('compression')),
            'Content-ID' => array(),
            'Content-Language' => array('response'=>true),
            'Content-Length' => array('response'=>true),
            'Content-Location' => array('response'=>true),
            'Content-MD5' => array(),
            'Content-Range' => array('response'=>true),
            'Content-Script-Type' => array(),
            'Content-Security-Policy' => array(),
            'Content-Security-Policy-Report-Only' => array(),
            'Content-Style-Type' => array(),
            'Content-Type' => array(),
            'Content-Version' => array(),
            'Cookie' => array(),
            'Cookie2' => array('obsolete'=>true),
            'C-Opt' => array(),
            'C-PEP-Info' => array(),
            'Cross-Origin-Resource-Policy' => array(),
            'Date' => array(),
            'DAV' => array(),
            'Default-Style' => array(),
            'Delta-Base' => array(),
            'Depth' => array(),
            'Derived-From' => array(),
            'Destination' => array(),
            'Device-Memory' => array(),
            'Differential-ID' => array(),
            'Digest' => array('response'=>true),
            'DNT' => array(),
            'DPR' => array(),
            'Early-Data' => array(),
            'ETag' => array('response'=>true, 'tags' => array('cache')),
            'Expect' => array('response'=>true),
            'Expect-CT' => array(),
            'Expires' => array('response'=>true, 'tags' => array('cache')),
            'Ext' => array(),
            'Feature-Policy' => array('response'=>true, 'tags' => array('feature', 'security'), "directives"=>array(
                "accelerometer",
                "ambient-light-sensor",
                "autoplay",
                "battery",
                "camera",
                "display-capture",
                "document-domain",
                "encrypted-media",
                "fullscreen",
                "geolocation",
                "gyroscope",
                "layout-animations",
                "legacy-image-formats",
                "magnetometer",
                "microphone",
                "midi",
                "oversized-images",
                "payment",
                "picture-in-picture",
                "publickey-credentials-get",
                "sync-xhr",
                "unoptimized-images",
                "unsized-media",
                "usb",
                "vibrate",
                "vr",
                "wake-lock",
                "xr",
                "xr-spatial-tracking"                
            )),
            'Forwarded' => array(),
            'From' => array('response'=>true),
            'GetProfile' => array(),
            'Host' => array('response'=>true),
            'If' => array(),
            'If-Match' => array('response'=>true),
            'If-Modified-Since' => array('response'=>true),
            'If-None-Match' => array('response'=>true),
            'If-Range' => array('response'=>true),
            'If-Unmodified-Since' => array('response'=>true),
            'IM' => array(),
            'Index' => array(),
            'Keep-Alive' => array('response'=>true),
            'Label' => array(),
            'Large-Allocation' => array(),
            'Last-Modified' => array('response'=>true),
            'Link' => array('response'=>true),
            'Location' => array('response'=>true),
            'Lock-Token' => array(),
            'Man' => array(),
            'Max-Forwards' => array(),
            'Meter' => array(),
            'MIME-Version' => array(),
            'Negotiate' => array(),
            'NEL' => array(),
            'Opt' => array(),
            'Ordering-Type' => array(),
            'Origin' => array(),
            'Overwrite' => array(),
            'P3P' => array(),
            'PEP' => array(),
            'Pep-Info' => array(),
            'PICS-Label' => array(),
            'Position' => array(),
            'Pragma' => array('response'=>true, 'tags' => array('cache', 'deprecated')),
            'ProfileObject' => array(),
            'Protocol' => array(),
            'Protocol-Info' => array(),
            'Protocol-Query' => array(),
            'Protocol-Request' => array(),
            'Proxy-Authenticate' => array('response'=>true),
            'Proxy-Authentication-Info' => array(),
            'Proxy-Authorization' => array('response'=>true),
            'Proxy-Features' => array(),
            'Proxy-Instruction' => array(),
            'Public' => array(),
            'Public-Key-Pins' => array('tags'=>array('deprecated', 'obsolete')),
            'Public-Key-Pins-Report-Only' => array('tags'=>array('deprecated', 'obsolete')),
            'Range' => array('response'=>true),
            'Referer' => array('response'=>true),
            'Referrer-Policy' => array(),
            'Retry-After' => array('response'=>true),
            'Safe' => array(),
            'Save-Data' => array(),
            'Sec-Fetch-Dest' => array(),
            'Sec-Fetch-Mode' => array(),
            'Sec-Fetch-Site' => array(),
            'Sec-Fetch-User' => array(),
            'Security-Scheme' => array(),
            'Sec-WebSocket-Accept' => array(),
            'Server' => array('response'=>true, 'unwantedregex' => '[0-9]*\.[0-9\.]*'),
            'Server-Timing' => array(),
            'Set-Cookie' => array('response'=>true),
            'Set-Cookie2' => array('obsolete'=>true, 'response'=>true),
            'SetProfile' => array(),
            'SoapAction' => array(),
            'SourceMap' => array(),
            'Status-URI' => array(),
            'Strict-Transport-Security' => array(),
            'Surrogate-Capability' => array(),
            'Surrogate-Control' => array(),
            'TCN' => array(),
            'TE' => array('response'=>true),
            'Timeout' => array(),
            'Timing-Allow-Origin' => array(),
            'Tk' => array(),
            'Trailer' => array('response'=>true),
            'Transfer-Encoding' => array('response'=>true),
            'Upgrade' => array(),
            'Upgrade-Insecure-Requests' => array(),
            'URI' => array(),
            'User-Agent' => array('response'=>true),
            'Variant-Vary' => array(),
            'Vary' => array('response'=>true),
            'Via' => array('response'=>true),
            'Want-Digest' => array('response'=>true),
            'Warning' => array('response'=>true),
            'WWW-Authenticate' => array('response'=>true),
            'X-Content-Type-Options' => array(),
            'X-DNS-Prefetch-Control' => array(),
            'X-Frame-Options' => array(),
            'X-XSS-Protection' => array(),
        // ),
        // see  https://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Common_non-standard_response_fields
        // 'non-standard' => array(
            'Refresh' => array('response'=>true, 'tags' => array('non-standard')),
            'Status' => array('response'=>true, 'tags' => array('non-standard')),
            'Timing-Allow-Origin' => array('response'=>true, 'tags' => array('non-standard')),
            'X-Content-Duration' => array('response'=>true, 'tags' => array('non-standard')),
            'X-Content-Security-Policy' => array('response'=>true, 'tags' => array('non-standard')),
            'X-Correlation-ID' => array('response'=>true, 'tags' => array('non-standard')),
            'X-Forwarded-For' => array('response'=>true, 'tags' => array('non-standard')),
            'X-Forwarded-Host' => array('response'=>true, 'tags' => array('non-standard')),
            'X-Forwarded-Proto' => array('response'=>true, 'tags' => array('non-standard')),
            'X-Pingback' => array('response'=>true, 'tags' => array('non-standard')), // http://www.hixie.ch/specs/pingback/pingback#TOC2.1
            'X-Powered-By' => array('response'=>true, 'tags' => array('non-standard', 'unwanted'), 'unwantedregex' => '[0-9]*\.[0-9\.]*'),
            'X-Request-ID' => array('response'=>true, 'tags' => array('non-standard')),
            'X-Robots-Tag' => array('response'=>true, 'tags' => array('non-standard')),
            'X-UA-Compatible' => array('response'=>true, 'tags' => array('non-standard')),
            'X-WebKit-CSP' => array('response'=>true, 'tags' => array('non-standard')),
        // ),
        // see https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#tab=Headers
        // 'security' => array(
            'Content-Security-Policy' =>           array('response'=>true, 'tags' => array('security'), 'badvalueregex' => 'unsafe\-'),
            'Expect-CT' =>                         array('response'=>true, 'tags' => array('security')),
            'Feature-Policy' =>                    array('response'=>true, 'tags' => array('feature', 'security')),
            'Public-Key-Pins' =>                   array('response'=>true, 'tags' => array('security', 'deprecated')),
            'Referrer-Policy' =>                   array('response'=>true, 'tags' => array('security')),
            'Strict-Transport-Security' =>         array('response'=>true, 'tags' => array('security')),
            'X-Content-Type-Options' =>            array('response'=>true, 'tags' => array('security')),
            'X-Frame-Options' =>                   array('response'=>true, 'tags' => array('security'), 'badvalueregex' => 'ALLOW-FROM'),
            'X-Permitted-Cross-Domain-Policies' => array('response'=>true, 'tags' => array('security')),
            'X-XSS-Protection' =>                  array('response'=>true, 'tags' => array('security')),
        ),
    );
    protected $_sHeader = '';
    protected $_aHeader = array();
    protected $_aParsedHeader = array();

    // ----------------------------------------------------------------------
    // CONSTRUCT
    // ----------------------------------------------------------------------
    public function __construct() {
        return true;
    }

    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------

    /**
     * helper: split header info by ":" and return an array with variable and value
     * 
     * @param  string  $sLine  single http response header line
     * @return array
     */
    protected function _splitHeaderLine($sLine) {
        $aTmp = explode(":", $sLine, 2);
        $sVarname = count($aTmp) > 1 ? $aTmp[0] : '_status';
        $value = count($aTmp) > 1 ? $aTmp[1] : $sLine;
        return array($sVarname, trim($value));
    }

    /**
     * set http response header to analyze
     * 
     * @param string $sJson
     * @return array
     */
    public function setHeaderAsString($sHeader) {
        $this->_aHeader = array();
        $this->_sHeader = '';
        if(!$sHeader){
            return false;
        }
        $aTmp = explode("\r\n", $sHeader);
        // echo "DEBUG: " . $sHeader."<br>";
        if ($aTmp && is_array($aTmp) && count($aTmp)) {
            foreach ($aTmp as $sLine) {
                if (!$sLine) {
                    break;
                }
                $this->_aHeader[] = $this->_splitHeaderLine($sLine);
                $this->_sHeader .= $sLine . "\r\n";
            }
        }
        $this->_aParsedHeader = $this->parseHeaders();

        return $this->_aHeader;
    }

    // ----------------------------------------------------------------------

    /**
     * get the current http response header as array of lines
     * @return array
     */
    public function getHeaderAsArray() {
        return $this->_aHeader;
    }

    /**
     * get the conmlete http response header as single string
     * @return string
     */
    public function getHeaderAsString() {
        return $this->_sHeader;
    }

    // ----------------------------------------------------------------------
    // Security Headers
    // ----------------------------------------------------------------------

    /**
     * get hash with known headers in the config that match a tag
     * 
     * @param   string  $sTag  name of tag to filter
     * @return array
     */
    protected function _getHeaderCfgOfGivenTag($sTag){
        $aReturn=array();
    
        foreach ($this->_aHeaderVars as $sSection => $aSection) {
            foreach ($aSection as $sVar => $aParams) {
                if(isset($aParams['tags']) && in_array($sTag, $aParams['tags'])){
                    $aReturn[$sVar]=$aParams;
                }
            }
        }
        return $aReturn;
    }

    /**
     * get an array with defined securtity headers and existance in the current
     * response header data
     * 
     * @return array
     */
    public function getSecurityHeaders() {
        $aReturn = array();
        foreach ($this->_getHeaderCfgOfGivenTag('security') as $sVar=>$aChecks) {
            $aReturn[$sVar]=false;
            $iLine = 0;
            foreach ($this->getHeadersWithGivenTag('security') as $aLine) {
                $iLine++;
                if(strtolower($aLine['var'])=== strtolower($sVar)){
                $aReturn[$sVar]=$aLine;
                }
            }
        }
        return $aReturn;
    }

    /**
     * get count of securtity headers that were NOT found
     * 
     * @return integer
     */
    public function getCountBadSecurityHeaders() {
        $iReturn = 0;
        foreach ($this->getSecurityHeaders() as $val) {
            $iReturn += $val ? 0 : 1;
        }
        return $iReturn;
    }

    /**
     * get count of found securtity headers
     * 
     * @return integer
     */
    public function getCountOkSecurityHeaders() {
        $iReturn = 0;
        foreach ($this->getSecurityHeaders() as $val) {
            $iReturn += $val ? 1 : 0;
        }
        return $iReturn;
    }

    // ----------------------------------------------------------------------

    /**
     * helper: get an array of tags by given http response header var + value
     * 
     * @param string  $varname  http response variable
     * @param string  $val      its value
     * @return array
     */
    protected function _getTagsOfHeaderline($varname, $val) {
        $aTags = array();
        $aRegex = array();
        if($varname=='_status'){
            $sVersionStatus=$this->getHttpVersionStatus();
            $aTags[] = 'http';
            $aTags[] ='httpversion';
            $aTags[] ='httpstatus';
            return array($aTags, $aRegex);
        }
        foreach ($this->_aHeaderVars as $sSection => $aSection) {
            foreach ($aSection as $sVar => $aParams) {

                if (strtolower($varname) === strtolower($sVar)) {
                    $aTags[] = $sSection;
                    if (isset($aParams['tags'])) {
                        $aTags = array_merge($aTags, $aParams['tags']);
                    }
                    if (isset($aParams['unwantedregex'])) {
                        preg_match('/(' . $sVar . '):\ (.*' . $aParams['unwantedregex'] . '.*)/i', "$varname: $val", $aMatches);
                        if (count($aMatches)) {
                            $aTags[] = 'unwanted';
                            $aRegex['unwantedregex'] = $aParams['unwantedregex'];
                        }
                    }
                    if (isset($aParams['badvalueregex'])) {
                        preg_match('/(' . $sVar . '):\ (.*' . $aParams['badvalueregex'] . '.*)/i', "$varname: $val", $aMatches);
                        if (count($aMatches)) {
                            $aTags[] = 'badvalue';
                            $aRegex['badvalueregex'] = $aParams['badvalueregex'];
                        }
                    }
                }
            }
        }
        if (!count($aTags)) {
            $aTags[] = 'unknown';
        }
        // echo "DEBUG: $varname: $val - ".print_r($aTags, 1).'<br>';
        return array($aTags, $aRegex);
    }

    /**
     * ckeck if a header item matches a given tag and return its value
     * 
     * @param array   $aItem
     * @param string  $sTag   tag to search for
     * @return string
     */
    protected function _hasTag($aItem, $sTag) {
        return array_search($sTag, $aItem['tags']) !== false;
    }

    /**
     * check if a header item tag contains a known header var;
     * if true it returns a string with the section
     * 
     * @param array  $aItem
     * @return boolean
     */
    protected function _isKnownHeader($aItem) {
        if($aItem['tags'] && is_array($aItem['tags'])){
            foreach(array('security', 'non-standard', 'http') as $sSection){
                if(in_array($sSection, $aItem['tags'])){
                    return $sSection;
                }
            }
        }
        /*
        foreach (array('httpv1', 'non-standard', 'security') as $sSection) {
            if ($this->_hasTag($aItem, $sSection)) {
                return $sSection;
            }
        }
         * 
         */
        return false;
    }
    /**
     * get array of deprecated http response headers
     * @return array
     */
    public function getDeprecatedHeaders() {
        return $this->getHeadersWithGivenTag('deprecated');
    }

    /**
     * get array of common but non-standard http response headers
     * @return array
     */
    public function getNonStandardHeaders() {
        return $this->getHeadersWithGivenTag('non-standard');
    }

    /**
     * get array of unknown http response headers
     * @return array
     */
    public function getUnknowHeaders() {
        $aReturn = array();
        foreach ($this->parseHeaders() as $aData) {
            if ($aData['found'] === 'unknown') {
                $aReturn[] = $aData;
            }
        }
        return $aReturn;
    }

    /**
     * get array of unwanted http response headers
     * @return array
     */
    public function getUnwantedHeaders() {
        return $this->getHeadersWithGivenTag('unwanted');
    }

    /**
     * get array of http headers with headers matching a given tag
     * @return array
     */
    public function getHeadersWithGivenTag($sTag) {
        $aReturn = array();
        foreach ($this->parseHeaders() as $aData) {
            if (array_search($sTag, $aData['tags']) !== false) {
                $aReturn[] = $aData;
                ;
            }
        }
        return $aReturn;
    }

    /**
     * get array all found tags and its count in http response header data
     * @return array
     */
    public function getExistingTags() {
        $aReturn = array();
        foreach ($this->parseHeaders() as $aData) {
            if (isset($aData['tags']) && count($aData['tags'])) {
                foreach ($aData['tags'] as $sTag) {
                    $aReturn[$sTag] = isset($aReturn[$sTag]) ? $aReturn[$sTag] + 1 : 1;
                }
            }
        }
        return $aReturn;
    }

    /**
     * get http version from http response status line i.e. "1.1" or "2"
     * @return string
     */
    public function getHttpVersion(){
        $sReturn = '';
        foreach ($this->_aHeader as $aData) {
            if ($aData[0]=='_status'){
                return preg_replace('#.*/([0-9\.]*)\ .*#u', '\1', $aData[1]);
            }
        }
        return false;
    }
    /**
     * helper function for rendering / reporting: get a status value as string
     * one of ok|warning|error 
     * in dependency of http version.
     * @param string $sVersion  optional: version number; default: take version from current http header
     * @return string
     */
    public function getHttpVersionStatus($sVersion=false){
        if (!$sVersion){
            $sVersion=$this->getHttpVersion();
        }
        return ($sVersion >= '2' ? 'ok' : ($sVersion < '1.1' ? 'error' : 'warning') );
    }
    /**
     * get array with cookie data from curl cookie file
     * https://stackoverflow.com/questions/410109/php-reading-a-cookie-file
     * array(
     *      'metainfos' => array(
     *          'file' => {string} filename
     *      ),
     *      'cookies' => {array} list of cookies,
     *      'error' => {string} on error only: one of NOT_READABLE|NOT_FOUND 
     *  );
     * 
     * @param string $sFile  filename of cookie file
     * @return array
     */
    public function parseCookiefile($sFile) {
        $aReturn = array(
            'metainfos' => array(
                'file' => $sFile
            ),
            'cookies' => array(),
        );
        if (is_readable($sFile)) {
            $lines = explode(PHP_EOL, file_get_contents($sFile));

            foreach ($lines as $line) {

                $cookie = array();

                // detect httponly cookies and remove #HttpOnly prefix
                if (substr($line, 0, 10) == '#HttpOnly_') {
                    $line = substr($line, 10);
                    $cookie['httponly'] = true;
                } else {
                    $cookie['httponly'] = false;
                }

                // we only care for valid cookie def lines
                if (strlen($line) > 0 && $line[0] != '#' && substr_count($line, "\t") == 6) {

                    // get tokens in an array
                    $tokens = explode("\t", $line);

                    // trim the tokens
                    $tokens = array_map('trim', $tokens);

                    // Extract the data
                    $cookie['domain'] = $tokens[0]; // The domain that created AND can read the variable.
                    $cookie['flag'] = $tokens[1];   // A TRUE/FALSE value indicating if all machines within a given domain can access the variable. 
                    $cookie['path'] = $tokens[2];   // The path within the domain that the variable is valid for.
                    $cookie['secure'] = $tokens[3]; // A TRUE/FALSE value indicating if a secure connection with the domain is needed to access the variable.

                    $cookie['expiration-epoch'] = $tokens[4];  // The UNIX time that the variable will expire on.   
                    $cookie['name'] = urldecode($tokens[5]);   // The name of the variable.
                    $cookie['value'] = urldecode($tokens[6]);  // The value of the variable.
                    // Convert date to a readable format
                    $cookie['expiration'] = isset($tokens[4]) && $tokens[4] > "0" ? date('Y-m-d h:i:s', (int) $tokens[4]) : '-';

                    // Record the cookie.
                    $aReturn['cookies'][$cookie['domain'] . '/' . $cookie['name']] = $cookie;
                }
            }
        } else {
            $aReturn['error']=file_exists($sFile)
                ? 'NOT_READABLE'
                : 'NOT_FOUND'
            ;
        }
        ksort($aReturn['cookies']);
        return $aReturn;
    }

    /**
     * get an helper array with all header lines
     * @return array
     */
    public function parseHeaders() {
        $aReturn = array();

        $iLine = 0;
        foreach ($this->_aHeader as $aLine) {
            $iLine++;
            list($varname, $val) = $aLine;

            // $aReturn[strtolower($varname)]=array(
            // $sFound = count($this->isKnownHeadervar($sVar, $val)) ? true : 'unknown';
            $aTagData = $this->_getTagsOfHeaderline($varname, $val);
            $aItem = array(
                'var'        => $varname,
                'value'      => $val,
                'line'       => $iLine,
                'tags'       => $aTagData[0],
                'regex'      => $aTagData[1],
            );
            $aItem['found']      = ($this->_isKnownHeader($aItem) ? $this->_isKnownHeader($aItem) : 'unknown');
            // TEST $aItem['bad']        = $this->_hasTag($aItem, 'unwanted');
            $aItem['unwanted']   = $this->_hasTag($aItem, 'unwanted');
            $aItem['deprecated'] = $this->_hasTag($aItem, 'deprecated');
            $aItem['obsolete']   = $this->_hasTag($aItem, 'obsolete');
            // $aItem['bad']=$this->_isKnownHeader($aItem);
            $aReturn[] = $aItem;
        }
        // echo '<pre>'.print_r($aReturn, 1).'</pre>';
        return $aReturn;
    }

}
