<?php

require_once 'crawler-base.class.php';

/**
 * ____________________________________________________________________________
 *          __    ______                    __             
 *   ____ _/ /_  / ____/________ __      __/ /__  _____    
 *  / __ `/ __ \/ /   / ___/ __ `/ | /| / / / _ \/ ___/    
 * / /_/ / / / / /___/ /  / /_/ /| |/ |/ / /  __/ /        
 * \__,_/_/ /_/\____/_/   \__,_/ |__/|__/_/\___/_/         
 * ____________________________________________________________________________ 
 * Free software and OpenSource * GNU GPL 3
 * DOCS https://www.axel-hahn.de/docs/ahcrawler/index.htm
 * 
 * THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE <br>
 * LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR <br>
 * OTHER PARTIES PROVIDE THE PROGRAM ?AS IS? WITHOUT WARRANTY OF ANY KIND, <br>
 * EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED <br>
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE <br>
 * ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. <br>
 * SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY <br>
 * SERVICING, REPAIR OR CORRECTION.<br>
 * 
 * ----------------------------------------------------------------------------
 * SEARCH
 * 
 * usage:
 * require_once("../search.class.php");
 * 
 * */
class ahsearch extends crawler_base {

    // ----------------------------------------------------------------------
    // searchresults
    // ----------------------------------------------------------------------
    /*
    private $_aRankCounter = array(
        'matchWord' => array(
            'title' => 50,
            'keywords' => 50,
            'description' => 50,
            'url' => 500,
            'content' => 5,
        ),
        'WordStart' => array(
            'title' => 20,
            'keywords' => 20,
            'description' => 20,
            'url' => 30,
            'content' => 3,
        ),
        'any' => array(
            'title' => 2,
            'keywords' => 2,
            'description' => 2,
            'url' => 5,
            'content' => 1,
        ),
    );
     */
    private $_aRankCounter = array();
    
    private $_aFormNames = array(
        'language'=>'lang',
        'category'=>'subdir',
    );

    // ----------------------------------------------------------------------
    /**
     * new crawler
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct($iSiteId = false) {
        
        $aOptions=$this->getEffectiveOptions();
        $this->_aRankCounter = $aOptions['searchindex']['rankingWeights'];

        $this->setSiteId($iSiteId);
        $this->setLangFrontend();
        return true;
    }

    public function getQueryValue($sKey){
        $aSource=(isset($_POST) && is_array($_POST) && count($_POST))
                ? $_POST
                : ((isset($_GET) && is_array($_GET) && count($_GET))
                    ? $_GET
                    : false
                )
                ;
        if(!$aSource){
            return false;
        }
        if(!isset($aSource[$sKey]) || !$aSource[$sKey]){
            return false;
        }
        
        // TODO: clean value
        return $aSource[$sKey];
    }
    
    /**
     * get categories to search in ... it returns the structure from config 
     * below profiles -> [id] -> searchcategories
     * @return array
     */
    public function getSearchCategories($bAddNone=false) {
        $aReturn=array();
        if (!isset($this->aProfileSaved['frontend']['searchcategories']) || !count($this->aProfileSaved['frontend']['searchcategories'])) {
            return false;
        }
        if($bAddNone){
            $aReturn[$this->lF('label.searchsubdir-none')]='';
        }
        return array_merge($aReturn, $this->aProfileSaved['frontend']['searchcategories']);
    }
    /**
     * get categories to search in ... it returns the structure from config 
     * below profiles -> [id] -> searchlang
     * @return array
     */
    public function getSearchLang($bAddNone=false) {
        $aReturn=array();
        if (!isset($this->aProfileSaved['frontend']['searchlang']) || !count($this->aProfileSaved['frontend']['searchlang'])) {
            return false;
        }
        if($bAddNone){
            $aReturn[$this->lF('label.searchlang-none')]='';
        }
        foreach ($this->aProfileSaved['frontend']['searchlang'] as $sMyLang){
            $aReturn[$sMyLang]=$sMyLang;
        }
        return $aReturn;
    }
    
