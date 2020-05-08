<?php
/**
 * page analysis :: Http header check
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sReturn = '';
$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');

$aCountByStatuscode=$this->_getStatusinfos(array('_global'));
$iRessourcesCount=$aCountByStatuscode['_global']['ressources']['value'];
$iPagesCount=$aCountByStatuscode['_global']['pages']['value'];
if (!$iPagesCount) {
    return $sReturn.'<br>'.
        $this->_getMessageBox(
            sprintf($this->lB('status.emptyindex'), $this->_sTab),
            'warning'
        )
        ;
}
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

$sInfos=$aPagedata[0]['header'];

$aInfos=json_decode($sInfos,1);
// _responseheader ?? --> see crawler.class - method processResponse()
$oHttpheader->setHeaderAsString($aInfos['_responseheader']);

$aSecHeader=$oHttpheader->getSecurityHeaders();

// ----------------------------------------------------------------------
// tiles
// ----------------------------------------------------------------------
$aFoundTags=$oHttpheader->getExistingTags();
// print_r($aFoundTags);
$iTotalHeaders=count($oHttpheader->getHeaderAsArray());
$iSecHeader=isset($aFoundTags['security'])  ? $aFoundTags['security']  : 0;
$iUnkKnown=isset($aFoundTags['unknown'])  ? $aFoundTags['unknown']  : 0;
$iUnwanted=isset($aFoundTags['unwanted']) ? $aFoundTags['unwanted'] : 0;
$iNonStandard=isset($aFoundTags['non-standard']) ? $aFoundTags['non-standard'] : 0;
$sTiles=''
    . $this->_getTilesOfAPage()
    /*
        
    .'<div style="clear: both;"></div>'

    . $oRenderer->renderTile('', $this->lB('httpheader.header.total'), $iTotalHeaders, '')
        
    . (isset($aFoundTags['httpv1'])
        ? $oRenderer->renderTile(($aFoundTags['httpv1']+$iSecHeader===$iTotalHeaders ? 'ok' : '' ), $this->lB('httpheader.header.httpv1'), $aFoundTags['httpv1'], '', '')
        : $oRenderer->renderTile('warning', $this->lB('httpheader.header.httpv1'), 0, '', '')
      )

    . ($iUnkKnown
        ? $oRenderer->renderTile('warning', $this->lB('httpheader.header.unknown'), $aFoundTags['unknown'], (floor($aFoundTags['unknown']/$iTotalHeaders*1000)/10).'%', '#warnunknown')
        : $oRenderer->renderTile('ok',      $this->lB('httpheader.header.unknown'), 0, '', '')
      )
    . ($iUnwanted
        ? $oRenderer->renderTile('warning', $this->lB('httpheader.header.unwanted'), $iUnwanted, (floor($iUnwanted/$iTotalHeaders*1000)/10).'%', '#warnunwanted')
        : $oRenderer->renderTile('ok',      $this->lB('httpheader.header.unwanted'), 0, '', '')
      )
    . ($iNonStandard
        ? $oRenderer->renderTile('warning', $this->lB('httpheader.header.non-standard'), $iNonStandard, (floor($iNonStandard/$iTotalHeaders*1000)/10).'%', '#warnnonstandard')
        : $oRenderer->renderTile('ok',      $this->lB('httpheader.header.non-standard'), 0, '', '')
      )
        
    . (isset($aFoundTags['cache'])
        ? $oRenderer->renderTile('ok',      $this->lB('httpheader.header.cache'), $aFoundTags['cache'], '', '')
        : $oRenderer->renderTile('warning', $this->lB('httpheader.header.cache'), $oRenderer->renderShortInfo('miss'), '', '#warnnocache')
      )
    . (isset($aFoundTags['compression'])
        ? $oRenderer->renderTile('ok',      $this->lB('httpheader.header.compression'), $aFoundTags['compression'], '', '')
        : $oRenderer->renderTile('warning', $this->lB('httpheader.header.compression'), $oRenderer->renderShortInfo('miss'), '', '#warnnocompression')
      )

    . ($iSecHeader
        ? $oRenderer->renderTile('ok',      $this->lB('httpheader.header.security'), $aFoundTags['security'], '', '#securityheaders')
        : $oRenderer->renderTile('warning', $this->lB('httpheader.header.security'), $oRenderer->renderShortInfo('miss'), '', '#securityheaders')
      )
     */
    ;
        
