<?php

/**
 * 
 * AXLES CRAWLER :: ANALYZER HTML
 * 
 * Analyzer for html documents. 
 * It fetches metainfos, css, scripts, images, links.
 * 
 * dependencies:
 * - fetchUrl function requires rollingcurl/RollingCurl.php
 * 
 * 
 * @example
 * - analyze an url:
 * <pre>require_once './analyzer.html.class.php';<br>
 * $oHtml=new analyzerHtml();<br>
 * $oHtml->fetchUrl("https://www.axel-hahn.de/kiste/");<br>
 * print_r($oHtml->getReport());</pre>
 * 
 * @example
 * - analyze already fetched html content with its url:
 * <pre>$sHtml=(...);<br>
 * $sUrl=(...);<br>
 * $oHtml=new analyzerHtml($sHtml, $sUrl);<br>
 * print_r($oHtml->getReport());</pre>
 * 
 * */
class analyzerHtml {

    private $_sHtml = false;
    private $_oDom = false;
    private $_sUrl = false;
    private $_sBaseHref = false;
    private $_sScheme = false;
    private $_sDomain = false;
    private $_aHttpResponseHeader = array();

    // ----------------------------------------------------------------------

    /**
     * new instance
     * @param string  $sHtmlcode  html document
     * @param string  $sUrl       url
     * @return type
     */
    public function __construct($sHtmlcode = false, $sUrl = false) {
        return $this->setHtml($sHtmlcode, $sUrl);
    }

    // ----------------------------------------------------------------------
    // 
    // GET DOCUMENT
    // 
    // ----------------------------------------------------------------------

    /**
     * get base url from url of the document or base href
     * @return boolean
     */
    private function _getBaseHref(){
        $this->_sBaseHref = false;
        $partsBaseHref=array();
        if (!$this->_oDom){
            return false;
        }
        $anchors=$this->_getNodesByTagAndAttribute('base', 'href');
        /*
        if (!count(($anchors))){
            return false;
        }
         * 
         */
        if (count(($anchors))){
            foreach ($anchors as $element) {
                $sBaseHref = $element->getAttribute('_href');
                break;
            }
            $partsBaseHref = parse_url($sBaseHref);
        }
        $partsUrl = parse_url($this->_sUrl);
        $this->_sBaseHref = ''
                
                // start with scheme
                .(isset($partsBaseHref['scheme']) ? $partsBaseHref['scheme'] : $partsUrl['scheme'])
                .'://'
                
                // add user + password ... if they exist "[user]:[pass]@"
                .(isset($partsBaseHref['user']) 
                    ? $partsBaseHref['user'].':'.$partsBaseHref['pass'].'@' 
                    : (isset($partsUrl['user'])
                        ? $partsUrl['user'].':'.$partsUrl['pass'].'@'
                        : ''
                    )
                 )
                 // 
                .(isset($partsBaseHref['host']) ? $partsBaseHref['host']     : (isset($partsUrl['host']) ? $partsUrl['host']     : ''))
                .(isset($partsBaseHref['port']) ? ':'.$partsBaseHref['port'] : (isset($partsUrl['port']) ? ':'.$partsUrl['port'] : ''))
                .(isset($partsBaseHref['path']) ? $partsBaseHref['path']     : (isset($partsUrl['path']) ? $partsUrl['path']     : ''))
            
            ;
        return $this->_sBaseHref;
    }
    
