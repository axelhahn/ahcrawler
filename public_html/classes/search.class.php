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
 * DOCS https://www.axel-hahn.de/docs/ahcrawler/
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
 * 2024-09-13  v0.167  php8 only; add typed variables; use short array syntax
 * 
 */
class ahsearch extends crawler_base
{

    // ----------------------------------------------------------------------
    // searchresults
    // ----------------------------------------------------------------------
    /*
    private $_aRankCounter = [
        'matchWord' => [
            'title' => 50,
            'keywords' => 50,
            'description' => 50,
            'url' => 500,
            'content' => 5,
        ],
        'WordStart' => [
            'title' => 20,
            'keywords' => 20,
            'description' => 20,
            'url' => 30,
            'content' => 3,
        ],
        'any' => [
            'title' => 2,
            'keywords' => 2,
            'description' => 2,
            'url' => 5,
            'content' => 1,
        ],
    ];
     */

    /**
     * Config array for ranking weights
     * @var array
     */
    private array $_aRankCounter = [];


    // ----------------------------------------------------------------------
    /**
     * Constructor 
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct(int $iSiteId = 0)
    {

        $aOptions = $this->getEffectiveOptions();
        $this->_aRankCounter = $aOptions['searchindex']['rankingWeights'];

        $this->setSiteId($iSiteId);
        $this->setLangFrontend();
    }

    /**
     * Get a value from $_POST or $_GET
     * @param string $sKey
     * @return mixed
     */
    public function getQueryValue(string $sKey): mixed
    {
        $aSource = (isset($_POST) && is_array($_POST) && count($_POST))
            ? $_POST
            : ((isset($_GET) && is_array($_GET) && count($_GET))
                ? $_GET
                : false
            )
        ;
        if (!$aSource) {
            return false;
        }
        if (!isset($aSource[$sKey]) || !$aSource[$sKey]) {
            return false;
        }

        // TODO: clean value
        return $aSource[$sKey];
    }

    /**
     * Get categories to search to render a dropdown
     * It returns the structure from config below 
     * profiles -> [id] -> searchcategories
     * 
     * @param bool $bAddNone  Flag to add an empty category; dafault: false (= no)
     * @return array
     */
    public function getSearchCategories(bool $bAddNone = false): array
    {
        $aReturn = [];
        if (!isset($this->aProfileSaved['frontend']['searchcategories']) || !count($this->aProfileSaved['frontend']['searchcategories'])) {
            return [];
        }
        if ($bAddNone) {
            $aReturn[$this->lF('label.searchsubdir-none')] = '';
        }
        return array_merge($aReturn, $this->aProfileSaved['frontend']['searchcategories']);
    }
    /**
     * get languages ... it returns the structure from config 
     * below profiles -> [id] -> searchlang
     * 
     * @param bool $bAddNone  Flag to add an empty field; dafault: false (= no)
     * @return array
     */
    public function getSearchLang(bool $bAddNone = false): array
    {
        $aReturn = [];
        if (!isset($this->aProfileSaved['frontend']['searchlang']) || !count($this->aProfileSaved['frontend']['searchlang'])) {
            return [];
        }
        if ($bAddNone) {
            $aReturn[$this->lF('label.searchlang-none')] = '';
        }
        foreach ($this->aProfileSaved['frontend']['searchlang'] as $sMyLang) {
            $aReturn[$sMyLang] = $sMyLang;
        }
        return $aReturn;
    }

    // ----------------------------------------------------------------------
    // ACTIONS SEARCH
    // ----------------------------------------------------------------------

    /**
     * Create a search phrase for WHERE clause
     * This method replaces % with [%]
     * 
     * @param string   $sTerm  search term
     * @param boolean  $bLike  surround % for search with like (for Medoo)
     * @return string
     */
    private function _replaceSearchterm4Sql(string $sTerm, bool $bLike = true): string
    {
        $sReturn = $sTerm;
        if (strstr($sReturn, '%')) {
            $sReturn = str_replace('%', '/%', $sTerm);
            // for Medoo: if the search term contains a % then it
            // does not surround the searchterm with % on using like
            $sReturn = $bLike ? "%$sReturn%" : $sReturn;
        }

        return $sReturn;
    }

