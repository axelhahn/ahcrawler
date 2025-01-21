<?php
/**
 * SETTINGS
 */
$oRenderer=new ressourcesrenderer($this->_sTab);


/**
 * @var array  full config with app settings without profiles
 */
$aOptionDefaults = $this->_loadConfigfile();
// $aOptions = ['options'=>$this->aOptions];
$aOptions = ['options'=>$this->getEffectiveOptions()];
$sReturn='';

$sBtnBack='<br>'.$this->lB('setup.program.save.error.back').'<br><hr><br>'
    .$oRenderer->oHtml->getTag('button',[
    'href' => '#',
    'class' => 'pure-button button-secondary',
    'onclick' => 'history.back();',
    'title' => $this->lB('button.back.hint'),
    'label' => $this->lB('button.back'),
]);
$sBtnContinue='<hr><br>'
    .$oRenderer->oHtml->getTag('a',[
    'href' => '?'.$_SERVER['QUERY_STRING'],
    'class' => 'pure-button button-secondary',
    'title' => $this->lB('button.continue.hint'),
    'label' => $this->lB('button.continue'),
]);

$sPasswordDummy='12345678Dummy';

$iSizeInInput=72;
$iColsInTA=70;

$sPatternNumber='^[0-9]*';

/**
 * helper function to render setup checkboxes for menu in frontend and backend
 * @param string $sItem     menu key
 * @param array  $aOptions  array of options
 * @param string $sSubkey   one of menu|menu-public
 * @param string  $sPrefix   html code for spacing before checkbox
 * @return string
 */
function _renderCB(string $sItem, array $aOptions, string $sSubkey, string $sPrefix='', string $sLabel = ''){
    $sReturn='';
    $val=$aOptions['options'][$sSubkey][$sItem];
    $cbid='cbMenu'.$sSubkey.$sItem;
    $sLabel=$sLabel ?? $sItem;
    $sReturn.=''
        . '<label for="'.$cbid.'" class="align-left">'
        . $sPrefix
        . '<input type="checkbox" name="options['.$sSubkey.']['.$sItem.']" value="true" id="'.$cbid.'"'.($val ? ' checked="checked"' : '').'>'
        . ' '. $sLabel// . $this->lB('setup.section.backend.debug.off')
        . '</label><br>'
    ;
    return $sReturn;
}

// ----------------------------------------------------------------------
// handle POST DATA
// ----------------------------------------------------------------------