// ----------------------------------------------------------------------
// header dump
// ----------------------------------------------------------------------
$sReturn.= '<h3>' . $this->lB('httpheader.data') . '</h3>'
        . '<p>'
        . sprintf($this->lB('httpheader.data.description'), $aPagedata[0]['url']).'<br><br>'
        . '</p>'
        . $oRenderer->renderTileBar($sTiles, '').'<div style="clear: both;"></div>'
        . $oRenderer->renderToggledContent($this->lB('httpheader.plain'),'<pre>'.htmlentities(print_r($aInfos['_responseheader'], 1)).'</pre>', false)
        . '<br>'
        . $oRenderer->renderHttpheaderAsTable($oHttpheader->parseHeaders())
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
    $aUnknownheader=$oHttpheader->getUnknowHeaders();
    if(is_array($aUnknownheader) && count($aUnknownheader)){
        $iWarnings+=$iUnkKnown;

        $sWarnings.= ''
                . '<h4 id="warnunknown">'.str_replace('<br>', ' ', $this->lB('httpheader.header.unknown')).'</h4>'
                .$this->_getMessageBox($oRenderer->renderShortInfo('warn') . $this->lB('httpheader.unknown.description'), 'warning')
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
    $aWarnheader=$oHttpheader->getUnwantedHeaders();
    if(is_array($aWarnheader) && count($aWarnheader)){
        // $iWarnings+=count($aWarnheader);
        $iWarnings+=$iUnwanted;
        $sWarnings.= ''
                . '<h4 id="warnunwanted">'.str_replace('<br>', ' ', $this->lB('httpheader.header.unwanted')).'</h4>'
                .$this->_getMessageBox($oRenderer->renderShortInfo('warn') . $this->lB('httpheader.warnings.description'), 'warning');
        foreach($aWarnheader as $sKey=>$aHeaderitem){
            $sWarnings .= $oRenderer->renderTileBar(
                    $oRenderer->renderTile('warning', $aHeaderitem['var'], $aHeaderitem['value'])
                    );
            $sLegendeWarn .='<li>'
                    . $this->lB('httpheader.'.strtolower($aHeaderitem['var']).'.description')
                    . '<pre>['.$aHeaderitem['line'].'] '.$aHeaderitem['var'].': '.$aHeaderitem['value'].'</pre><br>'
                    . '</li>'
                    ;
        }
        $sWarnings.= '</ul>'
            . '<div style="clear: both;"></div>'
            . '<ul>'.$sLegendeWarn.'</ul>'
            ;
    } 
    // --- common but non-standard header vars
    if ($iNonStandard){
        $aNonStdHeader=$oHttpheader->getNonStandardHeaders();
        $iWarnings+=$iNonStandard;
        $sWarnings.= ''
            . '<h4 id="warnnonstandard">'.$this->lB('httpheader.header.non-standard').'</h4>'
            . $this->_getMessageBox($oRenderer->renderShortInfo('warn') . $this->lB('httpheader.warnings.non-standard'), 'warning')
            . '<ul>'
            ;
        foreach($aNonStdHeader as $sKey=>$aHeaderitem){
            /*
             * TODO: add translation texts for all non-standard header variables
            $sWarnings.='<li>'
                    . $this->lB('httpheader.'.strtolower($aHeaderitem['var']).'.description')
                    . '<pre>['.$aHeaderitem['line'].'] '.$aHeaderitem['var'].': '.$aHeaderitem['value'].'</pre><br>'
                    . '</li>'
                    ;
             */
            $sWarnings.='<li><pre>['.$aHeaderitem['line'].'] '.$aHeaderitem['var'].': '.$aHeaderitem['value'].'</pre></li>';
        }
        $sWarnings.= '</ul><br>';
    }

    // --- no caching?
    if (!isset($aFoundTags['cache'])){
        $iWarnings++;
        
        $sWarnings.= '<h4 id="warnnocache">'.str_replace('<br>', ' ', $this->lB('httpheader.header.cache')).'</h4>'
                .$this->_getMessageBox($oRenderer->renderShortInfo('warn') . $this->lB('httpheader.warnings.nocache'), 'warning').'<br>'
                /*
                . $oRenderer->renderTileBar(
                    $oRenderer->renderTile('warning', $this->lB('httpheader.header.cache'), $oRenderer->renderShortInfo('miss'), '', '')
                )
                 */
            . '<div style="clear: both;"></div>'              
            ;
    }
    
    // --- no compression?
    if (!isset($aFoundTags['compression'])){
        $iWarnings++;
        $sWarnings.= '<h4 id="warnnocompression">'.str_replace('<br>', ' ', $this->lB('httpheader.header.compression')).'</h4>'
                . $this->_getMessageBox($oRenderer->renderShortInfo('warn') . $this->lB('httpheader.warnings.nocompression'), 'warning').'<br>'
                /*
                .$oRenderer->renderTileBar(
                    $oRenderer->renderTile('warning', $this->lB('httpheader.header.compression'), $oRenderer->renderShortInfo('miss'), '', '')
            )
            . '<div style="clear: both;"></div>'
            */
        ;
    }
    

// ----------------------------------------------------------------------
// security header
// ----------------------------------------------------------------------

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

// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------

$sReturn.= '<h3>' . sprintf($this->lB('httpheader.warnings'), $iWarnings) . '</h3>'
    . ($iWarnings
        ? $sWarnings
        : '<ul class="tiles warnings">'
            . '<li><a href="#" onclick="return false;" class="tile ok">' . $this->lB('httpheader.warnings.ok-label').'<br><strong>'.$this->lB('httpheader.warnings.ok').'</strong></a></li>'
            . '</ul>'
            . '<div style="clear: both;"></div>'
    )

    .'<h3 id="securityheaders">' . sprintf($this->lB('httpheader.securityheaders'), $iFoundSecHeader, count($aSecHeader)) . '</h3>'
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