    /**
     * Do search through pages and return an array with metadata, timers 
     * and results ordered by ranking.
     * It returns false if
     * - no site id was set
     * - the query is empty
     * 
     * @param string  $q          search string
     * @param array $aOptions     options
     *   url    {string}  limit url i.e. //[domain]/[path] - without "%"
     *   subdir {string}  => subset of search without domain with starting slash (/[path])
     *   mode   {string}  one of AND | OR | PHRASE (default: AND)
     *   lang   {string}  force language of the document; default: all
     * @return array
     */
    public function search(string $q, array $aOptions = []): bool|array
    {
        $iTimerStart = microtime(true);
        $sContentDbColumn = 'content'; // could switch to "response"
        $aResult = [];
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
            $aOptions = [];
        }

        // get GET / POST values from a sent form
        foreach (['subdir', 'lang', 'mode', 'contentcolumn'] as $sOptionKey) {
            $sVal = $this->getQueryValue($sOptionKey);
            if ($sVal) {
                $aOptions[$sOptionKey] = $sVal;
            }
        }

        // echo '<pre>'.print_r($aOptions,1).'</pre>';
        if (!array_key_exists('url', $aOptions)) {
            $aOptions['url'] = '//' . parse_url($this->aProfileSaved['searchindex']['urls2crawl'][0], PHP_URL_HOST);
        }

