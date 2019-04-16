<?php
/**
 * HOME
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sHtml='';
$sTable='';
$sTiles='';


if(!$this->_configExists() || !$this->oDB){
    
    // ------------------------------------------------------------
    // INITIAL SETUP PART ONE
    // program settings
    // ------------------------------------------------------------
    $oRenderer=new ressourcesrenderer($this->_sTab);
    $sHtml.=''
    . '<h3>' . $this->lB('home.welcome') . '</h3>'
    .$this->lB('home.welcome.introtext').'<br><br>'
    .$oRenderer->oHtml->getTag('a',array(
        'href' => '?page=setup',
        'class' => 'pure-button button-secondary',
        'title' => $this->lB('nav.setup.hint'),
        'label' => $this->_getIcon('setup').$this->lB('nav.setup.label'),
    ))
    ;
} else {

    $aProfiles=$this->getProfileIds();
    $aTable=array();
    if(!$aProfiles || !count($aProfiles)){
        // ------------------------------------------------------------
        // INITIAL SETUP PART TWO
        // setup a website profile
        // ------------------------------------------------------------
        $sTiles.=''
            . '<h3>' . $this->lB('home.welcome') . '</h3>'
            // . $oRenderer->renderTile('', $this->lB('nav.profiles.label'), 0, '', '')
            . $this->lB('home.noprojectyet').'<br><br>'
            . $oRenderer->oHtml->getTag('a',array(
                        'href'=>'?page=profiles&tab=add',
                        'class'=>'pure-button button-secondary',
                        'title' => $this->lB('nav.profiles.hint'),
                        'label' => $this->_getIcon('profiles').$this->lB('nav.profiles.label'),
                        ))
                ;
    } else {
        // ------------------------------------------------------------
        // DEFAULT INTRO PAGE
        // ------------------------------------------------------------
        $iPagesTotal=$this->getRecordCount('pages');
        $iResTotal=$this->getRecordCount('ressources');
        $iSearchesTotal=$this->getRecordCount('searches');
        
        
        $sTiles.=''
                . $oRenderer->renderTile('', $this->lB('nav.profiles.label'), count($aProfiles), '', '')
                . $oRenderer->renderTile('', $this->lB('nav.search.label'), $iPagesTotal, '', '')
                . $oRenderer->renderTile('', $this->lB('nav.ressources.label'), $iResTotal, '', '')
                . $oRenderer->renderTile('', $this->lB('nav.searches.label'), $iSearchesTotal, '', '')
                ;
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
                                'href'=>'?page=profiles&tab='.$iProfileId,
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
                                'href'=>'?page=status&tab='.$iProfileId,
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
                                'href'=>'?page=ressources&tab='.$iProfileId,
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
                                'href'=>'?page=searches&tab='.$iProfileId,
                                'popup'=>false,
                                'class'=>'button-secondary',
                                // 'label'=>'searches',
                                ))
                          .'</div>'
                        : '-'
                    ,
            );
        }
        $sTable='<p>' . $this->lB('home.status.hint') . '</p>'
                . $this->_getSimpleHtmlTable($aTable, true)
                // . '<br><br>'
                // . $this->_renderChildItems($this->_aMenu)
                ;
    }

    $sHtml.=''
            .$oRenderer->renderTileBar($sTiles).'<div style="clear: both;"></div>'
            .$sTable
            ;
}
return $sHtml;