    public function __TO_REMOVE__getSearchtermsOfUsers(){
            $sQuery=''
                    . 'SELECT query, count(query) as count, results '
                    . 'FROM searches '
                    . 'WHERE siteid = '.$this->_sTab.' '
                    . 'AND ts > \''.date("Y-m-d H:i:s", (date("U") - (60 * 60 * 24 * $iDays))).'\' '
                    . 'GROUP BY query '
                    . 'ORDER BY count desc, query asc '
                    . 'LIMIT 0,10';
            $oResult=$this->oDB->query($sQuery);
            
            /*
             * TODO: FIX ME
            $oResult = $this->oDB->select(
                    'searches', 
                    array('ts', 'query', 'count(query) as count', 'results'),
                    array(
                        'AND' => array(
                            'siteid' => $this->_sTab,
                            '[>]ts' => date("Y-m-d H:i:s", (date("U") - (60 * 60 * 24 * $iDays))),
                        ),
                        "GROUP" => "query",
                        "ORDER" => array("count"=>"DESC", "query"=>"asc"),
                        "LIMIT" => 10
                    )
            );
             */
            
            // echo "$sQuery ".($oResult ? "OK" : "fail")."<br>";
            $aSearches[$iDays]=($oResult ? $oResult->fetchAll(PDO::FETCH_ASSOC) : array());
    }
            
            
    // ----------------------------------------------------------------------
    // ACTIONS SEARCH
    // ----------------------------------------------------------------------

    /**
     * create a search phrase for WHERE clause
     * This method replaces % with [%]
     * 
     * @param string   $sTerm  search term
     * @param boolean  $bLike  surround % for search with like (for Medoo)
     * @return type
     */
    private function _replaceSearchterm4Sql($sTerm, $bLike=true){
        $sReturn=$sTerm;
        if(strpos($sReturn, '%')!==false){
            $sReturn=str_replace('%', '/%', $sTerm);
            // for Medoo: if the search term contains a % then it
            // does not surround the searchterm with % on using like
            $sReturn=$bLike ? '%'.$sReturn.'%' : $sReturn;
        }
        
        return $sReturn;
    }
    
