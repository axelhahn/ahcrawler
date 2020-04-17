<?php
/**
 * page searchindex :: status
 */
$oRenderer=new ressourcesrenderer($this->_sTab);

$sReturn = '';
$iCountEntries = 5;

$aHeaderIndex = array('id', 'ts', 'url', 'title', 'errorcount', 'lasterror');


$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=search')
    // .'<h3>' . $this->lB('status.overview') . '</h3>'
    ;

// ----------------------------------------------------------------------
// get deta for tiles
// ----------------------------------------------------------------------



$oCrawler=new crawler($this->_sTab);
$iUrls = $oCrawler->getCount();        
if(!$iUrls){
    $sReturn.= $this->_getMessageBox(sprintf($this->lB('status.emptyindex'), $this->_sTab), 'warning');
    return $sReturn;
}

;

    // ----------------------------------------------------------------------
    // search form
    // ----------------------------------------------------------------------

    $sQuery = $this->_getRequestParam('q');
    $o = new ahsearch($this->_sTab);

    $aResult = $o->search($sQuery);

    $sForm = '<h3>'.$this->lB('status.search').'</h3>'
            . '<p>'.$this->lB('status.search.hint').'</p>'

            . '<div div class="actionbox">'
                . '<form action="" method="get" class="pure-form">'
                    . '<input type="hidden" name="page" value="searchindextester">'
                    . '<input type="hidden" name="action" value="search">'
                    . '<input type="hidden" name="siteid" value="' . $this->_sTab . '">'

                    . $o->renderLabelSearch().' '. $o->renderInput(array('size'=>100))
                    . '<button class="pure-button button-success">' . $this->_getIcon('button.search') . $o->lF('btn.search.label') . '</button> '
                    . ($sQuery ? '<a href="?page=status" class="pure-button button-error">' . $this->_getIcon('button.close') . '</a>' : '' )
                    . '<br><br><br>'
                    . '<div style="margin-left: 5em;">'
                        . '<strong>'.$this->lB('status.searchoptions').':</strong><br>'
                        .($o->getSearchCategories(false) ? $o->renderLabelCategories() .': '.$o->renderSelectCategories().' ' : '')
                        .($o->getSearchLang(false)       ? $o->renderLabelLang()       .': '.$o->renderSelectLang().' '       : '')
                        . '<br>'
                        . $o->renderLabelMode() .': '. $o->renderSelectMode(array('class'=>'form-control'))
                    . '</div>'
                . '</form>'
            . '</div>'
            ;

    $iResults = $o->getCountOfSearchresults($aResult);
    $sReturn .= ''
            . $sForm
            . ($sQuery ? '<p>' . $this->lB('searches.results') . ': <strong>' . $iResults . '</strong><p>' : '');

    $aTable = array();

    $iCounter = 0;
    $iMaxRanking = false;

    if ($sQuery && $iResults) {
        foreach ($aResult as $iRanking => $aDataItems) {
            if (!$iMaxRanking) {
                $iMaxRanking = $iRanking ? $iRanking : 1;
            }
            $aRow = array();
            foreach ($aDataItems as $aItem) {
                // unset($aItem['content']);
                // echo '<pre>'.print_r($aItem, 1); die();
                $iCounter ++;
                $sResult = '';
                foreach ($aItem['results'] as $sWord => $aMatchTypes) {
                    $sResult.='<br>'.$this->_getIcon('button.search').'<strong>' . $sWord . '</strong> ...<br>';
                    foreach ($aMatchTypes as $sType => $aHits) {
                        $sMatches = '';
                        foreach ($aHits as $sWhere => $aValues) {
                            if ($aValues[0]) {
                                $sMatches.='<nobr style="text-align: right; display: block;">&nbsp;&nbsp;- <strong>' . $aValues[0] . '</strong> x ' . $sWhere . ' (' . $aValues[0] . 'x'.$aValues[1].'=<strong>'.($aValues[0]*$aValues[1]).'</strong>)</nobr>';
                            }
                        }
                        if ($sMatches) {
                            $sResult.='<br>* '.$sType . ':<br>' . $sMatches;
                        }
                    }
                }
                $aTable[] = array(
                    'id' => $iCounter,
                    'ranking' => '<strong>'.$iRanking . '</strong> ('.round($iRanking/$iMaxRanking*100).'%)<br>' . $sResult . '</span>',
                    'summary' => $this->_getSimpleHtmlTable(array(
                            array('title', '<strong><a href="' . $aItem['url'] . '" target="_blank">' . $aItem['title'] . '</a></strong>'),
                            array('url', str_ireplace('','',$aItem['url'])),
                            array('lang', $aItem['lang']),
                            array('description', $aItem['description']),
                            array('keywords', $aItem['keywords']),
                            array('content', $this->_prettifyString($aItem['content'], 400)),
                        ))
                    ,
                    // 'search.ranking' => '<a href="#" onclick="return false;" class="hoverinfos">' . $iRanking . '<span>' . $sResult . '<!-- <pre>' . print_r($aItem['results'], 1) . '</pre>--></span></a>',
                    'actions' => $this->_getButton(array(
                        'href' => './?page=searchindexstatus&id='.(int)$aItem['id'],
                        'class' => 'button-secondary',
                        'popup' => false,
                        'target' => '_top',
                        'label' => 'button.view'
                     ))

                );
            }
        }
    } else {
        $sReturn.= $sQuery ? $this->_getMessageBox($this->lB('status.noresult'), 'warning') : '';
    }
    $sReturn.=''
        . $this->_getHtmlTable($aTable,'db-search.')
        // . (($iResults > 3) ? '<br>' . $sForm : '')
        // . '<br>'
        /*
        . $this->_getButton(array(
            'href' => './?page=searches',
            'class' => 'button-secondary',
            'target' => '_top',
            'label' => 'button.close'
         ))
         */
    ;
        

return $sReturn;
