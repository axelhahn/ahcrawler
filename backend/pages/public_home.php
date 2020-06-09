<?php
/**
 * ======================================================================
 * 
 * page PUBLIC home
 * 
 * ======================================================================
 */

$sReturn='';
$oRenderer=new ressourcesrenderer();
    
$aPages=$this->aOptions['menu'];
unset($aPages['home']);

// ----------------------------------------------------------------------
// render boxes for "large" navigation
// ----------------------------------------------------------------------
if(count($aPages)){
    foreach($aPages as $sPagename=>$bActive){
        
        $sReturn.=$bActive ? $this->_getLinkAsBox(array(
                    'url'=>'?page=' . $sPagename,
                    'hint'=>$this->lB('nav.' . $sPagename . '.hint'),
                    'icon'=>$this->_aIcons['menu'][$sPagename],
                    'title'=>$this->lB('nav.' . $sPagename . '.label'),
                    'text'=>$this->lB('nav.' . $sPagename . '.hint'),
                ))
                : ''
                ;
        
    }
}
if(!$sReturn){
    $sReturn=$oRenderer->renderMessagebox($this->lB('home.no-pages'), 'warning');
}
return $sReturn;