    /**
     * do search through pages
     * @param string  $q          search string
     * @param type $aOptions
     *   url    {string}  limit url i.e. //[domain]/[path] - without "%"
     *   subdir {string}  => subset of search without domain with starting slash (/[path])
     *   mode   {string}  one of AND | OR | PHRASE (default: AND)
     *   lang   {string}  force language of the document; default: all
     * @param string  $aOptions     options
     * @return array
     */
    public function search($q, $aOptions = array()) {
        $iTimerStart=microtime(true);
        $sContentDbColumn='content'; // could switch to "response"
        $aResult=array();
        if (!$this->iSiteId) {
            // echo "ABORT - keine this->iSiteId = ".$this->iSiteId."<br>";
            return false;
        }
        // $this->_scanKeywords();
        // echo "DEBUG: q = $q<br>\n<pre>".print_r($aOptions, 1)."</pre><br>\n";
        if (!$q) {
            return false;
        }

        if (!is_array($aOptions)) {
            $aOptions = array();
        }
        
        // get GET / POST values from a sent form
        foreach(array('subdir', 'lang', 'mode', 'contentcolumn') as $sOptionKey){
            $sVal=$this->getQueryValue($sOptionKey);
            if ($sVal){
                $aOptions[$sOptionKey]=$sVal;
            }
        }

        // echo '<pre>'.print_r($aOptions,1).'</pre>';
        if (!array_key_exists('url', $aOptions)){
            $aOptions['url']='//'.parse_url($this->aProfileSaved['searchindex']['urls2crawl'][0], PHP_URL_HOST);
        }
        
        if (array_key_exists('subdir', $aOptions)){
            $aOptions['url'].=str_replace('//','/','/'.$aOptions['subdir']);
        }
        
        if (!array_key_exists('mode', $aOptions)) {
            $aOptions['mode'] = 'AND';
        }
        
        // --- prepare search options
        if (!array_key_exists('url', $aOptions)) {
            $aOptions['url'] = '';
        } else {
            // remove '%' ... it is added below in 'url[~]' => $aOptions['url'],
            // $aOptions['url'] = str_replace('%', '', $aOptions['url']);
        }
        // added in v0.155
        if (isset($aOptions['contentcolumn']) && $aOptions['contentcolumn']) {
            $sContentDbColumn = $aOptions['contentcolumn'];
        }

        $sPhrase=$this->_replaceSearchterm4Sql($q);
        if($aOptions['mode']==='PHRASE'){
            $aQuery['OR'] = array(
                'title[~]' => $sPhrase,
                'description[~]' =>$sPhrase,
                'keywords[~]' => $sPhrase,
                'url[~]' => $sPhrase,
                $sContentDbColumn.'[~]' => $sPhrase,
            );
            $aOptions['mode']='AND';
        } else {
            foreach (explode(" ", $q) as $sWord) {
                $sPhrase=$this->_replaceSearchterm4Sql($sWord);
                $aQuery['OR # query for ['.$sWord.']'] = array(
                    'title[~]' => $sPhrase,
                    'description[~]' => $sPhrase,
                    'keywords[~]' => $sPhrase,
                    'url[~]' => $sPhrase,
                    $sContentDbColumn.'[~]' => $sPhrase,
                );
            }
        }
        // print_r($aOptions);echo "<hr>";
        $aSelect=array(
            'siteid' => $this->iSiteId,
            'errorcount' => 0,
            'url[~]' => $aOptions['url'],
            $aOptions['mode'] => $aQuery,
        );
        
        if (isset($aOptions['lang']) && $aOptions['lang']){
            $aSelect['lang']=$aOptions['lang'];
        }
        $iTimerStartQuery=microtime(true);
        
        $aDbitems = $this->oDB->select(
                'pages', 
                array('id', 'url', 'lang', 'title', 'description', 'keywords', $sContentDbColumn.'(content)', 'ts'), 
                array(
                    'AND' => $aSelect,
                    // LIMIT on db can miss best ranked items 
                    // 'LIMIT' => 55
                )
        );
        /*
        echo "DEBUG aQuery = <pre>" . print_r($aQuery, 1) ."</pre><br>";
        echo 'DEBUG: Query = ' . $this->oDB->last() . '<br>';
        echo "DEBUG aOptions = <pre>" . print_r($aOptions, 1) ."</pre><br>"; 
         */
        $iTimerStartRanking=microtime(true);
        if (is_array($aDbitems) && count($aDbitems)) {
            $aResult = $this->_reorderByRanking($aDbitems, $q);
            /*
            while(count($aResult)>55){
                array_pop($aResult);
            } 
            */
        }
        $iTimerEnd=microtime(true);
        $aTimers = [
            'prepare'=>($iTimerStartQuery-$iTimerStart)*1000,
            'dbquery'=>($iTimerStartRanking-$iTimerStartQuery)*1000,
            'sorting'=>($iTimerEnd-$iTimerStartRanking)*1000,
            'total'=>($iTimerEnd-$iTimerStart)*1000,
        ];

        $aReturn=[
            'meta' => [
                'query' => $q,
                'options' => $aOptions,
                'timers' => $aTimers,
                'result_count' => $this->getCountOfSearchresults($aResult),
            ],
            'data' => $aResult,
        ];
        // echo "DEBUG ".__METHOD__."() <pre>".print_r($aReturn, 1)."</pre>"; die();

        return $aReturn;
    }

    /**
     * get valid keywords by last word of a searchstring ordered by count of results
     * @param string  $q  search string
     * @return array
     */
    public function searchKeyword($q) {
        if (!$this->iSiteId) {
            return false;
        }
        $aTmp = explode(" ", $q);
        $sWord = array_pop($aTmp);
        if (!$sWord) {
            return false;
        }
        $aQuery = array(
            'OR' => array(
                'word' => $sWord,
                'word[~]' => $sWord,
            )
        );
        $aResult = $this->oDB->select(
                'words', array('word', 'count'), array(
            'AND' => array(
                'siteid' => $this->iSiteId,
                'AND' => $aQuery,
            ),
            "order" => 'count',
            "LIMIT" => 11
                )
        );
        // echo $this->oDB->last_query() . "\n";
        // print_r($aResult);
        return $aResult;
    }

