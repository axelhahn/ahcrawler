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
 * DOCS https://www.axel-hahn.de/docs/ahcrawler/
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
 * 
 * 2024-09-13  v0.167  php8 only; add typed variables; use short array syntax
 */
class httpheader
{
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

    /**
     * Hash of known http header variables.
     * data will be loaded from httpheader.data.php
     * @var array
     */
    protected array $_aHeaderVars = [];

    /**
     * Http response header as string
     * @var string
     */
    protected string $_sHeader = '';

    /**
     * Http response header as array
     * @var array
     */
    protected array $_aHeader = [];

    /**
     * Result hash of parsed header
     * @var array
     */
    protected array $_aParsedHeader = [];

    // ----------------------------------------------------------------------
    // CONSTRUCT
    // ----------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_aHeaderVars = include('httpheader.data.php');
    }

    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------

    /**
     * Helper: split header info by ":" and return an array with variable and 
     * value. If no ":" was found it creates a key named "_status" for the 
     * http return status
     * 
     * @param  string  $sLine  single http response header line
     * @return array
     */
    protected function _splitHeaderLine(string $sLine): array
    {
        $aTmp = explode(":", $sLine, 2);
        $sVarname = count($aTmp) > 1 ? $aTmp[0] : '_status';
        $value = count($aTmp) > 1 ? $aTmp[1] : $sLine;
        return [$sVarname, trim($value)];
    }

    /**
     * Set http response header to analyze
     * 
     * @param string $sHeader  http response header as string with line breaks
     * @return array
     */
    public function setHeaderAsString(string $sHeader): array
    {
        $this->_aHeader = [];
        $this->_sHeader = '';
        if (!$sHeader) {
            return [];
        }
        $aTmp = explode("\r\n", $sHeader);
        // echo "DEBUG: " . $sHeader."<br>";
        if ($aTmp && is_array($aTmp) && count($aTmp)) {
            foreach ($aTmp as $sLine) {
                if (!$sLine) {
                    break;
                }
                $this->_aHeader[] = $this->_splitHeaderLine($sLine);
                $this->_sHeader .= "$sLine\r\n";
            }
        }
        $this->_aParsedHeader = $this->parseHeaders();

        return $this->_aHeader;
    }

    // ----------------------------------------------------------------------

    /**
     * Get the current http response header as array of lines
     * @return array
     */
    public function getHeaderAsArray(): array
    {
        return $this->_aHeader;
    }

    /**
     * Get the current http response header as single string with line breaks
     * @return string
     */
    public function getHeaderAsString(): string
    {
        return $this->_sHeader;
    }

    // ----------------------------------------------------------------------
    // Security Headers
    // ----------------------------------------------------------------------

    /**
     * Helper: Get hash with known headers in the config that match a tag
     * 
     * @param   string  $sTag  name of tag to filter
     * @return array
     */
    protected function _getHeaderCfgOfGivenTag(string $sTag): array
    {
        $aReturn = [];

        foreach ($this->_aHeaderVars as $sSection => $aSection) {
            foreach ($aSection as $sVar => $aParams) {
                if (isset($aParams['tags']) && in_array($sTag, $aParams['tags'])) {
                    $aReturn[$sVar] = $aParams;
                }
            }
        }
        return $aReturn;
    }

    /**
     * Get an array with defined securtity headers and existance in the current
     * response header data
     * 
     * @return array
     */
    public function getSecurityHeaders(): array
    {
        $aReturn = [];
        foreach ($this->_getHeaderCfgOfGivenTag('security') as $sVar => $aChecks) {
            $aReturn[$sVar] = false;
            $iLine = 0;
            foreach ($this->getHeadersWithGivenTag('security') as $aLine) {
                $iLine++;
                if (strtolower($aLine['var']) === strtolower($sVar)) {
                    $aReturn[$sVar] = $aLine;
                }
            }
        }
        return $aReturn;
    }

    /**
     * Get count of securtity headers that were NOT found
     * 
     * @return integer
     */
    public function getCountMissedSecurityHeaders(): int
    {
        $iReturn = 0;
        foreach ($this->getSecurityHeaders() as $val) {
            $iReturn += $val ? 0 : 1;
        }
        return $iReturn;
    }

    /**
     * Get count of found securtity headers
     * 
     * @return integer
     */
    public function getCountOkSecurityHeaders(): int
    {
        $iReturn = 0;
        foreach ($this->getSecurityHeaders() as $val) {
            $iReturn += $val ? 1 : 0;
        }
        return $iReturn;
    }

    // ----------------------------------------------------------------------

    /**
     * Helper: get an array of tags by given http response header var + value
     * 
     * @param string  $varname  http response variable
     * @param string  $val      its value
     * @return array
     */
    protected function _getTagsOfHeaderline(string $varname, string $val): array
    {
        $aTags = [];
        $aRegex = [];
        if ($varname == '_status') {
            // $sVersionStatus = $this->getHttpVersionStatus();
            $aTags[] = 'http';
            // $aTags[] = 'httpversion';
            $aTags[] = 'httpstatus';
            return [$aTags, $aRegex];
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
        return [array_unique($aTags), $aRegex];
    }

    /**
     * Helper for parseHeaders()
     * Check if a header item matches a given tag and return its value
     * eg. $this->_hasTag($aItem, 'unwanted');
     * 
     * @param array   $aItem  item to check against its key "tags"
     * @param string  $sTag   tag to search for
     * @return bool|int|string
     */
    protected function _hasTag(array $aItem, string $sTag): bool|int|string
    {
        return array_search($sTag, $aItem['tags']) !== false;
    }

    /**
     * Helper: check if a header item tag contains a known header var;
     * if true it returns a string with the section
     * 
     * @param array   $aItem  item to check against its key "tags"
     * @return string
     */
    protected function _isKnownHeader(array $aItem): string
    {
        if ($aItem['tags'] && is_array($aItem['tags'])) {
            foreach (['security', 'non-standard', 'http'] as $sSection) {
                if (in_array($sSection, $aItem['tags'])) {
                    return $sSection;   
                }
            }
        }
        /*
        foreach (['httpv1', 'non-standard', 'security'] as $sSection) {
            if ($this->_hasTag($aItem, $sSection)) {
                return $sSection;
            }
        }
         * 
         */
        return '';
    }

    /**
     * Get array of deprecated http response headers
     * @return array
     */
    public function getDeprecatedHeaders(): array
    {
        return $this->getHeadersWithGivenTag('deprecated');
    }

    /**
     * Get array of experimental http response headers
     * @return array
     */
    public function getExperimentalHeaders(): array
    {
        return $this->getHeadersWithGivenTag('experimental');
    }

    /**
     * Get array of common but non-standard http response headers
     * @return array
     */
    public function getNonStandardHeaders(): array
    {
        return $this->getHeadersWithGivenTag('non-standard');
    }

    /**
     * Get array of unknown http response headers
     * @return array
     */
    public function getUnknowHeaders(): array
    {
        $aReturn = [];
        foreach ($this->parseHeaders() as $aData) {
            if ($aData['found'] === 'unknown') {
                $aReturn[] = $aData;
            }
        }
        return $aReturn;
    }

    /**
     * Get array of unwanted http response headers
     * @return array
     */
    public function getUnwantedHeaders(): array
    {
        return $this->getHeadersWithGivenTag('unwanted');
    }

    /**
     * Get array of http headers with headers matching a given tag
     * eg. $this->getHeadersWithGivenTag('unwanted');
     * 
     * @param string  $sTag   tag to search for
     * @return array
     */
    public function getHeadersWithGivenTag(string $sTag): array
    {
        $aReturn = [];
        foreach ($this->parseHeaders() as $aData) {
            if (array_search($sTag, $aData['tags']) !== false) {
                $aReturn[] = $aData;
                ;
            }
        }
        return $aReturn;
    }

    /**
     * Get array all found tags and its count in http response header data
     * @return array
     */
    public function getExistingTags(): array
    {
        $aReturn = [];
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
     * Get http version from http response status line i.e. "1.1" or "2"
     * @return string
     */
    public function getHttpVersion(): string
    {
        foreach ($this->_aHeader as $aData) {
            if ($aData[0] == '_status') {
                return preg_replace('#.*/([0-9\.]*)\ .*#u', '\1', $aData[1]);
            }
        }
        return '';
    }

    /**
     * Helper function for rendering / reporting: get a status value as string
     * one of ok|warning|error 
     * in dependency of http version.
     * 
     * @param string $sVersion  optional: version number; default: take version from current http header
     * @return string
     */
    public function getHttpVersionStatus(string $sVersion = '')
    {
        if (!$sVersion) {
            $sVersion = $this->getHttpVersion();
        }
        return $sVersion >= '2' ? 'ok' : ($sVersion < '1.1' ? 'error' : 'warning');
    }

    /**
     * Get array with cookie data from curl cookie file
     * https://stackoverflow.com/questions/410109/php-reading-a-cookie-file
     * [
     *      'metainfos' => [
     *          'file' => {string} filename
     *      ],
     *      'cookies' => {array} list of cookies,
     *      'error' => {string} on error only: one of NOT_READABLE|NOT_FOUND 
     *  ];
     * 
     * @param string $sFile  filename of cookie file
     * @return array
     */
    public function parseCookiefile(string $sFile): array
    {
        $aReturn = [
            'metainfos' => [
                'file' => $sFile
            ],
            'cookies' => [],
        ];
        if (is_readable($sFile)) {
            $lines = explode(PHP_EOL, file_get_contents($sFile));

            foreach ($lines as $line) {

                $cookie = [];

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
            $aReturn['error'] = file_exists($sFile)
                ? 'NOT_READABLE'
                : 'NOT_FOUND'
            ;
        }
        ksort($aReturn['cookies']);
        return $aReturn;
    }

    /**
     * Parse http response header get an helper array with all header lines
     * @return array
     */
    public function parseHeaders(): array
    {
        $aReturn = [];

        $iLine = 0;
        foreach ($this->_aHeader as $aLine) {
            $iLine++;
            list($varname, $val) = $aLine;

            $aTagData = $this->_getTagsOfHeaderline($varname, $val);
            $aItem = [
                'var' => $varname,
                'value' => $val,
                'line' => $iLine,
                'tags' => $aTagData[0],
                'regex' => $aTagData[1],
            ];
            $aItem['found'] = ($this->_isKnownHeader($aItem) ? $this->_isKnownHeader($aItem) : 'unknown');
            // TEST $aItem['bad']        = $this->_hasTag($aItem, 'unwanted');
            $aItem['unwanted'] = $this->_hasTag($aItem, 'unwanted');
            $aItem['deprecated'] = $this->_hasTag($aItem, 'deprecated');
            $aItem['obsolete'] = $this->_hasTag($aItem, 'obsolete');
            // $aItem['bad']=$this->_isKnownHeader($aItem);
            $aReturn[] = $aItem;
        }
        // echo '<pre>'.print_r($aReturn, 1).'</pre>';
        return $aReturn;
    }

}
