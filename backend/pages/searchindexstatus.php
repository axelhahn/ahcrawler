<?php

/**
 * page searchindex :: status
 * 
 * TODO: actions
 * - reindex --> $oCrawler = new crawler(1); $oCrawler->updateSingleUrl($sPageUrl);
 * 
 */
$oRenderer = new ressourcesrenderer($this->_sTab);

$sReturn = '';
$iCountEntries = 5;

$aHeaderIndex = array('id', 'ts', 'url', 'title', 'errorcount', 'lasterror');


$sReturn .= $this->_getNavi2($this->_getProfiles(), false, '?page=home');




$oCrawler = new crawler($this->_sTab);
$iUrls = $oCrawler->getCount();
if (!$iUrls) {
    $sReturn .= $oRenderer->renderMessagebox(sprintf($this->lB('status.emptyindex'), $this->_sTab), 'warning');
    return $sReturn;
}


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
        $aTableInfos = array();
        $aTableWords = array();
        $aTable = array();
        $sBackUrl="?page=searchindexstatus&siteid=".$this->_sTab;
        $sBaseUrl=$sBackUrl."&id=".$iPageId;
        
        // --- general infos
        $aTableInfos=array(
            array(
                $this->_getIcon('description').$this->lB('db-pages.description'), 
                ($aItem[0]['description'] 
                    ? $aItem[0]['description']
                    : $oRenderer->renderMessagebox('('.$this->lB('htmlchecks.tile-check-no-description').')', 'warning')
                )),
            array(
                $this->_getIcon('keywords').$this->lB('db-pages.keywords'), 
                ($aItem[0]['keywords'] 
                    ? $aItem[0]['keywords'].'<br>' 
                    : $oRenderer->renderMessagebox('('.$this->lB('htmlchecks.tile-check-no-keywords').')', 'warning')
                )),
            array(
                $this->_getIcon('url').$this->lB('db-pages.url'), 
                '<a href="'.$aItem[0]['url'].'" target="_blank">'.$aItem[0]['url'].'</a>'),
            array(
                $this->_getIcon('ts').$this->lB('db-pages.ts'), 
                $aItem[0]['ts']
                    ),
        );

        // --- used words in the content
        $aWc=array();
        $sUsedWords='';
        
        $aWordsInContent=$this->getWordsInAText($aItem[0]['content']);
        $iMinCount=$this->_getRequestParam('kwcount', false, 'int');
        $iMinCount=$iMinCount ? $iMinCount : 3;
        
        $aCounters=array();
        foreach ($aWordsInContent as $sMyWord=>$iWordCount){
            if($iWordCount>=$iMinCount){
                $aTableWords[]=array($iWordCount, $sMyWord);
            }
            $aWc[$iWordCount]=true;
        }

        $sNavWc='';
        if(count($aWc)){
            krsort($aWc);
            $sNavWc.='<p>'. sprintf($this->lB('status.detail.words.intro'), $iMinCount).'</p>'
                    . '<nav>';
            
            foreach(array_keys($aWc) as $iMyCount){
                $sNavWc.='<a href="'.$sBaseUrl.'&kwcount='.$iMyCount.'#wordlist" class="pure-button'.($iMyCount===$iMinCount ? ' button-secondary' : '').'">'.$iMyCount.'</a> ';
            }
            $sNavWc.='</nav><br>';
        }

        // --- raw data
        foreach ($aItem[0] as $sKey => $sVal) {
            $aTable[] = array(
                $sKey,
                $this->_prettifyString($sVal, 500)
                    .(is_string($sVal) && strlen($sVal)>50 ? ' ['.strlen($sVal).']' : '')
            );
        }
        
        return '<h3>' . $this->lB('status.detail') . '</h3>'
                . $this->_getButton(array(
                    'href' => $sBackUrl,
                    'class' => 'button-secondary',
                    'popup' => false,
                    'label' => 'button.back'
                )) . '<br><br>'
                
                . '<h4>'.($aItem[0]['title'] ? $aItem[0]['title'] : $aItem[0]['url']).'</h4>'
                . $this->_getSimpleHtmlTable($aTableInfos).'<br>'
                . '<h4 id="wordlist">'.$this->lB('status.detail.words').'</h4>'
                
                . $sNavWc 
                . $this->_getSimpleHtmlTable($aTableWords).'<br>'
               
                . $oRenderer->renderToggledContent(
                        $this->lB('status.detail.raw'),
                        '<form class="pure-form">'. $this->_getSimpleHtmlTable($aTable). '</form>',
                        false
                  )
                .'<br>'
                
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
                    'href' => $sBackUrl,
                    'class' => 'button-secondary',
                    'popup' => false,
                    'label' => 'button.back'
                )) . '<br><br>'

        ;
    }
}

