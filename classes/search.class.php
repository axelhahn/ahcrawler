<?php

require_once 'crawler-base.class.php';

/**
 * 
 * AXLES CRAWLER :: SEARCH
 * 
 * usage:
 * require_once("../search.class.php");
 * 
 * */
class ahsearch extends crawler_base {

    // ----------------------------------------------------------------------
    // searchresults
    // ----------------------------------------------------------------------
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

    // ----------------------------------------------------------------------
    /**
     * new crawler
     * @param integer  $iSiteId  site-id of search index
     */
    public function __construct($iSiteId = false) {
        $this->setSiteId($iSiteId);
        $this->setLangFrontend();
        return true;
    }

    /**
     * get categories to search in ... it returns the structure from config 
     * below profiles -> [id] -> searchcategories
     * @return array
     */
    public function getSearchcategories() {
        if (!is_array($this->aProfile) || !array_key_exists('searchcategories', $this->aProfile) || !count($this->aProfile['searchcategories'])) {
            return false;
        }
        return $this->aProfile['searchcategories'];
    }

    // ----------------------------------------------------------------------
    // ACTIONS SEARCH
    // ----------------------------------------------------------------------

    /**
     * do search through pages
     * @param string  $q          search string
     * @param type $aOptions
     *   url  string  limit url i.e. //[domain]/[path] - without "%"
     *   subdir => subset of search without domain with starting slash (/[path])
     *   mode string  one of AND |OR (default: OR)
     * @param string  $aOptions     options
     * @return array
     */
    public function search($q, $aOptions = array()) {
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

        // echo '<pre>'.print_r($aOptions,1).'</pre>';
        if (!array_key_exists('url', $aOptions)){
            $aOptions['url']='//'.$this->aProfile['searchindex']['stickydomain'];
        }
        if (array_key_exists('subdir', $aOptions)){
            $aOptions['url'].=str_replace('//','/','/'.$aOptions['subdir']);
        }
        
        if (!array_key_exists('mode', $aOptions)) {
            $aOptions['mode'] = 'OR';
        }
        if (!array_key_exists('url', $aOptions)) {
            $aOptions['url'] = '';
        } else {
            // remove '%' ... it is added below in 'url[~]' => $aOptions['url'],
            $aOptions['url'] = str_replace('%', '', $aOptions['url']);
        }
        $aSearchwords = explode(" ", $q);
        foreach ($aSearchwords as $sWord) {
            $aQuery[] = array(
                'title[~]' => $sWord,
                'description[~]' => $sWord,
                'keywords[~]' => $sWord,
                'url[~]' => $sWord,
                'content[~]' => $sWord,
            );
        }
        // print_r($aOptions);echo "<hr>";
        $aResult = $this->oDB->select(
                'pages', 
                array('url', 'title', 'description', 'keywords', 'content', 'ts'), 
                array(
                    'AND' => array(
                        'siteid' => $this->iSiteId,
                        'errorcount' => 0,
                        'url[~]' => $aOptions['url'],
                        'OR' => $aQuery,
                    ),
                        "LIMIT" => 55
                )
        );
        // echo 'DEBUG: ' . $this->oDB->last() . '<br>';
        if (is_array($aResult) && count($aResult)) {
            $aResult = $this->_reorderByRanking($aResult, $q);
        }
        return $aResult;
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

        preg_match_all('/\W' . $sNeedle . '\W/i', $sHaystack, $a1);
        $iMatchWord = is_array($a1) ? count($a1[0]) : 0;
        preg_match_all('/^' . $sNeedle . '\W/i', $sHaystack, $a1);
        $iMatchWord += is_array($a1) ? count($a1[0]) : 0;
        preg_match_all('/^' . $sNeedle . '$/i', $sHaystack, $a1);
        $iMatchWord += is_array($a1) ? count($a1[0]) : 0;

        preg_match_all('/\W' . $sNeedle . '/i', $sHaystack, $a2);
        $iWordStart = is_array($a2) ? count($a2[0]) : 0;
        preg_match_all('/^' . $sNeedle . '\W/i', $sHaystack, $a1);
        $iWordStart += is_array($a2) ? count($a2[0]) : 0;

        preg_match_all('/' . $sNeedle . '/i', $sHaystack, $a3);
        return array(
            'matchWord' => $iMatchWord,
            'WordStart' => $iWordStart,
            'any' => is_array($a3) ? count($a3[0]) : 0,
        );
    }