if(isset($_POST['action'])){
    
    if(!isset($_POST['options']['auth']['password']) && isset($aOptions['options']['auth']['password'])) {
        $_POST['options']['auth']['password']=$aOptions['options']['auth']['password'];
    }

    // $sReturn.='DEBUG: <pre>POST '.print_r($_POST, 1).'</pre>';
    // $sReturn.='DEBUG: <pre>options '.print_r($aOptions, 1).'</pre>';

    switch($_POST['action']){
        // set all aoptions
        case 'setoptions':
            
            // --------------------------------------------------
            // check user / password 
            // --------------------------------------------------
            if(
                // if the username wants to be changed from an existing to a new user
                (
                    $_POST['options']['auth']['user'] 
                    && (isset($aOptions['options']['auth']['user']))
                    && $_POST['options']['auth']['user']!==$aOptions['options']['auth']['user']
                )
                // if the new username is empty but existing is set
                || (
                    !$_POST['options']['auth']['user'] 
                    && (isset($aOptions['options']['auth']['user']))
                    && ($aOptions['options']['auth']['user'])
                )
            ){
                if(
                    (
                        $_POST['currentpassword'] 
                        // && md5($_POST['currentpassword'])===$aOptions['options']['auth']['password']
                        && password_verify($_POST['currentpassword'], $aOptions['options']['auth']['password'])
                    ) 
                    // || (!$_POST['currentpassword'])
                    // && $_POST['options']['pw1']!==$aOptions['options']['auth']['password']
                ){
                    // $sReturn.="ok, a new user wants to be set and the old pw was correct<br>";
                    $this->_setUser('');
                } else {
                    // $sReturn.='DEBUG: '.$_POST['currentpassword'] . ' - md5: ' . md5($_POST['currentpassword']).'<br>';
                    // $sReturn.='DEBUG: vs current '.$aOptions['options']['auth']['password'].'<br>';
                    $sReturn.=$oRenderer->renderMessagebox($this->lB('setup.program.save.error.wrong-current-pw'), 'error')
                        .$sBtnBack
                        ;
                    return $sReturn;
                }
            }
            
            // test if a new password was given
            if(
                $_POST['pw1']
            ){
                if(
                    (
                        (
                            $_POST['currentpassword'] 
                            // && md5($_POST['currentpassword'])===$aOptions['options']['auth']['password']
                            && password_verify($_POST['currentpassword'],$aOptions['options']['auth']['password'])
                        ) 
                        || (!$_POST['currentpassword'])
                    ) && $_POST['pw1']===$_POST['pw2']
                ){
                    // $_POST['options']['auth']['password']=md5($_POST['pw1']);
                    $_POST['options']['auth']['password']=password_hash($_POST['pw1'], PASSWORD_DEFAULT);
                    $this->_setUser('');
                } else {
                    $sReturn.=$oRenderer->renderMessagebox($this->lB('setup.program.save.error.new-password'), 'error')
                        .$sBtnBack
                        ;
                    return $sReturn;
                }
                
            }
            
            // check: if a user was set then a password must exist
            if($_POST['options']['auth']['user'] && !isset($_POST['options']['auth']['password'])) {
                $sReturn.=$oRenderer->renderMessagebox($this->lB('setup.program.save.error.user-needs-a-password'), 'error')
                    .$sBtnBack
                    ;
                return $sReturn;
            }

            // if there is no user then remove section auth
            if(!$_POST['options']['auth']['user']){
                unset($_POST['options']['auth']);
                $this->_setUser(''); // logoff
            }
            
            // prepare new config array
            $aOptionsCurrent=$aOptions;
            $aOptions['options']=$_POST['options'];
            
            // ----- fix boolean options
            if(!isset($aOptions['options']['cache'])){
                $aOptions['options']['cache']=false;
            }
            if(!isset($aOptions['options']['debug'])){
                $aOptions['options']['debug']=false;
            }
            // ----- fix integer options
            
            $this->_configMakeInt($aOptions, 'options.database.port');
            $this->_configMakeInt($aOptions, 'options.crawler.searchindex.simultanousRequests');
            $this->_configMakeInt($aOptions, 'options.crawler.ressources.simultanousRequests');
            $this->_configMakeInt($aOptions, 'options.crawler.timeout');
            $this->_configMakeInt($aOptions, 'options.analysis.MinTitleLength');
            $this->_configMakeInt($aOptions, 'options.analysis.MinDescriptionLength');
            $this->_configMakeInt($aOptions, 'options.analysis.MinKeywordsLength');
            $this->_configMakeInt($aOptions, 'options.analysis.MaxPagesize');
            $this->_configMakeInt($aOptions, 'options.analysis.MaxLoadtime');

            foreach(['matchWord', 'WordStart', 'any'] as $sMatchSection){
                foreach(['title', 'keywords', 'description', 'url', 'content'] as $sMatchField){
                    $this->_configMakeInt($aOptions, 'options.searchindex.rankingWeights.'.$sMatchSection.'.'.$sMatchField);
                }
            }

            foreach (['menu', 'menu-public'] as $sSection){
                foreach(array_keys($aOptionsCurrent['options'][$sSection]) as $sMenukey){
                    $aOptions['options'][$sSection][$sMenukey]=isset($aOptions['options'][$sSection][$sMenukey])
                        ? $aOptions['options'][$sSection][$sMenukey]
                        : false
                    ;
                }
            }

            // ----- fix array values
            $aArrays=[
                'output'=>['customfooter'],
                'searchindex'=>['regexToRemove', 'defaultUrls'],
            ];
            foreach($aArrays as $sIndex1=>$aSubArrays){
                foreach($aSubArrays as $sIndex2){
                    if(isset($aOptions['options'][$sIndex1][$sIndex2]) && $aOptions['options'][$sIndex1][$sIndex2]){
                        // echo "set [$sIndex1][$sIndex2]<br>";
                        $aOptions['options'][$sIndex1][$sIndex2]=explode("\n", str_replace("\r", '', $aOptions['options'][$sIndex1][$sIndex2]));
                    } else {
                        $aOptions['options'][$sIndex1][$sIndex2]=[];
                    }
                }
            }

            // --------------------------------------------------
            // check database access
            // --------------------------------------------------
            if($aOptions['options']['database']['password']==$sPasswordDummy){
                $aOptions['options']['database']['password']=$aOptionsCurrent['options']['database']['password'];
            }
            try{
                $oDbtest=new Medoo\Medoo($this->_getRealDbConfig($aOptions['options']['database']));
            } catch (Exception $ex) {
                $sReturn.=$oRenderer->renderMessagebox($this->lB('setup.program.save.error.wrong-dbsettings'), 'error')
                    .(isset($oDbtest) ? print_r($oDbtest->error, 1) : '')
                    .$sBtnBack
                    ;
                return $sReturn;
            }
            
            
            // --------------------------------------------------
            // SAVE
            // --------------------------------------------------
           
            // $sReturn.='<pre>new options: '. htmlentities(print_r($aOptions['options'], 1)).'</pre>'; die($sReturn);
            if ($this->_saveConfig($aOptions)){
                $sReturn.=$oRenderer->renderMessagebox($this->lB('setup.program.save.ok'), 'ok')
                    .'<script>
                        window.setTimeout("window.location.href = \''.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'\'", 3000);
                    </script>'
                ;
            } else {
                $sReturn.=$oRenderer->renderMessagebox($this->lB('setup.program.save.error'), 'error');
            }
            break;
            ;;
        default: 
            $sReturn.=$oRenderer->renderMessagebox('ERRROR: unknown action ['.htmlentities($_POST['action']).'] :-/ skipping ... just in case', 'warning');
    }
    
    $sReturn.=$sBtnContinue;
    // $sReturn.='<pre>'.print_r($aOptions, 1).'</pre>';
    return $sReturn;
}



// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

$aDbOptions=[];
$sDefaultDb=isset($aOptions['options']['database']['database_type']) ? $aOptions['options']['database']['database_type'] : 'sqlite';
foreach(['sqlite', 'mysql'] as $sDbtype){   
    $aDbOptions[$sDbtype]=[
        'label'=>$this->lB('setup.section.database.type.'.$sDbtype),
        'value'=>$sDbtype,
    ];
}
$aDbOptions[$sDefaultDb]['selected']='selected';
// $aOptions['options']['database']['database_type']=$sDefaultDb;

