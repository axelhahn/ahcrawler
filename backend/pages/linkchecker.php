<?php
/**
 * page analysis :: link checker
 */
$sReturn = '';
$sTilesOnTop='';
$sResResult='';

$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');

$oRessources=new ressources($this->_sTab);
$oRenderer=new ressourcesrenderer($this->_sTab);

$aCountByStatuscode=$this->_getStatusinfos(array('_global','linkchecker'));


$iRessourcesCount=$aCountByStatuscode['_global']['ressources']['value'];
if (!$iRessourcesCount) {
    $iPagesCount=$aCountByStatuscode['_global']['pages']['value'];
    $sReturn.='<h3>'.$this->lB("error.not-enough-data").'</h3>';
    if (!$iPagesCount) {
        $sReturn.=$oRenderer->renderMessagebox(sprintf($this->lB('status.emptyindex'), $this->_sTab), 'warning');
    }
    return $sReturn.$oRenderer->renderMessagebox(sprintf($this->lB('ressources.empty'), $this->_sTab), 'warning');
}

// crawling of ressources is in progress?
if(!isset($aCountByStatuscode['linkchecker'])){
    return $sReturn
        .'<h3>'.$this->lB("error.not-enough-data").'</h3>'
        .$oRenderer->renderMessagebox(sprintf($this->lB('ressources.crawler-not-finished-yet'), $this->_sTab), 'warning');
}

$iExternal=$this->oDB->count('ressources',array('siteid'=>$this->_sTab,'isExternalRedirect'=>'1'));
if($iExternal){
    $aChartItems[]=array(
        'label'=>$this->lB('linkchecker.found-http-external').': '.$iExternal,
        'value'=>$iExternal,
        'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-warning\')',
        //'legend'=>$this->lB('linkchecker.found-http-external-hint'),
    );
}


foreach ($aCountByStatuscode['linkchecker'] as $sSection=>$aData){
    $sLegende='';
    $aChartItemsOfSection=array();
    $sBoxes='';
    $iCodeCount=0;

    if($aData['value']){

        // --- pie chart 
        $aChartItems[]=array(
            'label'=>$this->lB('linkchecker.found-http-'.$sSection).': '.$aData['value'],
            'value'=>$aData['value'],
            'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-'.$sSection.'\')',
            // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': ',
        );

        if ($aData['value']){
            $sResResult.=''
                    . '<h3 id="h3-'.$sSection.'">'.$this->lB('linkchecker.found-http-'.$sSection) . ' (' .$aData['value'].')</h3>'
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
                $sLegende.($sLegende ? '<br>' : '')
                        .'<strong>'.$this->lB('linkchecker.found-http-external').'</strong><br>'
                        . $this->lB('linkchecker.found-http-external-hint')
                        . '<br><em>'.$this->lB('httpcode.todo') .'</em>: '. $this->lB('linkchecker.found-http-external-todo')
                        .'<br>'
                        ;
            }


            foreach ($aData['_data'] as $iHttp_code=>$iCount){
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
                        '?page=ressources&siteid='.$this->_sTab.'&showreport=1&showtable=0&filteritem[]=http_code&filtervalue[]='.$iHttp_code.'#restable'
                    )
                    ;

                $sLegende.= ($sLegende ? '<br>' : '')
                        .'<strong>'.$this->lB('db-ressources.http_code').'</strong> '
                        . $oRenderer->renderValue('http_code', $iHttp_code)
                        // . '<strong>'.$iHttp_code.'</strong> '
                        . ' '
                        . '<strong>'.$shttpStatusLabel.'</strong><br>'
                        . $shttpStatusDescr
                        . ($shttpStatusTodo ? "<br><em>".$this->lB('httpcode.todo') ."</em>: ". $shttpStatusTodo : '')
                        .'<br>'
                        ;
            }
        }
        $sResResult.=''
            . '<div class="floatright">'
                . $this->_getChart(array(
                    'type'=>'pie',
                    'data'=>$aChartItemsOfSection
                ))
            .'</div>'
                . $sBoxes.'</ul>'
            . ($sLegende 
                ? '<div style="clear: left;"></div>'.$this->_getHtmlLegend($sLegende)
                : ''
            )
            . '<div style="clear: both;"></div>'
            ;
    }

}
$sReturn.='<h3>'.$this->lB("linkchecker.check-links").'</h3>'
        . $oRenderer->renderRessourceStatus() 
        . $oRenderer->renderTileBar(
                $sTilesOnTop
                . $this->_getTilesOfAPage()

            ).'<div style="clear: both;"></div>'
            . $this->_getChart(array(
                'type'=>'pie',
                'data'=>$aChartItems
            ))

        . $sResResult
        ;


return $sReturn;