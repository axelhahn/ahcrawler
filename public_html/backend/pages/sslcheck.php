<?php
/**
 * page analysis :: Http header check
 * TODO: use $this->_getStatusinfos(['_global','sslcheck']);
 */

$aSSLPorts=[
    'wellknown'=>[
        [443, 'HTTPS'],
        [465, 'SMTPS'],
        [563, 'NNTPS'],
        [585, 'IMAPS'],
        [636, 'LDAPS'],
        [695, 'IEEE-MMS-SSL'],
        [898, 'Brocade SMI-S RPC SSL'],
        [989, 'FTPS data'],
        [990, 'FTPS control'],
        [992, 'Telnet protocol over TLS/SSL'],
        [993, 'IMAPS'],
        [994, 'IRCS'],
        [995, 'POP3S'],
    ],
    'registered'=>[
        [1311, 'Dell OpenManage HTTPS'],
        [2083, 'Secure RADIUS Service (radsec)'],
        [2083, 'cPanel default SSL'],
        [2087, 'WebHost Manager default SSL'],
        [2096, 'cPanel default SSL web mail'],
        [2376, 'Docker REST API'],
        [2484, 'Oracle database listening'],
        [3269, 'Microsoft Global Catalog over SSL'],
        [3224, 'Xware xTrm Communication Protocol over SSL'],
        [3389, 'RDP'],
        [4843, 'OPC UA TCP Protocol over TLS/SSL'],
        [5223, 'XMPP client connection over SSL'],
        [5986, 'Windows Remote Management Service (WinRM-HTTPS)'],
        [6619, 'odette-ftps'],
        [6679, 'IRC SSL'],
        [6697, 'IRC SSL'],
        [7002, 'BEA WebLogic HTTPS server'],
        [8243, 'HTTPS listener for Apache Synapse'],
        [8333, 'VMware VI Web Access via HTTPS'],
        [8443, 'Apache Tomcat SSL'],
        [8443, 'Promise WebPAM SSL'],
        [8443, 'iCal over SSL'],
        [8448, 'Matrix homeserver federation over HTTPS'],
        [8531, 'Windows Server Update Services over HTTPS'],
        [8888, 'HyperVM over HTTPS'],
        [9091, 'Openfire Administration Console SSL'],
        [9443, 'VMware Websense Triton console'],
        [11214, 'memcached incoming SSL proxy'],
        [11215, 'memcached internal outgoing SSL proxy'],
        [12443, 'IBM HMC web browser management access over HTTPS'],
        [18091, 'memcached Internal REST HTTPS for SSL'],
        [18092, 'memcached Internal CAPI HTTPS for SSL'],
        [32976, 'LogMeIn Hamachi'],
    ],
];

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
                        ? $oRenderer->oHtml->getTag('a', ['label'=>$this->_getIcon('button.close'), 'class'=>'pure-button button-error', 'href'=>'?page=sslcheck&lang='.$this->sLang]).' '
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
    $aSslInfos=['type'=>'none'];
    if(!$sMyUrl){
        if (!$this->_bIsPublic){
            $sReturn.= '<h3>' . $this->lB('sslcheck.label') . '</h3>';
            $sReturn.='<br>'.$oRenderer->renderMessagebox($this->lB('sslcheck.nostarturl'), 'warning');
        }
        return $sReturn;
    } else if(strstr($sMyUrl, 'http://')){
        $sReturn.= '<h3>' . $this->lB('sslcheck.label') . '</h3>';
        $sReturn.= $oRenderer->renderTileBar(
                $oRenderer->renderTile('error', $this->_getIcon('ssl.type-none').$this->lB('sslcheck.httponly'), $this->lB('sslcheck.httponly.description'), $this->lB('sslcheck.httponly.hint')) 
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
        $sCertType=$oSsl->getCertType();
        
        $sOwnerInfos='';
        // if($aSslInfos['type']==='EV'){
        if(count($aSslInfos['subject'])){
            $aTblOwner=[];
            foreach($aSslInfos['subject'] as $sKey=>$value){
                $aTblOwner[]=[$sKey, $value];
            }
            $sOwnerInfos=$this->_getSimpleHtmlTable($aTblOwner, false);
        }
        $aCData=$oSsl->checkCertdata();
        $sTbl='<thead><tr>'
                . '<th>'.$this->lB('sslcheck.thlabel').'</th>'
                . '<th>'.$this->lB('sslcheck.thvalue').'</th>'
                . '</tr></thead><tbody>';
        
        foreach([
            'CN', 
            'type',
            'domainowner',
            'issuer',
            'CA',
            'chaining',
            'DNS',
            'signatureTypeSN',
            'validfrom',
            'validto',
        ] as $sKey){
            $soutdata='';
            switch ($sKey) {
                case 'chaining':
                    $soutdata.=($sCertType=='selfsigned' 
                            ? '('.$this->lB('sslcheck.type.selfsigned').')'
                            : ''
                                . ($aSslInfos[$sKey] 
                                    ? $this->lB('sslcheck.chaining.ok') 
                                    : $this->lB('sslcheck.chaining.fail')
                                )
                            // .'<br><pre> > ' . $aSslInfos['CN'] . '<br>&nbsp;&nbsp; > '.$aSslInfos['CA'].'</pre>'
                        )
                        ;
                    break;
                case 'domainowner':
                    $soutdata.=$sOwnerInfos;
                    break;
                case 'type':
                    $soutdata.='<strong>'.$this->_getIcon('ssl.type-'.$aSslInfos['type']).$this->lB('sslcheck.type.'.$aSslInfos['type']).'</strong><br><br>'
                                . $this->lB('sslcheck.type.usage').':<br>'
                                . $this->lB('sslcheck.type.'.$aSslInfos['type'].'.usage')
                            ;
                    break;
                default:
                    $soutdata.=$aSslInfos[$sKey];
                    break;
            }
            $sTbl.='<tr '.(isset($aCData['keys'][$sKey]) && $aCData['keys'][$sKey]!=='ok' ? ' class="'.$aCData['keys'][$sKey].'"' : '') .'>'
                    . '<td>'.$this->lB('sslcheck.'.$sKey).'</td>'
                    . '<td>'.$soutdata.'</td>'
                . '</tr>';
        }

        $iDaysleft = round((date("U", strtotime($aSslInfos['validto'])) - date('U')) / 60 / 60 / 24);
        $aTbl[]=[$this->lB('sslcheck.validleft'), $iDaysleft];
        

        $sReturn.= '<h3>' . $this->lB('sslcheck.label') . '</h3>';
        /*
        $sReturn.= (!$this->_bIsPublic) ? $oRenderer->renderContextbox(
                $oRenderer->renderBookmarklet('sslcheck')
                , $this->lB('bookmarklet.sslcheck.head')
            ) : ''
            . '<div style="clear: left;"></div>';
         * 
         */
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
                $oRenderer->renderTile($sStatus, $this->_getIcon('ssl.type-'.$aSslInfos['type']).$aSslInfos['CN'], $aSslInfos['issuer'] ? $aSslInfos['issuer'] : $this->lB('sslcheck.type.selfsigned'), $aSslInfos['validto'].' ('.$iDaysleft.' d)')
            )
        . '<div style="clear: both;"></div>'
                
        .'<table class="pure-table pure-table-horizontal datatable">'.$sTbl.'</table>'
        // . $this->_getSimpleHtmlTable($aTbl, 1)
        ;
    }
        $sTblLevel='<thead><tr>'
                . '<th>'.$this->lB('sslcheck.type').'</th>'
                . '<th>'.$this->lB('sslcheck.type.description').'</th>'
                . '<th>'.$this->lB('sslcheck.type.usage').'</th>'
                . '</tr></thead><tbody>';
        foreach([
            'EV'=>'ok', 
            'Business SSL'=>'ok',
            'selfsigned'=>'warning',
            'none'=>'error',
        ] as $sKey=>$sClass){
            $bActive=isset($aSslInfos['type']) && $aSslInfos['type']==$sKey;
            
            // $sTblLevel.='<tr'.($bActive ? ' class="'.$sClass.'"' : '') .'>'
            $sTblLevel.='<tr '.($bActive ? ' class="'.$sClass.'"' : '') .'>'
                    . '<td class="'.$sClass.'">'
                        . $this->_getIcon('ssl.type-'.$sKey)
                        .($bActive ? '<strong>'.$this->lB('sslcheck.type.'.$sKey).'</strong>' : $this->lB('sslcheck.type.'.$sKey))
                    .'</td>'
                    . '<td>'.$this->lB('sslcheck.type.'.$sKey.'.description').'</td>'
                    . '<td>'.$this->lB('sslcheck.type.'.$sKey.'.usage').'</td>'
                    . '</tr>';
        }
        $sTblLevel.='</tbody>';
    

        $sReturn.= '<h3>'.$this->lB('sslcheck.type.levels').'</h3>'
            . '<p>'.sprintf($this->lB('sslcheck.type.intro'), $this->lB('sslcheck.type.'.$aSslInfos['type'])).'</p>'
            .'<table class="pure-table pure-table-horizontal datatable">'.$sTblLevel.'</table>'
        ;

        if(isset($aSslInfosAll)){
            $sReturn.= '<h3>' . $this->lB('sslcheck.raw') . '</h3>'
            . '<p>'.$this->lB('sslcheck.raw.hint').'</p>'
            . $oRenderer->renderToggledContent(
                $this->lB('sslcheck.raw.openclose'),
                '<pre>'.str_replace(['\n"', '\n'], ['"', '<br>'], json_encode($aSslInfosAll, JSON_PRETTY_PRINT)).'</pre>', false
                )
            ;
        }

        // ------------------------------------------------------------
        // scan http ressources
        // ------------------------------------------------------------
        
        if (!$this->_bIsPublic){
            $iRessourcesCount=$this->getRecordCount('ressources', ['siteid'=>$this->_sTab]);

            if($iRessourcesCount){

                $bShowAll=$this->_getRequestParam('showall');
                $bShowReport=$this->_getRequestParam('showreport');

                $sTableId='tableNonhttpsitems';
                $oRessources=new ressources();
                $aFields = ['id', 'siteid', 'url', 'http_code', 'ressourcetype', 'type', 'content_type'];
                $aWhere=['siteid' => $this->_sTab, 'url[~]'=>'http:%'];
                $aWhereNoLink=array_merge($aWhere, ['ressourcetype[!]' => 'link']);

                $sBtnReport=$this->_getButton([
                    'href'=>$this->_getQs([
                        'showreport'=>1,
                        'showtable'=>0,
                        'tab'=>$this->_sTab,
                    ]).'#',
                    'class'=>'button-secondary',
                    'label'=>'ressources.showreport',
                    'popup' => false
                ]);
                $sBtnTable=$this->_getButton([
                    'href'=>$this->_getQs([
                        'showreport'=>0,
                        'showtable'=>1,
                        'tab'=>$this->_sTab,
                    ]).'#',
                    'class'=>'button-secondary',
                    'label'=>'ressources.showtable',
                    'popup' => false
                ]);


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
                                $bShowAll ? '' : $this->_getQs(['showall'=>1]).'#h3nonhttps1'
                        )
                        .$oRenderer->renderTile(
                                $iNonHttpsNoLink ? 'warning' : 'ok', 
                                $this->lB('sslcheck.nonhttpscountNolink'),
                                $iNonHttpsNoLink,
                                $iNonHttpsNoLink ? (floor($iNonHttpsNoLink/$iRessourcesCount*1000)/10).'%' : '',
                                $bShowAll ? $this->_getQs(['showall'=>0]).'#h3nonhttps0' : ''
                        )
                    )
                    . '<div style="clear: both;"></div>'
                    .$this->_getHtmlchecksChart($iRessourcesCount, $iChartWarnings)    
                    ;


                if($iNonHttpsNoLink || ($iNonHttps && $bShowAll)){
                    $aTable = [];
                    $iReportCounter=0;

                    $aRessourcelist = $oRessources->getRessources($aFields, ($bShowAll ? $aWhere : $aWhereNoLink), ["url"=>"ASC"]);

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
                                . $this->_getHtmlLegend(array_keys($aRow), 'db-ressources.')

                            : ''
                        );
                }
            } else {
                $sReturn.='<br>'
                .$oRenderer->renderMessagebox(sprintf($this->lB('ressources.empty'), $this->_sTab), 'warning')
                ;
            }
        } // if (!$this->_bIsPublic) 
return $sReturn;
