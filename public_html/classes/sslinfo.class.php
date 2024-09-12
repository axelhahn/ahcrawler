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
 * Get infos to an ssl certificate
 * 
 * @example
 * <code>
 * require_once('sslinfo.class.php');
 * $oSsl = new sslinfo();
 * 
 * $sUrl='https://example.com';
 * 
 * // (1)
 * // calls with url:
 * print_r($oSsl->getCertinfosFromUrl(($sUrl));  // full cert infos with opnessl
 * print_r($oSsl->getSimpleInfosFromUrl($sUrl)); // simplified cert infos
 * print_r($oSsl->checkCertdata($sUrl));         // check status
 * 
 * // (2)
 * // calls without url: set it once
 * $oSsl->setUrl($sUrl);
 * // then you don't need the url as param
 * print_r($oSsl->getSimpleInfosFromUrl());
 * print_r($oSsl->getCertinfos());
 * print_r($oSsl->checkCertdata());

 * </code>
 * 
 * @version 0.1
 * @author Axel Hahn <axel.hahn@axel-hahn.de>
 * @license GNU GPL 3.0
 * 
 * 2024-09-13  v0.167  php8 only; add typed variables; use short array syntax
 */
class sslinfo
{
    # ----------------------------------------------------------------------
    # CONFIG
    # ----------------------------------------------------------------------

    /**
     * Url of a website
     * @var string
     */
    protected string $_sUrl = '';

    /**
     * Array with certificate infos
     * @var 
     */
    protected array $_aCertInfos = [];

    /**
     * Configuration: warning level as number of days before expiration
     * @var int
     */
    protected int $_iWarnBeforeExpiration = 30;

    /**
     * Hostname
     * @var string
     */
    protected string $_sHost = '';

    /**
     * Port number
     * @var integer
     */
    protected int $_iPort = 0;

    # ----------------------------------------------------------------------
    # CONSTRUCT
    # ----------------------------------------------------------------------

    /**
     * Constructor
     */
    function __construct()
    {
    }

    # ----------------------------------------------------------------------
    # PUBLIC :: CERT READING functions
    # ----------------------------------------------------------------------

    /**
     * Get an array with certificate infos with a ssl socket connection 
     * It returns a key _error if something went wrong.
     * 
     * @param string  $url  url to check; i.e. https://example.com or example.com:443
     * @return array
     */
    public function getCertinfos(string $url = ''): array
    {
        if ($url) {
            $this->setUrl($url);
        }
        if ($this->_aCertInfos) {
            return $this->_aCertInfos;
        }

        $iTimeout = 3;
        if (!$this->_sHost || !$this->_iPort) {
            // die(__METHOD__. "ERROR: I need host AND port\n");
            return ['_error' => 'ERROR: I need host AND port'];
        }

        // fetch data directly from the server
        $aStreamOptions = stream_context_create(
            [
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]
        );
        if (!$aStreamOptions) {
            return ['_error' => 'Error: Cannot create stream_context'];
        }

        $errno = '';
        $errstr = '';
        $read = @stream_socket_client("ssl://$this->_sHost:$this->_iPort", $errno, $errstr, $iTimeout, STREAM_CLIENT_CONNECT, $aStreamOptions);
        if (!$read) {
            return ['_error' => "Error $errno: $errstr; cannot create stream_context to ssl://$this->_sHost:$this->_iPort"];
        }
        $cert = stream_context_get_params($read);
        if (!$cert) {
            return ['_error' => "Error: socket was connected to ssl://$this->_sHost:$this->_iPort - but I cannot read certificate infos with stream_context_get_params "];
        }
        $this->_aCertInfos = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        // check chaining
        $aStreamOptions2 = stream_context_create(
            [
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => true,
                    'verify_peer_name' => true
                ]
            ]
        );
        $read2 = @stream_socket_client("ssl://$this->_sHost:$this->_iPort", $errno, $errstr, $iTimeout, STREAM_CLIENT_CONNECT, $aStreamOptions2);
        $this->_aCertInfos['chaining'] = $read2 ? true : false;

        return $this->_aCertInfos;
    }

    /**
     * Get an array check results from a given url; 
     * Checks are:
     *     - start date of cert was reached
     *     - end date is larger 30d (=ok); below 30d (=warning) or expired (=error)
     *     - hostname of given url is one of the DNS aliases?
     *     - if several DNS aliases: all hosts must exist and point to the same ip
     * 
     * the returned array contains the following keys
     *     - status   - string with final result; one of ok|warning|error
     *     - errors   - flat array with error messages
     *     - warnings - flat array with warnmings
     *     - ok       - flat array with successful tests
     * 
     * @param string  $url  url to check; i.e. https://example.com or example.com:443
     * @return array
     */
    public function checkCertdata(string $url = ''): array
    {
        $aReturn = [
            'errors' => [],
            'warnings' => [],
            'ok' => [],
            'keys' => [],
            'status' => false,
        ];
        if ($url) {
            $this->setUrl($url);
        }

        $certinfo = $this->getCertinfos();
        if (isset($certinfo['_error']) && $certinfo['_error']) {
            $aReturn['errors'][] = $certinfo['_error'];
            return $aReturn;
        }

        // ----- Check: is valid already
        /*
        $iStart = round(($certinfo['validFrom_time_t'] - date('U')) / 60 / 60 / 24);
        if ($iStart < date('U')) {
            $aReturn['ok'][] = "";
        } else {
            $aReturn['errors'][] = "";
        }
         * 
         */

        // ----- Check: is still valid ... or expiring soon?
        $iDaysleft = round(($certinfo['validTo_time_t'] - date('U')) / 60 / 60 / 24);

        if ($iDaysleft > $this->_iWarnBeforeExpiration) {
            $aReturn['ok'][] = "Certificate is still valid for $iDaysleft more days.";
            $aReturn['keys']['validto'] = 'ok';
        } elseif ($iDaysleft > 0) {
            $aReturn['warnings'][] = "Certificate expires in $iDaysleft days.";
            $aReturn['keys']['validto'] = 'warning';
        } else {
            $aReturn['errors'][] = "Certificate is invalid for " . (-$iDaysleft) . " days.";
            $aReturn['keys']['validto'] = 'error';
        }

        // ----- check: is chaining OK?
        $sCertType = $this->getCertType();
        if ($sCertType && $sCertType !== 'selfsigned' && isset($certinfo['chaining'])) {
            if ($certinfo['chaining']) {
                $aReturn['ok'][] = "Chaining is OK.";
                $aReturn['keys']['chaining'] = 'ok';
            } else {
                $aReturn['warnings'][] = "Problem with chaining.";
                $aReturn['keys']['chaining'] = 'warning';
            }
        }
        // ----- current domain is part of dns names?
        $bInDnsList = false;
        $sHost = $this->_sHost;
        $sDNS = isset($certinfo['extensions']['subjectAltName']) ? $certinfo['extensions']['subjectAltName'] : false;

        $sDNS = str_replace('DNS:', '', $sDNS);
        foreach (explode(',', $sDNS) as $sEntry) {
            if (strstr($sEntry, '*.')) {
                if (preg_match('/.' . trim($sEntry) . '/', $sHost)) {
                    $bInDnsList = true;
                }
            } else {
                if (trim($sEntry) === $sHost) {
                    $bInDnsList = true;
                }
            }
        }

        if ($bInDnsList) {
            $aReturn['ok'][] = "Domain $sHost is included as DNS alias in the certificate.";
            $aReturn['keys']['DNS'] = 'ok';
        } else {
            $aReturn['errors'][] = "Domain $sHost is not included as DNS alias in the certificate.";
            $aReturn['keys']['DNS'] = 'error';
        }

        /*

        // ----- check all DNS names

        preg_match_all('/DNS:([a-z0-9\-\.]*)/s', $certinfo['extensions']['subjectAltName'], $aMatches);
        $sMustIp = gethostbyname($sHost); // gets ipv4 address if OK - or hostname on failure
        if (preg_match('/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]/', $sMustIp)) {
            foreach ($aMatches[1] as $sMyhostname) {
                if ($sMyhostname !== $sHost) {
                    $sIp = gethostbyname($sMyhostname);
                    if ($sIp === $sMustIp) {
                        $aReturn['ok'][] = "DNS:$sMyhostname hat IP von $sHost ($sMustIp)";
                    } else {
                        $aReturn['errors'][] = preg_match('/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]/', $sIp) ? "DNS:$sMyhostname hat IP $sIp - diese weicht von $sHost ($sMustIp) ab." : "DNS:$sMyhostname - dieser Hostname existiert nicht."
                        ;
                    }
                }
            }
        } else {
            $aReturn['errors'][] = "DNS:$sHost - the hostname was not found";
        }
        */

        // ----- get return status
        $aReturn['status'] = count($aReturn['errors']) ? 'error' : (count($aReturn['warnings']) ? 'warning' : 'ok');
        // echo '<pre>'.print_r($certinfo, 1).'</pre>'; die();
        // echo '<pre>'.print_r($aReturn, 1).'</pre>'; die();

        return $aReturn;
    }

    /**
     * Get an array of cert infos with simplified keys
     * 
     * @param string  $url  url to check; i.e. https://example.com or example.com:443
     * @return array
     */
    public function getSimpleInfosFromUrl(string $url = ''): array
    {
        if ($url) {
            $this->setUrl($url);
        }

        $aInfos = [
            '_error' => false,
            'url' => $this->_sUrl,
            'domain' => $this->_sHost,
            'port' => $this->_iPort,
        ];
        $certinfo = $this->getCertinfos();

        if (isset($certinfo['_error']) && $certinfo['_error']) {
            $aInfos['_error'] = $certinfo['_error'];
            return $aInfos;
        }

        if ($certinfo) {

            $aInfos['name'] = $certinfo['name'];
            $aInfos['issuer'] = isset($certinfo['issuer']['O']) ? $certinfo['issuer']['O'] : false;
            $aInfos['CA'] = $certinfo['issuer']['CN'];
            $aInfos['CN'] = $certinfo['subject']['CN'];
            $aInfos['DNS'] = isset($certinfo['extensions']['subjectAltName']) ? $certinfo['extensions']['subjectAltName'] : false;

            $aInfos['type'] = $this->getCertType();
            $aInfos['subject'] = $certinfo['subject'];

            $aInfos['validfrom'] = date("Y-m-d H:i", $certinfo['validFrom_time_t']);
            $aInfos['validto'] = date("Y-m-d H:i", $certinfo['validTo_time_t']);
            $aInfos['signatureTypeSN'] = $certinfo['signatureTypeSN'];
            $aInfos['chaining'] = $certinfo['chaining'];
        } else {
            $aInfos['_error'] = 'Certificate is not readable.';
        }
        // echo 'DEBUG'.__FILE__.'<pre>';print_r($aInfos);print_r($certinfo); die();
        return $aInfos;
    }

    /**
     * Get type of ssl certificate; it returns one of
     *  - "" (empty string)
     *  - "EV"
     *  - "Business SSL"
     *  - "selfsigned"
     * @return string
     */
    public function getCertType(): string
    {
        $certinfo = $this->getCertinfos();
        if (isset($certinfo['_error']) && $certinfo['_error']) {
            return '';
        }
        $sReturn = 'selfsigned';
        if (isset($certinfo['subject']['jurisdictionC'])) {
            $sReturn = 'EV';
        } else if (isset($certinfo['extensions']['authorityInfoAccess'])) {
            $sReturn = 'Business SSL';
        }
        return $sReturn;
    }

    # ----------------------------------------------------------------------
    # PUBLIC :: SETTER
    # ----------------------------------------------------------------------

    /**
     * Set an url to memorize it for getter functions
     * 
     * @param string  $sUrl  url, i.e. ssl://[hostname]:[port]
     * @return boolean
     */
    public function setUrl(string $sUrl): bool
    {
        if ($sUrl && $sUrl === $this->_sUrl) {
            return false;
        }
        $this->_sUrl = '';
        $this->_aCertInfos = [];

        $aUrldata = parse_url($sUrl);
        $this->_sHost = isset($aUrldata['host']) ? $aUrldata['host'] : false;
        $this->_iPort = isset($aUrldata['port']) ? $aUrldata['port'] : ((isset($aUrldata['scheme']) && $aUrldata['scheme'] === 'https') ? 443 : false);
        if (!$this->_sHost || !$this->_iPort) {
            die(__METHOD__ . 'ERROR: cannot detect hostname and port number in given url ' . $sUrl);
        }
        $this->_sUrl = $sUrl;
        return true;
    }

    # ----------------------------------------------------------------------
    # EXPERIMENTAL SECTION
    # PUBLIC :: get items ... requires to setUrl() first
    # ----------------------------------------------------------------------

    /**
     * Get status as string error|warning|ok|(empty)
     * 
     * @return string
     */
    public function getStatus(): string
    {
        $aChecks = $this->checkCertdata();
        return $aChecks['status'] ?: '';
    }

}
