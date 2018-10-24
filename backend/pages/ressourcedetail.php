<?php
/**
 * page analysis
 */
$sReturn='';
$sReturn.=$this->_getNavi2($this->_getProfiles()).'<br>';
$iRessourceId = (int)$this->_getRequestParam('id');

$oRenderer=new ressourcesrenderer($this->_sTab);

// $aData=$oRessources->getRessources('*', array('url'=>$sQuery), array('url'=>'ASC'));
$oRessources=new ressources($this->_sTab);
$aData=$oRessources->getRessourceDetails($iRessourceId);

// echo '<pre>'.print_r($aData, 1).'</pre>' . count($aData);
if (count($aData)){
    foreach($aData as $aItem){
        $sReturn.='<h3>'.$this->lB('ressources.ressourceitemfull').'</h3>'
            .$oRenderer->renderRessourceItemFull($aItem);
        /*
        if ((int)$aItem['http_code']===200 && strpos($aItem['content_type'], 'html')>0){
            $oHtml=new analyzerHtml();
            $oHtml->fetchUrl($aItem['url']);
            $sReturn.='<h3>Live response of html analyzer</h3>'
                    . '<pre>'.print_r($oHtml->getReport(), 1).'</pre>';
        } else {
            // $sReturn.='skip live parsing<br>'.$aItem['http_code'] . ' - ' . $aItem['content_type'] . ' - ' . strpos($aItem['content_type'], 'html').'<br>';
        }
         * 
         */
    }
} else {
    $sReturn.= '<p>' . $this->lB('ressources.searchressourceid-hint') . '</p>'

            . '<a href="?page=linkchecker" class="pure-menu-linkwarnings"'
                . ' title="' . $this->lB('nav.linkchecker.hint') . '"'
                . '><i class="'.$this->_aIcons['menu']['linkchecker'].'"></i> ' 
                . $this->lB('nav.linkchecker.label') 
                .'</a>'
            .' | '
            . '<a href="?page=ressources" class="pure-menu-linkressources"'
                . ' title="' . $this->lB('nav.ressources.hint') . '"'
                . '><i class="'.$this->_aIcons['menu']['ressources'].'"></i> ' 
                . $this->lB('nav.ressources.label') 
                .'</a>'
            .'<br><br><hr><br>'

            .'<form action="" method="get" class="pure-form">'
            . '<input type="hidden" name="page" value="ressourcedetail">'
            . '<input type="hidden" name="siteid" value="' . $this->_sTab . '">'
            // . '<label>' . $this->lB('searches.url') . '</label> '
            . '<label>' . $this->lB('ressources.searchressourceid') . '</label>'
            . '<input type="text" name="id" value="' . $iRessourceId . '" required="required" size="5" placeholder="ID">'
            . ' '
            // . $sSelect
            . '<button class="pure-button button-success">' . $this->_getIcon('button.search') . $this->lB('button.search') . '</button>'
            . '</form><br><br>';
    if ($iRessourceId){
        $sReturn.='<p>'.$this->lB('ressources.itemsnone').'</p>';
    }
}
return $sReturn;

