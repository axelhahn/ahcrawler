<?php
/**
 * page analysis :: Html-check
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
$sReturn = '';

$aOptions = $this->_loadOptions();
$iMinTitleLength=$aOptions['options']['analysis']['MinTitleLength'];
$iMinDescriptionLength=$aOptions['options']['analysis']['MinDescriptionLength'];
$iMinKeywordsLength=$aOptions['options']['analysis']['MinKeywordsLength'];
$iMaxPagesize=$aOptions['options']['analysis']['MaxPagesize'];
$iMaxLoadtime=$aOptions['options']['analysis']['MaxLoadtime']; 

$sReturn.=$this->_getNavi2($this->_getProfiles(), false, '?page=analysis');
$iRessourcesCount=$this->oDB->count('pages',array('siteid'=>$this->_sTab));
if (!$iRessourcesCount) {
    return $sReturn.'<br>'.
        $this->_getMessageBox(
            sprintf($this->lB('status.emptyindex'), $this->_sTab),
            'warning'
        )
        ;
}
$oCrawler=new crawler($this->_sTab);

// ----------------------------------------------------------------------
// top area: tiles
// ----------------------------------------------------------------------

$sReturn.=''
        . '<h3>' . $this->lB('htmlchecks.overview') . '</h3>'
        ;

$iCountCrawlererrors=$oCrawler->getCount(array(
    'AND' => array(
        'siteid' => $this->_sTab,
        'errorcount[>]' => 0,
    )));

$iCountShortTitles=$this->_getHtmlchecksCount('title', $iMinTitleLength);
$iCountShortDescr=$this->_getHtmlchecksCount('description', $iMinDescriptionLength);
$iCountShortKeywords=$this->_getHtmlchecksCount('keywords', $iMinKeywordsLength);
$iCountLargePages=$this->_getHtmlchecksLarger('size', $iMaxPagesize);
$iCountLongload=$this->_getHtmlchecksLarger('time', $iMaxLoadtime);

$sTiles = ''
    . $oRenderer->renderTile('',            $this->lB('status.indexed_urls.label'), $iRessourcesCount, '', '')
    . ($iCountCrawlererrors
        ? $oRenderer->renderTile('error',   $this->lB('htmlchecks.tile-crawlererrors'), $iCountCrawlererrors, (floor($iCountCrawlererrors/$iRessourcesCount*1000)/10).'%', '#tblcrawlererrors')
        : $oRenderer->renderTile('ok',      $this->lB('htmlchecks.tile-crawlererrors'), $iCountCrawlererrors, '', '')
    )
    . ($iCountShortTitles
        ? $oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-short-title'), $iMinTitleLength), $iCountShortTitles, (floor($iCountShortTitles/$iRessourcesCount*1000)/10).'%', '#tblshorttitle')
        : $oRenderer->renderTile('ok',      sprintf($this->lB('htmlchecks.tile-check-short-title'), $iMinTitleLength), $iCountShortTitles, '', '')
    )
    . ($iCountShortDescr
        ? $oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-short-description'), $iMinDescriptionLength), $iCountShortDescr, (floor($iCountShortDescr/$iRessourcesCount*1000)/10).'%', '#tblshortdescription')
        : $oRenderer->renderTile('ok',      sprintf($this->lB('htmlchecks.tile-check-short-description'), $iMinDescriptionLength), $iCountShortDescr, '', '')
    )
    . ($iCountShortKeywords
        ? $oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-short-keywords'), $iMinKeywordsLength), $iCountShortKeywords, (floor($iCountShortKeywords/$iRessourcesCount*1000)/10).'%', '#tblshortkeywords')
        : $oRenderer->renderTile('ok',      sprintf($this->lB('htmlchecks.tile-check-short-keywords'), $iMinKeywordsLength), $iCountShortKeywords, '', '')
    )
    . ($iCountLongload
        ? $oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-loadtime-of-pages'), $iMaxLoadtime), $iCountLongload, (floor($iCountLongload/$iRessourcesCount*1000)/10).'%', '#tblloadtimepages')
        : $oRenderer->renderTile('ok',      sprintf($this->lB('htmlchecks.tile-check-loadtime-of-pages'), $iMaxLoadtime), $iCountLongload, '', '')
    )
    . ($iCountLargePages
        ? $oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-large-pages'), $iMaxPagesize), $iCountLargePages, (floor($iCountLargePages/$iRessourcesCount*1000)/10).'%', '#tbllargepages')
        : $oRenderer->renderTile('ok',      sprintf($this->lB('htmlchecks.tile-check-large-pages'), $iMaxPagesize), $iCountLargePages, '', '')
    )
    ;

$sReturn.=$oRenderer->renderTileBar($sTiles, '').'<div style="clear: both;"></div>'
        . '<p>'.$this->lB('htmlchecks.overview.introtext').'</p>'
        ;

if ($iCountCrawlererrors) {
    $sReturn.= '<h3 id="tblcrawlererrors">' . sprintf($this->lB('htmlchecks.tableCrawlererrors'), $iCountCrawlererrors) . '</h3>'
        .'<p>'.$this->lB('htmlchecks.tableCrawlererrors.description').'</p>'
        .$this->_getHtmlchecksChart($iRessourcesCount, $iCountCrawlererrors)    
        .$this->_getHtmlchecksTable('select title, length(title) as length, url
            from pages 
            where siteid='.$this->_sTab.' and length(title)<'.$iMinTitleLength.'
            order by length(title)',
            'tableCrawlerErrors'
        );
}
// for the other charts: 
$iRessourcesCount=$iRessourcesCount-$iCountCrawlererrors;


// ----------------------------------------------------------------------
// table with too short titles
// ----------------------------------------------------------------------
if ($iCountShortTitles) {
    $sReturn.= '<h3 id="tblshorttitle">' . sprintf($this->lB('htmlchecks.tableShortTitles'), $iCountShortTitles) . '</h3>'
        . '<div style="float: right; margin: 0 0 1em 1em;">'
            .$this->_getHtmlchecksChart($iRessourcesCount, $iCountShortTitles-$iCountNoTitle, $iCountNoTitle)
        . '</div>'
        .$oRenderer->renderTileBar(
                ($iCountNoTitle ? 
                    $oRenderer->renderTile('error', $this->lB('htmlchecks.tile-check-no-title'), $iCountNoTitle,(floor($iCountNoTitle/$iRessourcesCount*1000)/10).'%', '#tblshorttitle') 
                    : '')
                .$oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-short-title'), $iMinTitleLength), $iCountShortTitles-$iCountNoTitle, (floor(($iCountShortTitles-$iCountNoTitle)/$iRessourcesCount*1000)/10).'%', '#tblshorttitle')
                , '')
        .'<div style="clear: left;"></div>'
        .'<p>'.sprintf($this->lB('htmlchecks.tableShortTitles.description'), $iMinTitleLength).'</p>'
        .'<div style="clear: both;"></div>'
        .$this->_getHtmlchecksTable('select title, length(title) as length, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and length(title)<'.$iMinTitleLength.'
            order by length(title), title',
            'tableShortTitles'
        );
}

// ----------------------------------------------------------------------
// too short descriptions
// ----------------------------------------------------------------------
if ($iCountShortDescr) {
    $iCountNoDescr=$this->_getHtmlchecksCount('description', 1);
    $sReturn.= '<h3 id="tblshortdescription">' . sprintf($this->lB('htmlchecks.tableShortDescription'), $iCountShortDescr) . '</h3>'
        . '<div style="float: right; margin: 0 0 1em 1em;">'
            .$this->_getHtmlchecksChart($iRessourcesCount, $iCountShortDescr-$iCountNoDescr, $iCountNoDescr) 
        . '</div>'
        .$oRenderer->renderTileBar(
                ($iCountNoDescr ? 
                    $oRenderer->renderTile('error', $this->lB('htmlchecks.tile-check-no-description'), $iCountNoDescr,(floor($iCountNoDescr/$iRessourcesCount*1000)/10).'%', '#tblshortdescription') 
                    : '')
                .$oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-short-description'), $iMinDescriptionLength), $iCountShortDescr-$iCountNoDescr, (floor(($iCountShortDescr-$iCountNoDescr)/$iRessourcesCount*1000)/10).'%', '#tblshortdescription')
                , '')
        .'<div style="clear: left;"></div>'
        .'<p>'.sprintf($this->lB('htmlchecks.tableShortDescription.description'), $iMinDescriptionLength).'</p>'
        .'<div style="clear: both;"></div>'
        .$this->_getHtmlchecksTable('select description, length(description) as length, title, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and length(description)<'.$iMinDescriptionLength.'
            order by length, description'                        
            /*
            ,
            array(
                'pages',
                array('description',  $this->oDB->raw('length(description) as length'),'title', 'url'),
                array(
                    'AND'=>array(
                        'siteid'=>$this->_sTab,
                        'length(title)[<]'=>$iMinTitleLength,
                    ),
                    'ORDER' => array("length"=>"ASC", 'description'=>'ASC'),
                )
            )
             * 
             */
            ,
            'tableShortDescr'
        );
}

