<?php
/*
 *
 * AhCRAWLER :: Cronjob example
 *
 */

require_once(__DIR__ . '/../classes/crawler.class.php');


// ----------------------------------------------------------------------
// FUNCTIONS
// ----------------------------------------------------------------------

function run($sCmd){
        echo "\n";
        echo "RUN ::\n";
        echo "RUN :: **************************************** START\n";
        echo "RUN :: $$sCmd\n";
        echo "RUN ::\n\n";

        // exec($sCmd, $aOut, $iRc);
        // echo implode("\n", $aOut);
        system($sCmd, $iRc);


        echo "\n\n";
        echo "RUN ::\n";
        echo "RUN :: $$sCmd\n";
        echo "RUN :: rc=$iRc\n";
        echo "RUN :: **************************************** END\n\n";

        return $iRc;
}

// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------


$oCrawler=new crawler();
$aIds=$oCrawler->getProfileIds();

// ----- FLUSH
$sParams = '--action flush --data all';
$iRc=run("php " . __DIR__ . '/../bin/cli.php '.$sParams);


// ----- SCAN
foreach($aIds as $iProfile){

        $sParams = '--action index --data searchindex --profile '.$iProfile;
        $iRc=run("php " . __DIR__ . '/../bin/cli.php '.$sParams);

        $sParams = '--action index --data ressources --profile '.$iProfile;
        $iRc=run("php " . __DIR__ . '/../bin/cli.php '.$sParams);

}

// ----- UPDATE MISSING RESSOURCES
foreach($aIds as $iProfile){
        $sParams = '--action update --data ressources --profile '.$iProfile;
        $iRc=run("php " . __DIR__ . '/../bin/cli.php '.$sParams);
}


echo "\n--- DONE. Closing in 10 sec ...";
// sleep(10);
exit($iRc);
