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
    $aBoxes=array('todo'=>$aTmpItm, 'errors'=>$aTmpItm,'warnings'=>$aTmpItm, 'ok'=>$aTmpItm);

    // echo '<pre>$aCountByStatuscode = '.print_r($aCountByStatuscode,1).'</pre>';
    foreach ($aCountByStatuscode as $aStatusItem){
        $iHttp_code=$aStatusItem['http_code'];
        $iCount=$aStatusItem['count'];
        $oHttp=new httpstatus();
        $oHttp->setHttpcode($iHttp_code);

        if ($oHttp->isError()){
           $aBoxes['errors']['status'][$iHttp_code] = $iCount;
           $aBoxes['errors']['total']+=$iCount;
        }
        if ($oHttp->isRedirect()){
           $aBoxes['warnings']['status'][$iHttp_code] = $iCount;
           $aBoxes['warnings']['total']+=$iCount;
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
    $sBar='';
    $sResResult='';

    $iExternal=$this->oDB->count('ressources',array('siteid'=>$this->_sTab,'isExternalRedirect'=>'1'));

    if($iExternal){
        $aChartItems[]=array(
            'label'=>$this->lB('linkchecker.found-http-external').': '.$iExternal,
            'value'=>$iExternal,
            'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-warnings\')',
            //'legend'=>$this->lB('linkchecker.found-http-external-hint'),
        );
    }

    foreach (array_keys($aBoxes) as $sSection){
        if(!$aBoxes[$sSection]['total']){
            continue;
        }
        $aChartItems[]=array(
            'label'=>$this->lB('linkchecker.found-http-'.$sSection).': '.$aBoxes[$sSection]['total'],
            'value'=>$aBoxes[$sSection]['total'],
            'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-'.$sSection.'\')',
            // 'legend'=>$this->lB('linkchecker.found-http-'.$sSection).': ',
        );
        $sLegende='';

        if (array_key_exists($sSection, $aBoxes)){
            $aChartItemsOfSection=array();
            $sBoxes='';
            $iCodeCount=0;
            if (count($aBoxes[$sSection])){
                $sResResult.=''
                        . '<h3>'.sprintf($this->lB('linkchecker.found-http-'.$sSection), $aBoxes[$sSection]['total']).'</h3>'
                        . '<p>'.$this->lB('linkchecker.found-http-'.$sSection.'-hint').'</p>'
                        . '<ul class="tiles '.$sSection.'">';


                if($sSection==='warnings' && $iExternal){
                    $aChartItemsOfSection[]=array(
                        'label'=>$this->lB('linkchecker.found-http-external'),
                        'value'=>$iExternal,
                        'color'=>'getStyleRuleValue(\'color\', \'.chartcolor-'.($iCodeCount % 5 + 1).'\')',
                        'legend'=>$iExternal.' x '.$this->lB('linkchecker.found-http-external'),
                    );
                    $sBoxes.='<li>'
                            . '<a href="#" class="tile" title="'.$this->lB('linkchecker.found-http-external-hint').'"'
                            . ' onclick="return false;"'
                            . '>'
                            . $this->lB('linkchecker.found-http-external').' '
                            . '<br><br>'
                            . '<strong>'
                                .$iExternal
                            .'</strong><br>'
                            .(floor($iExternal/$iRessourcesCount*1000)/10).'%'
                            . '</a>'
                        . '</li>';
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

                    $sBar.='<div class="bar-'.$sSection.'" style="width: '.($iCount/$iRessourcesCount*100 - 3).'%; float: left;" '
                            . 'title="'.$iCount.' x '.$this->lB('db-ressources.http_code').' '.$iHttp_code.'">'.$iCount.'</div>';

                    $sBoxes.='<li>'
                            .'<a href="?page=ressources&showreport=1&showtable=0&filteritem[]=http_code&filtervalue[]='.$iHttp_code.'#restable" class="tile" '
                            . 'title="'.$iHttp_code.': '.$shttpStatusDescr.($shttpStatusTodo ? "&#13;&#13;".$this->lB('httpcode.todo') .":&#13;". $shttpStatusTodo : '').'">'
                            . $this->lB('db-ressources.http_code').' '
                            . $oRenderer->renderValue('http_code', $iHttp_code).'<br><br>'
                            . '<strong>'
                                .$iCount
                            .'</strong><br>'
                            .(floor($iCount/$iRessourcesCount*1000)/10).'%'
                            //. $shttpStatusLabel.'<br>'
                            . '</a>'
                        . '</li>';

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
                . $this->_getChart(array(
                    'type'=>'pie',
                    'data'=>$aChartItems
                ))

            // . '<div class="bar">'.$sBar.'&nbsp;</div><br><br><br><br><br>'
            . $sResResult
            ;

}


return $sReturn;