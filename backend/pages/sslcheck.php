<?php
/**
 * page analysis :: Http header check
 * TODO: use $this->_getStatusinfos(array('_global','sslcheck'));
 */

$aSSLPorts=array(
    'wellknown'=>array(
        array(443, 'HTTPS'),
        array(465, 'SMTPS'),
        array(563, 'NNTPS'),
        array(585, 'IMAPS'),
        array(636, 'LDAPS'),
        array(695, 'IEEE-MMS-SSL'),
        array(898, 'Brocade SMI-S RPC SSL'),
        array(989, 'FTPS data'),
        array(990, 'FTPS control'),
        array(992, 'Telnet protocol over TLS/SSL'),
        array(993, 'IMAPS'),
        array(994, 'IRCS'),
        array(995, 'POP3S'),
    ),
    'registered'=>array(
        array(1311, 'Dell OpenManage HTTPS'),
        array(2083, 'Secure RADIUS Service (radsec)'),
        array(2083, 'cPanel default SSL'),
        array(2087, 'WebHost Manager default SSL'),
        array(2096, 'cPanel default SSL web mail'),
        array(2376, 'Docker REST API'),
        array(2484, 'Oracle database listening'),
        array(3269, 'Microsoft Global Catalog over SSL'),
        array(3224, 'Xware xTrm Communication Protocol over SSL'),
        array(3389, 'RDP'),
        array(4843, 'OPC UA TCP Protocol over TLS/SSL'),
        array(5223, 'XMPP client connection over SSL'),
        array(5986, 'Windows Remote Management Service (WinRM-HTTPS)'),
        array(6619, 'odette-ftps'),
        array(6679, 'IRC SSL'),
        array(6697, 'IRC SSL'),
        array(7002, 'BEA WebLogic HTTPS server'),
        array(8243, 'HTTPS listener for Apache Synapse'),
        array(8333, 'VMware VI Web Access via HTTPS'),
        array(8443, 'Apache Tomcat SSL'),
        array(8443, 'Promise WebPAM SSL'),
        array(8443, 'iCal over SSL'),
        array(8448, 'Matrix homeserver federation over HTTPS'),
        array(8531, 'Windows Server Update Services over HTTPS'),
        array(8888, 'HyperVM over HTTPS'),
        array(9091, 'Openfire Administration Console SSL'),
        array(9443, 'VMware Websense Triton console'),
        array(11214, 'memcached incoming SSL proxy'),
        array(11215, 'memcached internal outgoing SSL proxy'),
        array(12443, 'IBM HMC web browser management access over HTTPS'),
        array(18091, 'memcached Internal REST HTTPS for SSL'),
        array(18092, 'memcached Internal CAPI HTTPS for SSL'),
        array(32976, 'LogMeIn Hamachi'),
    ),
);

// --- certificate
$oRenderer=new ressourcesrenderer($this->_sTab);
$sReturn='';



// ----------------------------------------------------------------------
// public: show form ... or backend: take first url of a project
// ----------------------------------------------------------------------
if ($this->_bIsPublic){

    // fetch GET to fill form
    $sMyHost=( isset($_GET['host']) && $_GET['host']) ? $_GET['host'] : '';
    $sMyPort=( isset($_GET['port']) && (int)$_GET['port'] ) ? (int)$_GET['port'] : '443';

    $sDatalist='';
    foreach($aSSLPorts as $sGroup=>$aItems){
        foreach ($aItems as $aPortItem){
            $sDatalist.='<option value="'.$aPortItem[0].'">'.$aPortItem[0].' - '.$aPortItem[1].'</option>';
        }
    }
    $sReturn.=''
        . $oRenderer->renderContextbox(
                $oRenderer->renderBookmarklet('sslcheck')
                , $this->lB('bookmarklet.sslcheck.head')
            )
            . '<h3>'.$this->lB('sslcheck.settarget').'</h3>'
            .'<p>'.$this->lB('sslcheck.settarget.hint').'</p>
            <form class="pure-form pure-form-aligned" method="GET" action="?">
                <input type="hidden" name="page" value="sslcheck">
                <input type="hidden" name="lang" value="'.$this->sLang.'">
                <nobr>
                <input type="text" size="30" name="host" id="e_host" value="'.htmlentities($sMyHost).'" placeholder="example.com" pattern="^[a-zA-Z0-9\.\-]*$">
                <input type="text" size="25" name="port" id="e_port" value="'.htmlentities($sMyPort).'" placeholder="443" pattern="^[0-9]*$" list="portlist">
                <datalist id="portlist">'.$sDatalist.'</datalist>'
                
                .($sMyHost
                        ? $oRenderer->oHtml->getTag('a', array('label'=>$this->_getIcon('button.close'), 'class'=>'pure-button button-error', 'href'=>'?page=sslcheck&lang='.$this->sLang)).' '
                        : ''
                )
                .'<button class="pure-button button-secondary">'.$this->_getIcon('button.save').'</button>'
                .'</nobr>'
            .'</form>'
            . '<div style="clear: both;"></div>'
            ;
    // verify host
    $sMyHost=( preg_replace('/^[a-zA-Z0-9\.\-]*$/i', '', $sMyHost) !== $sMyHost) ? $sMyHost : '';
    $sMyUrl=($sMyHost && $sMyPort) ? 'ssl://'.$sMyHost.':'.$sMyPort : false;
    
} else {
    $sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');
    $this->setSiteId($this->_sTab); // to load the profile into $this->aProfile
    $sMyUrl=isset($this->aProfileSaved['searchindex']['urls2crawl'][0]) ? $this->aProfileSaved['searchindex']['urls2crawl'][0] : false;
}

