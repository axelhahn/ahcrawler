<?php

$sAction = isset($_GET['action']) ? $_GET['action'] : '';

if(!$sAction){
    header('HTTP 1.1 400 Bad Request');
    die('<h1>400 Bad Request</h1>Nothing to do');
}

switch($sAction){
    case 'getstatus':
        require_once(dirname(__DIR__) . "/classes/backend.class.php");
        $oBackend = new backend();
        echo $oBackend->getStatus();
        break;
    
    default:
        header('HTTP 1.1 400 Bad Request');
        die('<h1>400 Bad Request</h1>Wrong action');
}
