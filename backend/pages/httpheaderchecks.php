<?php
/**
 * page analysis :: Http header check
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sReturn = '';
$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');
$iRessourcesCount=$this->oDB->count('pages',array('siteid'=>$this->_sTab));        
if (!$iRessourcesCount) {
    return $sReturn.'<br>'.$this->_getMessageBox(sprintf($this->lB('ressources.empty'), $this->_sTab), 'warning');
}
$iResId=$this->_getRequestParam('id', false, 'int');
$sUrl=$this->_getRequestParam('url');

if(!$iResId){
    
    // default: detect first url in pages table
    $aPagedata = $this->oDB->select(
        'pages', 
        array('url', 'header'), 
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
            "ORDER" => array("id"=>"ASC"),
            "LIMIT" => 1
        )
    );
    if (count($aPagedata)===0){
        return $sReturn;
    }
} else {

    // with get param id: get it from ressources (not pages!)
    $aPagedata = $this->oDB->select(
        'ressources', 
        array('url', 'header'), 
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
                'id' => $iResId,
            ),
            "ORDER" => array("id"=>"ASC"),
            "LIMIT" => 1
        )
    );
    if (count($aPagedata)===0){
        $sReturn.=$this->_getMessageBox(sprintf($this->lB('httpheader.nopage-with-id') , $iResId) .'<br>' . $this->oDB->last(), 'warning');
        return $sReturn;
    }
}

$oHttpheader=new httpheader();
$oRenderer=new ressourcesrenderer($this->_sTab);

$sInfos=$aPagedata[0]['header'];

$aInfos=json_decode($sInfos,1);
// _responseheader ?? --> see crawler.class - method processResponse()
$oHttpheader->setHeaderAsString($aInfos['_responseheader']);

// ----------------------------------------------------------------------
// header dump
// ----------------------------------------------------------------------
$sReturn.= '<h3>' . $this->lB('httpheader.data') . '</h3>'
        . '<p>'
        . sprintf($this->lB('httpheader.data.description'), $aPagedata[0]['url']).'<br><br>'
        . '</p>'
        . $oRenderer->renderHttpheaderAsTable($oHttpheader->checkHeaders())
        . '<h3>' . $this->lB('httpheader.plain') . '</h3>'
        . '<pre>'. htmlentities(print_r($aInfos['_responseheader'], 1)).'</pre>'
        ;

// ----------------------------------------------------------------------
// warnings
// ----------------------------------------------------------------------
$iWarnings=0;
$sWarnings='';
$sTiles='';

    $sLegendeUnknown='';
    $sLegendeWarn='';

    // --- unknown header vars
    // $sReturn.= '<pre>'.print_r($oHttpheader->checkHeaders(),1).'</pre>';
    $aUnknownheader=$oHttpheader->checkUnknowHeaders();
    // $sReturn.= '<pre>'.print_r($aUnknownheader,1).'</pre>';
    if(is_array($aUnknownheader) && count($aUnknownheader)){
        $iWarnings+=count($aUnknownheader);

        $sWarnings.= '<p>'
            . $this->lB('httpheader.unknown.description')
            . '</p>'
                ;
        foreach($aUnknownheader as $sKey=>$aHeaderitem){
            $sTiles .= $oRenderer->renderTile('warning', $this->lB('httpheader.varfound.unknown'), $aHeaderitem['var'], $aHeaderitem['value'])
                    // .'<li><a href="#" onclick="return false;" class="tile"><br><strong>' . $aHeaderitem['var'].'</strong><br>'.$aHeaderitem['value'].'</a></li>'
                    ;
            $sLegendeUnknown .='<li>'. '<pre>['.$aHeaderitem['line'].'] '.$aHeaderitem['var'].': '.$aHeaderitem['value'].'</pre></li>';
        }
        $sWarnings.= ''
            . $oRenderer->renderTileBar($sTiles)
            . '<div style="clear: both;"></div>'
            . $this->lB('httpheader.unknown.todo')
            . '<ul>'.$sLegendeUnknown.'</ul><br>'
            ;
    }
    // --- unwanted header vars
    $aWarnheader=$oHttpheader->checkUnwantedHeaders();
    if(is_array($aWarnheader) && count($aWarnheader)){
        $iWarnings+=count($aWarnheader);
        $sWarnings.= '<p>'
            . $this->lB('httpheader.warnings.description')
            . '</p>'
                ;
        foreach($aWarnheader as $sKey=>$aHeaderitem){
            $sWarnings .= $oRenderer->renderTileBar(
                    $oRenderer->renderTile('warning', $aHeaderitem['var'], $aHeaderitem['value'])
                    );
            $sLegendeWarn .='<li>'
                    . $this->lB('httpheader.'.strtolower($aHeaderitem['var']).'.description').'<pre>['.$aHeaderitem['line'].'] '.$aHeaderitem['var'].': '.$aHeaderitem['value'].'</pre><br></li>'
                    ;
        }
        /*
        foreach($aUnknownheader as $sKey=>$aHeaderitem){
            $sReturn .= '<li><a href="#" onclick="return false;" class="tile" title="'.$this->lB('httpheader.unknown').'">' . $this->lB('httpheader.unknown').'<br><strong>'.$aHeaderitem['var'].'</strong></a></li>';
            $sLegendeWarn .='<li>'
                    . $this->lB('httpheader.'.$sKey.'.description').'<pre>'.$aHeaderitem['var'].': '.$aHeaderitem['value'].'</pre><br></li>'
                    ;
        }
         * 
         */
        $sWarnings.= '</ul>'
            . '<div style="clear: both;"></div>'
            . '<ul>'.$sLegendeWarn.'</ul>'
            ;
    } 
    $sReturn.= '<h3>' . sprintf($this->lB('httpheader.warnings'), $iWarnings) . '</h3>'
        . ($iWarnings
            ? $sWarnings
            : '<ul class="tiles warnings">'
                . '<li><a href="#" onclick="return false;" class="tile ok">' . $this->lB('httpheader.warnings.ok-label').'<br><strong>'.$this->lB('httpheader.warnings.ok').'</strong></a></li>'
                . '</ul>'
                . '<div style="clear: both;"></div>'
        )
        ;
    // $sReturn.='<pre>'.print_r($aWarnheader, 1).'</pre>';

