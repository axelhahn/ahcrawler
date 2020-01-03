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
        'httpv1' => array(
            '_status' => array(),
            'A-IM' => array(),
            'Accept' => array(),
            'Accept-Additions' => array(),
            'Accept-Charset' => array(),
            'Accept-Encoding' => array(),
            'Accept-Features' => array(),
            'Accept-Language' => array(),
            'Accept-Ranges' => array(),
            'Age' => array('tags' => array('cache')),
            'Allow' => array(),
            'Alternates' => array(),
            'Authentication-Info' => array(),
            'Authorization' => array(),
            'C-Ext' => array(),
            'C-Man' => array(),
            'C-Opt' => array(),
            'C-PEP' => array(),
            'C-PEP-Info' => array(),
            'Cache-Control' => array('tags' => array('cache')),
            'Connection' => array(),
            'Content-Base' => array(),
            'Content-Disposition' => array(),
            'Content-Encoding' => array('tags' => array('compression')),
            'Content-ID' => array(),
            'Content-Language' => array(),
            'Content-Length' => array(),
            'Content-Location' => array(),
            'Content-MD5' => array(),
            'Content-Range' => array(),
            'Content-Script-Type' => array(),
            'Content-Style-Type' => array(),
            'Content-Type' => array(),
            'Content-Version' => array(),
            'Cookie' => array(),
            'Cookie2' => array(),
            'DAV' => array(),
            'Date' => array(),
            'Default-Style' => array(),
            'Delta-Base' => array(),
            'Depth' => array(),
            'Derived-From' => array(),
            'Destination' => array(),
            'Differential-ID' => array(),
            'Digest' => array(),
            'ETag' => array('tags' => array('cache')),
            'Expect' => array(),
            'Expires' => array('tags' => array('cache')),
            'Ext' => array(),
            'From' => array(),
            'GetProfile' => array(),
            'Host' => array(),
            'IM' => array(),
            'If' => array(),
            'If-Match' => array(),
            'If-Modified-Since' => array(),
            'If-None-Match' => array(),
            'If-Range' => array(),
            'If-Unmodified-Since' => array(),
            'Keep-Alive' => array(),
            'Label' => array(),
            'Last-Modified' => array(),
            'Link' => array(),
            'Location' => array(),
            'Lock-Token' => array(),
            'MIME-Version' => array(),
            'Man' => array(),
            'Max-Forwards' => array(),
            'Meter' => array(),
            'Negotiate' => array(),
            'Opt' => array(),
            'Ordering-Type' => array(),
            'Overwrite' => array(),
            'P3P' => array(),
            'PEP' => array(),
            'PICS-Label' => array(),
            'Pep-Info' => array(),
            'Position' => array(),
            'Pragma' => array('tags' => array('cache')),
            'ProfileObject' => array(),
            'Protocol' => array(),
            'Protocol-Info' => array(),
            'Protocol-Query' => array(),
            'Protocol-Request' => array(),
            'Proxy-Authenticate' => array(),
            'Proxy-Authentication-Info' => array(),
            'Proxy-Authorization' => array(),
            'Proxy-Features' => array(),
            'Proxy-Instruction' => array(),
            'Public' => array(),
            'Range' => array(),
            'Referer' => array(),
            'Retry-After' => array(),
            'Safe' => array(),
            'Security-Scheme' => array(),
            'Server' => array('badregex' => '[0-9]\.[0-9]'),
            'Set-Cookie' => array(),
            'Set-Cookie2' => array(),
            'SetProfile' => array(),
            'SoapAction' => array(),
            'Status-URI' => array(),
            'Surrogate-Capability' => array(),
            'Surrogate-Control' => array(),
            'TCN' => array(),
            'TE' => array(),
            'Timeout' => array(),
            'Trailer' => array(),
            'Transfer-Encoding' => array(),
            'URI' => array(),
            'Upgrade' => array(),
            'User-Agent' => array(),
            'Variant-Vary' => array(),
            'Vary' => array(),
            'Via' => array(),
            'WWW-Authenticate' => array(),
            'Want-Digest' => array(),
            'Warning' => array(),
        ),
        // see  https://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Common_non-standard_response_fields
        'non-standard' => array(
            'Refresh' => array(),
            'Status' => array(),
            'Timing-Allow-Origin' => array(),
            'X-Content-Duration' => array(),
            'X-Content-Security-Policy' => array(),
            'X-Correlation-ID' => array(),
            'X-Pingback' => array(), // http://www.hixie.ch/specs/pingback/pingback#TOC2.1
            'X-Powered-By' => array('tags' => array('unwanted')),
            'X-Request-ID' => array(),
            'X-Robots-Tag' => array(),
            'X-UA-Compatible' => array(),
            'X-WebKit-CSP' => array(),
        ),
        // see https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#tab=Headers
        'security' => array(
            'Content-Security-Policy' => array(),
            'Expect-CT' => array(),
            'Feature-Policy' => array(),
            'Public-Key-Pins' => array(),
            'Referrer-Policy' => array('badregex' => 'unsafe\-url'),
            'Strict-Transport-Security' => array(),
            'X-Content-Type-Options' => array(),
            'X-Frame-Options' => array(),
            'X-Permitted-Cross-Domain-Policies' => array(),
            'X-XSS-Protection' => array(),
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
        $aTmp = explode("\r\n", $sHeader);
        // echo "DEBUG: " . $sHeader."<br>";
        if ($aTmp && is_array($aTmp) && count($aTmp)) {
            foreach ($aTmp as $sLine) {
                if (!$sLine) {
                    continue;
                }
                $this->_aHeader[] = $this->_splitHeaderLine($sLine);
            }
            // $this->_aHeader=$aTmp;
        }
        $this->_sHeader = $sHeader . "\r\n";
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
     * get an array with defined securtity headers and existance in the current
     * response header data
     * 
     * @return array
     */
    public function getSecurityHeaders() {
        $aReturn = array();
        foreach (array_keys($this->_aHeaderVars['security']) as $sVar) {
            // $aReturn[$sVar]['found']= stristr($this->_sHeader, $sVar);
            preg_match('/(' . $sVar . '):\ (.*)\r\n/i', $this->_sHeader, $aMatches);
            $aReturn[$sVar] = count($aMatches) ? array('var' => $aMatches[1], 'value' => $aMatches[2]) : false;
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
     * helper: get an array of tags by given header var + value
     * 
     * @param string  $varname
     * @param string  $val
     * @return array
     */
    protected function _getTagsOfHeaderline($varname, $val) {
        $aTags = array();
        foreach ($this->_aHeaderVars as $sSection => $aSection) {
            foreach ($aSection as $sVar => $aChecks) {

                if (strtolower($varname) === strtolower($sVar)) {
                    $aTags[] = $sSection;
                    if (isset($aChecks['tags'])) {
                        $aTags = array_merge($aTags, $aChecks['tags']);
                    }
                    if (isset($aChecks['badregex'])) {
                        preg_match('/(' . $sVar . '):\ (.*' . $aChecks['badregex'] . '.*)/i', "$varname: $val", $aMatches);
                        if (count($aMatches)) {
                            $aTags[] = 'unwanted';
                        }
                    }
                }
            }
        }
        if (!count($aTags)) {
            $aTags[] = 'unknown';
        }
        // echo "DEBUG: $varname: $val - ".print_r($aTags, 1).'<br>';
        return $aTags;
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
        foreach (array('httpv1', 'non-standard', 'security') as $sSection) {
            if ($this->_hasTag($aItem, $sSection)) {
                return $sSection;
            }
        }
        return false;
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
                $aReturn[] = array('var' => $aData['var'], 'value' => $aData['value'], 'line' => $aData['line']);
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
     * get array with cookie data from curl cookie file
     * https://stackoverflow.com/questions/410109/php-reading-a-cookie-file
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
        if (file_exists($sFile)) {
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
            $aTags = $this->_getTagsOfHeaderline($varname, $val);
            $aItem = array(
                'var' => $varname,
                'value' => $val,
                'line' => $iLine,
                'tags' => $aTags,
            );
            $aItem['found'] = $this->_isKnownHeader($aItem) ? $this->_isKnownHeader($aItem) : 'unknown';
            $aItem['bad'] = $this->_hasTag($aItem, 'unwanted');
            // $aItem['bad']=$this->_isKnownHeader($aItem);
            $aReturn[] = $aItem;
        }
        // echo '<pre>'.print_r($aReturn, 1).'</pre>';
        return $aReturn;
    }

}
