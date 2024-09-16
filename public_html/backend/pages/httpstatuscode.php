<?php
/**
 * page list with http status codes
 */

$sReturn='';
$aTable=[];
$oRenderer=new ressourcesrenderer($this->_sTab);
$sUnknownLabel=$this->lB('httpcode.???.label');


$sReturn.= '<h3>' . $this->lB('httpstatuscode.headline') . '</h3>'
        . '<p>'.$this->lB('httpstatuscode.infos').'</p>';

$sLastStatus='';
for ($i=0; $i<1000; $i++){
    $oHttp=new httpstatus();
    $oHttp->setHttpcode($i);
    
    $sSection=$oHttp->getStatus();
    $shttpStatusLabel=$this->lB('httpcode.'.$i.'.label', 'httpcode.???.label');
    if($shttpStatusLabel!==$sUnknownLabel){

        $shttpStatusDescr=$this->lB('httpcode.'.$i.'.descr', 'httpcode.???.descr');
        $shttpStatusTodo=$this->lB('httpcode.'.$i.'.todo', 'httpcode.???.todo');

        /*
        if($sSection!==$sLastStatus){
            $sReturn.= '<h4>'.$sLastStatus.'</h4>'
                    .(count($aTable) ? $this->_getHtmlTable($aTable) : '')
                    ;
            $aTable=[];
            $sLastStatus=$sSection;
        }
         * 
         */

        $aTable[]=['status'=>$i, 'icon'=>$oRenderer->renderValue('http_code', $i), 'label'=>$shttpStatusLabel, 'description'=>$shttpStatusDescr, 'todo'=>$shttpStatusTodo];
    }
    
}
$sTableId='httpstatuscdodes';
$sReturn.= $this->_getHtmlTable($aTable,'httpstatus-', $sTableId)
        . '<div style="clear: both;"></div>'
        ;
return $sReturn;
