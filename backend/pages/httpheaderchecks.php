<?php
/**
 * page analysis :: Http header check
 */
$sReturn = '';
$sReturn.=$this->_getNavi2($this->_getProfiles());
$iSearchindexCount=$this->oDB->count('pages',array('siteid'=>$this->_sTab));        
if (!$iSearchindexCount) {
    return $sReturn.'<br><div class="warning">'.$this->lB('ressources.empty').'</div>';
}
$aFirstPage = $this->oDB->select(
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
if (count($aFirstPage)===0){
    return $sReturn;
}

$oHttpheader=new httpheader();
$oRenderer=new ressourcesrenderer($this->_sTab);

$sInfos=$aFirstPage[0]['header'];

$aInfos=json_decode($sInfos,1);
// _responseheader ?? --> see crawler.class - method processResponse()
$oHttpheader->setHeaderAsString($aInfos['_responseheader']);

// --- header dump
$sReturn.= '<h3>' . $this->lB('httpheader.data') . '</h3>'
        . '<p>'
        . $this->lB('httpheader.data.description').'<br><br>'
        .'<strong>'.$this->lB('httpheader.starturl').'</strong>: '
        .$aFirstPage[0]['url']
        . '</p>'
        // . '<pre>'.print_r($oHttpheader->getHeaderstring(), 1).'</pre>'
        . $oRenderer->renderHttpheaderAsTable($oHttpheader->checkHeaders());
        ;

// --- warnings
$iWarnings=0;
$sWarnings='';

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
                . '<ul class="tiles warnings">';
        foreach($aUnknownheader as $sKey=>$aHeaderitem){
            $sWarnings .= '<li><a href="#" onclick="return false;" class="tile"><br><strong>' . $aHeaderitem['var'].'</strong><br>'.$aHeaderitem['value'].'</a></li>';
            $sLegendeUnknown .='<li>'. '<pre>'.$aHeaderitem['var'].': '.$aHeaderitem['value'].'</pre></li>';
        }
        $sWarnings.= '</ul>'
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
                . '<ul class="tiles warnings">';
        foreach($aWarnheader as $sKey=>$aHeaderitem){
            $sWarnings .= '<li><a href="#" onclick="return false;" class="tile" title="'.$this->lB('httpheader.'.$sKey.'.description').'">' . $aHeaderitem['var'].'<br><strong>'.$aHeaderitem['value'].'</strong></a></li>';
            $sLegendeWarn .='<li>'
                    . $this->lB('httpheader.'.$sKey.'.description').'<pre>'.$aHeaderitem['var'].': '.$aHeaderitem['value'].'</pre><br></li>'
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

// --- security header
$aSecHeader=$oHttpheader->checkSecurityHeaders();

$sSecOk='';
$sSecMiss='';
$sLegendeSecOk='';
$sLegendeSecMiss='';
$iFoundSecHeader=0;
foreach($aSecHeader as $sVar=>$aData){
    if($aData){
        $iFoundSecHeader++;
        $sSecOk.='<li><a href="#" onclick="return false;" class="tile ok" title="'.$this->lB('httpheader.'.$sVar.'.description').'">' . $aData['var'].'<br>'.$aData['value'].'<br><strong>'.$oRenderer->renderShortInfo('found').'</strong></a></li>';
        $sLegendeSecOk.='<li>'.$oRenderer->renderShortInfo($aData ? 'found': 'miss')
                . ' <strong>' . $sVar. '</strong><br>'
                . ($aData ? '<pre>' . $aData['var'] . ': '.  $aData['value'].'</pre>' : '' )
                . $this->lB('httpheader.'.$sVar.'.description').'<br><br><br></li>'
                ;


    } else {
        $sSecMiss.='<li><a href="#" onclick="return false;" class="tile"    title="'.$this->lB('httpheader.'.$sVar.'.description').'">' . $sVar.'<br><br><strong>'.$oRenderer->renderShortInfo('miss').'</strong></a></li>';
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
return $sReturn;
