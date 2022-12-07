<?php
/**
 * INSTALLER
 */

require_once __DIR__ . '/../../classes/ahwi-installer.class.php';

$sReturn='';
$oRenderer=new ressourcesrenderer();

$sLanguage=$this->sLang;
$iStep=$this->_getRequestParam('step') ? (int)$this->_getRequestParam('step') : 0;

// $this->setLangBackend($sLanguage);
$aSteps=array(
    array('lang'),
    array('requirements'),
    array('database'),
    array('done'),
    array('start'),
);

$iNextStep=(int)($iStep+1);
if(($iNextStep)===count($aSteps)){
    header('Location: ?');
}
$iLastStep=(int)($iStep-1);

$aLast=$iLastStep >=0 ? array(
    'url'=>'?page=installer&lang='.$sLanguage.'&step='.$iLastStep,
    'label'=>sprintf($this->lB('installer.back'), $this->lB('installer.'.$aSteps[$iLastStep][0])),
) : array();
$aNext=$iStep < count($aSteps)-1 ? array(
    'url'=>'?page=installer&lang='.$sLanguage.'&step='.$iNextStep,
    'label'=>sprintf($this->lB('installer.continue'), $this->lB('installer.'.$aSteps[$iNextStep][0])),
) : array();
$aOptions = array();

$sIdPrefixDb='options-database-';
$sIdPrefixAuth='options-auth-';


// ----------------------------------------------------------------------
// handle POST vars ..
// ----------------------------------------------------------------------
if(isset($_POST) && is_array($_POST) && count($_POST)){
    // $sReturn.='DEBUG: <pre>POST '.print_r($_POST, 1).'</pre>';
    // $sReturn.='DEBUG: <pre>options '.print_r($aOptions, 1).'</pre>';
    $aOptions['options']=array_merge($this->getEffectiveOptions(), $_POST['options']);
    $aOptions['options']['lang']=$sLanguage;
    $this->_configMakeInt($aOptions, 'options.database.port');
    
    // --------------------------------------------------
    // check database access
    // --------------------------------------------------
    try{
        $oDbtest=new Medoo\Medoo($this->_getRealDbConfig($aOptions['options']['database']));
        if ($this->_saveConfig($aOptions)){
            header('Location: '.$aNext['url']);
        }
        
    } catch (Exception $ex) {
        $sReturn.= $oRenderer->renderMessagebox($this->lB('setup.program.save.error.wrong-dbsettings'), 'error')
            .(isset($oDbtest) ? print_r($oDbtest->error, 1) : '')
        ;
    }
    
}

// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------


$sReturn.='<h3>'.$this->lB('installer.'.$aSteps[$iStep][0]).'</h3>'
        .'<p>'.$this->lB('installer.'.$aSteps[$iStep][0].'.hint').'</p>';