// ------------------------------------------------------------
// SSL certificate infos
// ------------------------------------------------------------

    // --- http only?
    if(!$sMyUrl){
        if (!$this->_bIsPublic){
            $sReturn.= '<h3>' . $this->lB('sslcheck.label') . '</h3>';
            $sReturn.='<br>'.$oRenderer->renderMessagebox($this->lB('sslcheck.nostarturl'), 'warning');
        }
        return $sReturn;
    } else if(strstr($sMyUrl, 'http://')){
        $sReturn.= '<h3>' . $this->lB('sslcheck.label') . '</h3>';
        $sReturn.= $oRenderer->renderTileBar(
                $oRenderer->renderTile('error', $this->lB('sslcheck.httponly'), $this->lB('sslcheck.httponly.description'), $this->lB('sslcheck.httponly.hint')) 
            )
            . '<div style="clear: both;"></div>'
        ;
    } else {
        $sMyDomain = parse_url($sMyUrl,  PHP_URL_HOST);
        // https://www.ssllabs.com/ssltest/analyze.html?viaform=on&d=www.srf.ch&hideResults=on

        $oSsl=new sslinfo();
        $aSslInfos=$oSsl->getSimpleInfosFromUrl($sMyUrl);
        
        if(isset($aSslInfos['_error']) && $aSslInfos['_error']){
            $sReturn.='<br>'.$oRenderer->renderMessagebox($this->lB('sslcheck.nosslurl'), 'error');
            return $sReturn;
        }
        $sStatus=$oSsl->getStatus();

        // for RAW DATA
        $aSslInfosAll=$oSsl->getCertinfos($url=false);
        
        $sEvInfos='';
        if($aSslInfos['type']==='EV'){
            $aTblEV=array();
            foreach($aSslInfos['subject'] as $sKey=>$value){
                $aTblEV[]=array($sKey, $value);
            }
            $sEvInfos='<br><br>'.$this->_getSimpleHtmlTable($aTblEV, false);
        }
        $aTbl=array();
        $aTbl[]=array(
            $this->lB('sslcheck.thlabel'), 
            $this->lB('sslcheck.thvalue'), 
        );
        foreach(array(
            'CN', 
            'signatureTypeSN',
            'type',
            'issuer',
            'CA',
            'DNS',
            'validfrom',
            'validto',
        ) as $sKey){
            $aTbl[]=array(
                $this->lB('sslcheck.'.$sKey), 
                
                ($sKey=='type' 
                        ? '<strong>'.$this->lB('sslcheck.type.'.$aSslInfos['type']).'</strong><br><br>'
                            . $this->lB('sslcheck.type.usage').':<br>'
                            . $this->lB('sslcheck.type.'.$aSslInfos['type'].'.usage')
                            . $sEvInfos
                        : $aSslInfos[$sKey]
                    )
            );
        }

        $iDaysleft = round((date("U", strtotime($aSslInfos['validto'])) - date('U')) / 60 / 60 / 24);
        $aTbl[]=array($this->lB('sslcheck.validleft'), $iDaysleft);
        
        $aTblLevel=array();
        $aTblLevel[]=array('',$this->lB('sslcheck.type'), $this->lB('sslcheck.type.description'), $this->lB('sslcheck.type.usage'));
        foreach(array(
            'EV', 
            'Business SSL',
            'selfsigned',
            'none',
        ) as $sKey){
            // $bActive=$aSslInfos['type']==$this->lB('sslcheck.type.'.$sKey);
            $bActive=$aSslInfos['type']==$sKey;
            $aTblLevel[]=array(
                ($bActive ? ' >> ' : ''),
                ($bActive ? '<strong>'.$this->lB('sslcheck.type.'.$sKey).'</strong>' : $this->lB('sslcheck.type.'.$sKey)), 
                $this->lB('sslcheck.type.'.$sKey.'.description'),
                $this->lB('sslcheck.type.'.$sKey.'.usage')
            );
        }

        $sReturn.= '<h3>' . $this->lB('sslcheck.label') . '</h3>';
        $sReturn.= (!$this->_bIsPublic) ? $oRenderer->renderContextbox(
                $oRenderer->renderBookmarklet('sslcheck')
                , $this->lB('bookmarklet.sslcheck.head')
            ) : ''
            . '<div style="clear: left;"></div>';
        $sReturn.= $oRenderer->renderContextbox(
                '<p>'.$this->lB('sslcheck.context.links').'</p>'
                . '<ul>'
                    .'<li><a href="https://www.ssllabs.com/ssltest/analyze.html?viaform=on&d='.$sMyDomain.'&hideResults=on" target="_blank">ssllabs.com</a></li>'
                .'</ul>'
                , $this->lB('context.links')
            );
        $sReturn.= ''
            . '<p>'
                . sprintf($this->lB('sslcheck.description'), $aSslInfos['domain'], $aSslInfos['port'])
            . '</p>'

            .$oRenderer->renderTileBar(
                $oRenderer->renderTile($sStatus, $aSslInfos['CN'], $aSslInfos['issuer'] ? $aSslInfos['issuer'] : $this->lB('sslcheck.type.selfsigned'), $aSslInfos['validto'].' ('.$iDaysleft.' d)')
            )
        . '<div style="clear: both;"></div>'
                
        . $this->_getSimpleHtmlTable($aTbl, 1)
                

        . '<h3>'.$this->lB('sslcheck.type.levels').'</h3>'
        . '<p>'.sprintf($this->lB('sslcheck.type.intro'), $this->lB('sslcheck.type.'.$aSslInfos['type'])).'</p>'
        . $this->_getSimpleHtmlTable($aTblLevel, 1)


        . '<h3>' . $this->lB('sslcheck.raw') . '</h3>'
        . '<p>'.$this->lB('sslcheck.raw.hint').'</p>'
        . $oRenderer->renderToggledContent(
                $this->lB('sslcheck.raw.openclose'),
                '<pre>'.str_replace(array('\n"', '\n'), array('"', '<br>'), json_encode($aSslInfosAll, JSON_PRETTY_PRINT)).'</pre>', false
                )
        ;

        // ------------------------------------------------------------
        // scan http ressources
        // ------------------------------------------------------------
        
        if (!$this->_bIsPublic){
            $iRessourcesCount=$this->getRecordCount('ressources', array('siteid'=>$this->_sTab));

            if($iRessourcesCount){

                $bShowAll=$this->_getRequestParam('showall');
                $bShowReport=$this->_getRequestParam('showreport');

                $sTableId='tbl-nonhttpsitems';
                $oRessources=new ressources();
                $aFields = array('id', 'siteid', 'url', 'http_code', 'ressourcetype', 'type', 'content_type');
                $aWhere=array('siteid' => $this->_sTab, 'url[~]'=>'http:%');
                $aWhereNoLink=array_merge($aWhere, array('ressourcetype[!]' => 'link'));

                $sBtnReport=$this->_getButton(array(
                    'href'=>$this->_getQs(array(
                        'showreport'=>1,
                        'showtable'=>0,
                        'tab'=>$this->_sTab,
                    )).'#',
                    'class'=>'button-secondary',
                    'label'=>'ressources.showreport',
                    'popup' => false
                ));
                $sBtnTable=$this->_getButton(array(
                    'href'=>$this->_getQs(array(
                        'showreport'=>0,
                        'showtable'=>1,
                        'tab'=>$this->_sTab,
                    )).'#',
                    'class'=>'button-secondary',
                    'label'=>'ressources.showtable',
                    'popup' => false
                ));


                $iNonHttps=$this->oDB->count('ressources',$aWhere);
                $iNonHttpsNoLink=$this->oDB->count('ressources',$aWhereNoLink);
                // $sReturn.= $this->oDB->last().'<br>';
                $iChartWarnings = $bShowAll ? $iNonHttps : $iNonHttpsNoLink;

                $sReturn.= '<h3 id="h3nonhttps'.($bShowAll ? 1 : 0).'">' . sprintf($this->lB('sslcheck.nonhttps'), $iNonHttpsNoLink) . '</h3>'
                    .'<p>'.$this->lB('sslcheck.nonhttps.hint').'</p>'
                    // .$this->_getHtmlchecksChart($iRessourcesCount, $iNonHttps)
                    . $oRenderer->renderTileBar(
                        $oRenderer->renderTile('',            $this->lB('ressources.itemstotal'), $iRessourcesCount, '', '')
                        .$oRenderer->renderTile(
                                // $iNonHttps ? 'warning' : 'ok', 
                                '', 
                                $this->lB('sslcheck.nonhttpscount'),
                                $iNonHttps,
                                $iNonHttps ? (floor($iNonHttps/$iRessourcesCount*1000)/10).'%' : '',
                                $bShowAll ? '' : $this->_getQs(array('showall'=>1)).'#h3nonhttps1'
                        )
                        .$oRenderer->renderTile(
                                $iNonHttpsNoLink ? 'warning' : 'ok', 
                                $this->lB('sslcheck.nonhttpscountNolink'),
                                $iNonHttpsNoLink,
                                $iNonHttpsNoLink ? (floor($iNonHttpsNoLink/$iRessourcesCount*1000)/10).'%' : '',
                                $bShowAll ? $this->_getQs(array('showall'=>0)).'#h3nonhttps0' : ''
                        )
                    )
                    . '<div style="clear: both;"></div>'
                    .$this->_getHtmlchecksChart($iRessourcesCount, $iChartWarnings)    
                    ;


                if($iNonHttpsNoLink || ($iNonHttps && $bShowAll)){
                    $aTable = array();
                    $iReportCounter=0;

                    $aRessourcelist = $oRessources->getRessources($aFields, ($bShowAll ? $aWhere : $aWhereNoLink), array("url"=>"ASC"));

                    // --- what to create: table or report list
                    $sReturn.=($bShowReport ? $sBtnTable : $sBtnReport).'<br><br>';

                    foreach ($aRessourcelist as $aRow) {

                        if($bShowReport){
                            $iReportCounter++;
                            $sReturn.=''
                                    .'<div class="counter">'. $iReportCounter.'</div>'
                                    . '<div style="clear: left;"></div>'
                                    .$oRenderer->renderReportForRessource($aRow);
                        } else {
                            $aRow['url'] = '<a href="?page=ressourcedetail&id=' . $aRow['id'] . '&siteid=' . $this->_sTab.'">'.str_replace('/', '/&shy;', $aRow['url']).'</a>';
                            $aRow['ressourcetype'] = $oRenderer->renderArrayValue('ressourcetype', $aRow);
                            $aRow['type'] = $oRenderer->renderArrayValue('type', $aRow);
                            $aRow['http_code'] = $oRenderer->renderArrayValue('http_code', $aRow);
                            unset($aRow['id']);
                            unset($aRow['siteid']);
                            $aTable[] = $aRow;
                        }

                    }
                    $sReturn.=(count($aTable) 
                            ? $this->_getHtmlTable($aTable, "db-ressources.", $sTableId)
                                . $oRenderer->renderInitDatatable('#' . $sTableId)
                                . $this->_getHtmlLegend(array_keys($aRow), 'db-ressources.')

                            : ''
                        );
                }
            } else {
                $sReturn.='<br>'
                .$oRenderer->renderMessagebox(
                    sprintf($this->lB('status.emptyindex'), $this->_sTab),
                    'warning'
                )
                ;
            }
        } // if (!$this->_bIsPublic) 
}
return $sReturn;
