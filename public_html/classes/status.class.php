<?php
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
 * status ... set, read, delete status of a running action to prevent
 * multiple simultanous actions
 * 
 * 2017-04-nn          created
 * 2024-09-10  v0.167  php8 only; add typed variables; use short array syntax
 **/
class status
{

    /**
     * Timeout in sec
     * @var int
     */
    protected int $_iTimeout = 10; // value in sec

    /**
     * List of messages
     * @var array
     */
    protected array $_aMsg = [];

    // ----------------------------------------------------------------------

    /**
     * Constructor - new status
     */
    public function __construct()
    {
    }

    // ----------------------------------------------------------------------
    // PRIVATE
    // ----------------------------------------------------------------------

    /**
     * Get a (maybe) currently running action and return the id of it; 
     * it returns false or the name of the action
     * 
     * @return boolean|string
     */
    public function _getAction(): bool|string
    {
        $sFile = $this->_getTouchfile();

        // if there is no action then return false
        if (!file_exists($sFile)) {
            return false;
        }
        // if file is too old return false
        $aTmp = stat($sFile);
        if (date("U") - $aTmp['mtime'] > $this->_iTimeout) {
            $this->_delete();
            return false;
        }
        // 
        $aMsg = json_decode(file_get_contents($sFile), 1);
        if (!is_array($aMsg) || !array_key_exists('action', $aMsg)) {
            $this->_delete();
            return false;
        }
        $this->_aMsg = $aMsg;
        return $aMsg['action'];
    }

    /**
     * Generate a touchfile (lock file)
     * @return string
     */
    private function _getTouchfile(): string
    {
        return dirname(__DIR__) . '/data/ahcrawler_.lock';
        /*
        return is_writable(sys_get_temp_dir())
            ? sys_get_temp_dir() . '/ahcrawler_' . md5(__DIR__) . '.lock'
            : dirname(__DIR__) . '/data/ahcrawler_.lock'
        ;
        */
    }

    /**
     * Delete touchfile
     * @return boolean success
     */
    private function _delete(): bool
    {
        /*
        echo "STATUS: stopping " 
        . (array_key_exists('action', $this->_aMsg) ? $this->_aMsg['action'] : '')
        ."\n";
         * 
         */
        return unlink($this->_getTouchfile());
    }

    /**
     * Create or update touchfile
     * @return boolean|integer success
     */
    private function _save(): bool|int
    {
        // echo "STATUS: running " . $this->_aMsg['action']."\n";
        $sFile = $this->_getTouchfile();

        // $this->_aMsg['last']=microtime(1);
        $this->_aMsg['last'] = date("U");
        // file_put_contents does not update mtime ... so I delete it first
        if (file_exists($sFile)) {
            unlink($sFile);
        }
        return file_put_contents($sFile, json_encode($this->_aMsg));
    }
    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------

    /**
     * Start an action and save current action as running process
     * 
     * @param string $sMsgId  name of the process
     * @return boolean
     */
    public function startAction(string $sMsgId): bool
    {
        $aCurrent = $this->_getAction();
        if ($aCurrent) {
            return false;
        }
        $this->_aMsg = [
            'action' => $sMsgId,
            'start' => date("U"),
            'last' => date("U"),
        ];
        $this->_save();
        return true;
    }

    /**
     * Update .. set info that a process is still running
     * @param string $sMsgId   name of the process
     * @param string $sMessage message text
     * @return boolean
     */
    public function updateAction(string $sMsgId, string $sMessage): bool
    {
        $aCurrent = $this->_getAction();
        if ($aCurrent !== $sMsgId) {
            return false;
        }
        $this->_aMsg['lastmessage'] = $sMessage;
        $this->_save();
        return true;
    }

    /**
     * Done ... finish the process and delete lock file
     * @param string $sMsgId  name of the process
     * @return boolean
     */
    public function finishAction($sMsgId): bool
    {
        $aCurrent = $this->_getAction();
        if ($aCurrent !== $sMsgId) {
            return false;
        }
        return $this->_delete();
    }

    /**
     * Get status about a (maybe) currently running action
     * It returns false if it is not locked.
     * 
     * @return bool|array
     */
    public function getStatus(): bool|array
    {
        $aCurrent = $this->_getAction();
        if (!$aCurrent) {
            return false;
        }
        return $this->_aMsg;
    }

    /**
     * Show locking status with 
     * - current action
     * - last response
     * - running time
     * 
     * @return bool
     */
    public function showStatus(): bool
    {
        $aCurrent = $this->_getAction();
        if (!$aCurrent) {
            echo "STATUS: no running action\n";
            return false;
        }
        echo "STATUS: running action: "
            . "{$this->_aMsg['action']} \n"
            . "- started       " . date("Y-d-m H:i:s", $this->_aMsg['start']) . " \n"
            . "- last response " . date("Y-d-m H:i:s", $this->_aMsg['last']) . " "
            . "(" . (date("U") - $this->_aMsg['last']) . " sec ago ... ignoring it after " . $this->_iTimeout . " sec) \n"
            . "- running for   " . ($this->_aMsg['last'] - $this->_aMsg['start']) . " sec\n"
            . "\n";
        return true;
    }
}
