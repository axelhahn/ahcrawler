<?php
/**
 * page analysis :: Html-check
 */
$sReturn = '';

$iMinTitleLength=20;
$iMinDescriptionLength=40;
$iMinKeywordsLength=10;
$iMaxPagesize=150000; // pages large n byte
$iMaxLoadtime=500;   // load time in ms 

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

// --- Warnings from searchindex

$sReturn.=''
        . '<h3>' . $this->lB('htmlchecks.overview') . '</h3>'
        . '<p>'.$this->lB('htmlchecks.overview.introtext').'</p>'
        . '<p>'.$this->lB('status.indexed_urls.label').': <strong>'.$iRessourcesCount.'</strong></p>'
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

$sReturn.='<ul class="tiles warnings">'
    . ($iCountCrawlererrors
        ? '<li><a href="#tblcrawlererrors" class="tile error">'.$this->lB('htmlchecks.tile-crawlererrors').':<br><strong>'.$iCountCrawlererrors.'</strong><br>'.(floor($iCountCrawlererrors/$iRessourcesCount*1000)/10).'%</a></li>'
        : '<li><a href="#" class="tile ok">'.$this->lB('htmlchecks.tile-crawlererrors').':<br><strong>'.$iCountCrawlererrors.'</strong></a></li>'
    )
    . ($iCountShortTitles
        ? '<li><a href="#tblshorttitle" class="tile scroll-link">'.sprintf($this->lB('htmlchecks.tile-check-short-title'), $iMinTitleLength).':<br><strong>'.$iCountShortTitles.'</strong><br>'.(floor($iCountShortTitles/$iRessourcesCount*1000)/10).'%</a></li>'
        : '<li><a href="#" class="tile ok">'.sprintf($this->lB('htmlchecks.tile-check-short-title'), $iMinTitleLength).':<br><strong>'.$iCountShortTitles.'</strong></a></li>'
    )
    . ($iCountShortDescr
        ? '<li><a href="#tblshortdescription" class="tile scroll-link">'.sprintf($this->lB('htmlchecks.tile-check-short-description'), $iMinDescriptionLength).':<br><strong>'.$iCountShortDescr.'</strong><br>'.(floor($iCountShortDescr/$iRessourcesCount*1000)/10).'%</a></li>'
        : '<li><a href="#" class="tile ok">'.sprintf($this->lB('htmlchecks.tile-check-short-description'), $iMinDescriptionLength).':<br><strong>'.$iCountShortDescr.'</strong></a></li>'
    )
    . ($iCountShortKeywords
        ? '<li><a href="#tblshortkeywords" class="tile scroll-link">'.sprintf($this->lB('htmlchecks.tile-check-short-keywords'), $iMinKeywordsLength).':<br><strong>'.$iCountShortKeywords.'</strong><br>'.(floor($iCountShortKeywords/$iRessourcesCount*1000)/10).'%</a></li>'
        : '<li><a href="#" class="tile ok">'.sprintf($this->lB('htmlchecks.tile-check-short-keywords'), $iMinKeywordsLength).':<br><strong>'.$iCountShortKeywords.'</strong></a></li>'
    )
    . ($iCountLongload
        ? '<li><a href="#tblloadtimepages" class="tile scroll-link">'.sprintf($this->lB('htmlchecks.tile-check-loadtime-of-pages'), $iMaxLoadtime).':<br><strong>'.$iCountLongload.'</strong><br>'.(floor($iCountLongload/$iRessourcesCount*1000)/10).'%</a></li>'
        : '<li><a href="#" class="tile ok">'.sprintf($this->lB('htmlchecks.tile-check-loadtime-of-pages'), $iMaxLoadtime).':<br><strong>'.$iCountLongload.'</strong></a></li>'
    )
    . ($iCountLargePages
        ? '<li><a href="#tbllargepages" class="tile scroll-link">'.sprintf($this->lB('htmlchecks.tile-check-large-pages'), $iMaxPagesize).':<br><strong>'.$iCountLargePages.'</strong><br>'.(floor($iCountLargePages/$iRessourcesCount*1000)/10).'%</a></li>'
        : '<li><a href="#" class="tile ok">'.sprintf($this->lB('htmlchecks.tile-check-large-pages'), $iMaxPagesize).':<br><strong>'.$iCountLargePages.'</strong></a></li>'
    )
    . '</ul>'
    . '<div style="clear: both;"></div>'
    ;


// table with too short titles
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

// table with too short titles
if ($iCountShortTitles) {
    $sReturn.= '<h3 id="tblshorttitle">' . sprintf($this->lB('htmlchecks.tableShortTitles'), $iCountShortTitles) . '</h3>'
        .'<p>'.$this->lB('htmlchecks.tableShortTitles.description').'</p>'
        .$this->_getHtmlchecksChart($iRessourcesCount, $iCountShortTitles)    
        .$this->_getHtmlchecksTable('select title, length(title) as length, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and length(title)<'.$iMinTitleLength.'
            order by length(title), title',
            'tableShortTitles'
        );
}

// table with too short descriptions
if ($iCountShortDescr) {
    $sReturn.= '<h3 id="tblshortdescription">' . sprintf($this->lB('htmlchecks.tableShortDescription'), $iCountShortDescr) . '</h3>'
        .'<p>'.$this->lB('htmlchecks.tableShortDescription.description').'</p>'
        .$this->_getHtmlchecksChart($iRessourcesCount, $iCountShortDescr)    
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
// table with too short keyword
if ($iCountShortKeywords) {
    $sReturn.= '<h3 id="tblshortkeywords">' . sprintf($this->lB('htmlchecks.tableShortKeywords'), $iCountShortKeywords) . '</h3>'
        .'<p>'.$this->lB('htmlchecks.tableShortKeywords.description').'</p>'
        .$this->_getHtmlchecksChart($iRessourcesCount, $iCountShortKeywords)    
        .$this->_getHtmlchecksTable('select keywords, length(keywords) as length, title, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and length(keywords)<'.$iMinKeywordsLength.'
            order by length, keywords',
            'tableShortKeywords'
        );
}
if ($iCountLongload) {
    $sReturn.= '<h3 id="tblloadtimepages">' . sprintf($this->lB('htmlchecks.tableLoadtimePages'), $iCountLongload) . '</h3>'
        .'<p>'.$this->lB('htmlchecks.tableLoadtimePages.description').'</p>'
        .$this->_getHtmlchecksChart($iRessourcesCount, $iCountLongload)
        .$this->_getHtmlchecksTable('select title, time, size, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and time>'.$iMaxLoadtime.'
            order by time',
            'tableLongLoad'
        );
}
if ($iCountLargePages) {
    $sReturn.= '<h3 id="tbllargepages">' . sprintf($this->lB('htmlchecks.tableLargePages'), $iCountLargePages) . '</h3>'
        .'<p>'.$this->lB('htmlchecks.tableLargePages.description').'</p>'
        .$this->_getHtmlchecksChart($iRessourcesCount, $iCountLargePages)
        .$this->_getHtmlchecksTable('select title, size, time, url
            from pages 
            where siteid='.$this->_sTab.' and errorcount=0 and size>'.$iMaxPagesize.'
            order by size',
            'tableLargePages'
        );
}

// 

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