    /**
     * set a new html document with its sourcecode and url;
     * If you don't have the html source then use the method fetchUrl($sUrl)
     * 
     * @see fetchUrl($sUrl)
     * @param sring  $sHtmlcode  html body
     * @param string $sUrl       optional: url (used to generate full urls of internal links)
     * @return boolean
     */
    public function setHtml($sHtmlcode = false, $sUrl = false) {

        $this->_sHtml = $sHtmlcode;
        $this->_sUrl = $sUrl;
        $this->_sBaseHref = false;
        $this->_sScheme = false;
        $this->_sDomain = false;
        $this->_oDom = false;
        if ($sUrl) {
            $parts = parse_url($this->_sUrl);
            $this->_sScheme = $parts['scheme'];
            $this->_sDomain = $parts['host'];
        }
        $this->_aReport = array();

        $this->_oDom = new DOMDocument('1.0');
        @$this->_oDom->loadHTML($sHtmlcode);

        if (!$this->_oDom) {
            echo "WARNING: this is no valid DOM $sUrl \n<br>";
            return false;
        }
        $this->_getBaseHref();
        return true;
    }

    /**
     * fetch a given url as html document
     * If you have the html source already then use the method setHtml(($sHtmlcode, $sUrl)
     * 
     * @see setHtml($sHtmlcode = false, $sUrl=false)
     * @param string  $sUrl  url
     * @return boolean¨
     */
    public function fetchUrl($sUrl) {
        require_once __DIR__ . './../vendor/rolling-curl/src/RollingCurl/RollingCurl.php';
        require_once __DIR__ . './../vendor/rolling-curl/src/RollingCurl/Request.php';
        $this->_aHttpResponseHeader = array();

        $rollingCurl = new \RollingCurl\RollingCurl();
        $self = $this;
        $rollingCurl->setOptions(array(
                    CURLOPT_FOLLOWLOCATION => true,
                    // CURLOPT_HEADER => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.109 Safari/537.36',
                    CURLOPT_VERBOSE => false,
                    // TODO: this is unsafe .. better: let the user configure it
                    CURLOPT_SSL_VERIFYPEER => 0,
                ))
                ->get($sUrl)
                ->setCallback(function(\RollingCurl\Request $request) use ($self) {
                    $self->processResponse($request);
                })
                ->execute()
        ;
        /*
          $request = new \RollingCurl\Request($sUrl);
          $oRc = \RollingCurl\RollingCurl(array($this, 'processResponse'));
          $oRc->add($request);
          $oRc->execute();
         * 
         */
        return true;
    }

    /**
     * callback function for rollingCurl
     * @param object  $response  content
     * @param array   $info      http reponse header
     */
    public function processResponse($response) {
        $url = $response->getUrl();
        $this->_aHttpResponseHeader = $response->getResponseInfo();
        $this->setHtml($response->getResponseText(), $url);        
    }

    // ---------------------------------------------------------------------- 
    // 
    // PARSE AND ANALYSE
    //   
    // ----------------------------------------------------------------------    

