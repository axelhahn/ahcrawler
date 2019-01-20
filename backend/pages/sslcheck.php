<?php
/**
 * page analysis :: Http header check
 */

// --- https certificate
$sReturn='';


$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');
$this->setSiteId($this->_sTab); // to load the profile into $this->aProfile
$sFirstUrl=isset($this->aProfile['searchindex']['urls2crawl'][0]) ? $this->aProfile['searchindex']['urls2crawl'][0] : false;
$sReturn.= '<h3>' . $this->lB('sslcheck.label') . '</h3>'
    . '<p>'
        . $this->lB('sslcheck.description').'<br>'
    . '</p>'
        ;
    // --- http only?
    if(!$sFirstUrl){
        $sReturn.='<br>'.$this->_getMessageBox($this->lB('sslcheck.nostarturl'), 'warning');
        return $sReturn;
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

        // ------------------------------------------------------------
        // scan http ressources
        // ------------------------------------------------------------
        
        /*
        
        C O M I N G   S O O N  
          
        $iRessourcesCount=$this->getRecordCount('ressources', array('siteid'=>$iProfileId));
        $iRessourcesCount=1325;
        if($iRessourcesCount){
            $iNonHttps=$this->oDB->count('ressources',array(
                'siteid'=>$this->_sTab,
                'url[~]'=>'http:%',
                'ressourcetype'=>'',
            ));
            // $iNonHttps=345;
            // $sReturn.= $this->oDB->last().'<br>';
            
            $sReturn.= '<h3>' . sprintf($this->lB('sslcheck.nonhttps'), $iNonHttps) . '</h3>'
                .'<p>'.$this->lB('sslcheck.nonhttps.hint').'</p>'
                // .$this->_getHtmlchecksChart($iRessourcesCount, $iNonHttps)
                .'<ul class="tiles warnings">'
                    . ($iNonHttps
                        ? '<li><a class="tile error">'.$this->lB('sslcheck.nonhttpscount').':<br><strong>'.$iNonHttps.'</strong><br>'.(floor($iNonHttps/$iRessourcesCount*1000)/10).'%</a></li>'
                        : '<li><a href="#" class="tile ok">'.$this->lB('sslcheck.nonhttpscount').':<br><strong>'.$iNonHttps.'</strong></a></li>'
                    )
                .'</ul>'
                ;
            
            $sReturn.=$this->_getHtmlchecksTable('select title, length(title) as length, url
                    from pages 
                    where siteid='.$this->_sTab.' and length(title)<'.$iMinTitleLength.'
                    order by length(title)',
                    'tableCrawlerErrors'
                )
                 ;
            $sReturn.="$iNonHttps of $iRessourcesCount<br>";
            
            
        } else {
            $sReturn.='<br>'
            .$this->_getMessageBox(
                sprintf($this->lB('status.emptyindex'), $this->_sTab),
                'warning'
            )
            ;
        }
         */

        
        /*
        $aChartItems[]=array(
            'label'=>$this->lB('linkchecker.found-http-'.$sSection).': '.$aBoxes[$sSection]['total'],
            'value'=>$aBoxes[$sSection]['total'],
            'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-'.$sSection.'\')',
            // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': ',
        );
         * 
         */

}

// $sStartUrl=$this->aProfile['searchindex']['urls2crawl'][$sUrl][0];^$sReturn.=$sStartUrl.'<br>';
return $sReturn;
