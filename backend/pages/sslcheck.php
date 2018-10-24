<?php
/**
 * page analysis :: Http header check
 */

// --- https certificate
$sReturn='';


$sReturn.=$this->_getNavi2($this->_getProfiles());
$this->setSiteId($this->_sTab); // to load the profile into $this->aProfile
$sFirstUrl=isset($this->aProfile['searchindex']['urls2crawl'][0]) ? $this->aProfile['searchindex']['urls2crawl'][0] : false;
$sReturn.= '<h3>' . $this->lB('sslcheck.label') . '</h3>'
    . '<p>'
        . $this->lB('sslcheck.description').'<br>'
    . '</p>'
        ;
    // --- http only?
    if(!$sFirstUrl){
        return $sReturn.'<br><div class="warning">'.$this->lB('sslcheck.nostarturl').'</div>';
        
    } else if(strstr($sFirstUrl, 'http://')){
        // array_unshift($aWarnheader, $this->lB('httpheader.warnings.httponly'));
        $sReturn.= '<ul class="tiles errors">'
                . '<li>'
                    .'<a href="#" onclick="return false;" class="tile">'.$this->lB('sslcheck.httponly')
                    .'<br><strong>'.$this->lB('sslcheck.httponly.description').'</strong><br>'
                    . $this->lB('sslcheck.httponly.hint')
                    .'</a>'
                . '</li>'
                . '</ul><div style="clear: both;"></div>'
                ;
    } else {

        $oSsl=new sslinfo();
        $aSslInfos=$oSsl->getSimpleInfosFromUrl($sFirstUrl);
        $sStatus=$oSsl->getStatus();
        $aTbl=array();
        foreach(array(
            'CN', 
            'issuer',
            'CA',
            'DNS',
            'validfrom',
            'validto',
        ) as $sKey){
            $aTbl[]=array($this->lB('sslcheck.'.$sKey), $aSslInfos[$sKey]);
        }

        $iDaysleft = round((date("U", strtotime($aSslInfos['validto'])) - date('U')) / 60 / 60 / 24);
        $aTbl[]=array($this->lB('sslcheck.validleft'), $iDaysleft);

        $sReturn.= '<ul class="tiles '.$sStatus.' '.$sStatus.'s">'
                . '<li>'
                    .'<a href="#" onclick="return false;" class="tile">'
                    . $aSslInfos['CN']
                    .'<br><strong>'.$aSslInfos['issuer'].'</strong><br>'
                    . $aSslInfos['validto'].' ('.$iDaysleft.' d)'
                    .'</a>'
                . '</li>'
                . '</ul><div style="clear: both;"></div>'
                . $this->_getSimpleHtmlTable($aTbl)
                /*
                . '<br>'
                . '<p>'.$this->lB('httpheader.sslcheck.raw').':</p>'
                . '<pre>'
                . print_r($oSsl->getCertinfos($aFirstPage[0]['url']), 1)
                . '</pre>'
                 */
                ;

}

// $sStartUrl=$this->aProfile['searchindex']['urls2crawl'][$sUrl][0];^$sReturn.=$sStartUrl.'<br>';
return $sReturn;