        if (array_key_exists('subdir', $aOptions)) {
            $aOptions['url'] .= str_replace('//', '/', '/' . $aOptions['subdir']);
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

        $sPhrase = $this->_replaceSearchterm4Sql($q);
        if ($aOptions['mode'] === 'PHRASE') {
            $aQuery['OR'] = [
                'title[~]' => $sPhrase,
                'description[~]' => $sPhrase,
                'keywords[~]' => $sPhrase,
                'url[~]' => $sPhrase,
                $sContentDbColumn . '[~]' => $sPhrase,
            ];
            $aOptions['mode'] = 'AND';
        } else {
            foreach (explode(" ", $q) as $sWord) {
                $sPhrase = $this->_replaceSearchterm4Sql($sWord);
                $aQuery['OR # query for [' . $sWord . ']'] = [
                    'title[~]' => $sPhrase,
                    'description[~]' => $sPhrase,
                    'keywords[~]' => $sPhrase,
                    'url[~]' => $sPhrase,
                    $sContentDbColumn . '[~]' => $sPhrase,
                ];
            }
        }
        // print_r($aOptions);echo "<hr>";
        $aSelect = [
            'siteid' => $this->iSiteId,
            'errorcount' => 0,
            'url[~]' => $aOptions['url'],
            $aOptions['mode'] => $aQuery,
        ];

        if (isset($aOptions['lang']) && $aOptions['lang']) {
            $aSelect['lang'] = $aOptions['lang'];
        }
        $iTimerStartQuery = microtime(true);

        $aDbitems = $this->oDB->select(
            'pages',
            ['id', 'url', 'lang', 'title', 'description', 'keywords', $sContentDbColumn . '(content)', 'ts'],
            [
                'AND' => $aSelect,
                // LIMIT on db can miss best ranked items 
                // 'LIMIT' => 55
            ]
        );
        /*
        echo "DEBUG aQuery = <pre>" . print_r($aQuery, 1) ."</pre><br>";
        echo 'DEBUG: Query = ' . $this->oDB->last() . '<br>';
        echo "DEBUG aOptions = <pre>" . print_r($aOptions, 1) ."</pre><br>"; 
         */
        $iTimerStartRanking = microtime(true);
        if (is_array($aDbitems) && count($aDbitems)) {
            $aResult = $this->_reorderByRanking($aDbitems, $q);
            /*
            while(count($aResult)>55){
                array_pop($aResult);
            } 
            */
        }
        $iTimerEnd = microtime(true);
        $aTimers = [
            'prepare' => ($iTimerStartQuery - $iTimerStart) * 1000,
            'dbquery' => ($iTimerStartRanking - $iTimerStartQuery) * 1000,
            'sorting' => ($iTimerEnd - $iTimerStartRanking) * 1000,
            'total' => ($iTimerEnd - $iTimerStart) * 1000,
        ];

        $aReturn = [
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
     * Get valid keywords by last word of a searchstring ordered by count of 
     * results
     * 
     * @param string  $q  search string
     * @return boolean|array
    public function searchKeyword__(string $q): 
    {
        if (!$this->iSiteId) {
            return false;
        }
        $aTmp = explode(" ", $q);
        $sWord = array_pop($aTmp);
        if (!$sWord) {
            return false;
        }
        $aQuery = [
            'OR' => [
                'word' => $sWord,
                'word[~]' => $sWord,
            ]
        ];
        $aResult = $this->oDB->select(
            'words',
            ['word', 'count'],
            [
                'AND' => [
                    'siteid' => $this->iSiteId,
                    'AND' => $aQuery,
                ],
                "order" => 'count',
                "LIMIT" => 11
            ]
        );
        // echo $this->oDB->last_query() . "\n";
        // print_r($aResult);
        return $aResult;
    }
     */

    /**
     * Get valid titles by word of a searchstring
     * @param string  $q  search string
     * @return boolean|array
    public function searchTitle__(string $q): bool|array
    {
        if (!$this->iSiteId) {
            return false;
        }
        $aQuery = [
            'OR' => [
                'title[~]' => $q,
            ]
        ];
        $aResult = $this->oDB->select(
            'pages',
            ['title', 'url'],
            [
                'AND' => [
                    'siteid' => $this->iSiteId,
                    'AND' => $aQuery,
                ],
                "order" => 'title',
                "LIMIT" => 11
            ]
        );
        // echo $this->oDB->last_query() . "\n";
        // print_r($aResult);
        return $aResult;
    }
     */

    /**
     * Get array with hit counts of different type
     * @param string $sNeedle
     * @param string $sHaystack
     * @return array
     */
    private function _countHits(string $sNeedle, string $sHaystack): array
    {

        $iMatchWord = 0;
        $iWordStart = 0;

        // ----- matching word
        $a1 = [];

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
        $a2 = [];

        // detect searchterm as word start 
        preg_match_all('/\W' . $sNeedle . '/i', $sHaystack, $a2);
        $iWordStart += is_array($a2) ? count($a2[0]) : 0;

        // detect searchterm on start of text
        preg_match_all('/^' . $sNeedle . '/i', $sHaystack, $a1);
        $iWordStart += is_array($a1) ? count($a1[0]) : 0;

        // ----- any hit
        preg_match_all('/' . $sNeedle . '/i', $sHaystack, $a3);

        return [
            'matchWord' => $iMatchWord,
            'WordStart' => $iWordStart - $iMatchWord,
            'any' => is_array($a3) ? count($a3[0]) - $iWordStart : 0,
        ];
    }

    /**
     * Get html code with marked span tags in a given content
     * 
     * @param  string  $sText  Text to handle
     * @param  array   $aTags  list of tags to mark
     * @return string
     */
    private function _marktags(string $sText, array $aTags): string
    {
        $iWord = 0;
        // $sReturn=strip_tags($sText);
        $sReturn = $sText;
        foreach ($aTags as $sWord) {
            $iWord++;
            $sReturn = preg_replace('@('.$sWord.')@i', '<mark class="mark'.$iWord.'">\1</mark>', $sReturn);
        }
        return $sReturn;
    }

    /**
     * Reorder search result by getting weight and ranking; ordered by most
     * relevant item
     * 
     * @param array   $aData  searchresult from $this->search()
     * @param string  $q      search query
     * @return array
     */
    private function _reorderByRanking(array $aData, string $q): array
    {
        $aReturn = [];
        if (!is_array($aData) || !count($aData)) {
            return $aReturn;
        }
        $aSearchwords = explode(" ", $q);
        foreach ($aData as $aItem) {
            $iFirstContent = -1;
            $iCount = 0;
            $sUrl = $aItem['url'];

            // TODO: customize replacement
            $aItem['url'] = basename($aItem['url']);
            $aItem['url'] = str_replace('id_', '', $aItem['url']);
            $aItem['url'] = str_replace('.html', '', $aItem['url']);
            $aItem['url'] = str_replace('.php', '', $aItem['url']);
            // echo '['.$aItem['url']."]<br>";
            $aResults = [];
            $aWords = [];
            $sHitsByWord = '';
            foreach ($aSearchwords as $sWord) {

                $aWords[$sWord] = 0;
                $aResults[$sWord] = [];
                $sWordRegex = preg_replace('/([^a-zA-Z0-9])/', '\\\$1', $sWord);
                // in den einzelnen Spalten nach Anzahl Vorkommen des
                // Wortes (Übereinstimmung, am Anfang, irgendwo) suchen und 
                // deren Anzahl Treffer mit dem Ranking-Faktor multiplizieren 
                foreach (['title', 'description', 'keywords', 'url', 'content'] as $sCol) {
                    foreach ($this->_countHits($sWordRegex, $aItem[$sCol]) as $sKey => $iHits) {
                        $iCount += $iHits * $this->_aRankCounter[$sKey][$sCol];
                        $aResults[$sWord][$sKey][$sCol] = [$iHits, $this->_aRankCounter[$sKey][$sCol]];
                        if ($iCount) {
                            $aWords[$sWord] += $iHits;
                        }
                    }
                }
                // echo "DEBUG: Position von $sWord: ".strpos($aItem['content'], $sWord).'<br>';


                $iMyPos = stripos($aItem['content'], $sWord);
                if ($iMyPos !== false) {
                    $iFirstContent = $iFirstContent === -1
                        ? $iMyPos
                        : min($iFirstContent, $iMyPos);
                }

                // add searchterm in found words list
                $sHitsByWord .= ($sHitsByWord ? ' ... ' : '')
                    . ($aWords[$sWord]
                        ? $sWord . ' (' . $aWords[$sWord] . ')'
                        : '<del>' . $sWord . '</del>'
                    );
            }
            $iFirstContent = $iFirstContent ? $iFirstContent : 0;

            // update search result item
            $aItem['url'] = $sUrl;
            $aItem['results'] = $aResults;
            $aItem['weight'] = $iCount;
            $aItem['terms'] = $aWords;
            $aItem['weight'] = $iCount;

            $iStart = max(0, $iFirstContent - 10);
            $aItem['preview'] = ($iStart ? '...' : '')
                . substr($aItem['content'], $iStart, 300) . '...';
            $aItem['hits_per_term'] = $sHitsByWord;

            // add mark tags for found search terms
            foreach (['title', 'description', 'keywords', 'url', 'preview', 'hits_per_term'] as $sCol) {
                $aItem['html_' . $sCol] = $this->_markTags($aItem[$sCol], $aSearchwords);
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
     * Get count of search results 
     * @param array   array of $this->search()
     * @return int
     */
    public function getCountOfSearchresults(array $aResult): int
    {
        $iCounter = 0;
        if (!is_array($aResult)) {
            return 0;
        }
        foreach ($aResult as $iRanking => $aDataItems) {
            $iCounter += count($aDataItems);
        }
        return $iCounter;
    }

    // ----------------------------------------------------------------------
    // render functions to display search form
    // ----------------------------------------------------------------------

    /**
     * Generate attributes for html tags with a given key value hash
     * 
     * @param array $aAttributes  attributes as key=>value items
     * @return string
     */
    protected function _addAttributes(array $aAttributes): string
    {
        $sReturn = '';
        foreach ($aAttributes as $sAttr => $sValue) {
            $sReturn .= " $sAttr=\"$sValue\"";
        }
        return $sReturn;
    }

    /**
     * Generate a html id for a given keyword
     * 
     * @param string $sKeyword
     * @return string
     */
    protected function _getSelectId(string $sKeyword): string
    {
        return 'select' . $sKeyword;
    }

    /**
     * Get HTML code for a label for a given keyword
     * 
     * @param string $sKeyword
     * @param array  $aAttributes
     * @return string
     */
    protected function _renderLabel(string $sKeyword, array $aAttributes = []): string
    {
        if (!isset($aAttributes['for'])) {
            $aAttributes['for'] = $this->_getSelectId($sKeyword);
        }
        return '<label'
            . $this->_addAttributes($aAttributes)
            . '>' . $this->lF('label.search' . $sKeyword) . '</label>'
        ;
    }
    
    /**
     * Get html code for a select form field 
     * 
     * @param array  $aOptions     array with key = visible label; value= value in option
     * @param string $sName        name attribute for select field
     * @param array  $aAttributes  optional: html attributes for select tag
     * @param string $sSelected    value of item to select
     * @return string
     */
    protected function _renderSelect(array $aOptions, string $sName, array $aAttributes = [], string $sSelected = ''): string
    {
        $sReturn = '';
        if ($aOptions) {
            if (!isset($aAttributes['id'])) {
                $aAttributes['id'] = $this->_getSelectId($sName);
            }
            $aAttributes['name'] = $sName;
            if (!$sSelected) {
                $sSelected = $this->getQueryValue($sName);
            }
            foreach ($aOptions as $sLabel => $sValue) {
                $sReturn .= '<option value="' . $sValue . '"' . ($sSelected === $sValue ? ' selected="selected"' : '') . '>' . $sLabel . '</option>';
            }
            $sReturn = '<select' . $this->_addAttributes($aAttributes) . '>' . $sReturn . '</select>';
        }
        return $sReturn;
    }

    /**
     * Get html code to add site id (project) and frontend language
     * 
     * @since v0.98
     * 
     * @return string
     */
    public function renderHiddenfields(): string
    {
        return '<input'
            . $this->_addAttributes([
                'type' => 'hidden',
                'name' => 'siteid',
                'value' => $this->iSiteId,
            ])
            . '>'
            . '<input'
            . $this->_addAttributes([
                'type' => 'hidden',
                'name' => 'guilang',
                'value' => $this->sLang,
            ])
            . '>'
        ;
    }

    /**
     * Get html code for category selection label
     * 
     * @param array  $aAttributes  optional: html attributes for input tag
     * @return string
     */
    public function renderInput(array $aAttributes = []): string
    {
        return '<input'
            . $this->_addAttributes(array_merge([
                'type' => 'text',
                'name' => 'q',
                'id' => 'searchterm',
                'value' => htmlentities($this->getQueryValue('q')),
                'placeholder' => $this->lF('input.search.placeholder'),
                'title' => $this->lF('input.search.title'),
                'pattern' => '^..*',
                'required' => 'required',
            ], $aAttributes))
            . '>';
    }

    /**
     * Get html code for category selection label
     * 
     * @param array $aAttributes  optional: html attributes for label tag
     * @return string
     */
    public function renderLabelCategories(array $aAttributes = []): string
    {
        return $this->_renderLabel('subdir', $aAttributes);
    }

    /**
     * Get html code for lang selection label
     * 
     * @param array $aAttributes  optional: html attributes for label tag
     * @return string
     */
    public function renderLabelLang(array $aAttributes = []): string
    {
        return $this->_renderLabel('lang', $aAttributes);
    }

    /**
     * Get html code for mode selection label
     * 
     * @param array $aAttributes  optional: html attributes for label tag
     * @return string
     */
    public function renderLabelMode(array $aAttributes = []): string
    {
        return $this->_renderLabel('mode', $aAttributes);
    }

    /**
     * Get html code for searchterm label
     * 
     * @param array $aAttributes  optional: html attributes for term tag
     * @return string
     */
    public function renderLabelSearch(array $aAttributes = []): string
    {
        if (!isset($aAttributes['for'])) {
            $aAttributes['for'] = 'searchterm';
        }
        return $this->_renderLabel('term', $aAttributes);
    }

    /**
     * Get html code for category selection 
     * 
     * @param array $aAttributes  optional: html attributes for select tag
     * @return string
     */
    public function renderSelectCategories(array $aAttributes = []): string
    {
        return $this->_renderSelect($this->getSearchCategories(true), 'subdir', $aAttributes);
    }

    /**
     * Get html code for language selection
     * 
     * @param array $aAttributes  optional: html attributes for select tag
     * @return string
     */
    public function renderSelectLang(array $aAttributes = []): string
    {
        return $this->_renderSelect($this->getSearchLang(true), 'lang', $aAttributes);
    }

    /**
     * Get html code for mode selection 
     * 
     * @param array $aAttributes  optional: html attributes for select tag
     * @return string
     */
    public function renderSelectMode(array $aAttributes = []): string
    {
        return $this->_renderSelect([
            $this->lF('label.searchmode-and') => 'AND',
            $this->lF('label.searchmode-or') => 'OR',
            $this->lF('label.searchmode-phrase') => 'PHRASE',
        ], 'mode', $aAttributes);
    }

    /**
     * Get html code for content table selection
     * 
     * @param array $aAttributes  optional: html attributes for select tag
     * @return string
     */
    public function renderSelectContentTable(array $aAttributes = []): string
    {
        return $this->_renderSelect([
            $this->lF('label.search-in-content') => 'content',
            $this->lF('label.search-in-response') => 'response',
        ], 'contentcolumn', $aAttributes);
    }

    /**
     * Get htmlcode for a simple or extended search form
     * 
     * echo $o->renderSearchForm();
     * 
     * // with additional options
     * echo $o->renderSearchForm([
     *     'categories'=>1,
     *     'lang'=>1,
     *     'mode'=>1,
     * ]);

     * @param array $aOptions  options array; valid subkeys: 
     *                         - categories {bool} show subdirs of the website
     *                         - lang       {bool} show languages
     *                         - mode       {bool} show search modes
     *                         Without setting values to true they are hidden
     * @return string
     */
    public function renderSearchForm(array $aOptions = []): string
    {
        $sOptions = (isset($aOptions['categories']) && $aOptions['categories']
            ? '<tr><td>' . $this->renderLabelCategories() . '</td><td>' . $this->renderSelectCategories() . '</td></tr>'
            : '')
            . (isset($aOptions['lang']) && $aOptions['lang']
                ? '<tr><td>' . $this->renderLabelLang() . '</td><td>' . $this->renderSelectLang() . '</td></tr>'
                : '')
            . (isset($aOptions['mode']) && $aOptions['mode']
                ? '<tr><td>' . $this->renderLabelMode() . '</td><td>' . $this->renderSelectMode() . '</td></tr>'
                : '')
        ;
        $sReturn = '<form method="GET" action="?">'
            . $this->renderHiddenfields()
            . $this->lF('label.searchhelp') . '<br><br>'
            . $this->renderLabelSearch() . ': '
            . $this->renderInput(['size' => '50'])
            . ($sOptions ? '<br><br><strong>' . $this->lF('label.searchoptions') . '</strong>:<br><table>' . $sOptions . '</table><hr>' : '')
            . '<button>' . $this->lF('btn.search.label') . '</button>'
            . '</form>'
        ;
        return $sReturn;
    }
    // ----------------------------------------------------------------------
    // render function to display search result
    // ----------------------------------------------------------------------

    /**
     * Do search and render search results
     * 
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
    public function renderSearchresults(array $aParams = []): string
    {
        $aData = [];
        $iHits = 0;
        $sCss = '
            <!-- default css code added by ' . __CLASS__ . ' -->
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
        $iLimit = (isset($aParams['limit']) && (int) $aParams['limit'] ? (int) $aParams['limit'] : 50);
        $q = trim(isset($aParams['q']) ? $aParams['q'] : $this->getQueryValue('q'));

        // output of results:
        $sHead = '';
        $sResults = '';

        if ($q) {
            $aSet = [];
            foreach (['url', 'subdir', 'mode', 'lang'] as $sMyKey) {
                if (isset($aParams[$sMyKey])) {
                    $aSet[$sMyKey] = $aParams[$sMyKey];
                }
            }
            $aData = $this->search($q, $aSet);

            // $iHits = $this->getCountOfSearchresults($aData);
            $iHits = isset($aData['meta']['result_count']) ? $aData['meta']['result_count'] : 0;

            // LIMIT output ... maybe add a paging?
            while (count($aData['data']) > $iLimit) {
                array_pop($aData['data']);
            }

            // echo '<pre>'.print_r($_SERVER, 1).'</pre>'; die();
            $client_ip = $_SERVER['REMOTE_ADDR'] ?: $_SERVER['HTTP_X_FORWARDED_FOR'];
            $aResult = $this->oDB->insert(
                'searches',
                [
                    'ts' => date("Y-m-d H:i:s"),
                    'siteid' => $this->iSiteId,
                    'searchset' => $aSet,
                    'query' => $q,
                    'results' => $iHits,
                    'host' => $client_ip,
                    'ua' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-',
                    'referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '-',
                ]
            );
            // echo "\n" . $this->oDB->last() . '<br>'; 

            $sTplHead = isset($aParams['head'])
                ? $aParams['head']
                : (isset($aParams['result']) ? '' : $sCss) . '
                    <strong>{{RESULTS}}</strong> ({{TOTALTIME}})<br><br>
                    <p>{{HITS}}</p>
                '
            ;
            $sTplResult = isset($aParams['result']) ? $aParams['result'] : '
                <div class="searchresult">
                    <div class="bar">
                        <span>{{PERCENT}}%</span>
                        <div class="bar2" style="width: {{PERCENT}}%">&nbsp;</div>
                    </div>
                    <a href="{{URL}}">({{COUNTER}}) {{HTML_TITLE}}</a> <span class="date">{{AGE}}</span><br>

                    <div class="url">{{HTML_URL}}</div>
                    <div class="detail">
                        {{HTML_DETAIL}}<br>
                        &gt; {{HTML_TERMS}}
                    </div>
                </div>'
            ;

            $sInsResults = $this->lF('searchout.results');
            if (!$iHits) {
                $sInsHits = $this->lF('searchout.nohit');
            } else {
                if ($iHits > $iLimit) {
                    $sInsHits = $this->lF('searchout.too-many-hits');
                } else {
                    $sInsHits = sprintf($this->lF('searchout.hits'), $iHits);
                }
                $aMappingSearch = [
                    '{{RESULTS}}' => $sInsResults,
                    '{{HITS}}' => $sInsHits,
                    '{{HITCOUNT}}' => $iHits,
                    '{{TOTALTIME}}' => sprintf($this->lF('searchout.totaltime'), $aData['meta']['timers']['total']),
                ];
                $sHead = str_replace(
                    array_keys($aMappingSearch),
                    array_values($aMappingSearch),
                    $sTplHead
                );

                $iMaxRanking = false;
                $iCounter = 0;
                foreach ($aData['data'] as $iRanking => $aDataItems) {
                    if (!$iMaxRanking) {
                        $iMaxRanking = $iRanking ? $iRanking : 1;
                    }
                    foreach ($aDataItems as $aItem) {
                        $sAge = round((date("U") - date("U", strtotime($aItem['ts']))) / 60 / 60 / 24);
                        $sAge = $sAge > 1 ? sprintf($this->lF('searchout.days'), $sAge) : $this->lF('searchout.today');
                        $iCounter++;
                        $aMappingSearchterm = [
                            '{{COUNTER}}' => $iCounter,
                            '{{URL}}' => $aItem['url'],
                            '{{TITLE}}' => $aItem['title'],
                            '{{DESCRIPTION}}' => $aItem['description'],
                            '{{KEYWORDS}}' => $aItem['keywords'],
                            '{{DETAIL}}' => $aItem['preview'],
                            '{{TERMS}}' => $aItem['hits_per_term'],

                            '{{LANG}}' => $aItem['lang'],
                            '{{PERCENT}}' => round($iRanking / $iMaxRanking * 100),
                            '{{AGE}}' => $sAge,

                            '{{HTML_URL}}' => $aItem['html_url'],
                            '{{HTML_TITLE}}' => $aItem['html_title'],
                            '{{HTML_DESCRIPTION}}' => $aItem['html_description'],
                            '{{HTML_KEYWORDS}}' => $aItem['html_keywords'],
                            '{{HTML_DETAIL}}' => $aItem['html_preview'],
                            '{{HTML_TERMS}}' => $aItem['html_hits_per_term'],
                        ];

                        $sResults .= str_replace(
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
            . 'powered by <a href="' . $this->aAbout['urlDocs'] . '">' . $this->aAbout['product'] . ' ' . $this->aAbout['version'] . '</a>: '
            . $this->LF('about.infostring');
    }

}
