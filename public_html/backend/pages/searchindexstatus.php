<?php

/**
 * page searchindex :: status
 * 
 * TODO: actions
 * - reindex --> $oCrawler = new crawler(1); $oCrawler->updateSingleUrl($sPageUrl);
 * 
 */
if (!$this->_requiresPermission("viewer", $this->_sTab)){
    return include __DIR__ . '/error403.php';
}

$oRenderer = new ressourcesrenderer($this->_sTab);

$sReturn = '';
$iCountEntries = 5;

$aHeaderIndex = ['id', 'ts', 'url', 'title', 'errorcount', 'lasterror'];

$oCrawler = new crawler($this->_sTab);
$iUrls = $oCrawler->getCount();
if (!$iUrls) {
    $sReturn .= $oRenderer->renderMessagebox(sprintf($this->lB('status.emptyindex'), $this->_sTab), 'warning');
    return $sReturn;
}


// ----------------------------------------------------------------------
// detail view of a found page
// ----------------------------------------------------------------------

$sBackUrl="javascript:history.back();";

// add profiles navigation
$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '');

$iPageId = $this->_getRequestParam('id', false, 'int');
if ($iPageId) {
    $aItem = $this->oDB->select(
        'pages', '*', [
            'AND' => [
                'id' => $iPageId,
            ]
        ]
    );
    if (count($aItem)) {
        $aTableInfos = [];
        $aTableWords = [];
        $aTable = [];
        $sSelfUrl='?page=searchindexstatus&siteid='.$this->_sTab;
        $sBaseUrl=$sSelfUrl."&id=".$iPageId;
        
        $iResId=$this->getIdByUrl($aItem[0]['url'],'ressources');

        $aOptions = $this->getEffectiveOptions();

        // --- general infos
        $aTableInfos=[
            [
                '<nobr>'.$this->_getIcon('title').$this->lB('db-pages.title').'</nobr>',
                $aItem[0]['title']
            ],
            [
                '<nobr>'.$this->_getIcon('description').$this->lB('db-pages.description').'</nobr>',
                ($aItem[0]['description'] 
                    ? $aItem[0]['description']
                    : $oRenderer->renderMessagebox('('.$this->lB('htmlchecks.tile-check-no-description').')', 'warning')
                )
            ],
            [
                '<nobr>'.$this->_getIcon('keywords').$this->lB('db-pages.keywords').'</nobr>',
                ($aItem[0]['keywords'] 
                    ? $aItem[0]['keywords'].'<br>' 
                    : $oRenderer->renderMessagebox('('.$this->lB('htmlchecks.tile-check-no-keywords').')', 'warning')
                )
            ],
            [
                '<nobr>'.$this->_getIcon('lang').$this->lB('db-pages.lang').'</nobr>',
                ($aItem[0]['lang'] 
                    ? $aItem[0]['lang'].'<br>' 
                    : ''
                )
            ],
            [
                '<nobr>'.$this->_getIcon('size').$this->lB('db-pages.size').'</nobr>',
                ($aItem[0]['size'] 
                    ? $oRenderer->renderValue('_size_download1', $oRenderer->hrSize($aItem[0]['size'])).'<br>' 
                    : ''
                )
            ],
            [
                '<nobr>'.$this->_getIcon('ts').$this->lB('db-pages.ts').'</nobr>',
                $aItem[0]['ts']
            ],
            [
                '<nobr>'.$this->_getIcon('time').$this->lB('db-ressources.timers').'</nobr>',
                $oRenderer->renderNetworkTimer($aItem[0]['header'])
            ],
        ];

        // --- used words in the content
        $aWc=[];
        $sUsedWords='';
        
        $aWordsInContent=$this->getWordsInAText($aItem[0]['content']);
        $iMinCount=$this->_getRequestParam('kwcount', false, 'int');
        $iMinCount=$iMinCount ? $iMinCount : 3;
        
        $aCounters=[];
        foreach ($aWordsInContent as $sMyWord=>$iWordCount){
            if($iWordCount>=$iMinCount){
                $aTableWords[]=[$iWordCount, $sMyWord];
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
            $aTable[] = [
                $sKey,
                $this->_prettifyString($sVal, 500)
                    .(is_string($sVal) && strlen($sVal)>50 ? ' ['.strlen($sVal).']' : '')
            ];
        }
        
        
        $oHttpheader=new httpheader();
        $aHeaderJson=json_decode($aItem[0]['header'], 1);
        // print_r($aHeaderJson); die();
        $sReposneHeaderAsString= strlen($aHeaderJson['_responseheader'][0])!=1 ? $aHeaderJson['_responseheader'][0] : $aHeaderJson['_responseheader'];
        $oHttpheader->setHeaderAsString($sReposneHeaderAsString);
        
        $sCurl=$oRenderer->renderCurlMetadata($aItem[0]);

        return $sReturn 
                
                . $oRenderer->renderContextbox(
                    ''
                    .($iResId 
                        ? ''
                            .'<a href="?page=ressourcedetail&id=' . $iResId . '&siteid='.$this->iSiteId.'" class="pure-button"'
                            . ' title="'.$this->lB('status.link-to-res').'"'
                            . '>'.$oRenderer->_getIcon('switch-search-res').$this->lB('status.link-to-res').'</a><br><br>' 
                        : ''
                    )
                    . '<a href="' . $aItem[0]['url'] . '" target="_blank" class="pure-button" title="'.$this->lB('ressources.link-to-url').'">'. $oRenderer->_getIcon('link-to-url').$this->lB('ressources.link-to-url').'</a><br><br>'
                    . ($this->_requiresPermission("manager", $this->_sTab) 
                            ? '<br><hr>'
                                . $this->lB('status.reindex-url').'<br><br>'
                                . $oRenderer->renderIndexActions(['reindex'], 'singlepage', $this->_sTab, $aItem[0]['url'])
                            : ''
                        )

                    , $this->lB('context.links')
                )
                . '<h3>' . $this->lB('status.detail') . '</h3>'
                /*
                . $this->_getButton([
                    'href' => $sBackUrl,
                    'class' => 'button-secondary',
                    'popup' => false,
                    'label' => 'button.back'
                ]) . '<br><br>'
                */
                
                // ---- basic page data
                . '<strong>'.($aItem[0]['url']).'</strong>&nbsp; '
                    .($iResId 
                        ? '<a href="?page=ressourcedetail&id=' . $iResId . '&siteid='.$this->iSiteId.'" class="pure-button"'
                            . ' title="'.$this->lB('status.link-to-res').'"'
                            . '>'.$oRenderer->_getIcon('switch-search-res').'</a> ' 
                        : ''
                    )
                    . '<a href="' . $aItem[0]['url'] . '" target="_blank" class="pure-button" title="'.$this->lB('ressources.link-to-url').'">'. $oRenderer->_getIcon('link-to-url').'</a>'
                . '<br><br><br>'

                . $this->_getSimpleHtmlTable($aTableInfos).'<br>'

                // . $sConnect . '<pre>' . print_r($aTimers, 1) .'</pre>'
                // .'<pre>'.print_r($aCurlHeader, 1).'</pre>'

                // ---- http header
                .$oRenderer->renderToggledContent(
                    $oRenderer->lB('httpheader.data'),
                    $oRenderer->renderHttpheaderAsTable($oHttpheader->parseHeaders())
                        .(isset($this->aOptions['menu-public']['httpheaderchecks']) && $this->aOptions['menu-public']['httpheaderchecks']
                            ? '<br><a href="../?page=httpheaderchecks&urlbase64='.base64_encode($aItem[0]['url']).'" class="pure-button" target="_blank">'.$oRenderer->_getIcon('link-to-url') . $this->lB('ressources.httpheader-live').'</a>'
                            : ''
                        )
                    ,
                    false
                )
                // --- curl metadata
                . $oRenderer->renderToggledContent(
                    $this->lB('ressources.curl-metadata-h3'),
                    $sCurl,
                    false
                )
                
                // ---- wordlist
                . $oRenderer->renderToggledContent(
                        $this->lB('status.detail.words'),
                        $sNavWc . $this->_getSimpleHtmlTable($aTableWords).'<br>',
                        false
                  )
                
                /*
                . '<h4 id="wordlist">'.$this->lB('status.detail.words').'</h4>'
                . $sNavWc 
                . $this->_getSimpleHtmlTable($aTableWords).'<br>'
               */


                // ---- raw data
                . $oRenderer->renderToggledContent(
                        $this->lB('status.detail.raw'),
                        '<form class="pure-form">'. $this->_getSimpleHtmlTable($aTable). '</form>',
                        false
                  )
                .'<br>'
                
                /*
                  . $this->_getButton([
                  'href' => './?page=status',
                  'target' => '_top',
                  'class' => 'button-secondary',
                  'label' => 'button.close'
                  ])
                  . ' '
                  . $this->_getButton([
                  'href' => 'overlay.php?action=updateindexitem&url=' . $aItem[0]['url'] . '&siteid=' . $aItem[0]['siteid'],
                  'class' => 'button-success',
                  'label' => 'button.reindex'
                  ])
                  . ' '
                  . $this->_getButton([
                  'href' => 'overlay.php?action=deleteindexitem&id=' . $sId . '&siteid=' . $aItem[0]['siteid'],
                  'class' => 'button-error',
                  'label' => 'button.delete'
                  ])
                 * 
                 */
                . $this->_getButton([
                    'href' => $sBackUrl,
                    'class' => 'button-secondary',
                    'popup' => false,
                    'label' => 'button.back'
                ]) . '<br><br>'

        ;
    }
}

// ----------------------------------------------------------------------
// get deta for tiles
// ----------------------------------------------------------------------

$sReturn .= '<h3>' . $this->lB('status.overview') . '</h3>';
$sTiles = $oRenderer->renderTile('', $this->lB('status.indexed_urls.label'), $iUrls, $this->lB('status.indexed_urls.footer'), '');

$sLast = $oCrawler->getLastRecord();
$sOldest = $this->oDB->min('pages', ['ts'], [
    'AND' => [
        'siteid' => $this->_sTab,
    ],]);


$iUrlsLast24 = $oCrawler->getCount(
    [
            'siteid' => $this->_sTab,
            'ts[>]' => date("Y-m-d H:i:s", (date("U") - (60 * 60 * 24))),
        ]);

$iUrlsErr = $oCrawler->getCount([
    'AND' => [
        'siteid' => $this->_sTab,
        'errorcount[>]' => 0,
        ]
    ]);


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

    $aNewestInIndex=[];
    $aOldestInIndex=[];
    if(date('U', strtotime($sLast))-date('U', strtotime($sOldest)) > 60*60*6){
        $aNewestInIndex = $this->oDB->select(
            'pages', $aHeaderIndex, [
                'AND' => [
                    'siteid' => $this->_sTab,
                ],
                "ORDER" => ["ts" => "DESC"],
                "LIMIT" => $iCountEntries
            ]
        );
        $aOldestInIndex = $this->oDB->select(
            'pages', $aHeaderIndex, [
                'AND' => [
                    'siteid' => $this->_sTab,
                ],
                "ORDER" => ["ts" => "ASC"],
                "LIMIT" => $iCountEntries
            ]
        );
    }
    $aEmpty = $this->oDB->select(
        'pages', $aHeaderIndex, [
            'AND' => [
                'siteid' => $this->_sTab,
                'title' => '',
                'content' => '',
            ],
            "ORDER" => ["ts" => "ASC"],
            // "LIMIT" => 5
        ]
    );
    $aAllInIndex = $this->oDB->select(
        'pages', $aHeaderIndex, [
            'AND' => [
                'siteid' => $this->_sTab,
            ],
            "ORDER" => ["url" => "ASC"],
        ]
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
            'pages', $aHeaderIndex, [
                'AND' => [
                    'siteid' => $this->_sTab,
                    'errorcount[>=]' => 0,
                ],
                "ORDER" => ["ts" => "ASC"],
                "LIMIT" => 50
            ]
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
