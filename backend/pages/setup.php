<?php
/**
 * SETTINGS
 */
$oRenderer=new ressourcesrenderer($this->_sTab);


/**
 * @var array  full config with app settings without profiles
 */
// $aOptions = $this->_loadConfigfile();
// $aOptions = array('options'=>$this->aOptions);
$aOptions = array('options'=>$this->getEffectiveOptions());
$sReturn='';

$sBtnBack='<br>'.$this->lB('setup.program.save.error.back').'<br><hr><br>'
    .$oRenderer->oHtml->getTag('button',array(
    'href' => '#',
    'class' => 'pure-button button-secondary',
    'onclick' => 'history.back();',
    'title' => $this->lB('button.back.hint'),
    'label' => $this->lB('button.back'),
));
$sBtnContinue='<hr><br>'
    .$oRenderer->oHtml->getTag('a',array(
    'href' => '?'.$_SERVER['QUERY_STRING'],
    'class' => 'pure-button button-secondary',
    'title' => $this->lB('button.continue.hint'),
    'label' => $this->lB('button.continue'),
));

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
                        && md5($_POST['currentpassword'])===$aOptions['options']['auth']['password']
                    ) 
                    || (!$_POST['currentpassword'])
                    // && $_POST['options']['pw1']!==$aOptions['options']['auth']['password']
                ){
                    // $sReturn.="ok, a new user wants to be set and the old pw was correct<br>";
                    $this->_setUser('');
                } else {
                    // $sReturn.='DEBUG: '.$_POST['currentpassword'] . ' - md5: ' . md5($_POST['currentpassword']).'<br>';
                    // $sReturn.='DEBUG: vs current '.$aOptions['options']['auth']['password'].'<br>';
                    $sReturn.=$this->_getMessageBox($this->lB('setup.program.save.error.wrong-current-pw'), 'error')
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
                            && md5($_POST['currentpassword'])===$aOptions['options']['auth']['password']
                        ) 
                        || (!$_POST['currentpassword'])
                    ) && $_POST['pw1']===$_POST['pw2']
                ){
                    $_POST['options']['auth']['password']=md5($_POST['pw1']);
                    $this->_setUser('');
                } else {
                    $sReturn.=$this->_getMessageBox($this->lB('setup.program.save.error.new-password'), 'error')
                        .$sBtnBack
                        ;
                    return $sReturn;
                }
                
            }
            
            // check: if a user was set then a password must exist
            if($_POST['options']['auth']['user'] && !isset($_POST['options']['auth']['password'])) {
                $sReturn.=$this->_getMessageBox($this->lB('setup.program.save.error.user-needs-a-password'), 'error')
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
            $aOptions['options']=$_POST['options'];
            
            // ----- fix boolean options
            if(!isset($aOptions['options']['debug'])){
                $aOptions['options']['debug']=false;
            }
            // ----- fix integer options
            
            $this->_configMakeInt($aOptions, 'options.database.port');
            $this->_configMakeInt($aOptions, 'options.crawler.searchindex.simultanousRequests');
            $this->_configMakeInt($aOptions, 'options.crawler.ressources.simultanousRequests');
            $this->_configMakeInt($aOptions, 'options.analysis.MinTitleLength');
            $this->_configMakeInt($aOptions, 'options.analysis.MinDescriptionLength');
            $this->_configMakeInt($aOptions, 'options.analysis.MinKeywordsLength');
            $this->_configMakeInt($aOptions, 'options.analysis.MaxPagesize');
            $this->_configMakeInt($aOptions, 'options.analysis.MaxLoadtime');
            if(isset($aOptions['options']['menu']) 
                    && $aOptions['options']['menu']
                    && json_decode($aOptions['options']['menu'])
            ){
                $aOptions['options']['menu'] = json_decode($aOptions['options']['menu']);
            } else {
                $aOptions['options']['menu'] = array();
            }

            // ----- fix array values
            $aArrays=array(
                'searchindex'=>array('regexToRemove'),
            );
            foreach($aArrays as $sIndex1=>$aSubArrays){
                foreach($aSubArrays as $sIndex2){
                    if(isset($aOptions['options'][$sIndex1][$sIndex2]) && $aOptions['options'][$sIndex1][$sIndex2]){
                        // echo "set [$sIndex1][$sIndex2]<br>";
                        $aOptions['options'][$sIndex1][$sIndex2]=explode("\n", str_replace("\r", '', $aOptions['options'][$sIndex1][$sIndex2]));
                    } else {
                        $aOptions['options'][$sIndex1][$sIndex2]=array();
                    }
                }
            }

            // --------------------------------------------------
            // check database access
            // --------------------------------------------------
            try{
                $oDbtest=new Medoo\Medoo($this->_getRealDbConfig($aOptions['options']['database']));
            } catch (Exception $ex) {
                $sReturn.=$this->_getMessageBox($this->lB('setup.program.save.error.wrong-dbsettings'), 'error')
                    .(isset($oDbtest) ? print_r($oDbtest->error(), 1) : '')
                    .$sBtnBack
                    ;
                return $sReturn;
            }
            
            
            // --------------------------------------------------
            // SAVE
            // --------------------------------------------------
           
            // $sReturn.='<pre>new options: '. htmlentities(print_r($aOptions['options'], 1)).'</pre>'; die($sReturn);
            if ($this->_saveConfig($aOptions)){
                $sReturn.=$this->_getMessageBox($this->lB('setup.program.save.ok'), 'ok');
            } else {
                $sReturn.=$this->_getMessageBox($this->lB('setup.program.save.error'), 'error');
            }
            break;
            ;;
        default: 
            $sReturn.=$this->_getMessageBox('ERRROR: unknown action ['.$_POST['action'].'] :-/ skipping ... just in case', 'warning');
    }
    
    $sReturn.=$sBtnContinue;
    return $sReturn;
}



// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

$aDbOptions=array();
$sDefaultDb=isset($aOptions['options']['database']['database_type']) ? $aOptions['options']['database']['database_type'] : 'sqlite';
foreach(array('sqlite', 'mysql') as $sDbtype){   
    $aDbOptions[$sDbtype]=array(
        'label'=>$this->lB('setup.section.database.type.'.$sDbtype),
        'value'=>$sDbtype,
    );
}
$aDbOptions[$sDefaultDb]['selected']='selected';
// $aOptions['options']['database']['database_type']=$sDefaultDb;

$aLangOptions=array();
$sDefaultLang=isset($aOptions['options']['lang']) ? $aOptions['options']['lang'] : 'en';
foreach($this->getLanguages('backend') as $sLangOption=>$sLangname){
    $aLangOptions[$sLangOption]=array(
        'label'=>$sLangname,
        'value'=>$sLangOption,
    );
}
$aLangOptions[$sDefaultLang]['selected']='selected';

$aDebugOptions=array(
    false=>array(
        'label'=>$this->lB('setup.section.backend.debug.off'),
        'value'=>'',
    ),
    true=>array(
        'label'=>$this->lB('setup.section.backend.debug.on'),
        'value'=>true,
    )
);
// $aDebugOptions[$aOptions['options']['debug']]['selected']='selected';


$sIdPrefixDb='options-database-';
$sIdPrefixAuth='options-auth-';
$sIdPrefixCrawler='options-crawler-';
$sIdPrefixOther='options-';
$sIdPrefixSearchindex='options-searchindex-';
$sIdPrefixAnalyis='options-analysis-';


$aCbDebug=array(
    'id'=>$sIdPrefixOther.'debug', 
    'type'=>'checkbox',
    'name'=>'options[debug]',
    'value'=>'true',
);
if (isset($aOptions['options']['debug']) && $aOptions['options']['debug']){
    $aCbDebug['checked']='checked';
}