// ----------------------------------------------------------------------
// get deta for tiles
// ----------------------------------------------------------------------

$sReturn .= '<h3>' . $this->lB('status.overview') . '</h3>';
$sTiles = $oRenderer->renderTile('', $this->lB('status.indexed_urls.label'), $iUrls, $this->lB('status.indexed_urls.footer'), '');

$sLast = $oCrawler->getLastRecord();
$sOldest = $this->oDB->min('pages', array('ts'), array(
    'AND' => array(
        'siteid' => $this->_sTab,
    ),));


$iUrlsLast24 = $oCrawler->getCount(
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
        . $oRenderer->renderTile('', $this->lB('status.indexed_urls24h.label'), $iUrlsLast24, $this->lB('status.indexed_urls24h.footer'), '')
        . $oRenderer->renderTile(($iUrlsErr ? 'error' : 'ok'), $this->lB('status.error_urls.label'), $iUrlsErr, $this->lB('status.error_urls.footer'), '')
        . $oRenderer->renderTile('', $this->lB('status.last_updated.label'), $oRenderer->hrAge(date('U', strtotime($sLast))), $sLast, '')
        . $oRenderer->renderTile('', $this->lB('status.oldest_updated.label'), $oRenderer->hrAge(date('U', strtotime($sOldest))), $sOldest, '')
;

$sReturn .= $oRenderer->renderTileBar($sTiles) . '<div style="clear: both;"></div>'
        . $this->_getHistoryCounter(['pages'])
;
// ----------------------------------------------------------------------
// actions
// ----------------------------------------------------------------------

$sRunStatus=$this->getStatus();
// $sReturn .= $oRenderer->renderIndexActions('reindex', 'searchindex', $this->_sTab);

// ----------------------------------------------------------------------
// tables
// ----------------------------------------------------------------------

if (!$iPageId) {

    $aNewestInIndex = $this->oDB->select(
            'pages', $aHeaderIndex, array(
        'AND' => array(
            'siteid' => $this->_sTab,
        ),
        "ORDER" => array("ts" => "DESC"),
        "LIMIT" => $iCountEntries
            )
    );
    $aOldestInIndex = $this->oDB->select(
            'pages', $aHeaderIndex, array(
        'AND' => array(
            'siteid' => $this->_sTab,
        ),
        "ORDER" => array("ts" => "ASC"),
        "LIMIT" => $iCountEntries
            )
    );
    $aEmpty = $this->oDB->select(
            'pages', $aHeaderIndex, array(
        'AND' => array(
            'siteid' => $this->_sTab,
            'title' => '',
            'content' => '',
        ),
        "ORDER" => array("ts" => "ASC"),
            // "LIMIT" => 5
            )
    );
    $aAllInIndex = $this->oDB->select(
            'pages', $aHeaderIndex, array(
        'AND' => array(
            'siteid' => $this->_sTab,
        ),
        "ORDER" => array("url" => "ASC"),
            )
    );

    if (count($aNewestInIndex)) {
        $sReturn .= '<h3>' . $this->lB('status.newest_urls_in_index') . '</h3>'
                . $this->_getSearchindexTable($aNewestInIndex, 'db-pages.', false, false);
    }
    if (count($aOldestInIndex)) {
        $sReturn .= '<h3>' . $this->lB('status.oldest_urls_in_index') . '</h3>'
                . $this->_getSearchindexTable($aOldestInIndex, 'db-pages.', false, false);
    }

    if (count($aEmpty)) {
        $sReturn .= '<h3>' . $this->lB('status.empty_data') . '</h3>'
                . $this->_getSearchindexTable($aEmpty, 'db-pages.', false, false)
        ;
    }
    if ($iUrlsErr) {
        $aErrorUrls = $this->oDB->select(
                'pages', $aHeaderIndex, array(
            'AND' => array(
                'siteid' => $this->_sTab,
                'errorcount[>=]' => 0,
            ),
            "ORDER" => array("ts" => "ASC"),
            "LIMIT" => 50
                )
        );
        $sReturn .= '<h3>' . $this->lB('status.error_urls') . '</h3>'
                . $this->_getSearchindexTable($aErrorUrls, 'pages.', false, false)
        ;
    }
    if (count($aAllInIndex)) {
        $sTableId = 'tableAlldata';
        $sReturn .= '<h3>' . $this->lB('status.all_data') . ' (' . count($aAllInIndex) . ')</h3>'
                 . $this->_getSearchindexTable($aAllInIndex, 'db-pages.', $sTableId, true)
        ;
    }
}

return $sReturn;
