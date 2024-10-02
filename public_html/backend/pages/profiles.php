<?php
/**
 * page searchindex :: profiles
 */
$oRenderer=new ressourcesrenderer($this->_sTab);

$aOptions = $this->_loadConfigfile();
// $aOptions = ['options'=>$this->getEffectiveOptions()];
// TODO ?
// $aOptions['profiles'][currentid] = $this->getEffectiveProfile();
// echo '<pre>options: '.print_r($aOptions['profiles'], 1).'</pre><br>';

$sReturn = '';
$aTbl = [];
$sBtnBack='<br>'.$oRenderer->oHtml->getTag('button',[
    'href' => '#',
    'class' => 'pure-button button-secondary',
    'onclick' => 'history.back(); return false;',
    'title' => $this->lB('button.back.hint'),
    'label' => $this->lB('button.back'),
]);

$iSizeInInput=72;
$iColsInTA=70;

$sPatternNumber='^[0-9]*';

// ----------------------------------------------------------------------
// handle POST DATA
// ----------------------------------------------------------------------

// add profiles navigation
$sReturn.=$this->_getNavi2($this->_getProfiles(), true, '');

if(isset($_POST['action'])){
    // $sReturn.='DEBUG: <pre>POST '.print_r($_POST, 1).'</pre>';
    $aNewProfile=$_POST;
    $iProfileId=(int)$_POST['profile'];
    unset($aNewProfile['action']);
    unset($aNewProfile['profile']);
    
    switch($_POST['action']){
        case 'deleteprofile':
                $sReturn.= $oRenderer->renderMessagebox(sprintf($this->lB('profile.delete.confirm'), htmlentities($aNewProfile['label'])), 'warning')
                        .'<br><form class="" method="POST" action="?'.$_SERVER['QUERY_STRING'].'">' 
                        . $oRenderer->oHtml->getTag('input', [
                            'type'=>'hidden',
                            'name'=>'profile',
                            'value'=>$iProfileId,
                            ], false)
                        . $oRenderer->oHtml->getTag('input', [
                            'type'=>'hidden',
                            'name'=>'label',
                            'value'=> htmlentities($aNewProfile['label']),
                            ], false)
                        .$sBtnBack
                        .' '
                        .$oRenderer->oHtml->getTag('button',[
                            'href' => '#',
                            'class'=>'pure-button button-error',
                            'name'=>'action',
                            'label'=>$this->_getIcon('button.delete') . $this->lB('button.delete'), 
                            'value' => 'deleteprofileconfirmed',
                            
                        ])
                        .'</form>'
                        ;
                return $sReturn;
            break;;
            
        case 'deleteprofileconfirmed':
            if(!isset($aOptions['profiles'][$iProfileId])){
                $sReturn.= $oRenderer->renderMessagebox(sprintf($this->lB('profile.warning.wrongprofile'), $iProfileId), 'error')
                        .$sBtnBack;
                return $sReturn;
            }
            
            // --------------------------------------------------
            // delete data
            // --------------------------------------------------
            
            $this->flushData(['full'], $iProfileId);
            $this->logfileDelete();
            require_once __DIR__ . '/../../vendor/ahcache/cache.class.php';
            $oCache = new AhCache($this->getCacheModule());
            $oCache->deleteModule(false);
        
            // --------------------------------------------------
            // SAVE
            // --------------------------------------------------
           
            unset($aOptions['profiles'][$iProfileId]);
            // $sReturn.='<pre>new options: '.print_r($aOptions, 1).'</pre>';
            if ($this->_saveConfig($aOptions)){
                $sReturn.= $oRenderer->renderMessagebox(sprintf($this->lB('profile.delete.ok'), $aNewProfile['label']), 'ok');
                $iProfileId=false;
            } else {
                $sReturn.= $oRenderer->renderMessagebox(sprintf($this->lB('profile.delete.error'), $aNewProfile['label']), 'error');
            }
            break;;
        // set all aoptions
        case 'setprofile':
            
            // --------------------------------------------------
            // checks
            // --------------------------------------------------
            if(!$aNewProfile['label']){
                $sReturn.= $oRenderer->renderMessagebox(sprintf($this->lB('profile.warning.nolabel'), $iProfileId), 'error')
                        .$sBtnBack;
                return $sReturn;
            }

            if(!$iProfileId){
                $iProfileId=(isset($aOptions['profiles']) && is_array($aOptions['profiles']) && count($aOptions['profiles'])) 
                        ? max(array_keys($aOptions['profiles']))+1
                        : 1
                    ;
            }
            // fix array values - textareas with line by line values
            $aArrays=[
                'searchindex'=>['urls2crawl','include', 'includepath', 'exclude', 'regexToRemove'],
                'frontend'=>['searchlang'],
                'ressources'=>['blacklist'],
            ];
            
            foreach($aArrays as $sIndex1=>$aSubArrays){
                foreach($aSubArrays as $sIndex2){                    
                    if(isset($aNewProfile[$sIndex1][$sIndex2]) && $aNewProfile[$sIndex1][$sIndex2]){
                        $sTaContent=$aNewProfile[$sIndex1][$sIndex2];
                        $sTaContent.=strpos($sTaContent, "\r")===false ? "\r":'';
                        $aNewProfile[$sIndex1][$sIndex2]=explode("\n", str_replace("\r", '', $sTaContent));
                    } else {
                        $aNewProfile[$sIndex1][$sIndex2]=[];
                    }
                }
            }
            // fix integer values
            $this->_configMakeInt($aNewProfile, 'searchindex.iDepth');
            $this->_configMakeInt($aNewProfile, 'searchindex.iMaxUrls');
            $this->_configMakeInt($aNewProfile, 'searchindex.simultanousRequests');
            $this->_configMakeInt($aNewProfile, 'ressources.simultanousRequests');

            // check json data in textarea
            if(isset($aNewProfile['frontend']['searchcategories']) 
                    && $aNewProfile['frontend']['searchcategories']
                    && json_decode($aNewProfile['frontend']['searchcategories'])
            ){
                $aNewProfile['frontend']['searchcategories'] = json_decode($aNewProfile['frontend']['searchcategories']);
            } else {
                $aNewProfile['frontend']['searchcategories'] = [];
            }
                    
            // --------------------------------------------------
            // new profile image
            // --------------------------------------------------
            $aNewProfile['profileimagedata']=$aNewProfile['profileimagedatacurrent'];
            $sPostedImg = false;
            
            if(isset($aNewProfile['profileimagedatanew']) && $aNewProfile['profileimagedatanew']=='DELETE'){
                $aNewProfile['profileimagedata']='';
            }
            
            
            // pasted image
            if(isset($aNewProfile['profileimagedatanew']) && $aNewProfile['profileimagedatanew'] && strlen($aNewProfile['profileimagedatanew'])>100){
                $sPostedImg=base64_decode(str_replace('data:image/png;base64,', '', $aNewProfile['profileimagedatanew']));
            }
            
            // uploaded image (has priority over pasted image)
            // echo '<pre>'.print_r($_FILES, 1).'</pre>'; die();
            if(isset($_FILES['profileimagefile']['tmp_name']) && $_FILES['profileimagefile']['tmp_name']){
                $sPostedImg = file_get_contents($_FILES['profileimagefile']['tmp_name']);
            }

            if($sPostedImg){
                $sTempImage = imagecreatefromstring($sPostedImg);
                $iMaxDimension=600;
                list($iWidthOrig, $iHeightOrig) = getimagesizefromstring($sPostedImg);

                if ($iWidthOrig<=$iMaxDimension && $iHeightOrig<=$iMaxDimension){
                    $sSmallImage=$sTempImage;
                } else {
                    
                    if($iWidthOrig>$iHeightOrig){
                        $iWidthNew=$iMaxDimension;
                        $iHeightNew=round($iWidthNew/$iWidthOrig*$iHeightOrig);
                    } else {
                        $iHeightNew=$iMaxDimension;
                        $iWidthNew=round($iHeightNew/$iHeightOrig*$iWidthOrig);
                    }

                    $sSmallImage = imagecreatetruecolor($iWidthNew, $iHeightNew);
                    imagecopyresampled($sSmallImage, $sTempImage, 0, 0, 0, 0, $iWidthNew, $iHeightNew, $iWidthOrig, $iHeightOrig);
                    // imagecopyresized($sSmallImage, $sTempImage, 0, 0, 0, 0, $iWidthNew, $iHeightNew, $iWidthOrig, $iHeightOrig);
                }

                ob_start();
                    imagejpeg($sSmallImage, NULL, 65);
                    $sImageBase64 = base64_encode(ob_get_contents());
                ob_end_clean();
                $aNewProfile['profileimagedata']='data:image/jpg;base64,'.$sImageBase64;
            }
            unset($aNewProfile['profileimagedatanew']);
            
            // --------------------------------------------------
            // SAVE
            // --------------------------------------------------
           
            $aOptions['profiles'][$iProfileId]=$aNewProfile;
            if ($this->_saveConfig($aOptions)){
                $sReturn.= $oRenderer->renderMessagebox(sprintf($this->lB('profile.save.ok'), $aNewProfile['label']), 'ok');
                
            } else {
                $sReturn.= $oRenderer->renderMessagebox(sprintf($this->lB('profile.save.error'), $aNewProfile['label']), 'error');
            }
            break;
            ;;
        default: 
            $sReturn.= $oRenderer->renderMessagebox('ERRROR: unknown action ['.htmlentities($_POST['action']).'] :-/ skipping ... just in case', 'warning');
    }
    
    $sNextUrl=$_SERVER['QUERY_STRING'];
    $sNextUrl=preg_replace('/\&siteid=add/' , '', $sNextUrl);
    $sNextUrl=preg_replace('/\&siteid=[0-9]*/' , '', $sNextUrl);
    $sNextUrl.='&siteid='.$iProfileId;
    $sReturn.='<hr><br>'
        .$oRenderer->oHtml->getTag('a',[
            'href' => '?'.$sNextUrl,
            'class' => 'pure-button button-secondary',
            'title' => $this->lB('button.continue.hint'),
            'label' => $this->lB('button.continue'),
        ]);
    return $sReturn;
//    
}

// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------
$sSubmit='<br><br>'
. ($this->_sTab==='add'
    ? $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.create') . $this->lB('button.create'), 'class'=>'pure-button button-success'])
    : $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.save') . $this->lB('button.save'), 'class'=>'pure-button button-secondary'])
        // .' '
        // .$oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.delete') . $this->lB('button.delete'), 'class'=>'pure-button button-error', 'name'=>'action', 'value'=>'deleteprofile'])
)
;

$sReturn.=(!isset($_SERVER['HTTPS'])
            ? $oRenderer->renderMessagebox($this->lB('setup.error-no-ssl'), 'warning').'<br><br>'
            : ''
        )
            /*
            . ($this->_sTab==='add'
                ? '<h3>'.$this->lB('profile.new.searchprofile') . '</h3>'
                : '' 
            )
             * 
             */
            
        ;
$this->setSiteId($this->_sTab);
// $sReturn.='<pre>' . print_r($this->aProfile, 1) . '</pre>';


$sValueSearchCategories='';
if(isset($this->aProfileSaved['searchcategories']) && count($this->aProfileSaved['searchcategories'])){
    foreach($this->aProfileSaved['searchcategories'] as $sKey=>$value){
        $sValueSearchCategories.=$sKey.': "'.$value.'"' . "\n";
    }
}