// ----------------------------------------------------------------------
// security header
// ----------------------------------------------------------------------
$aSecHeader=$oHttpheader->checkSecurityHeaders();

$sSecOk='';
$sSecMiss='';
$sLegendeSecOk='';
$sLegendeSecMiss='';
$iFoundSecHeader=0;
foreach($aSecHeader as $sVar=>$aData){
    if($aData){
        $iFoundSecHeader++;
        // $sSecOk.='<li><a href="#" onclick="return false;" class="tile ok" title="'.$this->lB('httpheader.'.$sVar.'.description').'">' . $aData['var'].'<br>'.$aData['value'].'<br><strong>'.$oRenderer->renderShortInfo('found').'</strong></a></li>';
        $sSecOk.=$oRenderer->renderTile('ok',  $aData['var'].'<br>'.$aData['value'], $oRenderer->renderShortInfo('found'), '', '');
        $sLegendeSecOk.='<li>'.$oRenderer->renderShortInfo($aData ? 'found': 'miss')
                . ' <strong>' . $sVar. '</strong><br>'
                . ($aData ? '<pre>' . $aData['var'] . ': '.  $aData['value'].'</pre>' : '' )
                . $this->lB('httpheader.'.$sVar.'.description').'<br><br><br></li>'
                ;


    } else {
        // $sSecMiss.='<li><a href="#" onclick="return false;" class="tile"    title="'.$this->lB('httpheader.'.$sVar.'.description').'">' . $sVar.'<br><br><strong>'.$oRenderer->renderShortInfo('miss').'</strong></a></li>';
        $sSecMiss.=$oRenderer->renderTile('warning',  $sVar, $oRenderer->renderShortInfo('miss'), '', '');
        $sLegendeSecMiss.='<li>'.$oRenderer->renderShortInfo($aData ? 'found': 'miss')
                . ' <strong>' . $sVar. '</strong><br>'
                . ($aData ? '<pre>' . $aData['var'] . ': '.  $aData['value'].'</pre>' : '' )
                . $this->lB('httpheader.'.$sVar.'.description').'<br><br><br></li>'
                ;
    }
}
$sReturn.= '<h3>' . sprintf($this->lB('httpheader.securityheaders'), $iFoundSecHeader, count($aSecHeader)) . '</h3>'
    . '<p>'
        . $this->lB('httpheader.securityheaders.description').'<br>'
    . '</p>'
    . $this->_getHtmlchecksChart(count($aSecHeader), $oHttpheader->getCountBadSecurityHeaders())
    . '<ul class="tiles warnings">'
    . $sSecOk
    . $sSecMiss
    . '</ul>'
    . '<div style="clear: both;"></div>'
    . '<ul>' 
        . $sLegendeSecOk
        . $sLegendeSecMiss
    . '</ul>'
    ;

// $sStartUrl=$this->aProfile['searchindex']['urls2crawl'][$sUrl][0];^$sReturn.=$sStartUrl.'<br>';
// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------
return $sReturn;
