<?php
/**
 * page analysis :: link checker
 */
$sReturn = '';
$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');


$iRessourcesCount=$this->oDB->count('pages',array('siteid'=>$this->_sTab));        
if (!$iRessourcesCount) {
    return $sReturn.'<br>'.$this->_getMessageBox(sprintf($this->lB('ressources.empty'), $this->_sTab), 'warning');
}
$iRessourcesCount=$this->oDB->count('ressources',array('siteid'=>$this->_sTab));

if (!$iRessourcesCount) {
    return $sReturn.'<br>'.$this->_getMessageBox(sprintf($this->lB('ressources.empty'), $this->_sTab), 'warning');
}

$aPageFields=array('id', 'url', 'description', 'description');
$aWhere=array('siteid' => $this->_sTab);
$oRessources=new ressources($this->_sTab);
$oRenderer=new ressourcesrenderer($this->_sTab);



if ($iRessourcesCount){

    $aCountByStatuscode=$oRessources->getCountsOfRow(
        'ressources', 'http_code', 
        array(
            'siteid'=> $this->_sTab,
            'isExternalRedirect'=>'0',
        )
    );
    $aTmpItm=array('status'=>array(), 'total'=>0);
    $aBoxes=array('todo'=>$aTmpItm, 'error'=>$aTmpItm,'warning'=>$aTmpItm, 'ok'=>$aTmpItm);

    // echo '<pre>$aCountByStatuscode = '.print_r($aCountByStatuscode,1).'</pre>';
    foreach ($aCountByStatuscode as $aStatusItem){
        $iHttp_code=$aStatusItem['http_code'];
        $iCount=$aStatusItem['count'];
        $oHttp=new httpstatus();
        $oHttp->setHttpcode($iHttp_code);

        if ($oHttp->isError()){
           $aBoxes['error']['status'][$iHttp_code] = $iCount;
           $aBoxes['error']['total']+=$iCount;
        }
        if ($oHttp->isRedirect()){
           $aBoxes['warning']['status'][$iHttp_code] = $iCount;
           $aBoxes['warning']['total']+=$iCount;
        }
        if ($oHttp->isOperationOK()){
           $aBoxes['ok']['status'][$iHttp_code] = $iCount;
           $aBoxes['ok']['total']+=$iCount;
        }
        if ($oHttp->isTodo()){
           $aBoxes['todo']['status'][$iHttp_code] = $iCount;
           $aBoxes['todo']['total']+=$iCount;
        }
    }
    // echo '<pre>$aBoxes = '.print_r($aBoxes,1).'</pre>';
    $sResResult='';
    $aChartItems=array();

    $iExternal=$this->oDB->count('ressources',array('siteid'=>$this->_sTab,'isExternalRedirect'=>'1'));

    
    
    if($iExternal){
        $aChartItems[]=array(
            'label'=>$this->lB('linkchecker.found-http-external').': '.$iExternal,
            'value'=>$iExternal,
            'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-warning\')',
            //'legend'=>$this->lB('linkchecker.found-http-external-hint'),
        );
    }

    $sTilesOnTop='';
    
    foreach (array_keys($aBoxes) as $sSection){
        $sLegende='';
        $aChartItemsOfSection=array();
        $sBoxes='';
        $iCodeCount=0;
        
        // --- add a tile on top
        $sTileClass=(!$aBoxes[$sSection]['total'] || $sSection==='ok' ? 'ok' : $sSection );
        $sTilesOnTop.=$oRenderer->renderTile(
                $sTileClass, 
                $this->lB('linkchecker.found-http-'.$sSection), 
                $aBoxes[$sSection]['total'],
                (floor($aBoxes[$sSection]['total']/$iRessourcesCount*1000)/10).'%',
                ($aBoxes[$sSection]['total'] ? '#h3-'.$sSection : '')
        );
        
        if($aBoxes[$sSection]['total']){
            
            // --- pie chart 
            $aChartItems[]=array(
                'label'=>$this->lB('linkchecker.found-http-'.$sSection).': '.$aBoxes[$sSection]['total'],
                'value'=>$aBoxes[$sSection]['total'],
                'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-'.$sSection.'\')',
                // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': ',
            );
        
            if (count($aBoxes[$sSection])){
                $sResResult.=''
                        . '<h3 id="h3-'.$sSection.'">'.$this->lB('linkchecker.found-http-'.$sSection) . ' (' .$aBoxes[$sSection]['total'].')</h3>'
                        . '<p>'.$this->lB('linkchecker.found-http-'.$sSection.'-hint').'</p>'
                        . '<ul class="tiles '.$sSection.'">'
                        ;


                if($sSection==='warning' && $iExternal){
                    $aChartItemsOfSection[]=array(
                        'label'=>$this->lB('linkchecker.found-http-external'),
                        'value'=>$iExternal,
                        'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-'.($iCodeCount % 5 + 1).'\')',
                        'legend'=>$iExternal.' x '.$this->lB('linkchecker.found-http-external'),
                    );
                    $sBoxes.=$oRenderer->renderTile(
                            '',
                            $this->lB('linkchecker.found-http-external'),
                            $iExternal,
                            (floor($iExternal/$iRessourcesCount*1000)/10).'%'
                        )
                        ;
                    $iCodeCount++;
                    $sLegende.='<li>'
                            . '<strong>'.$this->lB('linkchecker.found-http-external').'</strong><br>'
                            . $this->lB('linkchecker.found-http-external-hint')
                            . '<br><em>'.$this->lB('httpcode.todo') .'</em>: '. $this->lB('linkchecker.found-http-external-todo')
                            .'<br><br>'
                            ;
                }


                foreach ($aBoxes[$sSection]['status'] as $iHttp_code=>$iCount){
                    $aChartItemsOfSection[]=array(
                        'label'=>$iHttp_code,
                        'value'=>$iCount,
                        'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-'.($iCodeCount % 5 + 1).'\')',
                        'legend'=>$iCount.' x '.$this->lB('db-ressources.http_code').' '.$iHttp_code,
                    );
                    $iCodeCount++;

                    $shttpStatusLabel=$this->lB('httpcode.'.$iHttp_code.'.label', 'httpcode.???.label');
                    $shttpStatusDescr=$this->lB('httpcode.'.$iHttp_code.'.descr', 'httpcode.???.descr');
                    $shttpStatusTodo=$this->lB('httpcode.'.$iHttp_code.'.todo', 'httpcode.???.todo');

                    $sBoxes.= $oRenderer->renderTile(
                            $sSection,
                            $this->lB('db-ressources.http_code').' '. $oRenderer->renderValue('http_code', $iHttp_code).'<br>',
                            $iCount,
                            (floor($iCount/$iRessourcesCount*1000)/10).'%',
                            '?page=ressources&showreport=1&showtable=0&filteritem[]=http_code&filtervalue[]='.$iHttp_code.'#restable'
                        )
                        ;

                    $sLegende.='<li>'
                            . $this->lB('db-ressources.http_code').' '
                            . $oRenderer->renderValue('http_code', $iHttp_code)
                            // . '<strong>'.$iHttp_code.'</strong> '
                            . ' '
                            . '<strong>'.$shttpStatusLabel.'</strong><br>'
                            . $shttpStatusDescr
                            . ($shttpStatusTodo ? "<br><em>".$this->lB('httpcode.todo') ."</em>: ". $shttpStatusTodo : '')
                            .'<br><br>'
                            ;
                }
            }
            $sResResult.=''
                . '<div style="float: right; margin: 0 0 1em 1em;">'
                    . $this->_getChart(array(
                        'type'=>'pie',
                        'data'=>$aChartItemsOfSection
                    ))
                .'</div>'
                    . $sBoxes.'</ul>'
                . ($sLegende ? '<div style="clear: left;"></div><p>'.$this->lB('linkchecker.legend').'</p><ul>'.$sLegende.'</ul>' : '')
                . '<div style="clear: both;"></div>'
                ;
        }

    }
    $sReturn.='<h3>'.$this->lB("linkchecker.check-links").'</h3>'
            . $oRenderer->renderRessourceStatus() 
            . $oRenderer->renderTileBar($sTilesOnTop).'<div style="clear: both;"></div>'
                . $this->_getChart(array(
                    'type'=>'pie',
                    'data'=>$aChartItems
                ))

            . $sResResult
            ;

}


return $sReturn;