    /**
     * get valid titles by word of a searchstring
     * @param string  $q  search string
     * @return array
     */
    public function searchTitle($q) {
        if (!$this->iSiteId) {
            return false;
        }
        $aQuery = array(
            'OR' => array(
                'title[~]' => $q,
            )
        );
        $aResult = $this->oDB->select(
                'pages', array('title', 'url'), array(
            'AND' => array(
                'siteid' => $this->iSiteId,
                'AND' => $aQuery,
            ),
            "order" => 'title',
            "LIMIT" => 11
                )
        );
        // echo $this->oDB->last_query() . "\n";
        // print_r($aResult);
        return $aResult;
    }

    /**
     * get array with hit counts of different type
     * @param string $sNeedle
     * @param string $sHaystack
     * @return array
     */
    private function _countHits($sNeedle, $sHaystack) {

        $iMatchWord=0;
        $iWordStart=0;
        
        // ----- matching word
        $a1=array();
        
        // detect a searchterm within the text
        preg_match_all('/\W' . $sNeedle . '\W/i', $sHaystack, $a1);
        $iMatchWord += is_array($a1) ? count($a1[0]) : 0;
        
        // detect a searchterm at the end of the text
        preg_match_all('/\W' . $sNeedle . '$/i', $sHaystack, $a1);
        $iMatchWord += is_array($a1) ? count($a1[0]) : 0;

        // detect a searchterm on start of the text
        preg_match_all('/^' . $sNeedle . '\W/i', $sHaystack, $a1);
        $iMatchWord += is_array($a1) ? count($a1[0]) : 0;

        // detect a searchterm as complete text
        preg_match_all('/^' . $sNeedle . '$/i', $sHaystack, $a1);
        $iMatchWord += is_array($a1) ? count($a1[0]) : 0;

        // ----- word start
        $a2=array();

        // detect searchterm as word start 
        preg_match_all('/\W' . $sNeedle . '/i', $sHaystack, $a2);
        $iWordStart += is_array($a2) ? count($a2[0]) : 0;

        // detect searchterm on start of text
        preg_match_all('/^' . $sNeedle . '/i', $sHaystack, $a1);
        $iWordStart += is_array($a1) ? count($a1[0]) : 0;

        // ----- any hit
        preg_match_all('/' . $sNeedle . '/i', $sHaystack, $a3);

        return array(
            'matchWord' => $iMatchWord,
            'WordStart' => $iWordStart-$iMatchWord,
            'any' => is_array($a3) ? count($a3[0]) - $iWordStart : 0,
        );
    }

