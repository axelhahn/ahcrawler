<?php
/**
 * page analysis :: search for an url
 */
$sReturn='';
$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');
$sQuery = $this->_getRequestParam('query');
$bRedirect = $this->_getRequestParam('redirect');

$aProfiles=$this->_getProfiles();
$sReturn.= '<h3>' . $this->lB('ressources.searchurl-head') . '</h3>'
        . '<div div class="actionbox">'
            . $this->lB('ressources.searchurl-hint').'<br><br>'
            . '<form action="" method="get" class="pure-form">'
            . '<input type="hidden" name="page" value="checkurl">'
            . '<input type="hidden" name="siteid" value="' . $this->_sTab . '">'
            . '<label>' . $this->lB('ressources.searchurl') . '</label> '
            . '<input type="text" name="query" value="' . htmlentities($sQuery) . '" required="required" size="80" placeholder="">'
            . ' '
            // . $sSelect
            . '<button class="pure-button button-success">' . $this->_getIcon('button.search') . $this->lB('button.search') . '</button>'
            . '</form>'
        . '</div>';

$oRenderer=new ressourcesrenderer($this->_sTab);
if ($sQuery){
    $oRessources=new ressources($this->_sTab);

    // $aData=$oRessources->getRessources('*', array('url'=>$sQuery), array('url'=>'ASC'));
    $aData=$oRessources->getRessourceDetailsByUrl($sQuery);

    if ($aData && count($aData)){
        if($bRedirect && count($aData)===1){
            $sUrl='?page=ressourcedetail&id=' . $aData[0]['id'] . '&siteid='.$aData[0]['siteid'];
            header('location: '.$sUrl);
        }
            
        // TODO lang text
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
                
                $sReturn.=''
                        // TODO
                        // . '<pre>'.print_r($aItem, 1).'</pre>' 
                        // if $this->_sTab === "all"
                        // $aItem['siteid'] --> project name
                        . ($this->_sTab==='all'
                            ? $this->_getIcon('project').$aProfiles[$aItem['siteid']]
                            : ''
                        )
                        // . $oRenderer->renderReportForRessource($aItem, false, false)
                        . $oRenderer->renderRessourceItemAsLine($aItem, true).'<br>'
                        ;
            }
        } else {
            $sReturn.='<br><p>'.$this->lB('ressources.itemsnone').'</p>';                    
        }
    }
}
    
return $sReturn;
