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
class httpstatus {
    //put your code here
    var $_responseInfo=false;
    var $_http_code=false;

    var $_aHttpStatus=array(
        "-1"=>'TODO... this url was not analyzed yet.',
        0=>'There is no connection to the target. It can be a network problem, host not found in DNS, no running servive, closed port, problem with SSL.',
        
        100=>'...information',
        101=>'...information',
        102=>'...information',
        
        200=>'OK',
        201=>'Created',
        202=>'Accepted',
        
        203=>'...successful operation',
        204=>'...successful operation',
        205=>'...successful operation',
        206=>'...successful operation',
        207=>'...successful operation',
        208=>'...successful operation',
        226=>'...successful operation',
        
        300=>'...redirect',
        301=>'...redirect',
        302=>'...redirect',
        303=>'...redirect',
        304=>'...redirect',
        305=>'...redirect',
        306=>'...redirect',
        307=>'...redirect',
        308=>'...redirect',
        
        400=>'...client error',
        401=>'...client error',
        402=>'...client error',
        403=>'...client error',
        404=>'...client error',
        405=>'...client error',
        406=>'...client error',
        407=>'...client error',
        408=>'...client error',
        409=>'...client error',
        410=>'...client error',
        411=>'...client error',
        412=>'...client error',
        413=>'...client error',
        414=>'...client error',
        415=>'...client error',
        416=>'...client error',
        417=>'...client error',
        418=>'...client error',

        420=>'...client error',
        421=>'...client error',
        422=>'...client error',
        423=>'...client error',
        424=>'...client error',
        425=>'...client error',
        426=>'...client error',

        428=>'...client error',
        429=>'...client error',
        431=>'...client error',
        451=>'...client error',
        
        444=>'...propriatary client error',
        449=>'...propriatary client error',
        499=>'...propriatary client error',
        
        500=>'...server error',
        501=>'...server error',
        502=>'...server error',
        503=>'...server error',
        504=>'...server error',
        505=>'...server error',
        506=>'...server error',
        507=>'...server error',
        508=>'...server error',
        509=>'...server error',
        510=>'...server error',
        511=>'...server error',
        
        999=>'...propriatary error',
        
    );
    
    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------
    /**
     * init http status code class
     * @param array|integer  $value  http header OR status code
     * @return boolean
     */
    public function __construct($value=false) {
        if(is_array($value)){
            return $this->setResponse($value);
        }
        if(is_integer($value)){
            return $this->setHttpcode($value);
        }
        return true;
    }
    
    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------
    
    /**
     * set status by a curl reposnse info array
     * 
     * @param array  $aResponseInfo
     * @return boolean
     */
    public function setResponse($aResponseInfo){
        if (!is_array($aResponseInfo) || !array_key_exists('http_code', $aResponseInfo)){
            return false;
        }
        $this->_responseInfo=$aResponseInfo;
        $this->_http_code=$aResponseInfo['http_code'];
        
        // patch curl data: add a missing redirect_url
        if ($this->isRedirect() && !$this->_responseInfo['redirect_url'] && isset($aResponseInfo['_responseheader'][0]) ){
            preg_match("/location: (.*)[\\r]/i" , $aResponseInfo['_responseheader'][0], $aTmp);
            $this->_responseInfo['redirect_url']=isset($aTmp[1]) ? $aTmp[1] : $this->_responseInfo['redirect_url'];
        }
    }
    
    public function setHttpcode($iHttpcode){
        /*
        if ($iHttpcode && !array_key_exists($iHttpcode, $this->_aHttpStatus)){
            echo "WARNING: http code must be a valid integer value... but was [".print_r($iHttpcode, 1)."]<br>\n";
            return false;
        }
         * 
         */
        $this->_responseInfo=false;
        $this->_http_code=($iHttpcode || $iHttpcode==="0"  || $iHttpcode===0) ? (int)$iHttpcode : -1 ;
        return true;
    }
    
    // ----------------------------------------------------------------------
    // GETTER :: boolean status
    // ----------------------------------------------------------------------
    
