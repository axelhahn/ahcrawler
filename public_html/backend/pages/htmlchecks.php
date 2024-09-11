<?php
/**
 * page analysis :: Html-check
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sReturn = '';

$aOptions = $this->getEffectiveOptions();
$iMinTitleLength=$aOptions['analysis']['MinTitleLength'];
$iMinDescriptionLength=$aOptions['analysis']['MinDescriptionLength'];
$iMinKeywordsLength=$aOptions['analysis']['MinKeywordsLength'];
$iMaxPagesize=$aOptions['analysis']['MaxPagesize'];
$iMaxLoadtime=$aOptions['analysis']['MaxLoadtime']; 

$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');

$aCountByStatuscode=$this->_getStatusinfos(['_global','htmlchecks']);
$iPagesTotalCount=$aCountByStatuscode['_global']['pages']['value'];
if (!$iPagesTotalCount) {
    return $sReturn.'<br>'
        .$oRenderer->renderMessagebox(sprintf($this->lB('status.emptyindex'), $this->_sTab),'warning')
        ;
}
$oCrawler=new crawler($this->_sTab);

// ----------------------------------------------------------------------
// top area: tiles
// ----------------------------------------------------------------------

$sReturn.=''
        . '<h3>' . $this->lB('htmlchecks.overview') . '</h3>'
        ;

$iCountCrawlererrors= $aCountByStatuscode['htmlchecks']['countCrawlerErrors']['value'];
$iCountShortTitles=   $aCountByStatuscode['htmlchecks']['countShortTitles']['value'];
$iCountShortDescr=    $aCountByStatuscode['htmlchecks']['countShortDescr']['value'];
$iCountShortKeywords= $aCountByStatuscode['htmlchecks']['countShortKeywords']['value'];
$iCountLongload=      $aCountByStatuscode['htmlchecks']['countLongLoad']['value'];
$iCountLargePages=    $aCountByStatuscode['htmlchecks']['countLargePages']['value'];
        
$sTiles = ''
    . $oRenderer->renderTile('',            $this->lB('status.indexed_urls.label'), $iPagesTotalCount, '', '')
    . $this->_getTilesOfAPage()
    ;

$sReturn.=$oRenderer->renderTileBar($sTiles, '').'<div style="clear: both;"></div>'
        . '<p>'.$this->lB('htmlchecks.overview.introtext').'</p>'
        ;

if ($iCountCrawlererrors) {
    $sReturn.= '<h3 id="tblcrawlererrors">' . sprintf($this->lB('htmlchecks.tableCrawlererrors'), $iCountCrawlererrors) . '</h3>'
        .'<p>'.$this->lB('htmlchecks.tableCrawlererrors.description').'</p>'
        .$this->_getHtmlchecksChart($iPagesTotalCount, $iCountCrawlererrors)    
        .$this->_getHtmlchecksTable('select title, length(title) as length, url
            from pages 
            where siteid='.$this->_sTab.' and length(title)<'.$iMinTitleLength.'
            order by length(title)',
            'tableCrawlerErrors'
        );
}
// for the other charts: 
$iPagesCount=$iPagesTotalCount-$iCountCrawlererrors;


// ----------------------------------------------------------------------
// table with too short titles
// ----------------------------------------------------------------------
if ($iCountShortTitles) {
    $iCountNoTitle=$this->getHtmlchecksCount('title', 1);
    $sReturn.= '<h3 id="tblshorttitle">' . sprintf($this->lB('htmlchecks.tableShortTitles'), $iCountShortTitles) . '</h3>'
        . '<div class="floatright">'
            .$this->_getHtmlchecksChart($iPagesCount, $iCountShortTitles-$iCountNoTitle, $iCountNoTitle)
        . '</div>'
        .$oRenderer->renderTileBar(
                ($iCountNoTitle ? 
                    $oRenderer->renderTile('error', $this->lB('htmlchecks.tile-check-no-title'), $iCountNoTitle,(floor($iCountNoTitle/$iPagesCount*1000)/10).'%', '') 
                    : '')
                .$oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-short-title'), $iMinTitleLength), $iCountShortTitles-$iCountNoTitle, (floor(($iCountShortTitles-$iCountNoTitle)/$iPagesCount*1000)/10).'%', '')
                , '')
        .'<div style="clear: left;"></div>'
        . $this->_getHistoryCounter(['countShortTitles'])
        .'<p>'.sprintf($this->lB('htmlchecks.tableShortTitles.description'), $iMinTitleLength).'</p>'
        .'<p>'.sprintf($this->lB('htmlchecks.customvalue'), $iMinTitleLength).'</p>'
        .'<div style="clear: both;"></div>'
        .$this->_getHtmlchecksTable('select title, length(title) as length, title_wc as words, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and length(title) < '.$iMinTitleLength.'
            order by length, words, title',
            'tableShortTitles'
        )
        ;
}

// ----------------------------------------------------------------------
// too short descriptions
// ----------------------------------------------------------------------
if ($iCountShortDescr) {
    $iCountNoDescr=$this->getHtmlchecksCount('description', 1);
    $sReturn.= '<h3 id="tblshortdescription">' . sprintf($this->lB('htmlchecks.tableShortDescription'), $iCountShortDescr) . '</h3>'
        . '<div class="floatright">'
            .$this->_getHtmlchecksChart($iPagesCount, $iCountShortDescr-$iCountNoDescr, $iCountNoDescr) 
        . '</div>'
        .$oRenderer->renderTileBar(
                ($iCountNoDescr ? 
                    $oRenderer->renderTile('error', $this->lB('htmlchecks.tile-check-no-description'), $iCountNoDescr,(floor($iCountNoDescr/$iPagesCount*1000)/10).'%', '') 
                    : '')
                .$oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-short-description'), $iMinDescriptionLength), $iCountShortDescr-$iCountNoDescr, (floor(($iCountShortDescr-$iCountNoDescr)/$iPagesCount*1000)/10).'%', '')
                , '')
        .'<div style="clear: left;"></div>'
        . $this->_getHistoryCounter(['countShortDescr'])
        .'<p>'.sprintf($this->lB('htmlchecks.tableShortDescription.description'), $iMinDescriptionLength).'</p>'
        .'<p>'.sprintf($this->lB('htmlchecks.customvalue'), $iMinDescriptionLength).'</p>'
        .'<div style="clear: both;"></div>'
        .$this->_getHtmlchecksTable('select description, length(description) as length,  description_wc as words, title, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and length(description)<'.$iMinDescriptionLength.'
            order by length, words, description'                        
            /*
            ,
            [
                'pages',
                ['description',  $this->oDB->raw('length(description) as length'),'title', 'url'],
                [
                    'AND'=>[
                        'siteid'=>$this->_sTab,
                        'length(title)[<]'=>$iMinTitleLength,
                    ],
                    'ORDER' => ["length"=>"ASC", 'description'=>'ASC'],
                ]
            ]
             * 
             */
            ,
            'tableShortDescr'
        )
        ;
}