    /**
     * reorder search result by getting weight and ranking; ordered by most
     * relevant item
     * @param array   $aData  searchresult from $this->search()
     * @param string  $q      search query
     * @return array
     */
    private function _reorderByRanking($aData, $q) {
        $aReturn = array();
        if (!is_array($aData) || !count($aData)) {
            return $aReturn;
        }
        $aSearchwords = explode(" ", $q);
        foreach ($aData as $aItem) {
            $iCount = 0;
            $sUrl = $aItem['url'];
            $aItem['url'] = basename($aItem['url']);
            $aItem['url'] = str_replace('id_', '', $aItem['url']);
            $aItem['url'] = str_replace('.html', '', $aItem['url']);
            $aItem['url'] = str_replace('.php', '', $aItem['url']);
            // echo '['.$aItem['url']."]<br>";
            $aResults = array();
            foreach ($aSearchwords as $sWord) {

                // in den einzelnen Spalten nach Anzahl Vorkommen des
                // Wortes (Übereinstimmung, am Anfang, irgendwo) suchen und 
                // deren Anzahl Treffer mit dem Ranking-Faktor multiplizieren 
                foreach (array('title', 'description', 'keywords', 'url', 'content') as $sCol) {
                    foreach ($this->_countHits($sWord, $aItem[$sCol]) as $sKey => $iHits) {
                        $iCount+=$iHits * $this->_aRankCounter[$sKey][$sCol];
                        $aResults[$sWord][$sKey][$sCol] = $iHits;
                    }
                }
            }
            $aItem['url'] = $sUrl;
            $aItem['results'] = $aResults;
            $aReturn[$iCount][] = $aItem;
        }
        if (count($aReturn)) {
            krsort($aReturn);
        }
        return $aReturn;
    }

    public function getCountOfSearchresults($aResult) {
        $iCounter = 0;
        if (!is_array($aResult)) {
            return false;
        }
        foreach ($aResult as $iRanking => $aDataItems) {
            $aRow = array();
            foreach ($aDataItems as $aItem) {
                // unset($aItem['content']);
                // echo '<pre>'.print_r($aItem, 1); die();
                $iCounter ++;
            }
        }
        return $iCounter;
    }