    /**
     * mark tags in a given texr
     * @param  string  $sText  Text to handle
     * @param  array   $aTags  list of tags to mark
     */
    private function _marktags($sText, $aTags){
        $iWord=0;
        // $sReturn=strip_tags($sText);
        $sReturn=$sText;
        foreach($aTags as $sWord){
            $iWord++;
            $sReturn= preg_replace('@('.$sWord.')@i', '<mark class="mark'.$iWord.'">\1</mark>', $sReturn);
        }
        return $sReturn;
    }
    /**
     * reorder search result by getting weight and ranking; ordered by most
     * relevant item
     * @param array   $aData  searchresult from $this->search()
     * @param string  $q      search query
     * @return array
     */
    private function _reorderByRanking($aData, $q) {
        $aReturn = [];
        if (!is_array($aData) || !count($aData)) {
            return $aReturn;
        }
        $aSearchwords = explode(" ", $q);
        foreach ($aData as $aItem) {
            $iFirstContent=-1;
            $iCount = 0;
            $sUrl = $aItem['url'];

            // TODO: customize replacement
            $aItem['url'] = basename($aItem['url']);
            $aItem['url'] = str_replace('id_', '', $aItem['url']);
            $aItem['url'] = str_replace('.html', '', $aItem['url']);
            $aItem['url'] = str_replace('.php', '', $aItem['url']);
            // echo '['.$aItem['url']."]<br>";
            $aResults = array();
            $aWords = [];
            $sHitsByWord='';
            foreach ($aSearchwords as $sWord) {

                $aWords[$sWord]=0;
                $aResults[$sWord]=[];
                $sWordRegex= preg_replace('/([^a-zA-Z0-9])/', '\\\$1', $sWord);
                // in den einzelnen Spalten nach Anzahl Vorkommen des
                // Wortes (Übereinstimmung, am Anfang, irgendwo) suchen und 
                // deren Anzahl Treffer mit dem Ranking-Faktor multiplizieren 
                foreach (array('title', 'description', 'keywords', 'url', 'content') as $sCol) {
                    foreach ($this->_countHits($sWordRegex, $aItem[$sCol]) as $sKey => $iHits) {
                        $iCount+=$iHits * $this->_aRankCounter[$sKey][$sCol];
                        $aResults[$sWord][$sKey][$sCol] = array($iHits, $this->_aRankCounter[$sKey][$sCol]);
                        if($iCount){
                            $aWords[$sWord]+=$iHits;
                        }
                    }
                }
                // echo "DEBUG: Position von $sWord: ".strpos($aItem['content'], $sWord).'<br>';


                $iMyPos=stripos($aItem['content'], $sWord);
                if($iMyPos!==false){
                    $iFirstContent=$iFirstContent===-1
                        ? $iMyPos
                        : min($iFirstContent, $iMyPos);
                }

                // add searchterm in found words list
                $sHitsByWord.=($sHitsByWord ? ' ... ': '' ) 
                    .($aWords[$sWord] 
                        ? $sWord.' ('.$aWords[$sWord].')'
                        :'<del>'.$sWord.'</del>'
                    );
            }
            $iFirstContent=$iFirstContent ? $iFirstContent : 0;

            // update search result item
            $aItem['url'] = $sUrl;
            $aItem['results'] = $aResults;
            $aItem['weight'] = $iCount;
            $aItem['terms'] = $aWords;
            $aItem['weight'] = $iCount;

            $iStart=max(0,$iFirstContent-10);
            $aItem['preview'] = ($iStart ? '...' : '' )
                .substr($aItem['content'], $iStart, 300).'...';
            $aItem['hits_per_term'] = $sHitsByWord;

            // add mark tags for found search terms
            foreach (array('title', 'description', 'keywords', 'url', 'preview', 'hits_per_term') as $sCol) {
                $aItem['html_'.$sCol] = $this->_markTags($aItem[$sCol], $aSearchwords);
            }
            
            // unset($aItem['content']);
            $aReturn[$iCount][] = $aItem;
        }
        if (count($aReturn)) {
            krsort($aReturn);
        }
        return $aReturn;
    }

    /**
     * get count of search results 
     * @param array   array of $this->search()
     * @return int
     */
    public function getCountOfSearchresults($aResult) {
        $iCounter = 0;
        if (!is_array($aResult)) {
            return 0;
        }
        foreach ($aResult as $iRanking => $aDataItems) {
            $iCounter+=count($aDataItems);
        }
        return $iCounter;
    }

    // ----------------------------------------------------------------------
    // render functions to display search form
    // ----------------------------------------------------------------------
    
        /**
         * generate attributes for html tags with a given kay value hash
         * @param array $aAttributes  attributes as key=>value items
         * @return string
         */
        protected function _addAttributes($aAttributes){
            $sReturn='';
            foreach($aAttributes as $sAttr=>$sValue){
                $sReturn.=' '.$sAttr.'="'.$sValue.'"';
            }
            return $sReturn;
        }
        protected function _getSelectId($sKeyword){
            return 'select'.$sKeyword;
        }

        protected function _renderLabel($sKeyword, $aAttributes=array()){
            if(!isset($aAttributes['for'])){
                $aAttributes['for']=$this->_getSelectId($sKeyword);
            }
            return '<label'
                . $this->_addAttributes($aAttributes)
                . '>'. $this->lF('label.search'.$sKeyword) .'</label>'
                ;
        }
        /**
         * return html code for a select form field 
         * 
         * @param array  $aOptions     array with key = visible label; value= value in option
         * @param string $sName        name attribute for select field
        *  @param array  $aAttributes  optional: html attributes for select tag
         * @param string $sSelected    value of item to select
         * @return string
         */
        protected function _renderSelect($aOptions, $sName, $aAttributes=array(), $sSelected=false){
            $sReturn='';
            if ($aOptions){
                if(!isset($aAttributes['id'])){
                    $aAttributes['id']=$this->_getSelectId($sName);
                }
                $aAttributes['name']=$sName;
                if(!$sSelected){
                    $sSelected=$this->getQueryValue($sName);
                }
                foreach ($aOptions as $sLabel=>$sValue){
                    $sReturn.='<option value="'.$sValue.'"'.($sSelected===$sValue?' selected="selected"':'').'>'.$sLabel.'</option>';
                }
                $sReturn='<select' . $this->_addAttributes($aAttributes) . '>'.$sReturn.'</select>';
            }
            return $sReturn;
        }
        