$aLangOptions=[];
$sDefaultLang=isset($aOptions['options']['lang']) ? $aOptions['options']['lang'] : 'en';
foreach($this->getLanguages('backend') as $sLangOption=>$sLangname){
    $aLangOptions[$sLangOption]=[
        'label'=>$sLangname,
        'value'=>$sLangOption,
    ];
}
$aLangOptions[$sDefaultLang]['selected']='selected';

// echo '<pre>'; print_r($this->getSkinsAvailable()); die();
$aSkinOptions=[];
// $sDefaultSkin=isset($aOptions['options']['skin']) ? $aOptions['options']['skin'] : 'default';
$sDefaultSkin=$this->getSkin();
$sSelectedSkin=false;
foreach($this->getSkinsAvailable() as $sSkin=>$aInfos){
    $aSkinOptions[$aInfos['label']]=[
        'value'=>$aInfos['label'],
        'label'=>''
            // .$aInfos['label'].' | '
            .$sSkin
            // .(isset($aInfos['description']) ? ' :: '. htmlentities($aInfos['description']) : '')
            .(isset($aInfos['author']) ? ' ('. htmlentities($aInfos['author']).')' : '')
        ,
    ];
    $sSelectedSkin=($aInfos['label']===$sDefaultSkin ? $aInfos['label'] : $sSelectedSkin);
}
$aSkinOptions[$sSelectedSkin]['selected']='selected';
// echo '<pre>'; print_r($aSkinOptions); // die();
        
$sMenuVisibility='';
$sFrontendVisibility='';
foreach ($this->_aMenu as $sItem=>$aSubItems) {
    $val=$aOptions['options']['menu'][$sItem];
    $sMenuVisibility.=_renderCB($sItem, $aOptions, 'menu', '', $this->lB('nav.'.$sItem.'.label'));

    if (isset($aSubItems['children']) && count($aSubItems['children'])){
        foreach ($aSubItems['children'] as $sItem2=>$aSubItems2) {
            $sMenuVisibility.=_renderCB($sItem2, $aOptions, 'menu', '&nbsp;&nbsp;&nbsp;&nbsp;', $this->lB('nav.'.$sItem2.'.label'));
        }
    }
    $sMenuVisibility.='<br>';
}
$sMenuVisibility='<div>'.$sMenuVisibility.'</div>';


foreach ($aOptions['options']['menu-public'] as $sItem=>$val) {
    $sFrontendVisibility.=_renderCB($sItem, $aOptions, 'menu-public', '', $this->lB('nav.'.$sItem.'.label'));;
}
$sFrontendVisibility='<div>'.$sFrontendVisibility.'</div>';

$aDebugOptions=[
    false=>[
        'label'=>$this->lB('setup.section.backend.debug.off'),
        'value'=>'',
    ],
    true=>[
        'label'=>$this->lB('setup.section.backend.debug.on'),
        'value'=>true,
    ]
];
// $aDebugOptions[$aOptions['options']['debug']]['selected']='selected';


$sIdPrefixDb='options-database-';
$sIdPrefixAuth='options-auth-';
$sIdPrefixCrawler='options-crawler-';
$sIdPrefixOther='options-';
$sIdPrefixSearchindex='options-searchindex-';
$sIdPrefixAnalyis='options-analysis-';


$aCbDebug=[
    'id'=>$sIdPrefixOther.'debug', 
    'type'=>'checkbox',
    'name'=>'options[debug]',
    'value'=>'true',
];
if (isset($aOptions['options']['debug']) && $aOptions['options']['debug']==true){
    $aCbDebug['checked']='checked';
}
$aCbNocache=[
    'id'=>$sIdPrefixOther.'cache', 
    'type'=>'checkbox',
    'name'=>'options[cache]',
    'value'=>'true',
    'checked'=>'checked',
];
if (isset($aOptions['options']['cache']) && $aOptions['options']['cache']==false){
    unset($aCbNocache['checked']);
}

$aCbShowSkip=[
    'id'=>$sIdPrefixOther.'showSkip', 
    'type'=>'checkbox',
    'name'=>'options[crawler][showSkip]',
    'value'=>'false',
];
if (isset($aOptions['options']['crawler']['showSkip']) && $aOptions['options']['crawler']['showSkip']==true){
    $aCbShowSkip['checked']='checked';
}

$sSubmit=(isset($aOptionDefaults['options']['searchindex'])
    ? $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.save') . $this->lB('button.save'), 'class'=>'pure-button button-secondary'])
    : $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.create') . $this->lB('button.create'), 'class'=>'pure-button button-success'])
);