    /**
     * helper function: get a value from html header
     * 
     * @param string   $sItem   header element to extract
     * @return boolean|array
     */
    private function _getMetaHead($sItem) {
        preg_match("@<head[^>]*>(.*?)<\/head>@si", $this->_sHtml, $regs);
        if (!is_array($regs) || count($regs) < 2) {
            return false;
        }
        $headdata = $regs[1];

        $res = Array();
        if ($headdata != "") {
            if (preg_match("@<" . $sItem . " *>(.*?)<\/" . $sItem . "*>@si", $this->_sHtml, $regs)) {
                return trim($regs[1]);
            }
            preg_match("/<meta +name *=[\"']?" . $sItem . "[\"']? *content=[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res);
            if (isset($res) && count($res) > 1) {
                return $res[1];
            }
        }
        return false;
    }

    private function url_remove_dot_segments($path) {
        // multi-byte character explode
        $inSegs = preg_split('!/!u', $path);
        $outSegs = array();
        foreach ($inSegs as $seg) {
            if ($seg == '' || $seg == '.')
                continue;
            if ($seg == '..')
                array_pop($outSegs);
            else
                array_push($outSegs, $seg);
        }
        $outPath = implode('/', $outSegs);
        if ($path[0] == '/')
            $outPath = '/' . $outPath;
        // compare last multi-byte character against '/'
        if ($outPath != '/' &&
                (mb_strlen($path) - 1) == mb_strrpos($path, '/', 'UTF-8'))
            $outPath .= '/';

        return $outPath;
    }

    /**
     * get an absolute url with fqdn from any relative or absolute url; 
     * it requres that the url of the document is known
     * @param string   $sRelUrl   url
     * @param string   $sDocUrl   optional: url of the current document to override
     * @return url
     */
    public function getFullUrl($sRelUrl, $sDocUrl=false) {

        $aPartsRelUrl = parse_url($sRelUrl);

        // if it has a scheme then it must be a full url
        if (array_key_exists('scheme', $aPartsRelUrl)) {
            return $sRelUrl;
        }
        if(!$sDocUrl){
            // $sDocUrl=$this->_sUrl;
            $sDocUrl=$this->_sBaseHref;
        }

        if (!$sDocUrl) {
            return false;
        }
        $aPartsDoc = parse_url($sDocUrl);

        // --- handle relative path values
        
        $sPath = isset($aPartsRelUrl['path']) ? $aPartsRelUrl['path'] : '';
        if ($sPath){
            if (strpos($sPath, '../') === 0) {
                $base = (($aPartsDoc['path'][strlen($aPartsDoc['path']) - 1]) !== '/') ? dirname($aPartsDoc['path']) . '/' : $aPartsDoc['path'];
                $sPath = $this->url_remove_dot_segments($base . $sPath);
            } else {
                $sPath = (array_key_exists('path', $aPartsRelUrl)) ? $aPartsRelUrl['path'] : $aPartsDoc['path'];
                if (strlen($sPath) && $sPath[0] != '/') {
                    $base = array_key_exists('path', $aPartsDoc) ? mb_strrchr($aPartsDoc['path'], '/', TRUE, 'UTF-8') : '';
                    $sPath = $base . '/' . $aPartsRelUrl['path'];
                }
                $sPath = str_replace('./', '', $sPath);
            }
            $aPartsRelUrl['path']=$sPath;
        }

        // --- create parts for target url
        
        $aPartsReturn = $aPartsRelUrl;
        foreach (array('scheme', /* 'user', 'pass', */ 'host', 'port', /*'path' , 'query', 'fragment' */) as $sKey){
            if(!array_key_exists($sKey, $aPartsReturn) && array_key_exists($sKey, $aPartsDoc)){
                $aPartsReturn[$sKey]=$aPartsDoc[$sKey];
            }
        }
        
        // no host an no path $sRelUrl --> then it is a relative path and we add it from current document
        if(!array_key_exists('host', $aPartsRelUrl) && !array_key_exists('path', $aPartsRelUrl)){
            $aPartsReturn['path']=$aPartsDoc['path'];
        }
        
        // take user + password ... only if scheme + host match
        if (
                $aPartsReturn['scheme']===$aPartsDoc['scheme']
                && $aPartsReturn['host']===$aPartsDoc['host']
                ){
            foreach (array('user', 'pass') as $sKey){
                if(!array_key_exists($sKey, $aPartsReturn) && array_key_exists($sKey, $aPartsDoc)){
                    $aPartsReturn[$sKey]=$aPartsDoc[$sKey];
                }
            }
        }
        
        // --- create url from target target parts
        $sUrl = $aPartsReturn['scheme'] . '://'
                . (array_key_exists('user', $aPartsReturn) ? $aPartsReturn['user'].':' : '')
                . (array_key_exists('pass', $aPartsReturn) ? $aPartsReturn['pass'].'' : '')
                . ((array_key_exists('user', $aPartsReturn) || array_key_exists('pass', $aPartsReturn)) ? '@' : '')
                . $aPartsReturn['host']
                . (array_key_exists('port', $aPartsReturn) ? ':'.$aPartsReturn['port'] : '')
                . (array_key_exists('path', $aPartsReturn) ? $aPartsReturn['path'] : '')
                . (array_key_exists('query', $aPartsReturn) ? '?'.$aPartsReturn['query'] : '')
                . (array_key_exists('fragment', $aPartsReturn) ? '#'.$aPartsReturn['fragment'] : '')
                ;
        // echo $sRelUrl . " -- " . print_r($aPartsRelUrl, 1) ." NEW \n". print_r($aPartsReturn, 1) . "\n--> $sUrl\n\n";
        return $sUrl;
    }

    /**
     * Get a type url based on link value of href or src attribute.
     * Possible return values are: 
     *      local     url starts with a hash
     *      internal  url is on same domain
     *      external  url is http(s) to other domains
     *      mailto    email
     *      script    script (javascript, jscript or vbscript)
     *      other     other protocol than http(s)
     * 
     * @see getValidUrlTypes()
     * @param string  $sHref
     * @return string
     */
    public function getUrlType($sHref) {

        $sType = 'internal';
        $aParse = parse_url($sHref);
        // print_r($aParse);

        $scheme = array_key_exists('scheme', $aParse) ? $aParse['scheme'] : false;

        switch ($scheme) {
            case false:
            case 'http':
            case 'https':
                if (!array_key_exists('host', $aParse)) {
                    if (array_key_exists('path', $aParse)) {
                        return 'internal';
                    }
                    if (array_key_exists('fragment', $aParse)) {
                        return 'local';
                    }
                }
                $sHost = array_key_exists('host', $aParse) ? $aParse['host'] : false;
                if ($sHost) {
                    if ($sHost !== $this->_sDomain) {
                        return 'external';
                    }
                    if ($scheme) {
                        return $scheme === $this->_sScheme ? 'internal' : 'external';
                    }
                }
                return 'internal';
                break;
            case 'mailto':
                return 'mailto';
                break;
            case 'javascript':
            case 'jscript':
            case 'vbscript':
                return 'script';
                break;
            case 'data':
                return 'local';
                break;
            case 'file':
                return 'file';
                break;
            /*
            case 'tel':
                return 'phone';
                break;
            */
            default:
                break;
        }
        return array_key_exists('scheme', $aParse) ? 'other::' . $aParse['scheme'] : 'other';
    }

    /**
     * return array of valid url types; by these types are grouped all
     * css-files, script-files, images, links
     * 
     * @see _getUrlType()
     * @return array
     */
    public function getValidUrlTypes() {
        return array(
            'local',
            'internal',
            'external',
            'script',
            'mailto',
            'phone',
            'other',
        );
    }

    /**
     * Parse mailto link and return items as array. Non existing fields in url
     * also do not exist in the returned array
     * Known fields are
     *     to       direct email receiver
     *     cc       carbon coby 
     *     bcc      blind carbon copy
     *     subject  subject
     *     body     body
     * examples
     *     mailto:axel@example.com
     *     mailto:axel@example.com?subject=23
     *     mailto:axel@example.com?subject=23&to=fred@example.com
     *     mailto:?subject=23&to=axel@example.com
     * 
     * @param string  $sHref
     * @return array
     */
    private function _parseEmail($sHref) {
        // echo "\n" . __FUNCTION__ .  "($sHref)\n\n";
        $aReturn = array();
        $aUrl = parse_url($sHref);
        if (!array_key_exists('scheme', $aUrl) || $aUrl['scheme'] != 'mailto') {
            return false;
        }

        // find to address in mailto:[email] or mailto:[email]?...
        if (array_key_exists('path', $aUrl)) {
            $aReturn['to'] = $aUrl['path'];
        } else {
            $aReturn['to'] = '';
        }

        // parse all behind '?'
        if (array_key_exists('query', $aUrl)) {
            parse_str($aUrl['query'], $aParams);
            foreach (array('to', 'cc', 'bcc', 'subject', 'body') as $sKey) {
                if (array_key_exists($sKey, $aParams) && $aParams[$sKey]) {
                    $aReturn[$sKey] = (array_key_exists($sKey, $aReturn) && $aReturn[$sKey]) ? $aReturn[$sKey] . ',' . $aParams[$sKey] : $aParams[$sKey];
                }
            }
        }
        return $aReturn;
    }

    // ----------------------------------------------------------------------
    // read header information
    // ----------------------------------------------------------------------

    /**
     * get follow links information from html head area
     * it returns true if is allowed to follow the links in the document
     * @return string
     */
    public function getHttpResponseHeader($bAllowRedirect=false) {
        if ($this->_sUrl 
                && count($this->_aHttpResponseHeader) 
                && ($bAllowRedirect ? true : $this->_aHttpResponseHeader['url'] == $this->_sUrl)
        ) {
            return $this->_aHttpResponseHeader;
        }
        return false;
    }

    // ----------------------------------------------------------------------
    // read header information
    // ----------------------------------------------------------------------

    /**
     * get follow links information from html head area and X-Robots-Tag
     * it returns true if is allowed to follow the links in the document
     * @return string
     */
    public function canFollowLinks() {
        $aHttpHeader=$this->getHttpResponseHeader();
        $sRobots =(isset($aHttpHeader['X-Robots-Tag']) ? $aHttpHeader['X-Robots-Tag'].',' : '')
         . $this->_getMetaHead('robots');
        if ($sRobots && (
                strpos($sRobots, 'none') === 0 || strpos($sRobots, 'none') > 0
                || strpos($sRobots, 'nofollow') === 0 || strpos($sRobots, 'nofollow') > 0)
        ) {
            return false;
        }
        
        return true;
    }

    /**
     * get follow links information from html head area and X-Robots-Tag
     * it returns true if is allowed to follow the links in the document
     * @return string
     */
    public function canIndexContent() {
        $aHttpHeader=$this->getHttpResponseHeader();
        $sRobots =(isset($aHttpHeader['X-Robots-Tag']) ? $aHttpHeader['X-Robots-Tag'].',' : '')
         . $this->_getMetaHead('robots');
        if ($sRobots && (
                strpos($sRobots, 'none') === 0 || strpos($sRobots, 'none') > 0
                || strpos($sRobots, 'noindex') === 0 || strpos($sRobots, 'noindex') > 0)
        ) {
            return false;
        }
        return true;
    }
    /**
     * TODO get words of a given string
     * @return string
     */
    public function getWords($sString) {
        $characterMap='À..ÿ'; // chars #192 .. #255
        $aWords=array();
        foreach(str_word_count(
                str_replace("'", '',$sString)
                ,2,$characterMap) as $sWord ){

            $sKey=$sWord;
            if(strlen($sKey)>2){
                if(!array_key_exists($sKey, $aWords)){
                    $aWords[$sKey]=1;
                } else {
                    $aWords[$sKey]++;
                }
            }
        }
        arsort($aWords);
        
        return $aWords;
    }

    /**
     * get description from html head area
     * @return string
     */
    public function getMetaDescription() {
        return $this->_getMetaHead('description');
    }

    /**
     * get keywords from html head area
     * @return string
     */
    public function getMetaKeywords() {
        return $this->_getMetaHead('keywords');
    }

    /**
     * get generator  from html head area
     * @return string
     */
    public function getMetaGenerator() {
        return $this->_getMetaHead('generator');
    }

    /**
     * get index content from html head area
     * @return string
     */
    public function getMetaIndex() {
        $sRobots = $this->_getMetaHead('robots');
        if ($sRobots && (strpos($sRobots, 'noindex') === 0 || strpos($sRobots, 'noindex') > 0)) {
            return false;
        }
        return true;
    }

    /**
     * get title from html head area
     * @return string
     */
    public function getMetaTitle() {
        return $this->_getMetaHead('title');
    }

    // ----------------------------------------------------------------------
    // read document
    // ----------------------------------------------------------------------

    /**
     * get all directly linked css files of this document
     * @return array
     */
    public function getCss() {
        $aReturn = array();
        if ($this->_oDom) {

            $anchors = $this->_oDom->getElementsByTagName('link');
            foreach ($anchors as $element) {
                if ($element->getAttribute('rel') == 'stylesheet' 
                    && $element->getAttribute('href')
                ) {
                    $sHref = $element->getAttribute('href');
                    $sType = $this->getUrlType($sHref);
                    $this->_add2Array($aReturn[$sType], array(
                        'ressourcetype' => 'css',
                        'href' => $element->getAttribute('href'),
                        '_url' => $this->getFullUrl($element->getAttribute('href')),
                        'media' => $element->getAttribute('media'),
                    ));
                }
            }
        }
        return $aReturn;
    }

    /**
     * get all images from IMG tags in this document
     * @param string  $sFilterByType  return links of this type only
     * @return array
     */
    public function getImages($sFilterByType = false) {
        $aReturn = array();
        if ($this->_oDom) {

            $anchors = $this->_oDom->getElementsByTagName('img');
            foreach ($anchors as $element) {
                if ($element->getAttribute('src')) {
                    $sHref = $element->getAttribute('src');
                    $sType = $this->getUrlType($sHref);
                    if ($sFilterByType && $sFilterByType != $sType) {
                        continue;
                    }
                    $this->_add2Array($aReturn[$sType], array(
                        'ressourcetype' => 'image',
                        'href' => $sHref,
                        'alt' => $element->getAttribute('alt'),
                        'title' => $element->getAttribute('title'),
                        '_url' => $this->getFullUrl($sHref),
                        '_tag' => 'img',
                    ));
                }
            }

            // thumbs in <link rel="image_src" href="[...]">
            $anchors = $this->_oDom->getElementsByTagName('link');
            foreach ($anchors as $element) {
                if ($element->getAttribute('rel')==='image_src' && $element->getAttribute('href')){
                    $sHref = $element->getAttribute('href');
                    $sType = $this->getUrlType($sHref);
                    if ($sFilterByType && $sFilterByType != $sType) {
                        continue;
                    }
                    $this->_add2Array($aReturn[$sType], array(
                        'ressourcetype' => 'image',
                        'href' => $sHref,
                        'alt' => '',
                        'title' => '',
                        '_url' => $this->getFullUrl($sHref),
                        '_tag' => 'link :: rel=image_src',
                    ));
                }
            }
            
            // thumbs in <meta property="og:image" content="[...]" />
            $anchors = $this->_oDom->getElementsByTagName('meta');
            foreach ($anchors as $element) {
                if ($element->getAttribute('property')==='og:image' && $element->getAttribute('content')){
                    $sHref = $element->getAttribute('content');
                    $sType = $this->getUrlType($sHref);
                    if ($sFilterByType && $sFilterByType != $sType) {
                        continue;
                    }
                    $this->_add2Array($aReturn[$sType], array(
                        'ressourcetype' => 'image',
                        'href' => $sHref,
                        'alt' => '',
                        'title' => '',
                        '_url' => $this->getFullUrl($sHref),
                        '_tag' => 'meta :: property=og:image',
                    ));
                }
            }
            // thumbs in <video poster="[...]" />
            $anchors = $this->_oDom->getElementsByTagName('video');
            foreach ($anchors as $element) {
                if ($element->getAttribute('poster')>''){
                    $sHref = $element->getAttribute('poster');
                    $sType = $this->getUrlType($sHref);
                    if ($sFilterByType && $sFilterByType != $sType) {
                        continue;
                    }
                    $this->_add2Array($aReturn[$sType], array(
                        'ressourcetype' => 'image',
                        'href' => $sHref,
                        'alt' => '',
                        'title' => '',
                        '_url' => $this->getFullUrl($sHref),
                        '_tag' => 'video poster',
                    ));
                }
            }
        }
        return $aReturn;
    }

    /**
     * get all scripts of this document
     * @param string  $sFilterByType  return links of this type only
     * @return array
     */
    public function getScripts($sFilterByType = false) {
        $aReturn = array();
        if ($this->_oDom) {

            $anchors = $this->_oDom->getElementsByTagName('script');
            foreach ($anchors as $element) {
                if ($element->getAttribute('src')) {
                    $sHref = $element->getAttribute('src');
                    $sType = $this->getUrlType($sHref);
                    if ($sFilterByType && $sFilterByType != $sType) {
                        continue;
                    }
                    $this->_add2Array($aReturn[$sType], array(
                        'ressourcetype' => 'script',
                        'href' => $sHref,
                        '_url' => $this->getFullUrl($element->getAttribute('src')),
                    ));
                }
            }
        }
        return $aReturn;
    }

    /**
     * helper: get links by tag name and attribute from already loaded html document
     * and return as array of dom objects
     * used in getLinks()
     * @see getLinks
     * @return array
     */
    public function _getNodesByTagAndAttribute($sTag, $sAttribute) {
        $aReturn = array();
        if ($this->_oDom) {

            $anchors = $this->_oDom->getElementsByTagName($sTag);
            if ($anchors) {
                foreach ($anchors as $element) {
                    $domAttribute = $this->_oDom->createAttribute('_attribute');
                    $domAttribute->value = $sAttribute;
                    $element->appendChild($domAttribute);

                    $sHref = $element->getAttribute($sAttribute);
                    $domAttribute2 = $this->_oDom->createAttribute('_href');
                    $domAttribute2->value = htmlentities($sHref);
                    $element->appendChild($domAttribute2);

                    $aReturn[] = $element;
                }
            }
        }
        return $aReturn;
    }

    /**
     * Get all links by type of this document. For internal links you get a
     * full url; email links will be parsed and yout get to, cc, subject, ... as
     * separate values.
     * 
     * Possible types are: 
     *      local     url starts with a hash
     *      internal  url is on same domain
     *      external  url is http(s) to other domains
     *      mailto    email
     *      script    script (javascript, jscript or vbscript)
     *      other     other protocol than http(s)
     * 
     * @param string  $sFilterByType  return links of this type only; deault: return all links
     * @param string  $bShowNofollow  skip links with attribute rel="nofollow"? default: return nofollow links
     * @return array
     */
    public function getLinks($sFilterByType = false, $bShowNofollow=true) {
        $aReturn = array();
        if ($this->_oDom) {

            $anchors = array_merge(
                    $this->_getNodesByTagAndAttribute('a', 'href'), 
                    $this->_getNodesByTagAndAttribute('area', 'href'), 
                    $this->_getNodesByTagAndAttribute('link', 'href'), 
                    $this->_getNodesByTagAndAttribute('frame', 'src'), 
                    $this->_getNodesByTagAndAttribute('iframe', 'src')
            );

            foreach ($anchors as $element) {
                $sHref = $element->getAttribute('_href');

                // skip link tag with rel="stylesheet" - these are fetched in getCss
                if ($element->getAttribute('rel') && (
                        $element->getAttribute('rel') === 'stylesheet'
                        || $element->getAttribute('rel') === 'dns-prefetch'
                        || $element->getAttribute('rel') === 'preconnect'
                        || $element->getAttribute('rel') === 'prefetch'
                        || $element->getAttribute('rel') === 'subresource'
                        || $element->getAttribute('rel') === 'pingback'
                        || $element->getAttribute('rel') === 'preload'
                        || (!$bShowNofollow && $element->getAttribute('rel') === 'nofollow')
                        )
                ) {
                    continue;
                }

                $sType = $this->getUrlType($sHref);
                if ($sFilterByType && $sFilterByType != $sType) {
                    continue;
                }

                $sUrl = false;
                if ($sType == "external") {
                    // $sUrl = preg_replace('/\#.*$/', '', $sHref);
                    $sUrl = preg_replace('/\#.*$/', '', $this->getFullUrl($sHref));
                }
                if ($sType == "internal") {
                    $sUrl = preg_replace('/\#.*$/', '', $this->getFullUrl($sHref));
                }
                $aLink = array(
                    '_tag' => $element->nodeName,
                    '_attribute' => $element->getAttribute('_attribute'),
                    'ressourcetype' => 'link',
                    'href' => $sHref,
                    // 'element' => $element,
                    'label' => $element->nodeValue,
                );
                if ($sType == "mailto") {
                    $aEmail = $this->_parseEmail($sHref);
                    $sUrl = 'mailto:' . $aEmail['to'];

                    $aLink = array_merge($aLink, $aEmail);
                }

                foreach (array('rel', 'title', 'class', 'id') as $sAttr) {
                    if ($element->getAttribute($sAttr)) {
                        $aLink[$sAttr] = $element->getAttribute($sAttr);
                    }
                }
                if ($sUrl) {
                    $aLink['_url'] = $sUrl;
                }

                $this->_add2Array($aReturn[$sType], $aLink);
                // $aReturn[$sType][] = $aLink;
            }
        }
        return $aReturn;
    }

    private function _add2Array(&$aArray, $aNewItem) {
        $aReturn = $aArray;
        $bFound = false;

        if (!array_key_exists('_url', $aNewItem)) {
            // echo "missing _url: ". print_r($aNewItem, 1)."\n<br>";
            return false;
        }
        $aAddItem = $aNewItem;
        unset($aAddItem['ressourcetype']);
        unset($aAddItem['_url']);

        if (is_array($aArray) && count($aArray)) {
            for ($i = 0; $i < count($aArray); $i++) {
                if ($aArray[$i]['_url'] === $aNewItem['_url']) {
                    $aArray[$i]['refcount'] ++;
                    $aArray[$i]['items'][] = $aAddItem;
                    $bFound = true;
                    continue;
                }
            }
        }
        if (!$bFound) {
            $aBase = array(
                'ressourcetype' => $aNewItem['ressourcetype'],
                '_url' => $aNewItem['_url'],
                'refcount' => 1,
                'items' => array($aAddItem),
            );
            $aArray[] = $aBase;
        }
        return $aArray;
    }

    // ----------------------------------------------------------------------
    // 
    // REPORT
    // 
    // ----------------------------------------------------------------------

    /**
     * return array with full report
     * @return array
     */
    public function getReport() {
        /*
          $this->_getFullUrl('tel:0049123456');
          $this->_getFullUrl('/absolute/path/test.html');
          $this->_getFullUrl('https://otherhost/path2/test.html');
          $this->_getFullUrl('//myhost/path3/test.html');
          $this->_getFullUrl('rel/reldocument.html');
          $this->_getFullUrl('rel/reldocument.html#testhash');
          $this->_getFullUrl('rel/reldocument.html?a=3');
          $this->_getFullUrl('./rel/reldocument.html');
          $this->_getFullUrl('../../backward/and/forward.html');
          echo "<hr>";
         * 
         */
        return array(
            'document' => array(
                'url' => $this->_sUrl,
                'basehref' => $this->_sBaseHref,
                'size' => strlen($this->_sHtml),
                'isXml' => is_object($this->_oDom),
            ),
            'meta' => array(
                'css' => $this->getCss(),
                'description' => $this->getMetaDescription(),
                'keywords' => $this->getMetaKeywords(),
                'follow' => $this->canFollowLinks(),
                'generator' => $this->getMetaGenerator(),
                'index' => $this->getMetaIndex(),
                'scripts' => $this->getScripts(),
                'title' => $this->getMetaTitle(),
            ),
            'body' => array(
                'images' => $this->getImages(),
                'links' => $this->getLinks(),
            )
        );
    }

}
