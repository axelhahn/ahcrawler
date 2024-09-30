<?php
/**
 * HOME
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sHtml='';
$sTable='';
$sTiles='';

$aOptions = $this->getEffectiveOptions();
$bShowProject=true;

if(!$this->_configExists() ){
    // ------------------------------------------------------------
    // INITIAL SETUP PART ONE
    // program settings
    // ------------------------------------------------------------
    header('Location: ?page=installer');
    die();

} else {
    $aProfiles=$this->getProfileIds();
    $iProfileId=$this->_getTab();
    if(!$aProfiles || !count($aProfiles)){
        // ------------------------------------------------------------
        // INITIAL SETUP PART THREE
        // setup a website profile
        // ------------------------------------------------------------
        $sHtml.=$sHtml ? '' : '<h3>' . $this->lB('home.welcome') . '</h3>' ;
        $bShowProject=false;
        $sHtml.=''
            // . $oRenderer->renderTile('', $this->lB('nav.profiles.label'), 0, '', '')
            . $this->lB('home.noprojectyet').'<br><br>'
            . $oRenderer->oHtml->getTag('a',[
                    'href'=>'?page=profiles&siteid=add',
                    'class'=>'pure-button button-secondary',
                    'title' => $this->lB('nav.profiles.hint'),
                    'label' => $this->_getIcon('profiles').$this->lB('nav.profiles.label'),
                    ])
            ;
    }
    if ($bShowProject) {
        
            // ------------------------------------------------------------
            // STATUS OF A SINGLE PROJECT
            // ------------------------------------------------------------        
            $this->setSiteId($iProfileId);

            $aGlobal=$this->_getStatusinfos(['_global']);
            // echo '<pre>aGlobal = '.print_r($aGlobal, 1).'</pre>';
            
            $sTiles2='';
            foreach($aGlobal['_global'] as $sMyType=>$aData){
                if(isset($aData['value']) && $aData['value']){
                    $sTiles2.=$oRenderer->renderTile(
                        $aData['status']
                        , $aData['thead']
                        , $aData['value']
                        , $aData['tfoot']
                        , '?page='.$aData['page'].'&siteid='.$iProfileId
                  );
                }
            }
                        
            // ----------------------------------------------------------------------
            // collect hints for improvements
            
            $sHints='';            
            $sHints.=($aGlobal['_global']['ressources']['value']==1
                    ? $oRenderer->renderMessagebox($this->lB('ressources.only-one'), 'warning').'<br>'
                    : ''
            );
            $sHints.=(count($this->_getStatusInfoByLevel('error'))
                        ? ''
                        : (count($this->_getStatusInfoByLevel('warning')) 
                            ? $oRenderer->renderMessagebox($this->lB('home.hints.no-critical-was-found'), 'ok')
                            : ''
                        )
                    )
                    ;

            // ----- by level
            $sLastTarget='';
            foreach (['error', 'warning'] as $sMyKey) {
                // foreach($aHints[$sMyKey] as $aMsg){
                foreach($this->_getStatusInfoByLevel($sMyKey) as $aMsg) {
                    if(!$aMsg['target'] || $aMsg['target']==='_global' && $sMyKey=='warning'){
                        continue;
                    }
                    if($aMsg['target']!==$sLastTarget){
                        $sSpacer=($aMsg['target']==='_global') ? '0' : '5';
                        $sHints.=($sHints ? '<br><hr>' : '') 
                                . ($aMsg['target']==='_global' 
                                    ? '' 
                                    : ''
                                        // . '<h4>'.$this->lB('nav.'.$aMsg['target'].'.label').'</h4>'
                                        . $this->_getLink2Navitem($aMsg['target']).'<br>'
                                        . '<p>'.$this->lB('nav.'.$aMsg['target'].'.hint').'</p>'
                                );
                        $sLastTarget=$aMsg['target'];
                    }
                    $sHints.='<div style="margin-left: '.$sSpacer.'em;">'
                            .$oRenderer->renderMessagebox($aMsg['message'], $sMyKey)
                        .'</div>';
                }        
            }
            
            // ----------------------------------------------------------------------
            $sStartUrls='';
            foreach($this->aProfileSaved['searchindex']['urls2crawl'] as $sUrl){
                $sStartUrls.='<li><a href="'.$sUrl.'" target="_blank">'.$sUrl.'</a></li>';
            }
            $sHtml.=''
                .$this->_getNavi2($this->_getProfiles(), false, '')
                    
                // ----- context box
                .$oRenderer->renderContextbox(
                        ($this->aProfileSaved['label'] ? '<strong>'.$this->aProfileSaved['label'].'</strong><br>' : '')
                        .($this->aProfileSaved['description'] ? '<em>'.$this->aProfileSaved['description'].'</em>' : '')
                        .'<hr>'
                        . $this->getProfileImage()

                        // start urls
                        .'<p>'.$this->_getIcon('checkurl'). $this->lB('home.starturls').'</p>'
                        .'<ul>'.$sStartUrls.'</ul>'
                        
                        // count of blacklisted items
                        .(isset($this->aProfileSaved['ressources']['blacklist']) && count($this->aProfileSaved['ressources']['blacklist'])
                            ? '<hr>'.sprintf($this->lB('home.denyentries'), '<strong>'. count($this->aProfileSaved['ressources']['blacklist']).'</strong>') . '<br><br>'
                            : ''
                        )
                        . $this->_getLink2Navitem('profiles')
                        . '<hr>'
                        . $oRenderer->renderIndexActions('reindex', 'searchindex', $this->_sTab)
                        ,
                    
                        $this->lB('context.infos')
                        )

                // ----- main info
                .'<h3>'.$this->aProfileSaved['label'].'</h3>'
                . ($sTiles2 ? $oRenderer->renderTileBar($sTiles2) : '')
                .'<div style="clear: left;"></div>'

                // ----- graph
                .($aGlobal['_global']['pages']['value']
                        ? ''
                        . '<div class="floatleft">'
                        . '<br>'
                        . $this->lB('home.hints.loadingtime-all-pages').'<br><br>'
                        . $this->_getChartOfRange(
                            'select time
                            from pages 
                            where siteid='.$this->_sTab.' and errorcount=0
                            order by time desc',
                            'time',
                            $aOptions['analysis']['MaxLoadtime']
                        )
                        . '</div>'
                        . '<div style="clear: left;"></div>'
                        :''
                )
                // ----- hints for improvements
                .($sHints
                     ?  '<h3>'.$this->lB('home.hints').'</h3>'
                        . $this->_getHistoryCounter(['TotalWarnings','TotalErrors'])
                        .$sHints
                     : ''
                )
                ;
    }
}
return $sHtml;