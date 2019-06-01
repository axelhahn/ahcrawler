<?php
/**
 * page searchindex :: status
 */
$oRenderer=new ressourcesrenderer($this->_sTab);

$sReturn = '';
$iCountEntries = 5;

$aHeaderIndex = array('id', 'ts', 'url', 'title', 'errorcount', 'lasterror');


$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=search')
    .'<h3>' . $this->lB('status.overview') . '</h3>'
    ;

// ----------------------------------------------------------------------
// get deta for tiles
// ----------------------------------------------------------------------



$oCrawler=new crawler($this->_sTab);
$iUrls = $oCrawler->getCount();        
$sTiles = $oRenderer->renderTile('', $this->lB('status.indexed_urls.label'), $iUrls, $this->lB('status.indexed_urls.footer'), '');
if(!$iUrls){
    $sReturn.= $oRenderer->renderTileBar($sTiles).'<div style="clear: both;"></div>'
            .'<br>'.$this->_getMessageBox(sprintf($this->lB('status.emptyindex'), $this->_sTab), 'warning');
    return $sReturn;
}

$sLast = $oCrawler->getLastRecord();
$sOldest = $this->oDB->min('pages', array('ts'), array(
    'AND' => array(
        'siteid' => $this->_sTab,
    ),));


$iUrlsLast24=$oCrawler->getCount(
    array(
        'siteid' => $this->_sTab,
        'ts[>]' => date("Y-m-d H:i:s", (date("U") - (60 * 60 * 24))),
    ));

$iUrlsErr = $oCrawler->getCount(array(
    'AND' => array(
        'siteid' => $this->_sTab,
        'errorcount[>]' => 0,
    )));


// ----------------------------------------------------------------------
// render tiles
// ----------------------------------------------------------------------

$sTiles .= ''
        .$oRenderer->renderTile('', $this->lB('status.indexed_urls24h.label'), $iUrlsLast24, $this->lB('status.indexed_urls24h.footer'), '')
        .$oRenderer->renderTile(($iUrlsErr ? 'error' : 'ok') , $this->lB('status.error_urls.label'), $iUrlsErr, $this->lB('status.error_urls.footer'), '')
        .$oRenderer->renderTile('', $this->lB('status.last_updated.label'), $oRenderer->hrAge(date('U', strtotime($sLast))), $sLast, '')
        .$oRenderer->renderTile('', $this->lB('status.oldest_updated.label'), $oRenderer->hrAge(date('U', strtotime($sOldest))), $sOldest, '')
        ;

$sReturn.= $oRenderer->renderTileBar($sTiles).'<div style="clear: both;"></div>'
;