    /**
     * get html code to add site id (project) and frontend language
     * @since v0.98
     * @return string
     */
    public function renderHiddenfields(){
        return '<input'
            . $this->_addAttributes(array(
                'type'=>'hidden',
                'name'=>'siteid',
                'value'=>$this->iSiteId,
            ))
            .'>'
            . '<input'
            . $this->_addAttributes(array(
                'type'=>'hidden',
                'name'=>'guilang',
                'value'=>$this->sLang,
            ))
            .'>'
            ;
    }

    /**
     * get html code for category selection label
     * @param array  $aAttributes  optional: html attributes for input tag
     * @return string
     */
    public function renderInput($aAttributes=array()){
        return '<input'
            . $this->_addAttributes(array_merge(array(
                'type'=>'text',
                'name'=>'q',
                'id'=>'searchterm',
                'value'=>htmlentities($this->getQueryValue('q')),
                'placeholder'=>$this->lF('input.search.placeholder'),
                'title'=>$this->lF('input.search.title'),
                'pattern'=>'^..*',
                'required'=>'required',
            ),$aAttributes))
            .'>';
    }
    /**
     * get html code for category selection label
     * @return string
     */
    public function renderLabelCategories($aAttributes=array()){
        return $this->_renderLabel('subdir', $aAttributes);
    }
    /**
     * get html code for lang selection label
     * @return string
     */
    public function renderLabelLang($aAttributes=array()){
        return $this->_renderLabel('lang', $aAttributes);
    }
    /**
     * get html code for mode selection label
     * @return string
     */
    public function renderLabelMode($aAttributes=array()){
        return $this->_renderLabel('mode', $aAttributes);
    }
    /**
     * get html code for searchterm label
     * @return string
     */
    public function renderLabelSearch($aAttributes=array()){
        if(!isset($aAttributes['for'])){
            $aAttributes['for']='searchterm';
        }
        return $this->_renderLabel('term', $aAttributes);
    }

    
    /**
     * get html code for category selection 
     * @return string
     */
    public function renderSelectCategories($aAttributes=array()){
        return $this->_renderSelect($this->getSearchCategories(true), 'subdir', $aAttributes);
    }
    /**
     * get html code for language selection 
     * @return string
     */
    public function renderSelectLang($aAttributes=array()){
        return $this->_renderSelect($this->getSearchLang(true), 'lang', $aAttributes);
    }
    /**
     * get html code for mode selection 
     * @return string
     */
    public function renderSelectMode($aAttributes=array()){
        return $this->_renderSelect(array(
            $this->lF('label.searchmode-and')=>'AND',
            $this->lF('label.searchmode-or')=>'OR',
            $this->lF('label.searchmode-phrase')=>'PHRASE',
        ), 'mode', $aAttributes);
    }
    /**
     * get html code for content table selection
     * @return string
     */
    public function renderSelectContentTable($aAttributes=array()){
        return $this->_renderSelect(array(
            $this->lF('label.search-in-content')=>'content',
            $this->lF('label.search-in-response')=>'response',
        ), 'contentcolumn', $aAttributes);
    }

