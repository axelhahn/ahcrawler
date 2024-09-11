<?php
/**
 * page searchindex :: status
 */
$oRenderer=new ressourcesrenderer($this->_sTab);

$sReturn = '';
$iCountEntries = 5;

$aHeaderIndex = ['id', 'ts', 'url', 'title', 'errorcount', 'lasterror'];


$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=home')
    // .'<h3>' . $this->lB('status.overview') . '</h3>'
    ;

// ----------------------------------------------------------------------
// get deta for tiles
// ----------------------------------------------------------------------



$oCrawler=new crawler($this->_sTab);
$iUrls = $oCrawler->getCount();        
if(!$iUrls){
    $sReturn.= $oRenderer->renderMessagebox(sprintf($this->lB('status.emptyindex'), $this->_sTab), 'warning');
    return $sReturn;
}

;

    // ----------------------------------------------------------------------
    // search form
    // ----------------------------------------------------------------------

    $sQuery = $this->_getRequestParam('q');
    $o = new ahsearch($this->_sTab);
    
    $aResult = $o->search($sQuery);
    // echo '<pre>'.print_r($aResult, 1).'</pre>';

    $sForm = '<h3>'.$this->lB('status.search').'</h3>'
            . '<p>'.$this->lB('status.search.hint').'</p>'

            . '<div div class="actionbox">'
                . '<form action="" method="get" class="pure-form">'
                    . '<input type="hidden" name="page" value="searchindextester">'
                    . '<input type="hidden" name="action" value="search">'
                    . '<input type="hidden" name="siteid" value="' . $this->iSiteId . '">'

                    . $o->renderLabelSearch().' '. $o->renderInput(['size'=>100])
                    . '<button class="pure-button button-success">' . $this->_getIcon('button.search') . $o->lF('btn.search.label') . '</button> '
                    . ($sQuery ? '<a href="?page=searchindextester" class="pure-button button-error">' . $this->_getIcon('button.close') . '</a>' : '' )
                    . '<br><br><br>'
                    . '<div style="margin-left: 5em;">'
                        . '<strong>'.$this->lB('status.searchoptions').':</strong><br>'
                        .($o->getSearchCategories(false) ? $o->renderLabelCategories() .': '.$o->renderSelectCategories().' ' : '')
                        .($o->getSearchLang(false)       ? $o->renderLabelLang()       .': '.$o->renderSelectLang().' '       : '')
                        . '<br>'
                        . $o->renderLabelMode() .': '. $o->renderSelectMode(['class'=>'form-control'])
                        .'<br><br>'
                        . $this->lB('status.search.contentselect') . ': ' . $o->renderSelectContentTable(['class'=>'form-control'])
                    . '</div>'
                . '</form>'
            . '</div>'
            ;

    // $iResults = $o->getCountOfSearchresults($aResult);
    $iResults = isset($aResult['meta']['result_count']) ? $aResult['meta']['result_count'] : 0;
    $aTimerTable=[];
    if(isset($aResult['meta'])){
        foreach (array_keys($aResult['meta']['timers']) as $sTimekey){
            $aTimerTable[]=[$this->lB('status.search.timers.'.$sTimekey), sprintf("%01.3f", $aResult['meta']['timers'][$sTimekey]) . " ms"];
        }
    }
    $sReturn .= ''
            . $sForm
            . ($sQuery 
                ? '<p>' 
                        . $this->lB('searches.results') . ': <strong>' . $iResults . '</strong>'
                        // . print_r($aResult['meta'], 1)
                    .'<p>'
                    . $oRenderer->renderToggledContent(
                        $this->lB('status.search.timers'),
                        $this->_getSimpleHtmlTable($aTimerTable),
                        true
                    )
                : '');

    $aTable = [];

    $iCounter = 0;
    $iMaxRanking = false;

    if ($sQuery && $iResults) {
        foreach ($aResult['data'] as $iRanking => $aDataItems) {
            if (!$iMaxRanking) {
                $iMaxRanking = $iRanking ? $iRanking : 1;
            }
            $aRow = [];
            foreach ($aDataItems as $aItem) {
                // unset($aItem['content']);
                // echo '<pre>'.print_r($aItem, 1); die();
                $iCounter ++;
                $sResult = '';
                foreach ($aItem['results'] as $sWord => $aMatchTypes) {
                    $aMatches=[];
                    $sResult.=''.$this->_getIcon('button.search').'<strong>' . $sWord . '</strong> ...<br><br>';
                    foreach ($aMatchTypes as $sType => $aHits) {
                        foreach ($aHits as $sWhere => $aValues) {
                            if ($aValues[0]) {
                                $aMatches[]=[
                                    $sType,
                                    '<strong>' . $aValues[0] . '</strong> x in',
                                    '[' . $sWhere . ']',
                                    $aValues[1],
                                    $aValues[0] . ' x '.$aValues[1],
                                    '<strong>'.($aValues[0]*$aValues[1]).'</strong>'
                                ];
                            }
                        }
                    }
                    $sResult.=count($aMatches) ? $this->_getSimpleHtmlTable($aMatches) : '--<br>';
                    $sResult.='<br>';
                }
                $aTable[] = [
                    'id' => $iCounter,
                    'ranking' => '<strong>'.$iRanking . '</strong> ('.round($iRanking/$iMaxRanking*100).'%)<br><!-- ' . $sResult . '--></span>',
                    'summary' => $this->_getSimpleHtmlTable([
                            // ['title', '<strong><a href="' . $aItem['url'] . '" target="_blank">' . $aItem['title'] . '</a></strong>'],
                            [
                                $this->_getIcon('title').$this->lB('db-pages.title'), ''
                                // . '<a href="' . $aItem['url'] . '" target="_blank" style="float: right;" class="pure-button" title="'.$this->lB('ressources.link-to-url').'">'. $oRenderer->_getIcon('link-to-url').'</a>'
                                . '<strong>' . $aItem['html_title'] . '</strong>'
                            ],
                            // [$this->lB('db-pages.url'), str_ireplace('','',$aItem['url'])],
                            [$this->_getIcon('url').$this->lB('db-pages.url'), ''
                                .'<a href="./?page=searchindexstatus&id='.(int)$aItem['id'].'">'.$aItem['html_url'].'</a><br><br>' 
                                .$aItem['html_preview'].'<br><br>'
                                .$aItem['html_hits_per_term'].'<br>'
                            ],
                            [$this->_getIcon('lang').$this->lB('db-pages.lang'), $aItem['lang']],
                            [$this->_getIcon('description').$this->lB('db-pages.description'), $aItem['html_description']],
                            [$this->_getIcon('keywords').$this->lB('db-pages.keywords'), $aItem['html_keywords']],
                            [$this->_getIcon('ranking').$this->lB('db-search.ranking'), $sResult],
                        ])
                    ,
                        // 'search.ranking' => '<a href="#" onclick="return false;" class="hoverinfos">' . $iRanking . '<span>' . $sResult . '<!-- <pre>' . print_r($aItem['results'], 1) . '</pre>--></span></a>',
                    'actions' => ''
                        . '<a href="' . $aItem['url'] . '" target="_blank" class="pure-button" title="'.$this->lB('ressources.link-to-url').'">'. $oRenderer->_getIcon('link-to-url').'</a>'
                    
                ];
            }
        }
    } else {
        $sReturn.= $sQuery ? $oRenderer->renderMessagebox($this->lB('status.noresult'), 'warning') : '';
    }
    $sReturn.=''
        . $this->_getHtmlTable($aTable,'db-search.')

        . '<br>'
        . $oRenderer->renderToggledContent(
            $this->lB('status.search.ranking.legend'),
            $this->lB('setup.section.search.hint')
                . '<pre>'.print_r($this->aOptions['searchindex']['rankingWeights'], 1).'</pre>',
            false
      )
        // . (($iResults > 3) ? '<br>' . $sForm : '')
        // . '<br>'
        /*
        . $this->_getButton([
            'href' => './?page=searches',
            'class' => 'button-secondary',
            'target' => '_top',
            'label' => 'button.close'
         ])
         */
    ;
        

return $sReturn;