// ----------------------------------------------------------------------
// table with too short keyword
// ----------------------------------------------------------------------
if ($iCountShortKeywords) {
    $iCountNoKeywords=$this->_getHtmlchecksCount('keywords', 1);
    $sReturn.= '<h3 id="tblshortkeywords">' . sprintf($this->lB('htmlchecks.tableShortKeywords'), $iCountShortKeywords) . '</h3>'
        . '<div style="float: right; margin: 0 0 1em 1em;">'
            .$this->_getHtmlchecksChart($iRessourcesCount, $iCountShortKeywords-$iCountNoKeywords, $iCountNoKeywords)    
        . '</div>'
        .$oRenderer->renderTileBar(
                ($iCountNoKeywords ? 
                    $oRenderer->renderTile('error', $this->lB('htmlchecks.tile-check-no-keywords'), $iCountNoKeywords,(floor($iCountNoKeywords/$iRessourcesCount*1000)/10).'%', '#tblshortkeywords') 
                    : '')
                .$oRenderer->renderTile('warning', sprintf($this->lB('htmlchecks.tile-check-short-keywords'), $iMinKeywordsLength), $iCountShortKeywords-$iCountNoKeywords, (floor(($iCountShortKeywords-$iCountNoKeywords)/$iRessourcesCount*1000)/10).'%', '#tblshortkeywords')
                , '')
        .'<div style="clear: left;"></div>'
        .'<p>'.sprintf($this->lB('htmlchecks.tableShortKeywords.description'), $iMinKeywordsLength).'</p>'
        .'<div style="clear: both;"></div>'
        .$this->_getHtmlchecksTable('select keywords, length(keywords) as length, title, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and length(keywords)<'.$iMinKeywordsLength.'
            order by length, keywords',
            'tableShortKeywords'
        );
}