    /**
     * get htmlcode for a simple or extended search form
     * 
     * echo $o->renderSearchForm();
     * 
     * // with additional options
     * echo $o->renderSearchForm(array(
     *     'categories'=>1,
     *     'lang'=>1,
     *     'mode'=>1,
     * ));

     * @param type $aOptions
     * @return string
     */
    public function renderSearchForm($aOptions=array()){
        $sOptions=(isset($aOptions['categories']) && $aOptions['categories'] 
                ? '<tr><td>'.$this->renderLabelCategories() .'</td><td>'. $this->renderSelectCategories().'</td></tr>'
                : '')
            .(isset($aOptions['lang']) && $aOptions['lang'] 
                ? '<tr><td>'.$this->renderLabelLang() .'</td><td>'. $this->renderSelectLang().'</td></tr>'
                : '')
            .(isset($aOptions['mode']) && $aOptions['mode'] 
                ? '<tr><td>'.$this->renderLabelMode() .'</td><td>'. $this->renderSelectMode().'</td></tr>'
                : '')
            ;
        $sReturn='<form method="GET" action="?">'
                . $this->renderHiddenfields()
                . $this->lF('label.searchhelp').'<br><br>'
                . $this->renderLabelSearch().': '
                . $this->renderInput(array('size'=>'50'))
                . ($sOptions ? '<br><br><strong>'.$this->lF('label.searchoptions').'</strong>:<br><table>'.$sOptions.'</table><hr>' : '')
                .'<button>'.$this->lF('btn.search.label').'</button>'
                .'</form>'
                ;
        return $sReturn;
    }
    // ----------------------------------------------------------------------
    // render function to display search result
    // ----------------------------------------------------------------------
    
