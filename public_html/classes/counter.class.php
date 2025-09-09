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
 * @example <code>
 * // add counters with a value
 * $oCounter=new counter();
 * $oCounter->mysiteid($this->iSiteId);
 * $oCounter->add("mykey-1", $countervalue1);
 * $oCounter->add("mykey-N", $countervalueN);
 * 
 * // cleanup after $_iKeepDays days:
 * $oCounter->cleanup();
 * </code>
 * 
 * ----------------------------------------------------------------------------
 * 
 * 2024-09-03  v0.167  php8 only; add typed variables; use short array syntax
 * */
class counter extends crawler_base
{

    /**
     * name of database table for counters
     * @var string
     */
    private string $_db_table = 'counters';

    /**
     * Name of rel table
     * @var string
     */
    private string $_db_reltable = 'counteritems';

    /**
     * Flag: Use only last counter set on a day
     * @var boolean
     */
    private bool $_bOneValuePerDay = true;

    /**
     * Remove older counter values if older than N days
     * @var integer
     */
    private int $_iKeepDays = 90;

    /**
     * Web ID of site
     * @var int
     */
    private int $_iWebId = 1;

    // ----------------------------------------------------------------------
    // private functions
    // ----------------------------------------------------------------------

    protected function _setDbIfNeeded(): void
    {
        if (!isset($this->oDB)) {
            // echo "ERROS: no database object set - use method setSiteId() first." . __LINE__ . "<br>";
            $this->setSiteId($this->_iWebId);
        }
        if (!isset($this->oDB)) {
            die("ERROR: No database object was set ... and counters class was unable to init it. Use method setSiteId() first.");
        }
    }

    /**
     * Get id of a counter item as integer. If it does not exist and 
     * bAutoCreate is true then it will be generated.
     * Otherwise it returns false.
     * 
     * @param string  $sCounterItem  Name of the counter
     * @param boolean $bAutoCreate   Flag: create counter item if it does not exist; default: false (=NO)
     * @return boolean
     */
    protected function _getCounterId(string $sCounterItem, bool $bAutoCreate = false): bool|int
    {
        $this->_setDbIfNeeded();
        $aResult = $this->oDB->select(
            $this->_db_reltable,
            ['id', 'label'],
            ['label' => $sCounterItem]
        );
        if ($aResult && isset($aResult[0]['id']) && (int) $aResult[0]['id']) {
            return $aResult[0]['id'];
        }
        if ($bAutoCreate) {
            $aInsert = $this->oDB->insert(
                $this->_db_reltable,
                ['label' => $sCounterItem]
            );
            return (int) $this->oDB->id();
        } else {
            return false;
        }
    }

    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------

    /**
     * Add a counter value.
     * Give a name and a value. It will be stored with a timestamp.
     * If global value _bOneValuePerDay is true then all other values of the current day will be deleted.
     * It returns a PDOStatement object if the counter item was added.
     * It returns false if the counter item does not exist.
     * 
     * @param string $sCounterItem  Name of the counter
     * @param mixed $sValue         Value of the counter as int/ string/ json/ ...
     * @return PDOStatement|null|boolean
     */
    public function add(string $sCounterItem, mixed $sValue): PDOStatement|null|bool
    {
        $iCouterId = $this->_getCounterId($sCounterItem, true);
        if ($iCouterId) {

            // optional: delete all values of the current day
            if ($this->_bOneValuePerDay) {
                $aResult = $this->oDB->delete(
                    $this->_db_table,
                    [
                        'siteid' => $this->_iWebId,
                        'counterid' => $iCouterId,
                        'ts[>]' => date("Y-m-d"),
                    ]
                );
            }
            $aResult = $this->oDB->insert(
                $this->_db_table,
                [
                    'siteid' => $this->_iWebId,
                    'counterid' => $iCouterId,
                    'value' => $sValue,
                    'ts' => date("Y-m-d H:i:s"),
                ]
            );
            return $aResult;
        }
        return false;
    }

    /**
     * Set siteid for counters
     * 
     * @param int $iSiteId
     * @return bool
     */
    public function mysiteid(int $iSiteId): bool
    {
        $this->_iWebId = (int) $iSiteId;
        return true;
    }

    // ----------------------------------------------------------------------
    // GETTER
    // ----------------------------------------------------------------------

    /**
     * Cleanup older counter values of the current web
     * @return boolean
     */
    public function cleanup(): bool
    {
        $sDeleteBefore = date("Y-m-d", date("U") - $this->_iKeepDays * 24 * 60 * 60);
        $aResult = $this->oDB->delete(
            $this->_db_table,
            [
                'siteid' => $this->_iWebId,
                // 'counterid' => $iCouterId,
                'ts[<]' => $sDeleteBefore,
            ]
        );
        return true;
    }

    /**
     * Dump all counters in an HTML table
     * It generates output with echo.
     * 
     * @return void
     */
    public function dump(): void
    {
        echo '<pre>'
            . print_r(
                $this->oDB->select(
                    $this->_db_table,
                    ["[>]$this->_db_reltable" => ['counterid' => 'id']],
                    [
                        "$this->_db_table.siteid",
                        "$this->_db_table.counterid",
                        "$this->_db_reltable.label",
                        "$this->_db_table.value",
                        "$this->_db_table.ts",
                    ]
                )
                ,
                1
            )
            . 'SQL: ' . $this->oDB->last() . '<br>'
            . '</pre>'
        ;
    }

    /**
     * Get ids of existing counters.
     * If no $mySiteIdOnly is false it returns *all* counter names.
     * Otherwise it returns only the counter names of the current site.
     * 
     * 
     * @param boolean $bMySiteIdOnly   optional: flag to get only counters of the current site
     * @return array
     */
    public function getCounterItems(bool $bMySiteIdOnly = false): array|null
    {
        $this->_setDbIfNeeded();
        if (!$bMySiteIdOnly) {
            return $this->oDB->select($this->_db_reltable, 'label');
        }
        $aReturn = [];
        $aTmp = $this->oDB->select(
            $this->_db_table,
            ["[>]$this->_db_reltable" => ['counterid' => 'id']],
            [
                "@$this->_db_reltable.label",  // @ is DISTINCT
            ],
            [
                "$this->_db_table.siteid" => $this->_iWebId
            ]
        );
        foreach (array_values($aTmp) as $aData) {
            $aReturn[] = $aData['label'];
        }
        sort($aReturn);

        return $aReturn;
    }

    /**
     * Get last set values of given counter id
     * 
     * @param string  $sCounter  name of counter
     * @param int     $iMax      max count of last values to fetch; default: 30
     * @return array
     */
    public function getCountersHistory(string $sCounter, int $iMax = 30): array
    {
        $this->_setDbIfNeeded();
        $aReturn = $this->oDB->select(
            $this->_db_table,
            ["[>]$this->_db_reltable" => ['counterid' => 'id']],
            [
                // $this->_db_table.'.siteid',
                // $this->_db_table.'.counterid',
                // $this->_db_reltable.'.label',
                "$this->_db_table.value",
                "$this->_db_table.ts",
            ],
            [
                $this->_db_table . '.siteid' => $this->_iWebId,
                $this->_db_reltable . '.label' => $sCounter,
            ],
            [
                "LIMIT" => [0, $iMax],
            ]
        );
        // echo 'SQL: ' . $this->oDB->last().'<br>';
        return $aReturn;
    }

    // ----------------------------------------------------------------------

}
