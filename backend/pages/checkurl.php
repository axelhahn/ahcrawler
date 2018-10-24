<?php
/**
 * page analysis
 */
$sReturn='';
$sReturn.=$this->_getNavi2($this->_getProfiles()).'<br>';
$sQuery = $this->_getRequestParam('query');

$sReturn.= '<p>' . $this->lB('ressources.searchurl-hint') . '</p>'
        .'<form action="" method="get" class="pure-form">'
        . '<input type="hidden" name="page" value="checkurl">'
        . '<input type="hidden" name="siteid" value="' . $this->_sTab . '">'
        . '<label>' . $this->lB('ressources.searchurl') . '</label>'
        . '<input type="text" name="query" value="' . $sQuery . '" required="required" size="80" placeholder="https://www...">'
        . ' '
        // . $sSelect
        . '<button class="pure-button button-success">' . $this->_getIcon('button.search') . $this->lB('button.search') . '</button>'
        . '</form><br><br>';

if ($sQuery){
    $oRessources=new ressources($this->_sTab);
    $oRenderer=new ressourcesrenderer($this->_sTab);

    // $aData=$oRessources->getRessources('*', array('url'=>$sQuery), array('url'=>'ASC'));
    $aData=$oRessources->getRessourceDetailsByUrl($sQuery);

    if ($aData && count($aData)){
        $sReturn.='<h3>exact results '.count($aData).' </h3>'
                . $this->lB('ressources.total')
                . ': <strong>' . count($aData) . '</strong><br><br>'
                ;
        foreach($aData as $aItem){
            $sReturn.=$oRenderer->renderRessourceItemAsLine($aItem, true).'<br>';
        }
    } else {

        // search again ... but use "like" now
        $aDataLazy=$oRessources->getRessourceDetailsByUrl($sQuery, true);
        if ($aDataLazy && count($aDataLazy)){
            $sReturn.='<h3>lazy results</h3>'
                . $this->lB('ressources.itemstotal')
                . ': <strong>' . count($aDataLazy) . '</strong><br><br>'
                ;
            foreach($aDataLazy as $aItem){
                $sReturn.=$oRenderer->renderRessourceItemAsLine($aItem, true).'<br>';
            }
        } else {
            $sReturn.='<p>'.$this->lB('ressources.itemsnone').'</p>';                    
        }
    }


}
return $sReturn;
