<?php
/*
 * Hi!
 * 
 * This file gives you a demonstration to implement a search form in your own website.
 * To make changes: make a copy with name "custom_[yourname].php" and then make 
 * your changes in that copy.
 * 
 * With it you can request this page to integrate a search on another domain.
 * For more information see 
 * https://www.axel-hahn.de/docs/ahcrawler/searchform.htm
 * 
 *  
 */
require_once(__DIR__ . "/../classes/search.class.php");

// --- defaults:
$sDefaultSiteId=1;
$sDefaultLang='en';

// ----- check current params:

$iSiteId=isset($_GET['siteid'])?(int)$_GET['siteid'] : $sDefaultSiteId;
$sLang=isset($_GET['lang'])?$_GET['lang'] : $sDefaultLang;

// ----- (1) init with site id:
$o = new ahsearch();
$o->setSiteId($iSiteId);

// or shorter:
// $o = new ahsearch(1);

// ----- (2) set the frontend language
$o->setLangFrontend($sLang);

?>
<form method="GET" action="?">
    <?php 
        echo $o->renderHiddenfields();
        echo $o->lF('label.searchhelp'); 
    ?><br>
    <br>

    <div>
        <?php 
        echo $o->renderLabelSearch().' '
            . $o->renderInput(array('class'=>'form-control', 'size'=>70))
            . '<button class="btn btn-success" type="submit">'
                . $o->lF('btn.search.label')
            . '</button>'
            ;
        ?>
        
    </div>
    <?php
    echo '<br>'
        . '<strong>'.$o->lF('label.searchoptions').'</strong>:<br>'
        . $o->renderLabelCategories() . ': ' . $o->renderSelectCategories(array('class'=>'form-control')).'<br>'
        . $o->renderLabelMode()       . ': ' . $o->renderSelectMode(array('class'=>'form-control')). ' ' 
        . $o->renderLabelLang()       . ': ' . $o->renderSelectLang(array('class'=>'form-control')) 
        ;
    ?>

</form>
<br>
<?php

echo $o->renderSearchresults();