// ----------------------------------------------------------------------
// table with too short keyword
// ----------------------------------------------------------------------
if ($iCountShortKeywords) {
    $iCountNoKeywords=$this->getHtmlchecksCount('keywords', 1);
    $sReturn.= '<h3 id="tblshortkeywords">' . sprintf($this->lB('htmlchecks.tableShortKeywords'), $iCountShortKeywords) . '</h3>'
        .$oRenderer->renderMessagebox($this->lB('htmlchecks.keywords-seo'),'warning')
        . '<div class="floatright">'
            .$this->_getHtmlchecksChart($iPagesCount, $iCountShortKeywords-$iCountNoKeywords, $iCountNoKeywords)    
        . '</div>'
        .$oRenderer->renderTileBar(
                ($iCountNoKeywords ? 
                    $oRenderer->renderTile('error', $this->lB('htmlchecks.tile-check-no-keywords'), $iCountNoKeywords,(floor($iCountNoKeywords/$iPagesCount*1000)/10).'%', '') 
                    : '')
                .$oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-short-keywords'), $iMinKeywordsLength), $iCountShortKeywords-$iCountNoKeywords, (floor(($iCountShortKeywords-$iCountNoKeywords)/$iPagesCount*1000)/10).'%', '')
                , '')
        .'<div style="clear: left;"></div>'
        . $this->_getHistoryCounter(['countShortKeywords'])
        .'<p>'.sprintf($this->lB('htmlchecks.tableShortKeywords.description'), $iMinKeywordsLength).'</p>'
        .'<p>'.sprintf($this->lB('htmlchecks.customvalue'), $iMinKeywordsLength).'</p>'
        .'<div style="clear: both;"></div>'
        .$this->_getHtmlchecksTable('select keywords, length(keywords) as length, keywords_wc as words, title, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and length(keywords)<'.$iMinKeywordsLength.'
            order by length, words, keywords',
            'tableShortKeywords'
        )
        ;
}