    /**
     * do search and render search results
     * @param string  $aParams     search options understanding those keys:
     * 
     *                  q      {string}  search string
     * 
     *                  url    {string}  limit url i.e. //[domain]/[path] - without "%"
     *                  subdir {string}  => subset of search without domain with starting slash (/[path])
     *                  mode   {string}  one of AND | OR | PHRASE (default: AND)
     *                  lang   {string}  force language of the document; default: all
     * 
     *                  limit  {integer} limit of max search results; default: 50
     * 
     *                  head   {string}  html template code for header before result list
     *                  result {string}  html template code for each result
     * @return string
     */
    public function renderSearchresults($aParams = array()) {
        $sOut = '';
        $aData = array();
        $iHits = 0;
        $sCss='
            <!-- default css code added by '.__CLASS__.' -->
            <style>
                .searchresult{margin: 0 0 1em 0; border: 0px solid #eee; border-left: 0px solid #eee; padding: 0.5em;}
                .searchresult:hover{background:#fafafa;}
                .searchresult a{color:#44a; font-size: 120%;}
                .searchresult .date{color:#fa3; font-style: italic; font-size: 80%;}
                .searchresult .url{color:#393;}
                .searchresult .detail{color:#888;}
                .searchresult .bar{width: 20%; height: 3em; border-top: 1px solid #ddd; float: right; margin-right: 1em; color:#888; }
                .searchresult .bar span{float: right}
                .searchresult .bar2{background:#e0f0ea; height: 1.5em; }
                .searchresult .bar2{background:#ced; height: 1.5em; }
               
                .searchresult del mark{background: none !important;}
                .searchresult .mark1{background:#fea;}
                .searchresult .mark2{background:#dfd;}
                .searchresult .mark3{background:#ddf;}
                .searchresult .mark4{background:#fdf;}
                .searchresult .mark5{background:#fcc;}

            </style>
            <!-- /css -->
            ';
        $iLimit=(isset($aParams['limit']) && (int)$aParams['limit'] ? (int)$aParams['limit'] : 50);
        $q=trim(isset($aParams['q']) ? $aParams['q'] : $this->getQueryValue('q'));

        // output of results:
        $sHead='';
        $sResults='';
        
        if ($q) {
            $aSet=array();
            foreach (array('url', 'subdir', 'mode', 'lang') as $sMyKey){
                if(isset($aParams[$sMyKey])){
                    $aSet[$sMyKey]=$aParams[$sMyKey];
                }
            }
            $aData = $this->search($q, $aSet);

            // $iHits = $this->getCountOfSearchresults($aData);
            $iHits = isset($aData['meta']['result_count']) ? $aData['meta']['result_count'] : 0;
            
            // LIMIT output ... maybe add a paging?
            while(count($aData['data'])>$iLimit){
                array_pop($aData['data']);
            } 

            // echo '<pre>'.print_r($_SERVER, 1).'</pre>'; die();
            if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $client_ip = $_SERVER['REMOTE_ADDR'];
            } else {
                $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            $aResult = $this->oDB->insert(
                    'searches', array(
                        'ts' => date("Y-m-d H:i:s"),
                        'siteid' => $this->iSiteId,
                        'searchset' => $aSet,
                        'query' => $q,
                        'results' => $iHits,
                        'host' => $client_ip,
                        'ua' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-',
                        'referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '-',
                    )
            );
            // echo "\n" . $this->oDB->last() . '<br>'; 

            $sTplHead=isset($aParams['head']) 
                ? $aParams['head'] 
                : (isset($aParams['result']) ? '' : $sCss ) . '
                    <strong>{{RESULTS}}</strong> ({{TOTALTIME}})<br><br>
                    <p>{{HITS}}</p>
                '
                ;
            $sTplResult=isset($aParams['result']) ? $aParams['result'] : '
                <div class="searchresult">
                    <div class="bar">
                        <span>{{PERCENT}}%</span>
                        <div class="bar2" style="width: {{PERCENT}}%">&nbsp;</div>
                    </div>
                    <a href="{{URL}}">{{COUNTER}} / {{HITCOUNT}}) {{HTML_TITLE}}</a> <span class="date">{{AGE}}</span><br>

                    <div class="url">{{HTML_URL}}</div>
                    <div class="detail">
                        {{HTML_DETAIL}}<br>
                        &gt; {{HTML_TERMS}}
                    </div>
                </div>'
                ;
                
            $sTplFoot='';
            
            
            $sInsResults=$this->lF('searchout.results');
            if (!$iHits) {
                $sInsHits=$this->lF('searchout.nohit');
            } else {
                if ($iHits > $iLimit) {
                    $sInsHits=$this->lF('searchout.too-many-hits');
                } else {
                    $sInsHits=sprintf($this->lF('searchout.hits'), $iHits);
                }
                $aMappingSearch=[
                    '{{RESULTS}}' => $sInsResults,
                    '{{HITS}}' => $sInsHits,
                    '{{HITCOUNT}}' => $iHits,
                    '{{TOTALTIME}}' => sprintf("%01.1f", $aData['meta']['timers']['total']) . " ms",
                ];
                $sHead=str_replace(
                    array_keys($aMappingSearch),
                    array_values($aMappingSearch),
                    $sTplHead
                );
                
                $iMaxRanking = false;
                $iCounter=0;
                foreach ($aData['data'] as $iRanking => $aDataItems) {
                    if (!$iMaxRanking) {
                        $iMaxRanking = $iRanking ? $iRanking : 1;
                    }
                    foreach ($aDataItems as $aItem) {
                        $sAge = round((date("U") - date("U", strtotime($aItem['ts'])) ) / 60 / 60 / 24);
                        $sAge = $sAge > 1 ? sprintf($this->lF('searchout.days'), $sAge) : $this->lF('searchout.today');
                        $iCounter++;
                        $aMappingSearchterm=[
                            '{{COUNTER}}'          => $iCounter,
                            '{{URL}}'              => $aItem['url'],
                            '{{TITLE}}'            => $aItem['title'],
                            '{{DESCRIPTION}}'      => $aItem['description'],
                            '{{KEYWORDS}}'         => $aItem['keywords'],
                            '{{DETAIL}}'           => $aItem['preview'],
                            '{{TERMS}}'            => $aItem['hits_per_term'],

                            '{{LANG}}'             => $aItem['lang'],
                            '{{PERCENT}}'          => round($iRanking / $iMaxRanking * 100), 
                            '{{AGE}}'              => $sAge,

                            '{{HTML_URL}}'         => $aItem['html_url'],
                            '{{HTML_TITLE}}'       => $aItem['html_title'],
                            '{{HTML_DESCRIPTION}}' => $aItem['html_description'],
                            '{{HTML_KEYWORDS}}'    => $aItem['html_keywords'],
                            '{{HTML_DETAIL}}'      => $aItem['html_preview'],
                            '{{HTML_TERMS}}'       => $aItem['html_hits_per_term'],
                        ];

                        $sResults.=str_replace(
                            array_keys(array_merge($aMappingSearchterm, $aMappingSearch)),
                            array_values(array_merge($aMappingSearchterm, $aMappingSearch)),
                            $sTplResult
                        );
                    }
                }
            }

        }
        return ''
                . $sHead
                . $sResults
                . '<br>'
                . 'powered by <a href="'.$this->aAbout['urlDocs'].'">' . $this->aAbout['product'].' '.$this->aAbout['version'].'</a>: '
                . $this->LF('about.infostring');
    }

}