// ----------------------------------------------------------------------
// detail view of a found page
// ----------------------------------------------------------------------


        $iPageId = $this->_getRequestParam('id', false, 'int');
        if ($iPageId) {
            $aItem = $this->oDB->select(
                    'pages', '*', array(
                'AND' => array(
                    'id' => $iPageId,
                )
                    )
            );
            if (count($aItem)) {
                $aTable = array();
                foreach ($aItem[0] as $sKey => $sVal) {
                    $aTable[] = array(
                        $sKey,
                        $this->_prettifyString($sVal, 5000)
                    );
                }
                $sReturn .= '<h3>'.$this->lB('status.detail').'</h3>'
                    . $this->_getButton(array(
                        'href' => 'javascript:history.back();',
                        'class' => 'button-secondary',
                        'popup' => false,
                        'label' => 'button.back'
                     )).'<br><br>'
                    .$this->_getSimpleHtmlTable($aTable)
                        . '<br>'
                        /*
                        . $this->_getButton(array(
                            'href' => './?page=status',
                            'target' => '_top',
                            'class' => 'button-secondary',
                            'label' => 'button.close'
                        ))
                        . ' '
                        . $this->_getButton(array(
                            'href' => 'overlay.php?action=updateindexitem&url=' . $aItem[0]['url'] . '&siteid=' . $aItem[0]['siteid'],
                            'class' => 'button-success',
                            'label' => 'button.reindex'
                        ))
                        . ' '
                        . $this->_getButton(array(
                            'href' => 'overlay.php?action=deleteindexitem&id=' . $sId . '&siteid=' . $aItem[0]['siteid'],
                            'class' => 'button-error',
                            'label' => 'button.delete'
                        ))
                         * 
                         */
                    . $this->_getButton(array(
                        'href' => 'javascript:history.back();',
                        'class' => 'button-secondary',
                        'popup' => false,
                        'label' => 'button.back'
                     )).'<br><br>'
                        
                ;
            }
        } else {
    
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
                            . '<input type="hidden" name="page" value="status">'
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
            /*
            $aTable = array(
                array('#', $this->lB('search.summary'), $this->lB('search.ranking'), '')
            );
             * 
             */

            $iCounter = 0;
            $iMaxRanking = false;

            if ($sQuery && $iResults) {
                foreach ($aResult as $iRanking => $aDataItems) {
                    if (!$iMaxRanking) {
                        $iMaxRanking = $iRanking;
                    }
                    $aRow = array();
                    foreach ($aDataItems as $aItem) {
                        // unset($aItem['content']);
                        // echo '<pre>'.print_r($aItem, 1); die();
                        $iCounter ++;
                        $sResult = '';
                        foreach ($aItem['results'] as $sWord => $aMatchTypes) {
                            // $sResult.='<strong>' . $sWord . '</strong><br>';
                            foreach ($aMatchTypes as $sType => $aHits) {
                                $sMatches = '';
                                foreach ($aHits as $sWhere => $aValues) {
                                    if ($aValues[0]) {
                                        $sMatches.='... ' . $sWhere . ': <strong>' . $aValues[0] . '</strong> (x'.$aValues[1].' = '.($aValues[0]*$aValues[1]).')<br>';
                                    }
                                }
                                if ($sMatches) {
                                    $sResult.=($sResult ? '<br>' : '') . $sType . '<br>' . $sMatches;
                                }
                            }
                        }
                        $aTable[] = array(
                            'id' => $iCounter,
                            'summary' => 
                                    $this->_getSimpleHtmlTable(array(
                                    array('title', '<strong><a href="' . $aItem['url'] . '" target="_blank">' . $aItem['title'] . '</a></strong>'),
                                    array('url', $aItem['url']),
                                    array('lang', $aItem['lang']),
                                    array('description', $aItem['description']),
                                    array('keywords', $aItem['keywords']),
                                    array('content', $this->_prettifyString($aItem['content'], 400)),
                                ))
                            ,
                            // 'search.ranking' => '<a href="#" onclick="return false;" class="hoverinfos">' . $iRanking . '<span>' . $sResult . '<!-- <pre>' . print_r($aItem['results'], 1) . '</pre>--></span></a>',
                            'ranking' => '<strong>'.$iRanking . '</strong><br><br>' . $sResult . '</span>',
                            'actions' => $this->_getButton(array(
                                'href' => './?'.$_SERVER['QUERY_STRING'].'&id='.$aItem['id'],
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
        }
        

// ----------------------------------------------------------------------
// tables
// ----------------------------------------------------------------------
if(!$iPageId && !$iResults){

    $aNewestInIndex = $this->oDB->select(
        'pages', 
        $aHeaderIndex, 
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
            "ORDER" => array("ts"=>"DESC"),
            "LIMIT" => $iCountEntries
        )
    );
    $aOldestInIndex = $this->oDB->select(
        'pages', 
        $aHeaderIndex, 
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
            "ORDER" => array("ts"=>"ASC"),
            "LIMIT" => $iCountEntries
        )
    );
    $aEmpty = $this->oDB->select(
        'pages', 
        $aHeaderIndex, 
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
                'title' => '',
                'content' => '',
            ),
            "ORDER" => array("ts"=>"ASC"),
            // "LIMIT" => 5
        )
    );
    $aAllInIndex = $this->oDB->select(
        'pages', 
        $aHeaderIndex, 
        array(
            'AND' => array(
                'siteid' => $this->_sTab,
            ),
            "ORDER" => array("url"=>"ASC"),
        )
    );
    
    if (count($aNewestInIndex)) {
        $sReturn.='<h3>' . $this->lB('status.newest_urls_in_index') . '</h3>'
                . $this->_getSearchindexTable($aNewestInIndex, 'db-pages.');
    }
    if (count($aOldestInIndex)) {
        $sReturn.='<h3>' . $this->lB('status.oldest_urls_in_index') . '</h3>'
                . $this->_getSearchindexTable($aOldestInIndex, 'db-pages.');
    }

    if (count($aEmpty)) {
        $sReturn.='<h3>' . $this->lB('status.empty_data') . '</h3>'
                . $this->_getSearchindexTable($aEmpty, 'db-pages.')
        ;
    }
    if ($iUrlsErr) {
        $aErrorUrls = $this->oDB->select(
                'pages', $aHeaderIndex, array(
            'AND' => array(
                'siteid' => $this->_sTab,
                'errorcount[>=]' => 0,
            ),
            "ORDER" => array("ts"=>"ASC"),
            "LIMIT" => 50
                )
        );
        $sReturn.='<h3>' . $this->lB('status.error_urls') . '</h3>'
                . $this->_getSearchindexTable($aErrorUrls, 'pages.')
        ;
    }
    if (count($aAllInIndex)) {
        $sTableId='tbl-alldata';
        $sReturn.='<h3>' . $this->lB('status.all_data') .' ('.count($aAllInIndex).')</h3>'
            . $this->_getSearchindexTable($aAllInIndex, 'db-pages.', $sTableId)
            . $oRenderer->renderInitDatatable('#' . $sTableId)
        ;
    }
}

return $sReturn;
