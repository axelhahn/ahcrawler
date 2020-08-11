<?php
/**
 * HOME
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sHtml='';
$sTable='';
$sTiles='';

$aOptions = $this->_loadConfigfile();
$bShowProject=true;

// echo '<pre>'.print_r($aOptions,1 ).'</pre>';
if(!$this->_configExists() ){
    // ------------------------------------------------------------
    // INITIAL SETUP PART ONE
    // program settings
    // ------------------------------------------------------------
    header('Location: ?page=installer');
    die();

} else {
    /*
     
    v0.111 - default settings are saved in the installer now.

    if (!isset($aOptions['options']['searchindex'])){
        // ------------------------------------------------------------
        // INITIAL SETUP PART TWO
        // program settings
        // ------------------------------------------------------------
        $sHtml.='<h3>' . $this->lB('home.welcome') . '</h3>';
        $bShowProject=false;
        $oRenderer=new ressourcesrenderer($this->_sTab);
        $sHtml.=''
            .$this->lB('home.nosavedsettings').'<br><br>'
            .$oRenderer->oHtml->getTag('a',array(
                'href' => '?page=setup',
                'class' => 'pure-button button-secondary',
                'title' => $this->lB('nav.setup.hint'),
                'label' => $this->_getIcon('setup').$this->lB('nav.setup.label'),
            ))
            .'<br><br><br>'
            ;
    } 
    */
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
            . $oRenderer->oHtml->getTag('a',array(
                    'href'=>'?page=profiles&siteid=add',
                    'class'=>'pure-button button-secondary',
                    'title' => $this->lB('nav.profiles.hint'),
                    'label' => $this->_getIcon('profiles').$this->lB('nav.profiles.label'),
                    ))    
            ;
    }
    if ($bShowProject) {
        if (array_search($iProfileId, $aProfiles)===false){
            $iPagesTotal=$this->getRecordCount('pages');
            $iResTotal=$this->getRecordCount('ressources');
            $iSearchesTotal=$this->getRecordCount('searches');

            $sTiles.=''
                    . $oRenderer->renderTile('', $this->lB('nav.profiles.label'), count($aProfiles), '', '')
                    . $oRenderer->renderTile('', $this->lB('nav.search.label'), $iPagesTotal, '', '')
                    . $oRenderer->renderTile('', $this->lB('nav.ressources.label'), $iResTotal, '', '')
                    . $oRenderer->renderTile('', $this->lB('nav.searches.label'), $iSearchesTotal, '', '')
                    ;
            /*
            // ------------------------------------------------------------
            // OVERVIEW OVER ALL PROJECTS
            // ------------------------------------------------------------        
            $aTable=array();    
            $aTable[]=array(
                $this->lB('nav.profiles.label'),
                // '',
                $this->lB('nav.search.label'),
                $this->lB('nav.ressources.label'),
                $this->lB('nav.searches.label'),
            );
            foreach($aProfiles as $iProfileId){
                $this->setSiteId($iProfileId);
                
                $iPages=$this->getRecordCount('pages', array('siteid'=>$iProfileId));
                $iRes=$this->getRecordCount('ressources', array('siteid'=>$iProfileId));
                $iSearches=$this->getRecordCount('searches', array('siteid'=>$iProfileId));
                $aTable[]=array(
                    '<strong>'.$this->aProfileSaved['label'].'</strong><br>'
                        . $this->aProfileSaved['description'].'<br><br>'
                        . $this->_getButton(array(
                                    'href'=>'?page=profiles&siteid='.$iProfileId,
                                    'popup'=>false,
                                    'class'=>'button-secondary',
                                    'label'=>'button.edit',
                                    ))
                    ,
                    $iPages
                            ? '<div class="tdcenter">'
                                . '<strong>'.$iPages.'</strong><br><br>'
                                . $this->getLastTsRecord('pages', array('siteid'=>$iProfileId)).'<br>'
                                . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('pages', array('siteid'=>$iProfileId))))).'<br>'
                                . $this->_getButton(array(
                                    'href'=>'?page=searchindexstatus&siteid='.$iProfileId,
                                    'popup'=>false,
                                    'class'=>'button-secondary',
                                    ))
                              .'</div>'
                            : '-'
                        ,

                        $iRes
                            ? '<div class="tdcenter">'
                                . '<strong>'.$iRes.'</strong><br><br>'
                                . $this->getLastTsRecord('ressources', array('siteid'=>$iProfileId)).'<br>'
                                . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('ressources', array('siteid'=>$iProfileId))))).'<br>'
                                . $this->_getButton(array(
                                    'href'=>'?page=ressources&siteid='.$iProfileId,
                                    'popup'=>false,
                                    'class'=>'button-secondary',
                                    ))
                              .'</div>'
                            : '-'
                        ,

                        $iSearches
                            ? '<div class="tdcenter">'
                                . '<strong>'.$iSearches.'</strong><br><br>'
                                .$this->getLastTsRecord('searches', array('siteid'=>$iProfileId)).'<br>'
                                . $oRenderer->hrAge(date('U', strtotime($this->getLastTsRecord('searches', array('siteid'=>$iProfileId))))).'<br>'
                                . $this->_getButton(array(
                                    'href'=>'?page=searches&siteid='.$iProfileId,
                                    'popup'=>false,
                                    'class'=>'button-secondary',
                                    // 'label'=>'searches',
                                    ))
                              .'</div>'
                            : '-'
                        ,
                );
                $sTable='<br><p>' . $this->lB('home.status.hint') . '</p>'
                        . $this->_getSimpleHtmlTable($aTable, true)
                        ;
            }
            */
            $sHtml.=''
                    .$oRenderer->renderTileBar($sTiles).'<div style="clear: both;"></div>'
                    .$this->_getNavi2($this->_getProfiles(), false, '')
                    .$sTable
                    ;
        } else if ($bShowProject) {
            // ------------------------------------------------------------
            // STATUS OF A SINGLE PROJECT
            // ------------------------------------------------------------        
            $this->setSiteId($iProfileId);

            $aGlobal=$this->_getStatusinfos(array('_global'));
            // echo '<pre>'.print_r($aGlobal, 1).'</pre>';
            
            $sTiles2='';
            foreach($aGlobal['_global'] as $sMyType=>$aData){
                if(isset($aData['value']) && $aData['value']){
                    $sTiles2.=$oRenderer->renderTile(
                        ''
                        , $aData['thead']
                        , $aData['value']
                        , $aData['tfoot']
                        , '?page='.$aData['page'].'&siteid='.$iProfileId
                  );
                }
            }
            
            // ----- collect hints

            
            // ----- render output
            // echo '<pre>'.print_r($this->_getAnalyseData(array('_global','htmlchecks')), 1).'</pre>';
            // echo '<pre>'.print_r($this->_getAnalyseData(), 1).'</pre>';
            // echo '<pre>'.print_r($this->_getStatusinfos(), 1).'</pre>';
            
            // echo '<pre>'.print_r($this->_getStatusInfoByLevel('error'), 1).'</pre>';
            // echo '<pre>'.print_r($this->_getStatusInfoByLevel('warning'), 1).'</pre>';
            
            
            $sHints='';
            
            
            // foreach (array('error', 'warning', 'ok', 'info') as $sMyKey) {
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
            foreach (array('error', 'warning') as $sMyKey) {
                // foreach($aHints[$sMyKey] as $aMsg){
                foreach($this->_getStatusInfoByLevel($sMyKey) as $aMsg) {
                    if(!$aMsg['target'] || $aMsg['target']==='_global' && $sMyKey=='warning'){
                        continue;
                    }
                    if($aMsg['target']!==$sLastTarget){
                        $sSpacer=($aMsg['target']==='_global') ? '0' : '5';
                        $sHints.=($sHints ? '<br><hr>' : '') 
                                . ($aMsg['target']==='_global' ? '' : $this->_getLink2Navitem($aMsg['target']).'<br>');
                        $sLastTarget=$aMsg['target'];
                    }
                    $sHints.='<div style="margin-left: '.$sSpacer.'em;">'.$oRenderer->renderMessagebox($aMsg['message'], $sMyKey).'</div>';
                }        
            }
            
            
            // $urlImage = '../image-'.$iProfileId.'.jpg';
            $urlImage = false;

            $sHtml.=''
                // .$oRenderer->renderTileBar($sTiles).'<div style="clear: both;"></div>'
                .$this->_getNavi2($this->_getProfiles(), false, '')
                    
                // profile 
                .$oRenderer->renderContextbox(
                        ($this->aProfileSaved['description'] ? '<strong>'.$this->aProfileSaved['description'].'</strong><hr>' : '')
                    
                        // start urls
                        .'<p>'.$this->_getIcon('checkurl'). $this->lB('home.starturls').'</p>'
                        .'<ul><li>'.implode('</li><li>',$this->aProfileSaved['searchindex']['urls2crawl']).'</li></ul>'
                        
                        // count of blacklisted items
                        .(isset($this->aProfileSaved['ressources']['blacklist']) && count($this->aProfileSaved['ressources']['blacklist'])
                            ? '<hr>'.sprintf($this->lB('home.blacklisted'), '<strong>'. count($this->aProfileSaved['ressources']['blacklist']).'</strong>') . '<br><br>'
                            : ''
                        )
                        . $this->_getLink2Navitem('profiles')
                        ,
                    
                        $this->lB('context.infos')
                        )
                .'<h3>'.$this->aProfileSaved['label'].'</h3>'
                

                    
                    
                // . '<br><br>'
                . ($sTiles2 ? $oRenderer->renderTileBar($sTiles2) : '')
                . ($urlImage ? '<img src="'.$urlImage.'" class="websnapshot">' : '')
                .'<div style="clear: both;"></div>'

                // .'<h3>'.$this->lB('home.status').'</h3>'
                // .$oRenderer->renderTileBar($sTiles2).'<div style="clear: both;"></div>'

                .'<h3>'.$this->lB('home.hints').'</h3>'
                
                .($sHints
                     ? $sHints
                     : $oRenderer->renderMessagebox($this->lB('home.hints.nothing-was-found'), 'ok')
                 )
                ;


                // . '<h4>'.$this->aProfileSaved['label'].'</h4>'
                //.$sTable
            // $sHtml.= '<pre>'.print_r($this->aProfileSaved, 1).'</pre>';
        }

    }
}
return $sHtml;