<?php
/**
 * @author hahn
 */
class ahpdo {


    /**
     * filename of sqlite database file
     * @var type 
     */
    private $_dbfile = false;
    
    /**
     * constructor ... no params
     */
    public function __construct(){
        
        // cache dir is hardcoded to versions directory :-/
        $this->_dbfile = __DIR__ . '/../data/versioncache.db';
        
        if (!file_exists($this->_dbfile)) {
            $this->_createDb();
        }
    }
    /**
     * add a log messsage
     * @global object $oLog
     * @param  string $sMessage  messeage text
     * @param  string $sLevel    warnlevel of the given message
     * @return bool
     */
    private function log($sMessage, $sLevel = "info") {
        global $oCLog;
        if($oCLog){
            return $oCLog->add(basename(__FILE__) . " class " . __CLASS__ . " - " . $sMessage, $sLevel);
        }
        return false;
    }
    
    // ----------------------------------------------------------------------
    // PRIVATE 
    // ----------------------------------------------------------------------

    /**
     * create sqlite database - called in constructor if the file does not exist
     */
    private function _createDb() {
        if (file_exists($this->_dbfile)) {
            echo $this->_bDebug ? "removing existing file $this->_dbfile ...<br>\n" : '';
            unlink($this->_dbfile);
        }
        echo $this->_bDebug ? "create database as file $this->_dbfile ...<br>\n" : '';
        $this->_makeQuery($this->_sCreate);
        if (!file_exists($this->_dbfile)) {
            $this->_quit(__FUNCTION__ , "ERROR: unable to create sqlite database " . $this->_dbfile);
        }
        return true;
    }

    /**
     * execute a sql statement
     * @param string $sSql   sql statement
     * @param array  $aVars  array with values (uses PDO::prepare(); $sSql must contain placeholders :key)
     * @return database object
     */
    private function _makeQuery($sSql, $aVars=false) {
        // $this->_log(__FUNCTION__."($sSql)");
        // echo "DEBUG: executing SQL<pre>$sSql</pre>";
        $this->log("start query");
        $oDb = new PDO("sqlite:" . $this->_dbfile);
        if ($aVars && is_array($aVars)){
            $oStatement = $oDb->prepare($sSql);
            $result = $oStatement->execute($aVars);
        } else {
            $result = $oDb->query($sSql);
        }
        $this->log("end query - ".$sSql);
        return $result;
    }
    
    /**
     * execute a sql statement
     * @param string $sSql sql statement
     * @return array of resultset
     */
    private function _makeSelectQuery($sSql, $aKey=false) {
        // $this->_log(__FUNCTION__."($sSql)");
        // echo "DEBUG: executing select SQL<pre>$sSql</pre>";
        $this->log("start query");
        $oDb = new PDO("sqlite:" . $this->_dbfile);
        $oStatement = $oDb->prepare($sSql);
        $oStatement->execute();
        $aReturn=array();
        while ($row = $oStatement->fetch(PDO::FETCH_ASSOC)) {
          if ($aKey && array_key_exists($aKey, $row)){
            $aReturn[] = $row[$aKey];
          } else {
            $aReturn[] = $row;
          }
        }        
        $this->log("end query - ".$sSql);
        return $aReturn;
    }

    /**
     * log error and quit. it echoes the error message on screen if debug 
     * is enabled.
     * @param string  $sFunction  name of method that throws the error
     * @param string  $sMessage   error message
     * @return boolean
     */
    private function _quit($sFunction, $sMessage){
        error_log(__CLASS__ . "::$sFunction - $sMessage " . "whereiam: " . print_r($this->whereiam(), 1));
        if ($this->_bDebug){
            echo __CLASS__ . "::$sFunction stopped.<br>\n"
                    . "whereiam: <pre>" . print_r($this->whereiam(), 1)."</pre>"
                    ;
            die($sMessage);
        } else {
            die("ERROR ... wrong usage of class ". __CLASS__);
        }
        return false;
    }

    // ----------------------------------------------------------------------
    // PUBLIC GETTER
    // ----------------------------------------------------------------------
    
    
    /**
     * get list of current projects
     * @return type
     */
    public function getProjects(){
        $sSql="select distinct(project) from `values`";
        return $this->_makeSelectQuery($sSql, 'project');
    }
    
    
}