switch ($iStep) {
    case 0:
        // ----------------------------------------------------------------------
        // select language
        // ----------------------------------------------------------------------
        $aLanguages=$this->getLanguages('backend');
        if(!$this->_getRequestParam('lang') || !$iStep){
            $sReturn.='<p>';
            foreach($aLanguages as $sLangOption=>$sLangname){
                $aLangOptions[$sLangOption]=array(
                    'label'=>$sLangname,
                    'value'=>$sLangOption,
                );
                $sReturn.=$oRenderer->oHtml->getTag('a',array(
                    'href' => '?page=installer&lang='.$sLangOption,
                    'class' => 'pure-button' . ($sLangOption===$sLanguage ? ' button-secondary' : ''),
                    'title' => $sLangname,
                    'label' => $sLangname,
                )).'<br><br>'
                ;
            }
            $sReturn.='</p>'
                    . '<hr>'
                    ;
            // return $sReturn;
        }
        break;
    case 1:
        // ----------------------------------------------------------------------
        // requirements
        // ----------------------------------------------------------------------
        $oInstaller=new ahwi(array(
            'product'=>$this->aAbout['product'].' v'.$this->aAbout['version'],
            'source'=>'',
            'installdir'=>'',
            'tmpzip'=>'',
            'checks'=>$this->aAbout['requirements'],
        ));
        $aErr=$oInstaller->getRequirementErrors();
        $aRequirements=$oInstaller->getRequirements();
        $aTableReq=array(
            array(
                $this->lB('installer.requirement.test'),
                $this->lB('installer.requirement.result'),
            )
        );
        if(isset($aRequirements['phpversion'])){
            $aTableReq[]=array(
                sprintf($this->lB('installer.requirement.phpversion'), $aRequirements['phpversion']['required']),
                ($aRequirements['phpversion']['result'] 
                    ? $oRenderer->renderShortInfo('found'). $this->lB('installer.requirement-ok') .' ('.$aRequirements['phpversion']['value'].')'
                    : $oRenderer->renderShortInfo('miss') . $this->lB('installer.requirement-fail') .' ('.$aRequirements['phpversion']['value'].')'
                ),
            );
        }
        if(isset($aRequirements['phpextensions'])){
            foreach($aRequirements['phpextensions'] as $sModule=>$aItem){
                $aTableReq[]=array(
                    sprintf($this->lB('installer.requirement.phpextension'), $sModule),
                    ($aItem['result'] 
                        ? $oRenderer->renderShortInfo('found'). $this->lB('installer.requirement-ok')
                        : $oRenderer->renderShortInfo('miss') . $this->lB('installer.requirement-fail')
                    ),
                );
            }
            
        }
        $sReturn.=$this->_getSimpleHtmlTable($aTableReq,1)
                .'<br><p>'
                .(count($aErr)
                    ? $this->lB('installer.requirement-fail.hint')
                        .'<br><br>'
                        .$oRenderer->oHtml->getTag('a',array(
                        'href' => $_SERVER['REQUEST_URI'],
                        'class' => 'pure-button',
                        'title' => $this->lB('installer.refresh'),
                        'label' => $this->_getIcon('button.refresh').$this->lB('installer.refresh'),
                    )).'<br><br>'
                    : ''
                )
                . '</p>'
                . '<hr>'
                ;
        // $sReturn.='<pre>'.print_r($oInstaller->getRequirements(), 1).'</pre>';
        // $sReturn.='<pre>'.print_r($aErr, 1).'</pre>';
        break;
    case 2:
        // ----------------------------------------------------------------------
        // set database
        // ----------------------------------------------------------------------
        $aDbOptions=array();
        $aAllMods=get_loaded_extensions(false);
        $sDefaultDb='mysql';
        foreach(array('sqlite', 'mysql') as $sDbtype){   
            $aDbOptions[$sDbtype]=array(
                'label'=>$this->lB('setup.section.database.type.'.$sDbtype),
                'value'=>$sDbtype,
            );
        }
        $aDbOptions[$sDefaultDb]['selected']='selected';
        $aDb=array(
            'server'=>(isset($aOptions['options']['database']['server']) ? $aOptions['options']['database']['server'] : 'localhost'),
            'port'=>(isset($aOptions['options']['database']['port']) ? $aOptions['options']['database']['port'] : ''),
            'database_name'=>(isset($aOptions['options']['database']['database_name']) ? $aOptions['options']['database']['database_name'] : 'ahcrawler'),
            'username'=>(isset($aOptions['options']['database']['username']) ? $aOptions['options']['database']['username'] : 'ahcrawler'),
            'password'=>(isset($aOptions['options']['database']['password']) ? $aOptions['options']['database']['password'] : ''),
            'charset'=>(isset($aOptions['options']['database']['charset']) ? $aOptions['options']['database']['charset'] : 'utf8'),
        );
        $sReturn.= (!isset($_SERVER['HTTPS'])
                ?  $oRenderer->renderMessagebox($this->lB('setup.error-no-ssl'), 'warning').'<br><br>'
                : ''
            )
            .'<form class="pure-form pure-form-aligned" method="POST" action="?'.$_SERVER['QUERY_STRING'].'">'
                
                . $this->lB('setup.section.database.hint').'<br><br>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'type', 'label'=>$this->lB('setup.section.database.type')))
                . $oRenderer->oHtml->getFormSelect(array(
                    'id'=>$sIdPrefixDb.'type', 
                    'name'=>'options[database][database_type]',
                    'onchange'=>'changeView(\'params-dbtype\', \'params-dbtype-\'+this.value); return false;'
                    ), $aDbOptions)
            . '</div>'

            // ----- sqlite
            . '<div id="params-dbtype-sqlite" class="params-dbtype">'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', array('label'=>sprintf($this->lB('installer.requirement.phpextension'), 'pdo_sqlite')))
                    . $oRenderer->oHtml->getTag('span', array(
                        'label'=>(!array_search('pdo_sqlite', $aAllMods)===false
                                ? $oRenderer->renderShortInfo('found'). $this->lB('installer.requirement-ok')
                                : $oRenderer->renderShortInfo('miss') . $this->lB('installer.requirement-fail')
                            )
                        ))
                    . '</div>'
                    . '<br>'

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
        
            // ----- mysql
            . '<div id="params-dbtype-mysql" class="params-dbtype">'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', array('label'=>sprintf($this->lB('installer.requirement.phpextension'), 'pdo_mysql')))
                    . $oRenderer->oHtml->getTag('span', array(
                        'label'=>(!array_search('pdo_mysql', $aAllMods)===false
                                ? $oRenderer->renderShortInfo('found'). $this->lB('installer.requirement-ok')
                                : $oRenderer->renderShortInfo('miss') . $this->lB('installer.requirement-fail')
                            )
                        ))
                    . '</div>'
                . '<br>'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'server', 'label'=>$this->lB('setup.section.database.server')))
                    . $oRenderer->oHtml->getTag('input', array(
                        'id'=>$sIdPrefixDb.'name', 
                        'name'=>'options[database][server]',
                        'value'=>$aDb['server'],
                        ), false)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'port', 'label'=>$this->lB('setup.section.database.port')))
                    . $oRenderer->oHtml->getTag('input', array(
                        'id'=>$sIdPrefixDb.'port', 
                        'name'=>'options[database][port]',
                        'value'=>$aDb['port'],
                        ), false)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'name', 'label'=>$this->lB('setup.section.database.name')))
                    . $oRenderer->oHtml->getTag('input', array(
                        'id'=>$sIdPrefixDb.'name', 
                        'name'=>'options[database][database_name]',
                        'value'=>$aDb['database_name'],
                        ), false)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'username', 'label'=>$this->lB('setup.section.database.username')))
                    . $oRenderer->oHtml->getTag('input', array(
                        'id'=>$sIdPrefixDb.'username', 
                        'name'=>'options[database][username]',
                        'value'=>$aDb['username'],
                        ), false)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', array('for'=>$sIdPrefixDb.'password', 'label'=>$this->lB('setup.section.database.password')))
                    . $oRenderer->oHtml->getTag('input', array(
                        'id'=>$sIdPrefixDb.'password', 
                        'type'=>'password',
                        'name'=>'options[database][password]',
                        'value'=>$aDb['password'],
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
                . '<p>'
                . $this->lB('installer.database.mysql')
                . '<pre>'
                . 'mysql> CREATE DATABASE '.$aDb['database_name'].';<br>'
                . 'mysql> CREATE USER \''.$aDb['username'].'\'@\'[webserver]\';<br>'
                . 'mysql> GRANT ALL PRIVILEGES ON '.$aDb['database_name'].'.* To \''.$aDb['username'].'\'@\'[webserver]\' IDENTIFIED BY \'[password]\';<br>'
                . '</pre><br>'
            . '</div>'
                . '<hr>'
            . $oRenderer->oHtml->getTag('a',array(
                'href' => $aLast['url'],
                'class' => 'pure-button',
                'label' => $this->_getIcon('button.back').$aLast['label'],
                )) 
                .' '
                . $oRenderer->oHtml->getTag('button', array('label'=>$this->_getIcon('button.continue').$aNext['label'], 'class'=>'pure-button button-secondary'))
            .'</form>
                    
            <script>
                changeView(\'params-dbtype\', \'params-dbtype-'.$sDefaultDb.'\');
            </script>
                    '
            ;
        return $sReturn;
        break;
    case 3:
        // ----------------------------------------------------------------------
        // start
        // ----------------------------------------------------------------------
        $sReturn.='';
    default:
        break;
}
$sReturn.=''
    . (count($aLast) 
        ? $oRenderer->oHtml->getTag('a',array(
            'href' => $aLast['url'],
            'class' => 'pure-button',
            'label' => $this->_getIcon('button.back').$aLast['label'],
        )) .' '
        : ''
    )
    . (count($aNext) 
        ? $oRenderer->oHtml->getTag('a',array(
            'href' => $aNext['url'],
            'class' => 'pure-button button-secondary',
            'label' => $this->_getIcon('button.continue').$aNext['label'],
        )).'<br><br>'
        : ''
    )
;
return $sReturn;