$sReturn.=(!isset($_SERVER['HTTPS'])
            ? $this->_getMessageBox($oRenderer->renderShortInfo('warn') . $this->lB('setup.error-no-ssl'), 'warning')
            : ''
        ).'
        <br>
        <form class="pure-form pure-form-aligned" method="POST" action="?'.$_SERVER['QUERY_STRING'].'">
            '
            . $oRenderer->oHtml->getTag('input', array(
                'type'=>'hidden',
                'name'=>'action',
                'value'=>'setoptions',
                ), false)
            // ------------------------------------------------------------
            // setup options - auth
            // ------------------------------------------------------------
            
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', array('class'=>'fa fa-user')) 
                . ' '.$this->lB('setup.section.auth')
            .'</h3>'
            . $this->lB('setup.section.auth.hint').'<br><br>'
            . (isset($aOptions['options']['auth']['user']) && $aOptions['options']['auth']['user']
                ? ''
                :$this->lB('setup.section.auth.no-user').'<br>'
            )
            .'<br>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAuth.'username', 'label'=>$this->lB('setup.section.auth.user')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixAuth.'user', 
                    'name'=>'options[auth][user]',
                    'value'=>isset($aOptions['options']['auth']['user']) ? $aOptions['options']['auth']['user'] : '',
                    ), false)
                . '</div>'
         
            // unneeded
            /*
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAuth.'password', 'label'=>$this->lB('setup.section.auth.password')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixAuth.'password', 
                    'name'=>'options[auth][password]',
                    'disabled'=>'disabled',
                    'value'=>isset($aOptions['options']['auth']['password']) ? $aOptions['options']['auth']['password'] : '',
                    ))
                . '</div>'
            */
            . '<br>'
        
            . (isset($aOptions['options']['auth']['user']) && $aOptions['options']['auth']['user']
                ? 
                    $this->lB('setup.section.auth.changeuser')

                    . '<div class="pure-control-group">'
                        . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAuth.'currentpassword', 'label'=>$this->lB('setup.section.auth.lastpw')))
                        . $oRenderer->oHtml->getTag('input', array(
                            'id'=>$sIdPrefixAuth.'currentpassword', 
                            'type'=>'password',
                            'name'=>'currentpassword',
                            'value'=>'',
                            ), false)
                        . '</div>'
                :   $oRenderer->oHtml->getTag('input', array(
                            'id'=>$sIdPrefixAuth.'currentpassword', 
                            'type'=>'hidden',
                            'name'=>'currentpassword',
                            'value'=>'',
                            ), false)
            )
        
            . $this->lB('setup.section.auth.changepassword')
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAuth.'pw1', 'label'=>$this->lB('setup.section.auth.pw1')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixAuth.'pw1', 
                    'type'=>'password',
                    'name'=>'pw1',
                    'value'=>'',
                    ), false)
                . '</div>'
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAuth.'pw1', 'label'=>$this->lB('setup.section.auth.pw2')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixAuth.'pw2', 
                    'type'=>'password',
                    'name'=>'pw2',
                    'value'=>'',
                    ), false)
                . '</div>'

            // ------------------------------------------------------------
            // setup options - database
            // ------------------------------------------------------------
        
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', array('class'=>'fa fa-database')) 
                . ' '.$this->lB('setup.section.database')
            .'</h3>'
            . $this->lB('setup.section.database.hint').'<br><br>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'type', 'label'=>$this->lB('setup.section.database.type')))
                . $oRenderer->oHtml->getFormSelect(array(
                    'id'=>$sIdPrefixDb.'type', 
                    'name'=>'options[database][database_type]',
                    'onchange'=>'changeView(\'params-dbtype\', \'params-dbtype-\'+this.value); return false;'
                    ), $aDbOptions)
            . '</div>'

            . '<div id="params-dbtype-sqlite" class="params-dbtype">'
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'file', 'label'=>$this->lB('setup.section.database.file')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixDb.'type', 
                    'name'=>'options[database][database_file]', 
                    'size'=>50, 
                    'value'=>isset($aOptions['options']['database']['database_file']) ? $aOptions['options']['database']['database_file'] : '__DIR__/data/ahcrawl.db',
                    ), false)
            . '</div>'
            . '</div>'
        
            . '<div id="params-dbtype-mysql" class="params-dbtype">'
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'server', 'label'=>$this->lB('setup.section.database.server')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixDb.'name', 
                    'name'=>'options[database][server]',
                    'value'=>isset($aOptions['options']['database']['server']) ? $aOptions['options']['database']['server'] : '',
                    ), false)
                . '</div>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'port', 'label'=>$this->lB('setup.section.database.port')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixDb.'port', 
                    'name'=>'options[database][port]',
                    'value'=>isset($aOptions['options']['database']['port']) ? $aOptions['options']['database']['port'] : '',
                    ), false)
                . '</div>'

            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'name', 'label'=>$this->lB('setup.section.database.name')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixDb.'name', 
                    'name'=>'options[database][database_name]',
                    'value'=>isset($aOptions['options']['database']['database_name']) ? $aOptions['options']['database']['database_name'] : '',
                    ), false)
                . '</div>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'username', 'label'=>$this->lB('setup.section.database.username')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixDb.'username', 
                    'name'=>'options[database][username]',
                    'value'=>isset($aOptions['options']['database']['username']) ? $aOptions['options']['database']['username'] : '',
                    ), false)
                . '</div>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'password', 'label'=>$this->lB('setup.section.database.password')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixDb.'password', 
                    'type'=>'password',
                    'name'=>'options[database][password]',
                    'value'=>isset($aOptions['options']['database']['password']) ? $aOptions['options']['database']['password'] : '',
                    ), false)
                . '</div>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'charset', 'label'=>$this->lB('setup.section.database.charset')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixDb.'charset', 
                    'name'=>'options[database][charset]',
                    'value'=>isset($aOptions['options']['database']['charset']) ? $aOptions['options']['database']['charset'] : 'utf8',
                    ), false)
                . '</div>'
            . '</div>'
        
        
            // ------------------------------------------------------------
            // setup options - backend
            // ------------------------------------------------------------
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', array('class'=>'fa fa-cogs')) 
                . ' '.$this->lB('setup.section.backend')
            .'</h3>'
            . $this->lB('setup.section.backend.hint').'<br><br>'

            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixOther.'lang', 'label'=>$this->lB('setup.section.backend.lang')))
                . $oRenderer->oHtml->getFormSelect(array(
                    'id'=>$sIdPrefixOther.'lang', 
                    'name'=>'options[lang]',
                    // 'onchange'=>'changeView(\'params-dbtype\', \'params-dbtype-\'+this.value); return false;'
                    ), $aLangOptions)
            . '</div>'

            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixOther.'menu', 'label'=>$this->lB('setup.section.backend.menu')))
                . $oRenderer->oHtml->getTag('textarea', array(
                    'id'=>$sIdPrefixOther.'menu', 
                    'name'=>'options[menu]',
                    'cols'=>50,
                    'rows'=>isset($aOptions['options']['menu']) && is_array($aOptions['options']['menu']) && count($aOptions['options']['menu']) ? count($aOptions['options']['menu'])+3 : 3 ,
                    // 'label'=>$sValueSearchCategories,
                    'label'=> json_encode($aOptions['options']['menu'], JSON_PRETTY_PRINT),
                    ), true)
                . '</div>'
            /*
            . '<div class="pure-control-group">'
                // . '<label> </label>'
                . '<label class="pure-checkbox" for="'.$sIdPrefixother.'debug">'
                . $oRenderer->oHtml->getTag('input', array(
                        'id'=>$sIdPrefixother.'debug', 
                        'type'=>'checkbox',
                        'name'=>'options[debug]',
                        'value'=>'true',
                        'checked'=>isset($aOptions['options']['debug']) && $aOptions['options']['debug'] ? 'checked' : '',
                        ))
                        .' '.$this->lB('setup.section.backend.debug')
                . '</label>'
                . '</div>'
             * 
             */
            . '<div class="pure-control-group">'
                // . '<label> </label>'
                . '<label class="pure-checkbox" for="'.$sIdPrefixOther.'debug">'
                . $oRenderer->oHtml->getTag('input', $aCbDebug, false)
                        .' '.$this->lB('setup.section.backend.debug')
                . '</label>'
                . '</div>'

            // ------------------------------------------------------------
            // setup options - crawler
            // ------------------------------------------------------------
            
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', array('class'=>'fa fa-spinner')) 
                . ' '.$this->lB('setup.section.crawler')
            .'</h3>'
            . $this->lB('setup.section.crawler.hint').'<br><br>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixCrawler.'username', 'label'=>$this->lB('setup.section.crawler.searchindex.simultanousRequests')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixCrawler.'searchindex-simultanousRequests', 
                    'name'=>'options[crawler][searchindex][simultanousRequests]',
                    'value'=>isset($aOptions['options']['crawler']['searchindex']['simultanousRequests']) ? (int)$aOptions['options']['crawler']['searchindex']['simultanousRequests'] : 2,
                    ), false)
                . '</div>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixCrawler.'password', 'label'=>$this->lB('setup.section.crawler.ressources.simultanousRequests')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixCrawler.'ressources-simultanousRequests', 
                    'name'=>'options[crawler][ressources][simultanousRequests]',
                    'value'=>isset($aOptions['options']['crawler']['ressources']['simultanousRequests']) ? (int)$aOptions['options']['crawler']['ressources']['simultanousRequests'] : 3,
                    ), false)
                . '</div>'
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixSearchindex.'regexToRemove', 'label'=>$this->lB('setup.section.searchindex.regexToRemove')))
                . $oRenderer->oHtml->getTag('textarea', array(
                    'id'=>$sIdPrefixSearchindex.'regexToRemove', 
                    'name'=>'options[searchindex][regexToRemove]',
                    'cols'=>50,
                    'rows'=>isset($aOptions['options']['searchindex']['regexToRemove']) && is_array($aOptions['options']['menu']) && count($aOptions['options']['searchindex']['regexToRemove']) ? count($aOptions['options']['searchindex']['regexToRemove'])+1 : 3 ,
                    // 'label'=>$sValueSearchCategories,
                    'label'=> implode("\n", $aOptions['options']['searchindex']['regexToRemove']),
                    ), true)
                . '</div>'

            // ------------------------------------------------------------
            // setup options - analysis constants
            // ------------------------------------------------------------
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', array('class'=>'fa fa-newspaper-o')) 
                . ' '.$this->lB('setup.section.analysis')
            .'</h3>'
            . $this->lB('setup.section.analysis.hint').'<br><br>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAnalyis.'MinTitleLength', 'label'=>$this->lB('setup.section.analysis.MinTitleLength')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixAnalyis.'MinTitleLength', 
                    'name'=>'options[analysis][MinTitleLength]',
                    'value'=>isset($aOptions['options']['analysis']['MinTitleLength']) && $aOptions['options']['analysis']['MinTitleLength'] ? $aOptions['options']['analysis']['MinTitleLength'] : 20,
                    ), false)
                . '</div>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAnalyis.'MinTitleLength', 'label'=>$this->lB('setup.section.analysis.MinDescriptionLength')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixAnalyis.'MinDescriptionLength', 
                    'name'=>'options[analysis][MinDescriptionLength]',
                    'value'=>isset($aOptions['options']['analysis']['MinDescriptionLength']) && $aOptions['options']['analysis']['MinDescriptionLength'] ? $aOptions['options']['analysis']['MinDescriptionLength'] : 40,
                    ), false)
                . '</div>'
       
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAnalyis.'MinKeywordsLength', 'label'=>$this->lB('setup.section.analysis.MinKeywordsLength')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixAnalyis.'MinKeywordsLength', 
                    'name'=>'options[analysis][MinKeywordsLength]',
                    'value'=>isset($aOptions['options']['analysis']['MinKeywordsLength']) && $aOptions['options']['analysis']['MinKeywordsLength'] ? $aOptions['options']['analysis']['MinKeywordsLength'] : 10,
                    ), false)
                . '</div>'
       
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAnalyis.'MaxPagesize', 'label'=>$this->lB('setup.section.analysis.MaxPagesize')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixAnalyis.'MaxPagesize', 
                    'name'=>'options[analysis][MaxPagesize]',
                    'value'=>isset($aOptions['options']['analysis']['MaxPagesize']) && $aOptions['options']['analysis']['MaxPagesize'] ? $aOptions['options']['analysis']['MaxPagesize'] : 150000,
                    ), false)
                . '</div>'
       
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixAnalyis.'MaxLoadtime', 'label'=>$this->lB('setup.section.analysis.MaxLoadtime')))
                . $oRenderer->oHtml->getTag('input', array(
                    'id'=>$sIdPrefixAnalyis.'MaxLoadtime', 
                    'name'=>'options[analysis][MaxLoadtime]',
                    'value'=>isset($aOptions['options']['analysis']['MaxLoadtime']) && $aOptions['options']['analysis']['MaxLoadtime'] ? $aOptions['options']['analysis']['MaxLoadtime'] : 500,
                    ), false)
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
            . '<br><hr><br>'
            .($this->_configExists()
                ? $oRenderer->oHtml->getTag('button', array('label'=>$this->_getIcon('button.save') . $this->lB('button.save'), 'class'=>'pure-button button-secondary'))
                : $oRenderer->oHtml->getTag('button', array('label'=>$this->_getIcon('button.create') . $this->lB('button.create'), 'class'=>'pure-button button-success'))
            )

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