// ----------------------------------------------------------------------
// long loading pages
// ----------------------------------------------------------------------
if ($iCountLongload) {
    $sReturn.= '<h3 id="tblloadtimepages">' . sprintf($this->lB('htmlchecks.tableLoadtimePages'), $iCountLongload) . '</h3>'
        .'<p>'.sprintf($this->lB('htmlchecks.tableLoadtimePages.description'), $iMaxLoadtime).'</p>'
        .$this->_getHtmlchecksChart($iRessourcesCount, $iCountLongload)
        .$this->_getHtmlchecksTable('select title, time, size, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and time>'.$iMaxLoadtime.'
            order by time',
            'tableLongLoad'
        );
}

// ----------------------------------------------------------------------
// large pages
// ----------------------------------------------------------------------
if ($iCountLargePages) {
    $sReturn.= '<h3 id="tbllargepages">' . sprintf($this->lB('htmlchecks.tableLargePages'), $iCountLargePages) . '</h3>'
        .'<p>'.sprintf($this->lB('htmlchecks.tableLargePages.description'), $iMaxPagesize).'</p>'
        .$this->_getHtmlchecksChart($iRessourcesCount, $iCountLargePages)
        .$this->_getHtmlchecksTable('select title, size, time, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and size>'.$iMaxPagesize.'
            order by size',
            'tableLargePages'
        );
}


// ----------------------------------------------------------------------
// javascript: define datatables
// ----------------------------------------------------------------------
$sReturn.='<script>$(document).ready(function () {'
        . '$(\'#tableCrawlerErrors\').DataTable({"aaSorting":[[1,"asc"]]});'
        . '$(\'#tableShortTitles\').DataTable({"aaSorting":[[1,"asc"]]});'
        . '$(\'#tableShortDescr\').DataTable({"aaSorting":[[1,"asc"]]});'
        . '$(\'#tableShortKeywords\').DataTable({"aaSorting":[[1,"asc"]]});'
        . '$(\'#tableLongLoad\').DataTable({"aaSorting":[[1,"desc"]]});'
        . '$(\'#tableLargePages\').DataTable({"aaSorting":[[1,"desc"]]});'
        . '} );'
        . '</script>';

return $sReturn;
