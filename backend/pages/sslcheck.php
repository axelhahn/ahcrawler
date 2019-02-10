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
        
        $iRessourcesCount=$this->getRecordCount('ressources', array('siteid'=>$this->_sTab));
        
        if($iRessourcesCount){
            $sTableId='tbl-nonhttpsitems';
            $oRessources=new ressources();
            $aFields = array('id', 'url', 'http_code', 'ressourcetype', 'type', 'content_type');
            $aWhere=array('siteid' => $this->_sTab, 'url[~]'=>'http:%');

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
            // $sReturn.= $this->oDB->last().'<br>';
            
            $sReturn.= '<h3>' . sprintf($this->lB('sslcheck.nonhttps'), $iNonHttps) . '</h3>'
                .'<p>'.$this->lB('sslcheck.nonhttps.hint').'</p>'
                // .$this->_getHtmlchecksChart($iRessourcesCount, $iNonHttps)
                . $oRenderer->renderTileBar(
                    $oRenderer->renderTile('',            $this->lB('ressources.itemstotal'), $iRessourcesCount, '', '')
                    .$oRenderer->renderTile(
                            $iNonHttps ? 'warning' : 'ok', 
                            $this->lB('sslcheck.nonhttpscount'),
                            $iNonHttps,
                            $iNonHttps ? (floor($iNonHttps/$iRessourcesCount*1000)/10).'%' : ''
                ))
                . '<div style="clear: both;"></div>'
                .$this->_getHtmlchecksChart($iRessourcesCount, $iNonHttps)    
                ;
            
            if($iNonHttps){
                $aTable = array();
                $iReportCounter=0;

                $aRessourcelist = $oRessources->getRessources($aFields, $aWhere, array("url"=>"ASC"));
                
                // --- what to create: table or report list
                $bShowReport=$this->_getRequestParam('showreport');
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
                        $aTable[] = $aRow;
                    }
                    
                }
                $sReturn.=(count($aTable) 
                        ? $this->_getHtmlTable($aTable, "db-ressources.", $sTableId)
                            .'<script>$(document).ready( function () {$(\'#'.$sTableId.'\').DataTable();} );</script>'
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
