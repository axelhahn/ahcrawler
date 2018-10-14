<?php
/*
 *
 * Hi!
 * 
 * This file gives you a demonstration to implenent a search form in your own
 * website.
 * 
 * for more information see 
 * https://www.axel-hahn.de/docs/ahcrawler/get_started.htm
 * 
 *  
 */
require_once("classes/search.class.php");

// ----- (1) init with site id:
$o = new ahsearch();
$o->setSiteId(1);

// or shorter:
// $o = new ahsearch(1);

// ----- (2) set the frontend language
$o->setLangFrontend('de');

// ----- (3) show form to enter search term
/*
// most simple way:
echo $o->renderSearchForm();

// with additional options
echo $o->renderSearchForm(array(
    'categories'=>1,
    'lang'=>1,
    'mode'=>1,
));

// ------ (4) output of results
// echo $o->renderSearchresults();

*/

?>

<form method="GET" action="?">
    <?php echo $o->lF('label.searchhelp'); ?><br>
    <br>

    <div>
        <div class="col-sm-12">
            <?php echo $o->renderLabelSearch() ?>
            <div class="input-group">
                <?php echo $o->renderInput(array('class'=>'form-control', 'style'=>'font-size:130%')) ?>
                <span class="input-group-btn">

                    <button class="btn btn-success" type="submit">
                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                        <?php echo $o->lF('btn.search.label'); ?>
                    </button>
                </span>
            </div>
        </div>
    </div>
    <br>
    <br>
    <br>
    <br>
    <?php echo $o->lF('label.searchoptions'); ?>:<br>
        <div class="col-sm-4">
            <?php echo $o->renderLabelCategories() . $o->renderSelectCategories(array('class'=>'form-control')) ?>
        </div>
        <div class="col-sm-2">
            <?php echo $o->renderLabelLang() . $o->renderSelectLang(array('class'=>'form-control')) ?>
        </div>
        <div class="col-sm-5">
            <?php echo $o->renderLabelMode() . $o->renderSelectMode(array('class'=>'form-control')) ?>
        </div>
    </div>

</form>
<div style="clear: both; margin-bottom: 2em;"></div>
<?php

echo $o->renderSearchresults();