$sReturn.=(!isset($_SERVER['HTTPS'])
            ? $oRenderer->renderMessagebox($this->lB('setup.error-no-ssl'), 'warning')
            : ''
        ).'
        <form class="pure-form pure-form-aligned" method="POST" action="?'.$_SERVER['QUERY_STRING'].'">
            '
            . $oRenderer->oHtml->getTag('input', [
                'type'=>'hidden',
                'name'=>'action',
                'value'=>'setoptions',
                ], false)


            .(!isset($aOptionDefaults['options']['searchindex'])
                ? $oRenderer->renderMessagebox($this->lB('setup.initial-save'), 'warning')
                : ''
            )
        
            // ------------------------------------------------------------
            // setup options - backend
            // ------------------------------------------------------------
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', ['class'=>'fa fa-cogs']) 
                . ' '.$this->lB('setup.section.backend')
            .'</h3>'
            . '<h4>'.$this->lB('setup.section.backend.hint').'</h4>'

            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixOther.'lang', 'label'=>$this->lB('setup.section.backend.lang')])
                . $oRenderer->oHtml->getFormSelect([
                    'id'=>$sIdPrefixOther.'lang', 
                    'name'=>'options[lang]',
                    // 'onchange'=>'changeView(\'params-dbtype\', \'params-dbtype-\'+this.value); return false;'
                    ], $aLangOptions)
            . '</div>'

        . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixOther.'skin', 'label'=>$this->lB('setup.section.backend.skin')])
                . $oRenderer->oHtml->getFormSelect([
                    'id'=>$sIdPrefixOther.'skin', 
                    'name'=>'options[skin]',
                    // 'onchange'=>'changeView(\'params-dbtype\', \'params-dbtype-\'+this.value); return false;'
                    ], $aSkinOptions)
            . '</div>'

            .$oRenderer->renderExtendedView()
            . '<div class="hintextended">'.$this->lB('hint.extended').'</div>'
            . '<div class="extended">'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixOther.'menu', 'label'=>$this->lB('setup.section.backend.menu')])
                    . $sMenuVisibility
                    /*
                    . $oRenderer->oHtml->getTag('textarea', [
                        'id'=>$sIdPrefixOther.'menu', 
                        'name'=>'options[menu]',
                        'cols'=>$iColsInTA,
                        'rows'=>isset($aOptions['options']['menu']) && is_array($aOptions['options']['menu']) && count($aOptions['options']['menu']) ? count($aOptions['options']['menu'])+3 : 3 ,
                        // 'label'=>$sValueSearchCategories,
                        'label'=> json_encode($aOptions['options']['menu'], JSON_PRETTY_PRINT),
                        ], true)
                     *  
                     */
                    . '</div>'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixOther.'customfooter', 'label'=>$this->lB('setup.section.backend.customfooter')])
                    . $oRenderer->oHtml->getTag('textarea', [
                        'id'=>$sIdPrefixOther.'customfooter', 
                        'name'=>'options[output][customfooter]',
                        'cols'=>$iColsInTA,
                        'rows'=>isset($aOptions['options']['output']['customfooter']) && is_array($aOptions['options']['output']['customfooter']) && count($aOptions['options']['output']['customfooter']) ? count($aOptions['options']['output']['customfooter'])+1 : 3 ,
                        // 'label'=>$sValueSearchCategories,
                        'label'=> implode("\n", $aOptions['options']['output']['customfooter']),
                        // 'label'=> $aOptions['options']['customfooter'],
                        ], true)
                    . '</div>'
                /*
                . '<div class="pure-control-group">'
                    // . '<label> </label>'
                    . '<label class="pure-checkbox" for="'.$sIdPrefixOther.'debug">'
                    . $oRenderer->oHtml->getTag('input', $aCbDebug, false)
                            .' '.$this->lB('setup.section.backend.debug')
                    . '</label>'
                    . '</div>'
                */
                . '<div class="pure-control-group">'
                    . '<label>'.$this->lB('setup.section.backend.fordevelopers').'</label>'
                    . '<div>'
                        . '<label class="align-left">'
                            . $oRenderer->oHtml->getTag('input', $aCbDebug, false)
                            .' '.$this->lB('setup.section.backend.debug')
                        . '</label><br>'
                        . '<label class="align-left">'
                            . $oRenderer->oHtml->getTag('input', $aCbNocache, false)
                            .' '.$this->lB('setup.section.backend.cache')
                        . '</label><br>'
                    . '</div>'
                    . '</div>'
            .'</div>'
            // ------------------------------------------------------------
            // setup options - auth
            // ------------------------------------------------------------
            
            . '<h4>'
                // . $oRenderer->oHtml->getTag('i', ['class'=>'fa fa-user']) 
                . ' '.$this->lB('setup.section.auth')
            .'</h4>'
            . $this->lB('setup.section.auth.hint').'<br><br>'
            . (isset($aOptions['options']['auth']['user']) && $aOptions['options']['auth']['user']
                ? ''
                :$this->lB('setup.section.auth.no-user').'<br>'
            )
            .'<br>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixAuth.'username', 'label'=>$this->lB('setup.section.auth.user')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'text',
                    'id'=>$sIdPrefixAuth.'user', 
                    'name'=>'options[auth][user]',
                    'size'=>$iSizeInInput,
                    'value'=>isset($aOptions['options']['auth']['user']) ? $aOptions['options']['auth']['user'] : '',
                    ], false)
                . '</div>'
         
            // unneeded
            /*
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixAuth.'password', 'label'=>$this->lB('setup.section.auth.password')])
                . $oRenderer->oHtml->getTag('input', [
                    'id'=>$sIdPrefixAuth.'password', 
                    'name'=>'options[auth][password]',
                    'disabled'=>'disabled',
                    'value'=>isset($aOptions['options']['auth']['password']) ? $aOptions['options']['auth']['password'] : '',
                    ])
                . '</div>'
            */
            . '<br>'
        
            . (isset($aOptions['options']['auth']['user']) && $aOptions['options']['auth']['user']
                ? 
                    $this->lB('setup.section.auth.changeuser')

                    . '<div class="pure-control-group">'
                        . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixAuth.'currentpassword', 'label'=>$this->lB('setup.section.auth.lastpw')])
                        . $oRenderer->oHtml->getTag('input', [
                            'id'=>$sIdPrefixAuth.'currentpassword', 
                            'type'=>'password',
                            'name'=>'currentpassword',
                            'size'=>$iSizeInInput,
                            'value'=>'',
                            ], false)
                        . '</div>'
                :   $oRenderer->oHtml->getTag('input', [
                            'id'=>$sIdPrefixAuth.'currentpassword', 
                            'type'=>'hidden',
                            'name'=>'currentpassword',
                            'value'=>'',
                    ], false)
            )
        
            . $this->lB('setup.section.auth.changepassword')
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixAuth.'pw1', 'label'=>$this->lB('setup.section.auth.pw1')])
                . $oRenderer->oHtml->getTag('input', [
                    'id'=>$sIdPrefixAuth.'pw1', 
                    'type'=>'password',
                    'name'=>'pw1',
                    'size'=>$iSizeInInput,
                    'value'=>'',
                    ], false)
                . '</div>'
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixAuth.'pw1', 'label'=>$this->lB('setup.section.auth.pw2')])
                . $oRenderer->oHtml->getTag('input', [
                    'id'=>$sIdPrefixAuth.'pw2', 
                    'type'=>'password',
                    'name'=>'pw2',
                    'size'=>$iSizeInInput,
                    'value'=>'',
                    ], false)
                . '</div>'
            // ------------------------------------------------------------
            // setup options - crawler
            // ------------------------------------------------------------
            
            . $sSubmit
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', ['class'=>'fa fa-spinner')] 
                . ' '.$this->lB('setup.section.crawler')
            .'</h3>'
            . $this->lB('setup.section.crawler.hint').'<br><br>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixCrawler.'searchindex-simultanousRequests', 'label'=>$this->lB('setup.section.crawler.searchindex.simultanousRequests')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'number',
                    'id'=>$sIdPrefixCrawler.'searchindex-simultanousRequests', 
                    'name'=>'options[crawler][searchindex][simultanousRequests]',
                    'size'=>$iSizeInInput,
                    'step'=>1,
                    'pattern'=>$sPatternNumber,
                    'placeholder'=>$this->aDefaultOptions['crawler']['searchindex']['simultanousRequests'],
                    'value'=>isset($aOptions['options']['crawler']['searchindex']['simultanousRequests']) 
                        ? (int)$aOptions['options']['crawler']['searchindex']['simultanousRequests'] 
                        : $this->aDefaultOptions['options']['crawler']['searchindex']['simultanousRequests'],
                    ], false)
                . '</div>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixCrawler.'ressources-simultanousRequests', 'label'=>$this->lB('setup.section.crawler.ressources.simultanousRequests')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'number',
                    'id'=>$sIdPrefixCrawler.'ressources-simultanousRequests', 
                    'name'=>'options[crawler][ressources][simultanousRequests]',
                    'pattern'=>$sPatternNumber,
                    'placeholder'=>$this->aDefaultOptions['crawler']['ressources']['simultanousRequests'],
                    'size'=>$iSizeInInput,
                    'step'=>1,
                    'value'=>isset($aOptions['options']['crawler']['ressources']['simultanousRequests']) 
                        ? (int)$aOptions['options']['crawler']['ressources']['simultanousRequests'] 
                        : $this->aDefaultOptions['crawler']['ressources']['simultanousRequests'],
                    ], false)
                . '</div>'

            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixCrawler.'timeout', 'label'=>$this->lB('setup.section.crawler.timeout')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'number',
                    'id'=>$sIdPrefixCrawler.'timeout', 
                    'name'=>'options[crawler][timeout]',
                    'size'=>$iSizeInInput,
                    'pattern'=>$sPatternNumber,
                    'placeholder'=>$this->aDefaultOptions['crawler']['timeout'],
                    'value'=>isset($aOptions['options']['crawler']['timeout']) ? $aOptions['options']['crawler']['timeout'] : $this->aDefaultOptions['crawler']['timeout'],
                    ], false)
                . '</div>'

            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixCrawler.'memoryLimit', 'label'=>$this->lB('setup.section.crawler.memoryLimit')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'text',
                    'id'=>$sIdPrefixCrawler.'memoryLimit', 
                    'name'=>'options[crawler][memoryLimit]',
                    'size'=>$iSizeInInput,
                    'placeholder'=>$this->aDefaultOptions['crawler']['memoryLimit'],
                    'value'=>isset($aOptions['options']['crawler']['memoryLimit']) ? $aOptions['options']['crawler']['memoryLimit'] : '',
                    ], false)
                . '</div>'

            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixCrawler.'userAgent', 'label'=>$this->lB('setup.section.crawler.userAgent')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'text',
                    'id'=>$sIdPrefixCrawler.'userAgent', 
                    'name'=>'options[crawler][userAgent]',
                    'size'=>$iSizeInInput,
                    'placeholder'=>$this->aDefaultOptions['crawler']['userAgent'],
                    'value'=>isset($aOptions['options']['crawler']['userAgent']) ? $aOptions['options']['crawler']['userAgent'] : '',
                    ], false)
                . '<br>'
                . $oRenderer->oHtml->getTag('label', [])
                . $oRenderer->oHtml->getTag('button', [
                    'class'=>'pure-button',
                    'label'=>$this->lB('setup.section.crawler.userAgent.button'),
                    'onclick'=>'$(\'#'.$sIdPrefixCrawler.'userAgent\').val(\''.$_SERVER['HTTP_USER_AGENT'].' '.$this->aAbout['product'] . '/' . $this->aAbout['version'].'\'); return false;',
                    'title'=>$_SERVER['HTTP_USER_AGENT'],
                    ], true)
                . ' '
                . $oRenderer->oHtml->getTag('button', [
                    'class'=>'pure-button',
                    'label'=>$this->_getIcon('button.close'),
                    'title'=>$this->aDefaultOptions['crawler']['userAgent'],
                    'onclick'=>'$(\'#'.$sIdPrefixCrawler.'userAgent\').val(\'\'); return false;',
                    ], true)
                . '</div>'
            . '<br>'
        

            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixSearchindex.'regexToRemove', 'label'=>$this->lB('setup.section.searchindex.regexToRemove')])
                . $oRenderer->oHtml->getTag('textarea', [
                    'id'=>$sIdPrefixSearchindex.'regexToRemove', 
                    'name'=>'options[searchindex][regexToRemove]',
                    'cols'=>$iColsInTA,
                    'rows'=>isset($aOptions['options']['searchindex']['regexToRemove']) && is_array($aOptions['options']['searchindex']['regexToRemove']) && count($aOptions['options']['searchindex']['regexToRemove']) ? count($aOptions['options']['searchindex']['regexToRemove'])+1 : 3 ,
                    // 'label'=>$sValueSearchCategories,
                    'label'=> implode("\n", $aOptions['options']['searchindex']['regexToRemove']),
                    ], true)
                . '</div>'

            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixSearchindex.'defaultUrls', 'label'=>$this->lB('setup.section.searchindex.defaultUrls')])
                . $oRenderer->oHtml->getTag('textarea', [
                    'id'=>$sIdPrefixSearchindex.'defaultUrls', 
                    'name'=>'options[searchindex][defaultUrls]',
                    'cols'=>$iColsInTA,
                    'rows'=>isset($aOptions['options']['searchindex']['defaultUrls']) && is_array($aOptions['options']['searchindex']['defaultUrls']) && count($aOptions['options']['searchindex']['defaultUrls']) ? count($aOptions['options']['searchindex']['defaultUrls'])+1 : 3 ,
                    // 'label'=>$sValueSearchCategories,
                    'label'=> isset($aOptions['options']['searchindex']['defaultUrls']) ? implode("\n", $aOptions['options']['searchindex']['defaultUrls']) : '',
                    ], true)
                . '</div>'
            . '<div class="pure-control-group">'
                . '<label>'.$this->lB('setup.section.crawler.logging').'</label>'
                . '<div>'
                    . '<label class="align-left">'
                        . $oRenderer->oHtml->getTag('input', $aCbShowSkip, false)
                        .' '.$this->lB('setup.section.crawler.showSkip')
                    . '</label><br>'
                . '</div>'
                . '</div>'

            // ------------------------------------------------------------
            // setup options - search result weights
            // ------------------------------------------------------------
            .$sSubmit            
            . '<h3>'
                . ' '.$this->lB('setup.section.search')
            .'</h3>'
            . $this->lB('setup.section.search.hint').'<br><br>'
            .$oRenderer->renderExtendedView()
            . '<div class="hintextended">'.$this->lB('hint.extended').'</div>'
            . '<div class="extended">';

                foreach(['matchWord', 'WordStart', 'any'] as $sMatchSection){
                    $sReturn.='<p><strong>'.$this->lB('setup.section.search.section.'.$sMatchSection).'</strong></p>';
                    foreach(['title', 'keywords', 'description', 'url', 'content'] as $sMatchField){
                        $sFieldId=$sIdPrefixSearchindex.'rw-'.$sMatchSection.'-title';
                        $sValue=isset($aOptions['options']['searchindex']['rankingWeights'][$sMatchSection][$sMatchField]) 
                                    ? (int)$aOptions['options']['searchindex']['rankingWeights'][$sMatchSection][$sMatchField]
                                    : $this->aDefaultOptions['searchindex']['rankingWeights'][$sMatchSection][$sMatchField]
                            ;
                        $sReturn.='<div class="pure-control-group">'
                            . $oRenderer->oHtml->getTag('label', ['for'=>$sFieldId, 'label'=>$this->lB('setup.section.search.rw.'.$sMatchField)])
                            . $oRenderer->oHtml->getTag('input', [
                                'type'=>'text',
                                'id'=>$sFieldId, 
                                'name'=>'options[searchindex][rankingWeights]['.$sMatchSection.']['.$sMatchField.']',
                                'size'=>$iSizeInInput,
                                'pattern'=>$sPatternNumber,
                                'placeholder'=>$this->aDefaultOptions['searchindex']['rankingWeights'][$sMatchSection][$sMatchField],
                                'value'=>$sValue,
                                ], false)
                            . '</div>'
                            ;
                    }
                }
            $sReturn.='</div>'
                . '<br>'
            ;
        
            // ------------------------------------------------------------
            // setup options - analysis constants
            // ------------------------------------------------------------
            $sReturn.=''
                .$sSubmit
                . '<h3>'
                // . $oRenderer->oHtml->getTag('i', ['class'=>'fa fa-newspaper-o']) 
                . ' '.$this->lB('setup.section.analysis')
            .'</h3>'
            . $this->lB('setup.section.analysis.hint').'<br><br>'

            .$oRenderer->renderExtendedView()
            . '<div class="hintextended">'.$this->lB('hint.extended').'</div>'
            . '<div class="extended">'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixAnalyis.'MinTitleLength', 'label'=>$this->lB('setup.section.analysis.MinTitleLength')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'text',
                    'id'=>$sIdPrefixAnalyis.'MinTitleLength', 
                    'name'=>'options[analysis][MinTitleLength]',
                    'pattern'=>$sPatternNumber,
                    'placeholder'=>$this->aDefaultOptions['analysis']['MinTitleLength'],
                    'size'=>$iSizeInInput,
                    'value'=>isset($aOptions['options']['analysis']['MinTitleLength']) 
                        && $aOptions['options']['analysis']['MinTitleLength'] ? $aOptions['options']['analysis']['MinTitleLength'] 
                        : $this->aDefaultOptions['analysis']['MinTitleLength'],
                    ], false)
                . '</div>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixAnalyis.'MinTitleLength', 'label'=>$this->lB('setup.section.analysis.MinDescriptionLength')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'text',
                    'id'=>$sIdPrefixAnalyis.'MinDescriptionLength', 
                    'name'=>'options[analysis][MinDescriptionLength]',
                    'pattern'=>$sPatternNumber,
                    'placeholder'=>$this->aDefaultOptions['analysis']['MinDescriptionLength'],
                    'size'=>$iSizeInInput,
                    'value'=>isset($aOptions['options']['analysis']['MinDescriptionLength']) && $aOptions['options']['analysis']['MinDescriptionLength'] 
                        ? $aOptions['options']['analysis']['MinDescriptionLength'] 
                        : $this->aDefaultOptions['analysis']['MinDescriptionLength'],
                    ], false)
                . '</div>'
       
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixAnalyis.'MinKeywordsLength', 'label'=>$this->lB('setup.section.analysis.MinKeywordsLength')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'text',
                    'id'=>$sIdPrefixAnalyis.'MinKeywordsLength', 
                    'name'=>'options[analysis][MinKeywordsLength]',
                    'pattern'=>$sPatternNumber,
                    'placeholder'=>$this->aDefaultOptions['analysis']['MinKeywordsLength'],
                    'size'=>$iSizeInInput,
                    'value'=>isset($aOptions['options']['analysis']['MinKeywordsLength']) && (int)$aOptions['options']['analysis']['MinKeywordsLength'] >= 0
                        ? $aOptions['options']['analysis']['MinKeywordsLength'] 
                        : $this->aDefaultOptions['analysis']['MinKeywordsLength'],
                    ], false)
                . '</div>'
       
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixAnalyis.'MaxPagesize', 'label'=>$this->lB('setup.section.analysis.MaxPagesize')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'text',
                    'id'=>$sIdPrefixAnalyis.'MaxPagesize', 
                    'name'=>'options[analysis][MaxPagesize]',
                    'pattern'=>$sPatternNumber,
                    'placeholder'=>$this->aDefaultOptions['analysis']['MaxPagesize'],
                    'size'=>$iSizeInInput,
                    'value'=>isset($aOptions['options']['analysis']['MaxPagesize']) && $aOptions['options']['analysis']['MaxPagesize'] 
                        ? $aOptions['options']['analysis']['MaxPagesize'] 
                        : $this->aDefaultOptions['analysis']['MaxPagesize'],
                    ], false)
                . '</div>'
       
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixAnalyis.'MaxLoadtime', 'label'=>$this->lB('setup.section.analysis.MaxLoadtime')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'text',
                    'id'=>$sIdPrefixAnalyis.'MaxLoadtime', 
                    'name'=>'options[analysis][MaxLoadtime]',
                    'pattern'=>$sPatternNumber,
                    'placeholder'=>$this->aDefaultOptions['analysis']['MaxLoadtime'],
                    'size'=>$iSizeInInput,
                    'value'=>isset($aOptions['options']['analysis']['MaxLoadtime']) && $aOptions['options']['analysis']['MaxLoadtime'] 
                        ? $aOptions['options']['analysis']['MaxLoadtime'] 
                        : $this->aDefaultOptions['analysis']['MaxLoadtime'],
                    ], false)
                . '</div>'
            . '</div>'
            . '<br>'

            // ------------------------------------------------------------
            // setup options - public services without login
            // ------------------------------------------------------------
            .$sSubmit
            .'<h3>'
                // . $oRenderer->oHtml->getTag('i', ['class'=>'fa fa-newspaper-o']) 
                . ' '.$this->lB('setup.section.public-services')
            .'</h3>'
            . $this->lB('setup.section.public-services.hint').'<br><br>'

            .$oRenderer->renderExtendedView()
            . '<div class="hintextended">'.$this->lB('hint.extended').'</div>'
            . '<div class="extended">'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixOther.'menu-public', 'label'=>$this->lB('setup.section.public-services.menu-public')])
                    . $sFrontendVisibility
                    /*
                    . $oRenderer->oHtml->getTag('textarea', [
                        'id'=>$sIdPrefixOther.'menu-public', 
                        'name'=>'options[menu-public]',
                        'cols'=>$iColsInTA,
                        'rows'=>isset($aOptions['options']['menu-public']) && is_array($aOptions['options']['menu-public']) && count($aOptions['options']['menu-public']) ? count($aOptions['options']['menu-public'])+3 : 3 ,
                        // 'label'=>$sValueSearchCategories,
                        'label'=> json_encode($aOptions['options']['menu-public'], JSON_PRETTY_PRINT),
                        ], true)
                     */
                    . '</div>'
            . '</div>'
            . '<br>'


            // ------------------------------------------------------------
            // setup options - database
            // ------------------------------------------------------------
        
            .$sSubmit
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', ['class'=>'fa fa-database']) 
                . ' '.$this->lB('setup.section.database')
            .'</h3>'
            . $this->lB('setup.section.database.hint').'<br><br>'
            .$oRenderer->renderExtendedView()
            . '<div class="hintextended">'.$this->lB('hint.extended').'</div>'
            . '<div class="extended">'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixDb.'type', 'label'=>$this->lB('setup.section.database.type')])
                    . $oRenderer->oHtml->getFormSelect([
                        'id'=>$sIdPrefixDb.'type', 
                        'name'=>'options[database][database_type]',
                        'onchange'=>'changeView(\'params-dbtype\', \'params-dbtype-\'+this.value); return false;'
                        ], $aDbOptions)
                . '</div>'

                . '<div id="params-dbtype-sqlite" class="params-dbtype">'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixDb.'file', 'label'=>$this->lB('setup.section.database.file')])
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'text',
                        'id'=>$sIdPrefixDb.'type', 
                        'name'=>'options[database][database_file]', 
                        'size'=>$iSizeInInput, 
                        'value'=>isset($aOptions['options']['database']['database_file']) ? $aOptions['options']['database']['database_file'] : '__DIR__/data/ahcrawl.db',
                        ], false)
                . '</div>'
                . '</div>'

                . '<div id="params-dbtype-mysql" class="params-dbtype">'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixDb.'server', 'label'=>$this->lB('setup.section.database.server')])
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'text',
                        'id'=>$sIdPrefixDb.'name', 
                        'name'=>'options[database][server]',
                        'size'=>$iSizeInInput,
                        'value'=>isset($aOptions['options']['database']['server']) ? $aOptions['options']['database']['server'] : '',
                        ], false)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixDb.'port', 'label'=>$this->lB('setup.section.database.port')])
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'text',
                        'id'=>$sIdPrefixDb.'port', 
                        'name'=>'options[database][port]',
                        'pattern'=>$sPatternNumber,
                        'size'=>$iSizeInInput,
                        'value'=>isset($aOptions['options']['database']['port']) ? $aOptions['options']['database']['port'] : '',
                        ], false)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixDb.'name', 'label'=>$this->lB('setup.section.database.name')])
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'text',
                        'id'=>$sIdPrefixDb.'name', 
                        'name'=>'options[database][database_name]',
                        'size'=>$iSizeInInput,
                        'value'=>isset($aOptions['options']['database']['database_name']) ? $aOptions['options']['database']['database_name'] : '',
                        ], false)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixDb.'username', 'label'=>$this->lB('setup.section.database.username')])
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'text',
                        'id'=>$sIdPrefixDb.'username', 
                        'name'=>'options[database][username]',
                        'size'=>$iSizeInInput,
                        'value'=>isset($aOptions['options']['database']['username']) ? $aOptions['options']['database']['username'] : '',
                        ], false)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixDb.'password', 'label'=>$this->lB('setup.section.database.password')])
                    . $oRenderer->oHtml->getTag('input', [
                        'id'=>$sIdPrefixDb.'password', 
                        'type'=>'password',
                        'name'=>'options[database][password]',
                        'size'=>$iSizeInInput,
                        // 'value'=>isset($aOptions['options']['database']['password']) ? $aOptions['options']['database']['password'] : '',
                        'value'=>$sPasswordDummy,
                        ], false)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>$sIdPrefixDb.'charset', 'label'=>$this->lB('setup.section.database.charset')])
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'text',
                        'id'=>$sIdPrefixDb.'charset', 
                        'name'=>'options[database][charset]',
                        'size'=>$iSizeInInput,
                        'value'=>isset($aOptions['options']['database']['charset']) ? $aOptions['options']['database']['charset'] : 'utf8',
                        ], false)
                    . '</div>'
                . '</div>'
            . '</div>'


        . '<br>'
        
/*
    'MinTitleLength' => 20,
    'MinDescriptionLength' => 40,
    'MinKeywordsLength' => 10,
    'MaxPagesize' => 150000, 
    'MaxLoadtime' => 500,
 */            
        
            // ------------------------------------------------------------
            // submit
            // ------------------------------------------------------------
            .$sSubmit

            /*
            . '<h3>'
                .$this->lB('setup.projects')
            . '</h3>'
            .$this->lB('setup.projects.hint').'<br><br>'
             * 
             */
        
        
            . '<br><br>'
            .'

        </form>

        <script>
            changeView(\'params-dbtype\', \'params-dbtype-'.$aOptions['options']['database']['database_type'].'\');
        </script>
    
';
return $sReturn;