    /**
     * do search and render search results
     * @param string  $q            search string
     * @param string  $aOptions     options
     *                  url => subset of search, i.e. '//[domain]/[path]'
     *                  subdir => subset of search without domain with starting slash (/[path])
     * @param string  $sOutputType  one of html| ...
     * @return string
     */
    public function renderSearchresults($q, $aOptions = array(), $sOutputType = 'html') {
        $sOut = '';
        $aData = array();
        $iHits = 0;
        $q = trim($q);
        if ($q) {
            $aData = $this->search($q, $aOptions);

            $iHits = $this->getCountOfSearchresults($aData);

            if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $client_ip = $_SERVER['REMOTE_ADDR'];
            } else {
                $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            $aResult = $this->oDB->insert(
                    'searches', array(
                'ts' => date("Y-m-d H:i:s"),
                'siteid' => $this->iSiteId,
                'searchset' => $aOptions,
                'query' => $q,
                'results' => $iHits,
                'host' => $client_ip,
                'ua' => $_SERVER['HTTP_USER_AGENT'],
                'referrer' => $_SERVER['HTTP_REFERER'],
                    )
            );
            //echo "\n" . $this->oDB->last_query() . '<br>';
        }

        switch ($sOutputType) {
            case 'html':
                if (!$iHits) {
                    $sOut = $q ? '<p>' . $this->lF('searchout.nohit') . '</p>' : '';
                } else {
                    $sOut = '
                        <style>
                        .searchresult{margin: 0 0 1em 0; border: 0px solid #eee; border-left: 0px solid #eee; padding: 0.5em;}
                        .searchresult:hover{background:#fafafa;}
                        .searchresult a{color:#44a; font-size: 120%;}
                        .searchresult .date{color:#fa3; font-style: italic; font-size: 80%;}
                        .searchresult .url{color:#393;}
                        .searchresult .detail{color:#888;}
                        .searchresult .bar{width: 20%; height: 3em; border-top: 1px solid #eee; float: right; margin-right: 1em; color:#888; }
                        .searchresult .bar2{background:#e0f0ea; height: 1.5em; }
                        
                        .searchresult .mark1{background:#fd3;}
                        .searchresult .mark2{background:#3f3;}
                        .searchresult .mark3{background:#f88;}
                        .searchresult .mark4{background:#ccf;}
                        
                        </style>';
                    if ($iHits > 50) {
                        $sOut .= '<p>' . $this->lF('searchout.too-many-hits') . '<br><br></p>';
                    } else {
                        $sOut .= '<p>' . sprintf($this->lF('searchout.hits'), $iHits) . '</p>';
                    }
                    $iMaxRanking = false;
                    foreach ($aData as $iRanking => $aDataItems) {
                        if (!$iMaxRanking) {
                            $iMaxRanking = $iRanking;
                        }
                        foreach ($aDataItems as $aItem) {
                            $sAge = round((date("U") - date("U", strtotime($aItem['ts'])) ) / 60 / 60 / 24);
                            $sAge = $sAge > 1 ? '(' . $sAge . ' Tage)' : '';

                            $sDetail = '';
                            if ($aItem['description']) {
                                $sDetail.=$aItem['description'] . '<br>';
                            }
                            // $sDetail.= '<pre>'.print_r($aItem['results'],true) . '</pre>';
                            //echo "<pre>" . print_r($aItem,1 ) . "</pre>";
                            $aPreviews = array();
                            $aSearchwords = explode(" ", $q);
                            foreach ($aSearchwords as $sWord) {

                                $iLastPos = 0;
                                $iSurround = 30;
                                while (!stripos($aItem['content'], $sWord, $iLastPos) === false) {
                                    $iLastPos = stripos($aItem['content'], $sWord, $iLastPos);
                                    $aPreviews[$iLastPos] = substr($aItem['content'], $iLastPos - $iSurround, ($iSurround * 4 + strlen($sWord)));
                                    $iLastPos++;
                                }
                            }
                            ksort($aPreviews);
                            // echo "<pre>" . print_r($aPreviews,1 ) . "</pre>";

                            if (count($aPreviews)) {
                                $iPreview = 0;
                                foreach ($aPreviews as $sPreview) {
                                    $iPreview++;
                                    if ($iPreview > 1) {
                                        $iMore = count($aPreviews) - $iPreview;
                                        // TODO: langTxt
                                        $sDetail.='... ' . $iMore . ' weitere' . ($iMore === 1 ? 'r' : '') . ' Treffer im Text';
                                        break;
                                    }
                                    $sDetail.='...' . $sPreview . '...<br>';
                                }
                            }
                            $iWord = 0;
                            foreach ($aSearchwords as $sWord) {
                                $iWord++;
                                $sClass = "mark${iWord}";
                                $sDetail = preg_replace('@' . $sWord . '@i', '<span class="' . $sClass . '">\\0</span>', $sDetail);
                            }

                            $sOut.='
                            <div class="searchresult" CConclickCC="location.href=\'' . $aItem['url'] . '\';">
                                <div class="bar">
                                    <span style="float: right">' . round($iRanking / $iMaxRanking * 100) . '%</span>
                                    <div class="bar2" style="width: ' . round($iRanking / $iMaxRanking * 100) . '%">&nbsp;</div>
                                </div>
                               <a href="' . $aItem['url'] . '">' . $aItem['title'] . '</a> <span class="date">' . $sAge . '</span><br>

                                <div class="url">' . $aItem['url'] . '</div>
                                <div class="detail">'
                                    . $sDetail . '
                                </div>
                            </div>
                                 ';
                        }
                    }
                }

                break;

            default:
                break;
        }
        return $sOut 
                . '<br>'
                . 'powered by <a href="'.$this->aAbout['urlDocs'].'">' . $this->aAbout['product'].' '.$this->aAbout['version'].'</a>: '
                . $this->LF('about.infostring');
    }

}
