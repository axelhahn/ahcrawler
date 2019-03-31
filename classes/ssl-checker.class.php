<?php
/**
 * 
 * CHECKER FOR SSL CERTIFICATES THAT ARE INSTALLED ON A SERVER
 * 
 * @example
 * <code>
 * require_once('ssl-checker.class.php');
 * $oSsl = new sslchecker();
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
 * 
 * @version 0.1
 * @author Axel Hahn <axel.hahn@iml.unibe.ch>
 * @license GNU GPL 3.0
 * 
 */
class sslchecker {
    # ----------------------------------------------------------------------
    # CONFIG
    # ----------------------------------------------------------------------

    /**
     * TTL for cert infos - when to re read from server; value is in sec
     * @var integer
     */
    protected $_iCacheTTL = 60 * 60;
    
    /**
     * warn level before a certificate expires; value is in days
     * @var integer
     */
    protected $_iWarnBeforeExpiration = 30;
    
    protected $_sUrl  = false;
    protected $_sHost = false;
    protected $_iPort = false;


    # ----------------------------------------------------------------------
    # CONSTRUCT
    # ----------------------------------------------------------------------

    function __construct() {
        return true;
    }

    # ----------------------------------------------------------------------
    # PROTECTED :: CERT CACHE
    # ----------------------------------------------------------------------

    /**
     * get a filename for a certificate specific cache file
     * 
     * @param string  $sHost  hostname
     * @param integer $iPort  port
     * @return string
     */
    protected function _certGetCachefile() {
        $sKey = $this->_sHost . '__port_' . $this->_iPort;
        return 'cache/' . preg_replace('/[^a-z0-9]/', '_', $sKey) . '__' . md5($sKey) . '.json';
    }

    /**
     * get cached cert infos (if they are still below TTL);
     * result is false if cache is invalid or does not exist yet.
     * 
     * @param string  $sHost  hostname
     * @param integer $iPort  port
     * @return boolean
     */
    protected function _certGetCache() {
        $sCachefile = $this->_certGetCachefile();
        if (file_exists($sCachefile)) {
            if (filemtime($sCachefile) > (date('U') - $this->_iCacheTTL)) {
                return json_decode(file_get_contents($sCachefile), 1);
            } else {
                unlink($sCachefile);
            }
        }
        return false;
    }

    /**
     * store cert infos in the cache; result is success of write action
     * 
     * @param string  $sHost     hostname
     * @param integer $iPort     port
     * @param array   $certinfo  openssl cert data
     * @return boolean
     */
    protected function _certSavecache($certinfo) {
        $sCachefile = $this->_certGetCachefile();
        return file_put_contents($sCachefile, json_encode($certinfo, JSON_PRETTY_PRINT));
    }

    # ----------------------------------------------------------------------
    # PUBLIC :: CERT READING functions
    # ----------------------------------------------------------------------

    /**
     * get an array with certificate infos with a ssl socket connection 
     * 
     * @param string  $url  url to check; i.e. https://example.com or example.com:443
     * @return array
     */
    public function getCertinfos($url=false) {
        if($url){
            $this->setUrl($url);
        }
        
        $iTimeout = 1;
        if (!$this->_sHost || !$this->_iPort) {
            // die(__METHOD__. "ERROR: I need host AND port\n");
            return array('_error' => 'ERROR: I need host AND port');
        }
        // try to read cache first
        $aCertinfo = $this->_certGetCache();
        if ($aCertinfo) {
            return $aCertinfo;
        }
        
        // fetch data directly from the server
        $aStreamOptions = stream_context_create(array(
            'ssl' => array(
                'capture_peer_cert' => true,
                'verify_peer'       => false,
                'verify_peer_name'  => false
            ))
        );
        if (!$aStreamOptions) {
            return array('_error' => 'Error: Cannot create stream_context');
        }
        
        $errno=''; 
        $errstr='';
        $read = stream_socket_client("ssl://$this->_sHost:$this->_iPort", $errno, $errstr, $iTimeout, STREAM_CLIENT_CONNECT, $aStreamOptions);
        if (!$read) {
            return array('_error' => "Error $errno: $errstr; cannot create stream_context to ssl://$this->_sHost:$this->_iPort");
        }
        $cert = stream_context_get_params($read);
        if (!$cert) {
            return array('_error' => "Error: socket was connected to ssl://$this->_sHost:$this->_iPort - but I cannot read certificate infos with stream_context_get_params ");
        }
        $aCertinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
        $this->_certSavecache($aCertinfo);
        return $aCertinfo;
    }

