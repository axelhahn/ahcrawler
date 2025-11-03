<?php
/**
 * page resource detail
 */
if (!$this->_requiresPermission("viewer", $this->_sTab)){
    return include __DIR__ . '/error403.php';
}

$sReturn='';
$iRessourceId = (int)$this->_getRequestParam('id');

$oRenderer=new ressourcesrenderer($this->_sTab);

// add profiles navigation
$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '');

// $aData=$oRessources->getRessources('*', ['url'=>$sQuery], ['url'=>'ASC']);
$oRessources=new ressources($this->_sTab);
$aData=$oRessources->getRessourceDetails($iRessourceId);

// echo '<pre>'.print_r($aData, 1).'</pre>' . count($aData);
if (count($aData)){
    foreach($aData as $aItem){
/*
        $iPageId = $this->getIdByUrl($aRessourceItem['url'], 'pages');

        $sLink2Searchindex = $aRessourceItem['isSource'] ? '?page=searchindexstatus&id=' . $iPageId . '&siteid=' . $aRessourceItem['siteid'] : false;

        $sReturn .= ''
            .'<div class="divRessource">'
            . '<div class="divRessourceHead">'
            
            . '<strong>' . str_replace('&', '&shy;&', htmlentities($this->_renderArrayValue('url', $aRessourceItem))) . '</strong>'
            . ' '
            . ($sLink2Searchindex
                ? '&nbsp; <a href="' . $sLink2Searchindex . '" class="pure-button"'
                . ' title="' . $this->lB('ressources.link-to-searchindex') . '"'
                . '>'
                . $this->_getIcon('switch-search-res')
                . '</a>'
                : ''
            )
*/
        $iPageId = $oRenderer->getIdByUrl($aItem['url'], 'pages');
        $sLink2Searchindex = '?page=searchindexstatus&id=' . $iPageId . '&siteid=' . $aItem['siteid'];

        $sReturn.=''
            .'<div class="contextbar">'
                . $oRenderer->renderContextbox(
                    ''
                    .($iPageId>0
                        ? ''
                            .'<a href="?page=searchindexstatus&id=' . $iPageId . '&siteid='.$this->iSiteId.'" class="pure-button"'
                            . ' title="'.$this->lB('ressources.link-to-searchindex').'"'
                            . '>'.$oRenderer->_getIcon('switch-search-res').$this->lB('ressources.link-to-searchindex').'</a><br><br>' 
                        : ''
                    )
                    . '<a href="' . $aItem['url'] . '" target="_blank" class="pure-button" title="'.$this->lB('ressources.link-to-url').'">'. $oRenderer->_getIcon('link-to-url').$this->lB('ressources.link-to-url').'</a><br><br>'
                    , $this->lB('context.links')
                    , false
                )
                . $oRenderer->renderContextbox(
                        $oRenderer->renderBookmarklet('details')
                        , $this->lB('bookmarklet.details.head')
                        , false
                    )
            .'</div>'
            . '<h3>'.$this->lB('ressources.ressourceitemfull').'</h3>'
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

            . $this->_getLink2Navitem('linkchecker')
            .' '
            . $this->_getLink2Navitem('ressources')
            .'<br><br><hr><br>'
            .'<div class="actionbox">'

            .'<form action="" method="get" class="pure-form">'
            . '<input type="hidden" name="page" value="ressourcedetail">'
            . '<input type="hidden" name="siteid" value="' . $this->_sTab . '">'
            // . '<label>' . $this->lB('searches.url') . '</label> '
            . '<label>' . $this->lB('ressources.searchressourceid') . '</label> '
            . '<input type="text" name="id" value="' . $iRessourceId . '" required="required" size="5" placeholder="ID">'
            . ' '
            // . $sSelect
            . '<button class="pure-button button-success">' . $this->_getIcon('button.search') . $this->lB('button.search') . '</button>'
            . '</form></div><br>';
    if ($iRessourceId){
        $sReturn.='<p>'.$this->lB('ressources.itemsnone').'</p>';
    }
}
return $sReturn;

