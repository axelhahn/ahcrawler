<?php
    require_once(__DIR__ . "/classes/backend.class.php");
    $oBackend = new backend();
    if(!$oBackend->installationWasDone() ){
        header('Location: backend/?page=installer');
        die();
    }

    header('HTTP/1.0 403 Forbidden');
?><html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex,nofollow">
    </head>
    <body>
        <h1>403 Forbidden</h1>
        Nothing here.
    </body>
</html>