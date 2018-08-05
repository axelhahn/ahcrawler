<?php
require_once(dirname(__DIR__) . "/classes/backend.class.php");
require_once(dirname(__DIR__) . "/classes/cdnorlocal.class.php");

$oBackend = new backend();
$oCdn=new axelhahn\cdnorlocal(array(
    'vendorrelpath'=>'../vendor/cache',
    // 'vendordir'=>__DIR__.'/../vendor/cache',
    // 'vendorurl'=>'../vendor/cache',
    'debug'=>0,
    ));

?><!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="robots" content="noindex,nofollow">
        <meta name="description" content="">

        <?php echo $oCdn->getHtmlInclude('pure/1.0.0/pure-min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('pure/1.0.0/buttons-min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('pure/1.0.0/grids-responsive-min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('font-awesome/4.7.0/css/font-awesome.min.css'); ?>
        <?php echo $oCdn->getHtmlInclude('datatables/1.10.15/css/jquery.dataTables.min.css'); ?>

        <?php echo $oCdn->getHtmlInclude('jquery/3.2.1/jquery.min.js'); ?>
        <?php echo $oCdn->getHtmlInclude('datatables/1.10.15/js/jquery.dataTables.min.js'); ?>

        <?php echo $oCdn->getHtmlInclude('Chart.js/2.7.2/Chart.min.js'); ?>
        
        <link rel="stylesheet" href="main.css">
        <!--
        <link rel="stylesheet" href="skins/sky/theme.css">
        -->
        <script>
            function showModal(sUrl){
                var divOverlay=document.getElementById('overlay');
                var sHtml='';
                
                sHtml+='<iframe src="'+sUrl+'" style="width: 100%; border: 0; height: 800px;"></iframe>';
                divOverlay.style.display='block';
                var divContent=document.getElementById('dialogcontent');
                divContent.innerHTML=sHtml;
            }
            function hideModal(){
                var divOverlay=document.getElementById('overlay');
                divOverlay.style.display='none';
                
            }
            /**
             * get css value by given property and selector
             * see https://stackoverflow.com/questions/16965515/how-to-get-a-style-attribute-from-a-css-class-by-javascript-jquery
             * 
             * @param {type} style
             * @param {type} selector
             * @param {type} sheet
             * @returns {.sheet@arr;cssRules.style}
             */
            function getStyleRuleValue1(style, selector, sheet) {
                var sheets = typeof sheet !== 'undefined' ? [sheet] : document.styleSheets;
                for (var i = 0, l = sheets.length; i < l; i++) {
                    var sheet = sheets[i];
                    if( !sheet.cssRules ) { continue; }
                    for (var j = 0, k = sheet.cssRules.length; j < k; j++) {
                        var rule = sheet.cssRules[j];
                        if (rule.selectorText && rule.selectorText.split(',').indexOf(selector) !== -1) {
                            return rule.style[style];
                        }
                    }
                }
                return null;
            }
            function getStyleRuleValue(style, selector, sheet) {
    var sheets = typeof sheet !== 'undefined' ? [sheet] : document.styleSheets;
    for (var i = 0, l = sheets.length; i < l; i++) {
        var sheet = sheets[i];
        if( !sheet.cssRules ) { continue; }
        for (var j = 0, k = sheet.cssRules.length; j < k; j++) {
            var rule = sheet.cssRules[j];
            if (rule.selectorText && rule.selectorText.split(',').indexOf(selector) !== -1) {
                return rule.style[style];
            }
        }
    }
    return null;
}
        </script>

    </head>
    <body>


        <div id="overlay" onclick="hideModal(); return false;">
            <div class="divdialog" onclick="return false;">
                <button class="button-error pure-button"
                   onclick="hideModal();return false;"
                   style="right: -1em; top: -1em; position: absolute;"
                   > X </button>
                <div id="dialogcontent"></div>
            </div>
        </div>
        
        <div id="content">
            <div class="maincontent">
                <?php 
                    echo $oBackend->getHead().$oBackend->getContent(); 
                ?>
            </div>
        </div>

        <div class="sidebar pure-u-1 ">
            <div class="header">
                <h1 class="brand-title"><a href="?">
                        <?php echo $oBackend->aAbout['product']; ?>
                    </a></h1>
                <div class="pure-menu">
                    
                    <?php echo $oBackend->getNavi(); ?>
                    
                </div>

            </div>
        </div>
        <style>
            span.warning1{background: #ff0000;}
        </style>

    </body></html>