// ----------------------------------------------------------------------
// long loading pages
// ----------------------------------------------------------------------
// $iCountLongload=$this->_getHtmlchecksLarger('time', $iMaxLoadtime);

// return $this->_getHtmlTable($aTable, "db-pages.", $sTableId);
$sReturn.= '<h3 id="tblloadtimepages">' . sprintf($this->lB('htmlchecks.tableLoadtimePages'), $iCountLongload) . '</h3>'
    .($iCountLongload
        ? '<div class="floatright">'
            .$this->_getHtmlchecksChart($iPagesCount, $iCountLongload)
        . '</div>'
        .$oRenderer->renderTileBar(
                $oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-loadtime-of-pages'), $iMaxLoadtime), $iCountLongload, (floor($iCountLongload/$iPagesCount*1000)/10).'%', '')
                , '')
        .'<div style="clear: left;"></div>'
        . $this->_getHistoryCounter(['countLongLoad'])
        .'<p>'.sprintf($this->lB('htmlchecks.tableLoadtimePages.description'), $iMaxLoadtime).'</p>'

        .'<div style="clear: right;"></div>'
        : ''
    )
    .'<div class="floatleft">'
        . $this->_getChartOfRange(
            'select time
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0
            order by time desc',
            'time',
            $iMaxLoadtime
        )
    . '</div>'
    .'<p>'.$this->lB('htmlchecks.tableLoadtimePages.range').'</p>'
    .$this->_getHtmlLegend($this->lB('htmlchecks.chartlegend'))
    .'<p>'.sprintf($this->lB('htmlchecks.customvalue'), $iMaxLoadtime.' ms').'</p>'

    .($iCountLongload
        ? $this->_getHtmlchecksTable('select title, time, size, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and time>'.$iMaxLoadtime.'
            order by time',
            'tableLongLoad'
        )
        :'<div style="clear: both;"></div>'
    )
    ;


// ----------------------------------------------------------------------
// large pages
// ----------------------------------------------------------------------
// $iCountLargePages=$this->_getHtmlchecksLarger('size', $iMaxPagesize);

    $sReturn.= '<h3 id="tbllargepages">' . sprintf($this->lB('htmlchecks.tableLargePages'), $iCountLargePages) . '</h3>'
        .($iCountLargePages
            ? '<div class="floatright">'
                .$this->_getHtmlchecksChart($iPagesCount, $iCountLargePages)
            . '</div>'
            .$oRenderer->renderTileBar(
                    $oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-large-pages'), $iMaxPagesize), $iCountLargePages, (floor($iCountLargePages/$iPagesCount*1000)/10).'%', '')
                    , '')
            .'<div style="clear: left;"></div>'
            . $this->_getHistoryCounter(['countLargePages'])
            .'<p>'.sprintf($this->lB('htmlchecks.tableLargePages.description'), $iMaxPagesize).'</p>'
            .'<div style="clear: right;"></div>'
            : ''
        )
        . '<div class="floatleft">'
            . $this->_getChartOfRange(
                'select size
                from pages 
                where siteid='.$this->_sTab.' and errorcount=0
                order by size desc',
                'size',
                $iMaxPagesize
            )
        . '</div>'
        .'<p>'.$this->lB('htmlchecks.tableLargePages.range').'</p>'
        .$this->_getHtmlLegend($this->lB('htmlchecks.chartlegend'))
        .'<p>'.sprintf($this->lB('htmlchecks.customvalue'), $iMaxPagesize.'').'</p>'

        .($iCountLargePages
            ? $this->_getHtmlchecksTable('select title, size, time, url
                from pages 
                where siteid='.$this->_sTab.' and errorcount=0 and size>'.$iMaxPagesize.'
                order by size',
                'tableLargePages'
            )
            : ''
        )
        ;

return $sReturn;
