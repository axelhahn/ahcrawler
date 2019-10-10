<?php
/**
 * page analysis :: Http header check
 */

// --- https certificate
$oRenderer=new ressourcesrenderer($this->_sTab);
$sReturn='';


$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');
$this->setSiteId($this->_sTab); // to load the profile into $this->aProfile
$sFirstUrl=isset($this->aProfileSaved['searchindex']['urls2crawl'][0]) ? $this->aProfileSaved['searchindex']['urls2crawl'][0] : false;


// ------------------------------------------------------------
// SSL certificate infos
// ------------------------------------------------------------

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
        $sReturn.= $oRenderer->renderTileBar(
                $oRenderer->renderTile('error', $this->lB('sslcheck.httponly'), $this->lB('sslcheck.httponly.description'), $this->lB('sslcheck.httponly.hint')) 
            )
            . '<div style="clear: both;"></div>'
        ;
    } else {

        $oSsl=new sslinfo();
        $aSslInfos=$oSsl->getSimpleInfosFromUrl($sFirstUrl);
        $sStatus=$oSsl->getStatus();
        $aSslInfosAll=$oSsl->getCertinfos($url=false);
        
        $aTbl=array();
        $aTbl[]=array(
            $this->lB('sslcheck.thlabel'), 
            $this->lB('sslcheck.thvalue'), 
        );
        foreach(array(
            'CN', 
            'type',
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

        $sReturn.= $oRenderer->renderTileBar(
                $oRenderer->renderTile($sStatus, $aSslInfos['CN'], $aSslInfos['issuer'], $aSslInfos['validto'].' ('.$iDaysleft.' d)')
        )
        . '</ul><div style="clear: both;"></div>'
        . $this->_getSimpleHtmlTable($aTbl, 1)
               
        . '<h3>' . $this->lB('sslcheck.raw') . '</h3>'
        . '<p>'.$this->lB('sslcheck.raw.hint').'</p>'
        . $oRenderer->renderToggledContent($this->lB('sslcheck.raw.openclose'),'<pre>'.json_encode($aSslInfosAll, JSON_PRETTY_PRINT).'</pre>', false)
        ;

        // ------------------------------------------------------------
        // scan http ressources
        // ------------------------------------------------------------
        
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
            
            $sReturn.= '<h3 id="h3nonhttps">' . sprintf($this->lB('sslcheck.nonhttps'), $iNonHttpsNoLink) . '</h3>'
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
                            $bShowAll ? '' : $this->_getQs(array('showall'=>1))
                    )
                    .$oRenderer->renderTile(
                            $iNonHttpsNoLink ? 'warning' : 'ok', 
                            $this->lB('sslcheck.nonhttpscountNolink'),
                            $iNonHttpsNoLink,
                            $iNonHttpsNoLink ? (floor($iNonHttpsNoLink/$iRessourcesCount*1000)/10).'%' : '',
                            $bShowAll ? $this->_getQs(array('showall'=>0)) : ''
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
            .$this->_getMessageBox(
                sprintf($this->lB('status.emptyindex'), $this->_sTab),
                'warning'
            )
            ;
        }
}
return $sReturn;