$sReturn.='
        <br>
        <form class="pure-form pure-form-aligned" method="POST"  enctype="multipart/form-data" action="?'.$_SERVER['QUERY_STRING'].'">
            '
            . $oRenderer->oHtml->getTag('input', [
                'type'=>'hidden',
                'name'=>'action',
                'value'=>'setprofile',
            ], false)
            . $oRenderer->oHtml->getTag('input', [
                'type'=>'hidden',
                'name'=>'profile',
                'value'=>$this->_sTab,
            ], false)
        
            // ------------------------------------------------------------
            // metadata
            // ------------------------------------------------------------
            
            .$oRenderer->renderExtendedView()
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', ['class'=>'fa fa-user']) 
                . ' '.$this->lB('profile.section.metadata')
            .'</h3>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>'label', 'label'=>$this->lB('profile.label')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'text',
                    'id'=>'label', 
                    'name'=>'label',
                    'size'=>$iSizeInInput,
                    'value'=>isset($this->aProfileSaved['label']) ? $this->aProfileSaved['label'] : '',
                    ], false)
                . '</div>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>'description', 'label'=>$this->lB('profile.description')])
                . $oRenderer->oHtml->getTag('textarea', [
                    'id'=>'description', 
                    'name'=>'description',
                    'cols'=>$iColsInTA,
                    'rows'=>3,
                    'label'=>isset($this->aProfileSaved['description']) ? $this->aProfileSaved['description'] : '',
                    ], true)
                . '</div>'

            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>'profileimagedata', 'label'=>$this->lB('profile.image')])
                . '<div id="myimagediv">'
                    . ($this->getProfileImage() 
                                ? ''
                                    . $this->getProfileImage() 
                                    . '<br>'
                                    . $oRenderer->oHtml->getTag('button', [
                                        'label'=>$this->_getIcon('button.delete') . $this->lB('button.delete'), 
                                        'class'=>'pure-button button-error',
                                        'id'=>'profileimagedelete',
                                      ])
                                    . '<br><br>'
                        
                                : '. . .'
                      )
                    . '</div>'
                . '</div>'
            . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['label'=>''])
                . '<div>'
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'hidden', 
                        'name'=>'profileimagedatacurrent', 
                        'placeholder'=>'',
                        'value'=>isset($this->aProfileSaved['profileimagedata']) ? $this->aProfileSaved['profileimagedata'] : '',
                        ], true)
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'hidden', 
                        'id'=>'profileimagedata', 
                        'name'=>'profileimagedatanew', 
                        'placeholder'=>'',
                        'value'=>'',
                        ], true)
                    . $oRenderer->oHtml->getTag('div', [
                        'id'=>'profileimageinserter', 
                        'class'=>'insertimage', 
                        'contentEditable'=>'true',
                        'label'=>$this->lB('profile.image.add'),
                        ], true)
                . '</div>'
                . '<br>'
                . '</div>'
            . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['label'=>''])
                . '<div>'
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'file', 
                        'id'=>'profileimagefile', 
                        'name'=>'profileimagefile', 
                        'placeholder'=>'',
                        'accept'=>'image/*',
                        'value'=>'',
                        ], true)
                . '</div>'

            // ------------------------------------------------------------
            // search index
            // ------------------------------------------------------------
            . $sSubmit
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', ['class'=>'fa fa-user']) 
                . ' '.$this->lB('profile.section.searchindex')
            .'</h3>'
        
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>'searchindex-urls2crawl', 'label'=>$this->lB('profile.searchindex.urls2crawl')])
                . $oRenderer->oHtml->getTag('textarea', [
                    'id'=>'searchindex-urls2crawl', 
                    'name'=>'searchindex[urls2crawl]',
                    'cols'=>$iColsInTA,
                    'rows'=>isset($this->aProfileSaved['searchindex']['urls2crawl']) && count($this->aProfileSaved['searchindex']['urls2crawl']) ? count($this->aProfileSaved['searchindex']['urls2crawl'])+1 : 3 ,
                    'label'=>isset($this->aProfileSaved['searchindex']['urls2crawl']) && count($this->aProfileSaved['searchindex']['urls2crawl']) ? implode("\n", $this->aProfileSaved['searchindex']['urls2crawl']) : '',
                    ], true)
                . '</div>'
            . '<div class="pure-control-group">'
                . $oRenderer->oHtml->getTag('label', ['for'=>'searchindex-iMaxUrls', 'label'=>$this->lB('profile.searchindex.iMaxUrls')])
                . $oRenderer->oHtml->getTag('input', [
                    'type'=>'text',
                    'id'=>'searchindex-iMaxUrls', 
                    'name'=>'searchindex[iMaxUrls]',
                    'size'=>$iSizeInInput,
                    'pattern'=>$sPatternNumber,
                    'placeholder'=>$this->aProfileDefault['searchindex']['iMaxUrls'],
                    'value'=>isset($this->aProfileSaved['searchindex']['iMaxUrls']) ? (int)$this->aProfileSaved['searchindex']['iMaxUrls'] : $this->aProfileDefault['searchindex']['iMaxUrls'],
                    ], false)
                . '</div>'
            . '<div class="hintextended">'.$this->lB('hint.extended').'</div>'
            . '<div class="extended">'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>'searchindex-include', 'label'=>$this->lB('profile.searchindex.include')])
                    . $oRenderer->oHtml->getTag('textarea', [
                        'id'=>'searchindex-include', 
                        'name'=>'searchindex[include]',
                        'cols'=>$iColsInTA,
                        'rows'=>isset($this->aProfileSaved['searchindex']['include']) && count($this->aProfileSaved['searchindex']['include']) ? count($this->aProfileSaved['searchindex']['include'])+1 : 3 ,
                        'label'=>isset($this->aProfileSaved['searchindex']['include']) && count($this->aProfileSaved['searchindex']['include']) ? implode("\n", $this->aProfileSaved['searchindex']['include']) : '',
                        ], true)
                    . '</div>'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>'searchindex-includepath', 'label'=>$this->lB('profile.searchindex.includepath')])
                    . $oRenderer->oHtml->getTag('textarea', [
                        'id'=>'searchindex-includepath', 
                        'name'=>'searchindex[includepath]',
                        'cols'=>$iColsInTA,
                        'rows'=>isset($this->aProfileSaved['searchindex']['includepath']) && count($this->aProfileSaved['searchindex']['includepath']) ? count($this->aProfileSaved['searchindex']['includepath'])+1 : 3 ,
                        'label'=>isset($this->aProfileSaved['searchindex']['includepath']) && count($this->aProfileSaved['searchindex']['includepath']) ? implode("\n", $this->aProfileSaved['searchindex']['includepath']) : '',
                        ], true)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>'searchindex-exclude', 'label'=>$this->lB('profile.searchindex.exclude')])
                    . $oRenderer->oHtml->getTag('textarea', [
                        'id'=>'searchindex-exclude', 
                        'name'=>'searchindex[exclude]',
                        'cols'=>$iColsInTA,
                        'rows'=>isset($this->aProfileSaved['searchindex']['exclude']) && count($this->aProfileSaved['searchindex']['exclude']) ? count($this->aProfileSaved['searchindex']['exclude'])+1 : 3 ,
                        'label'=>isset($this->aProfileSaved['searchindex']['exclude']) && count($this->aProfileSaved['searchindex']['exclude']) ? implode("\n", $this->aProfileSaved['searchindex']['exclude']) : '',
                        ], true)
                    . '</div>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>'searchindex-iDepth', 'label'=>$this->lB('profile.searchindex.iDepth')])
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'number',
                        'id'=>'searchindex-iDepth', 
                        'name'=>'searchindex[iDepth]',
                        'size'=>$iSizeInInput,
                        'step'=>1,
                        'pattern'=>$sPatternNumber,
                        'placeholder'=>$this->aProfileDefault['searchindex']['iDepth'],
                        'value'=>isset($this->aProfileSaved['searchindex']['iDepth']) ? $this->aProfileSaved['searchindex']['iDepth'] : '',
                        ], false)
                    . '</div>'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>'userpwd', 'label'=>$this->lB('profile.userpwd')])
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'text',
                        'id'=>'userpwd', 
                        'name'=>'userpwd',
                        'size'=>$iSizeInInput,
                        'placeholder'=>'',
                        'value'=>isset($this->aProfileSaved['userpwd']) ? $this->aProfileSaved['userpwd'] : '',
                        ], false)
                    . '</div>'
        
                . '<p>' . $this->lB('profile.ignore-Description') . '</p>'

                . '<div class="pure-control-group">'
                    // . $oRenderer->oHtml->getTag('label', ['for'=>'userpwd', 'label'=>$this->lB('profile.userpwd')])
                    . $oRenderer->oHtml->getTag('label', ['for'=>'searchindex-ignoreNoindex', 'label'=>$this->lB('profile.ignoreNoindex')])
                    . '<div>'
                        . '<label for="searchindex-ignoreNoindex" class="align-left">'
                        . '<input type="checkbox" name="searchindex[ignoreNoindex]" value="true" id="searchindex-ignoreNoindex"'.(isset($this->aProfileSaved['searchindex']['ignoreNoindex']) && $this->aProfileSaved['searchindex']['ignoreNoindex'] ? ' checked="checked"' : '').' />'
                        . ' '.$this->lB('profile.ignoreNoindex.description')
                        . '</label>'
                    . '</div>'
                    . '</div>'
                . '<div class="pure-control-group">'
                    // . $oRenderer->oHtml->getTag('label', ['for'=>'userpwd', 'label'=>$this->lB('profile.userpwd')])
                    . $oRenderer->oHtml->getTag('label', ['for'=>'searchindex-ignoreNoindex', 'label'=>$this->lB('profile.ignoreNofollow')])
                    . '<div>'
                        . '<label for="searchindex-ignoreNofollow" class="align-left">'
                        . '<input type="checkbox" name="searchindex[ignoreNofollow]" value="true" id="searchindex-ignoreNofollow"'.(isset($this->aProfileSaved['searchindex']['ignoreNofollow']) && $this->aProfileSaved['searchindex']['ignoreNofollow'] ? ' checked="checked"' : '').' />'
                        . ' '.$this->lB('profile.ignoreNofollow.description')
                        . '</label>'
                    . '</div>'
                    . '</div>'

                . '<p>' . $this->lB('profile.overrideDefaults') . '</p>'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', [
                        'for'=>'searchindex-simultanousRequests', 
                        'label'=>sprintf($this->lB('profile.searchindex.simultanousRequests'), $aOptions['options']['crawler']['searchindex']['simultanousRequests'])
                        ])
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'number',
                        'id'=>'searchindex-simultanousRequests', 
                        'name'=>'searchindex[simultanousRequests]',
                        'size'=>$iSizeInInput,
                        'step'=>1,
                        'pattern'=>$sPatternNumber,
                        'placeholder'=>isset($aOptions['options']['crawler']['searchindex']['simultanousRequests']) ? $aOptions['options']['crawler']['searchindex']['simultanousRequests'] : '',
                        'value'=>isset($this->aProfileSaved['searchindex']['simultanousRequests']) ? $this->aProfileSaved['searchindex']['simultanousRequests'] : '',
                        ], false)
                    . '</div>'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>'searchindex-regexToRemove', 'label'=>$this->lB('profile.searchindex.regexToRemove')])
                    . $oRenderer->oHtml->getTag('textarea', [
                        'id'=>'searchindex-regexToRemove', 
                        'name'=>'searchindex[regexToRemove]',
                        'cols'=>$iColsInTA,
                        'placeholder'=>implode("\n", $aOptions['options']['searchindex']['regexToRemove']),
                        'rows'=>isset($this->aProfileSaved['searchindex']['regexToRemove']) && count($this->aProfileSaved['searchindex']['regexToRemove']) ? count($this->aProfileSaved['searchindex']['regexToRemove'])+1 : 3 ,
                        'label'=>isset($this->aProfileSaved['searchindex']['regexToRemove']) && count($this->aProfileSaved['searchindex']['regexToRemove']) ? implode("\n", $this->aProfileSaved['searchindex']['regexToRemove']) : '',
                        ], true)
                    . '</div>'
            . '</div>'
            // ------------------------------------------------------------
            // search frontend
            // ------------------------------------------------------------
            
            . $sSubmit
            . '<h3>'
                // . $oRenderer->oHtml->getTag('i', ['class'=>'fa fa-user']) 
                . ' '.$this->lB('profile.section.frontend')
            .'</h3>'
            . '<div class="hintextended">'.$this->lB('hint.extended').'</div>'
            . '<div class="extended">'

                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>'frontend-searchcategories', 'label'=>$this->lB('profile.frontend.searchcategories')])
                    . $oRenderer->oHtml->getTag('textarea', [
                        'id'=>'frontend-searchcategories', 
                        'name'=>'frontend[searchcategories]',
                        'cols'=>$iColsInTA,
                        'rows'=>isset($this->aProfileSaved['frontend']['searchcategories']) && is_array($this->aProfileSaved['frontend']['searchcategories']) && count($this->aProfileSaved['frontend']['searchcategories']) ? count($this->aProfileSaved['frontend']['searchcategories'])+3 : 3 ,
                        // 'label'=>$sValueSearchCategories,
                        'label'=> (isset($this->aProfileSaved['frontend']['searchcategories']) 
                                ? json_encode($this->aProfileSaved['frontend']['searchcategories'], JSON_PRETTY_PRINT) 
                                : ''
                            ),
                            ], true)
                    . '</div>'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>'frontend-searchlang', 'label'=>$this->lB('profile.frontend.searchlang')])
                    . $oRenderer->oHtml->getTag('textarea', [
                        'id'=>'frontend-searchlang', 
                        'name'=>'frontend[searchlang]',
                        'cols'=>$iColsInTA,
                        'rows'=>isset($this->aProfileSaved['frontend']['searchlang']) && count($this->aProfileSaved['frontend']['searchlang']) ? count($this->aProfileSaved['frontend']['searchlang'])+1 : 3 ,
                        'label'=>isset($this->aProfileSaved['frontend']['searchlang']) && count($this->aProfileSaved['frontend']['searchlang']) ? implode("\n", $this->aProfileSaved['frontend']['searchlang']) : '',
                        ], true)
                    . '</div>'
            . '</div>'

            // ------------------------------------------------------------
            // ressources scan
            // ------------------------------------------------------------
            . $sSubmit
            . '<h3>'
                    // . $oRenderer->oHtml->getTag('i', ['class'=>'fa fa-user']) 
                    . ' '.$this->lB('profile.section.ressources')
                .'</h3>'
            . '<div class="hintextended">'.$this->lB('hint.extended').'</div>'
            . '<div class="extended">'

                . '<p>' . $this->lB('profile.overrideDefaults') . '</p>'
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', [
                        'for'=>'ressources-simultanousRequests', 
                        'label'=>sprintf($this->lB('profile.ressources.simultanousRequests'), $aOptions['options']['crawler']['ressources']['simultanousRequests'])
                    ])
                    . $oRenderer->oHtml->getTag('input', [
                        'type'=>'number',
                        'id'=>'ressources-simultanousRequests', 
                        'name'=>'ressources[simultanousRequests]',
                        'size'=>$iSizeInInput,
                        'step'=>1,
                        'pattern'=>$sPatternNumber,
                        'placeholder'=>isset($aOptions['options']['crawler']['ressources']['simultanousRequests']) ? $aOptions['options']['crawler']['ressources']['simultanousRequests'] : '',
                        'value'=>isset($this->aProfileSaved['ressources']['simultanousRequests']) ? $this->aProfileSaved['ressources']['simultanousRequests'] : '',
                        ], false)
                . '</div>'

                .'<br>'
                .'<br>'
                // deny list
                . '<div class="pure-control-group">'
                    . $oRenderer->oHtml->getTag('label', ['for'=>'ressources-blacklist', 'label'=>$this->lB('profile.ressources.denylist')])
                    . $oRenderer->oHtml->getTag('textarea', [
                        'id'=>'ressources-blacklist', 
                        'name'=>'ressources[blacklist]',
                        'cols'=>$iColsInTA,
                        'rows'=>isset($this->aProfileSaved['ressources']['blacklist'])  && count($this->aProfileSaved['ressources']['blacklist'])  ? count($this->aProfileSaved['ressources']['blacklist'])+1      : 3 ,
                        'label'=>isset($this->aProfileSaved['ressources']['blacklist']) && count($this->aProfileSaved['ressources']['blacklist']) ? implode("\n", $this->aProfileSaved['ressources']['blacklist']) : '',
                        ], true)
                    . '</div>'
            . '</div>'

            // ------------------------------------------------------------
            // submit
            // ------------------------------------------------------------
            . '<br><hr><br>'
            . $sSubmit

            . ($this->_sTab==='add'
                    ? '' : ' ' . $oRenderer->oHtml->getTag('button', ['label'=>$this->_getIcon('button.delete') . $this->lB('button.delete'), 'class'=>'pure-button button-error', 'name'=>'action', 'value'=>'deleteprofile'], true)
                    
                )
        
            .'</form>'
        
            ;

/*
// foreach ($this->_getProfileConfig($this->_sTab) as $sVar => $val) {
foreach ($this->aProfile as $sVar => $val) {

    $sTdVal = '';
    if (is_[$val]){
        foreach($val as $sKey=>$subvalue){
            $sTdVal .= '<span class="key2">'.$sKey.'</span>:<br>'
                    .((is_array($subvalue)) ? ' - <span class="value">' . implode('</span><br> - <span class="value">', $subvalue) : '<span class="value">'.$subvalue.'</span>')
                    .'</span><br><br>'
                    ;                    
        }
    } else {
        $sTdVal .= (is_array($val)) ? '<span class="value">'.implode('</span><br> - <span class="value">', $val).'</span>' : '<span class="value">'.$val.'</span>';
    }

    $aTbl[] = [$this->lB("profile." . $sVar], '<span class="key">'.$sVar.'</span>', $sTdVal);
}
$sReturn.=$this->_getSimpleHtmlTable($aTbl);
 * 
 */


/*
$sReturn.='<h3>' . $this->lB('rawdata') . '</h3>'
        . '<pre>' . print_r($this->_getProfileConfig($this->_sTab), 1) . '</pre>';
;
 * 
 */
return $sReturn;