    /**
     * get an array check results from a given url; 
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
    public function checkCertdata($url=false) {
        $aReturn = [
            'errors' => [],
            'warnings' => [],
            'ok' => [],
            'status' => false,
        ];
        if($url){
            $this->setUrl($url);
        }

        $certinfo = $this->getCertinfos();
        if (isset($certinfo['_error']) && $certinfo['_error']) {
            $aReturn['errors'][] = $certinfo['_error'];
            return $aReturn;
        }

        // ----- Check: is valid already
        $iStart = round(($certinfo['validFrom_time_t'] - date('U')) / 60 / 60 / 24);
        if ($iStart < date('U')) {
            $aReturn['ok'][] = "OK, Start-Datum des Zertifikats ist erreicht.";
        } else {
            $aReturn['errors'][] = "Start-Datum des Zertifikats ist in der Zukunft.";
        }

        // ----- Check: is still valid ... or expiring soon?
        $iDaysleft = round(($certinfo['validTo_time_t'] - date('U')) / 60 / 60 / 24);

        if ($iDaysleft > $this->_iWarnBeforeExpiration) {
            $aReturn['ok'][] = "Zertifikat ist noch $iDaysleft Tage gueltig";
        } elseif ($iDaysleft > 0) {
            $aReturn['warnings'][] = "Zertifikat läuft in in $iDaysleft Tagen ab.";
        } else {
            $aReturn['errors'][] = "Zertifikat ist " . (-$iDaysleft) . " Tage überschritten.";
        }

        // ----- current domain is part of dns names?
        $sHost = $this->_sHost;
        $sDNS = isset($certinfo['extensions']['subjectAltName']) ? $certinfo['extensions']['subjectAltName'] : false;
        if (strstr($sDNS, 'DNS:' . $sHost) === false) {
            $aReturn['errors'][] = "Domainname $sHost ist nicht als DNS ALias im Zertifikat enthalten.";
        } else {
            $aReturn['ok'][] = "Domainname $sHost ist als DNS ALias im Zertifikat enthalten.";
        }

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
            $aReturn['errors'][] = "DNS:$sHost - Der Hostname wurde nicht gefunden. Anm: Es gibt keinen Check der anderen DNS Aliase.";
        }

        // ----- get return status
        $aReturn['status'] = count($aReturn['errors']) ? 'error' : (count($aReturn['warnings']) ? 'warning' : 'ok');

        return $aReturn;
    }

    /**
     * get an array of cert infos with simplified keys
     * 
     * @param string  $url  url to check; i.e. https://example.com or example.com:443
     * @return array
     */
    public function getSimpleInfosFromUrl($url=false) {
        if($url){
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
            $aInfos['validfrom'] = date("Y-m-d H:i", $certinfo['validFrom_time_t']);
            $aInfos['validto'] = date("Y-m-d H:i", $certinfo['validTo_time_t']);

        } else {
            $aInfos['_error'] = 'Zertifikat nicht lesbar.';
        }
        return $aInfos;
    }

    # ----------------------------------------------------------------------
    # PUBLIC :: SETTER
    # ----------------------------------------------------------------------

    /**
     * set an url to memorize it for getter functions
     * @param type $sUrl
     * @return boolean
     */
    public function setUrl($sUrl) {
        $this->_sUrl=false;
        
        $aUrldata = parse_url($sUrl);
        $this->_sHost = isset($aUrldata['host']) ? $aUrldata['host'] : false;
        $this->_iPort = isset($aUrldata['port']) ? $aUrldata['port'] : ((isset($aUrldata['scheme']) && $aUrldata['scheme'] === 'https') ? 443 : false);
        if(!$this->_sHost || !$this->_iPort){
            die(__METHOD__ . 'ERROR: cannot detect hostname and port number in given url '.$sUrl);
        }
        $this->_sUrl = $sUrl;
        return true;
    }
    
    # ----------------------------------------------------------------------
    # EXPERIMENTAL SECTION
    # PUBLIC :: get items ... requires to setUrl() first
    # ----------------------------------------------------------------------
    
        protected function _getcertItem($s){
            $certinfo = $this->getCertinfos();
            return isset($certinfo[$s]) ? $certinfo[$s] : false;
        }

    /**
     * experimental
     * 
     * @return type
     */
    public function getName() {
        return $this->_getcertItem('name');
    }
    
    /**
     * experimental
     * 
     * @return boolean
     */
    public function isOK() {
        $aChecks=$this->checkCertdata();
        return isset($aChecks['status']) ? $aChecks['status']==='ok' : false;
    }
}