    /**
     * is it a wrong request? it returns true if status is 0
     * We don't have a http status if no http conecection was established. This
     * happens
     * - no host was found in dns
     * - host sends no answer (it has no running http or port is closed)
     * @return boolean
     */
    public function isTodo(){
        return ($this->_http_code===-1);
    }
    /**
     * is the request in process? 1xx Informational response
     * @return boolean
     */
    public function isProcessing(){
        return ($this->_http_code && $this->_http_code>=100 &&  $this->_http_code<200);
    }
    /**
     * was the http request OK? It is true if status is a 2xx.
     * It includes ok answers for partial content
     * @return boolean
     */
    public function isOperationOK(){
        return ($this->_http_code && $this->_http_code>=200 &&  $this->_http_code<300);
    }

    /**
     * was the http request OK and I have all Content? It is true if status is a 200 or 204.
     * @return boolean
     */
    public function isOK(){
        return ($this->_http_code && (
                    $this->_http_code===200 
                    || $this->_http_code<204
               ));
    }
    
    /**
     * is it a redirect? it returns true if status is a 3xx
     * @return boolean
     */
    public function isRedirect(){
        return ($this->_http_code && $this->_http_code>=300 && $this->_http_code<400);
    }
    
    /**
     * is it a client error? it returns true if status is a 4xx
     * @return boolean
     */
    public function isClientError(){
        return ($this->_http_code && $this->_http_code>=400 && $this->_http_code<500);
    }
    
    /**
     * is it a server error? it returns true if status is a 5xx
     * @return boolean
     */
    public function isServerError(){
        return ($this->_http_code && $this->_http_code>=500);
    }
    /**
     * is it a wrong request? it returns true if status is 0
     * We don't have a http status if no http conecection was established. This
     * happens
     * - no host was found in dns
     * - host sends no answer (it has no running http or port is closed)
     * @return boolean
     */
    public function isWrongRequest(){
        return $this->_http_code>=0 && $this->_http_code<100;
    }
    
    /**
     * is it any error? it returns true if error, client error or server error
     * occured
     * @return boolean
     */
    public function isError(){
        return ($this->isWrongRequest() || $this->isClientError() || $this->isServerError());
    }
    
    // ----------------------------------------------------------------------
    // GETTER :: status with value
    // ----------------------------------------------------------------------
    
    public function getContenttype(){
        if (!$this->_responseInfo){
            return false;
        }
        return preg_replace('/\;.*$/', '', $this->_responseInfo['content_type']);
    }
    
    /**
     * get value of http status
     * @return integer
     */
    public function getHttpcode(){
        return $this->_http_code;
    }

    /**
     * get redirect url from http response header
     * @return string
     */
    public function getRedirect(){
        if (!$this->isRedirect()){
            return false;
        }
        if (!$this->_responseInfo || !isset($this->_responseInfo['redirect_url']) || !$this->_responseInfo['redirect_url'] ){
            echo "WARNING: redirect was found but I have just the status code " . $this->_http_code . "<br>\n";
            print_r($this->_responseInfo);
            return false;
        }
        return $this->_responseInfo['redirect_url'];
    }
    
    /**
     * get textinfos of a status; can be used as css class
     * @return array
     */
    public function getStatus(){
        if($this->_http_code===false){
            return false;
        }
        if($this->isTodo()){
            return 'todo';
        }
        if($this->isWrongRequest()){
            return '0xx-noConnect';
        }
        if($this->isProcessing()){
            return '1xx-processing';
        }
        if($this->isOperationOK()){
            return '2xx-ok';
        }
        if($this->isRedirect()){
            return '3xx-redirect';
        }
        if($this->isClientError()){
            return '4xx-client-error';
        }
        if($this->isServerError()){
            return '5xx-server-error';
        }
        if($this->isError()){
            return 'other-error';
        }
        return 'unknown';
        /*
        
        return (array_key_exists($this->_http_code, $this->_aHttpStatus)
                ? $this->_aHttpStatus[$this->_http_code]
                : $this->_http_code.' - ???'
                );
         * 
         */
    }
}
