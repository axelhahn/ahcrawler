<!doctype html>
<?php
/**
 * ======================================================================
 *
 * Page for Cache admin
 *
 * Browse and filter all cached modules and their items.
 * Delete all items, all outdated cache items.
 *
 * ======================================================================
 *
 * (1) Copy this file somewhere below your webroot
 * (2) update the line require('classes/cache-admin.class.php'); 
 * (3) next to the classes/cache-admin.class.php create a file 
 *     cache-admin.class-enabled.php
 *     It must just exist to enable the cache admin.
 * (4) protect web access to the admin page (basic auth, ip restriction)
 *
 * ======================================================================
 */
require('../src/cache-admin.class.php');
$oCache = new AhCacheAdmin();


$sOut = '';
$sNav = '';

$sAction = $_POST['action'] ?? false;

global $sModule;
$sModule = $_GET['module'] ?? false;
global $sCachefile;
$sCachefile = $_GET['file'] ?? false;

// ----------------------------------------------------------------------
// display functions
// ----------------------------------------------------------------------

/**
 * get list of modules to build a navigation
 * @return string
 */
function getNav(): string
{
    global $sModule;
    $oCache = new AhCacheAdmin();
    return $oCache->renderModuleList([
        'baseurl' => '?',
        'module' => $sModule,
    ]);
}

/**
 * get a list of cached items of the selected module
 * @return string
 */
function getItems(): string
{
    global $sModule, $sCachefile;
    if ($sModule) {
        $oCache = new AhCacheAdmin($sModule);
        return $oCache->renderModuleItems([
            'baseurl' => '?',
            'module' => $sModule,
            'file' => $sCachefile,
        ]);
    } else {
        return '';
    }
}

/**
 * get details of a selected cache item as html code
 * @return void
 */
function getDetails(): void
{
    global $sModule, $sCachefile;
    if ($sModule && $sCachefile) {
        $sBackUrl = '?module=' . $sModule;
        echo '<div id="details" onclick="location.href=\'' . $sBackUrl . '\';" title="click to close detail page">'
            . '<a href="' . $sBackUrl . '">close</a><br><hr>';
        $oCache = new AhCacheAdmin();
        $oCache->loadCachefile($sCachefile);
        $oCache->dump();
        echo '</div>';
    } else {
        // return 'select an item ...';
    }
}

// ----------------------------------------------------------------------
// action functions
// ----------------------------------------------------------------------

/**
 * delete outdated cache items
 * @return void
 */
function actDeleteOutdated(): void
{
    global $sModule, $sCachefile;
    if ($sModule) {
        $oCache = new AhCacheAdmin($sModule);
        $aItems = $oCache->getCachedItems();
        foreach (array_keys($aItems) as $sFile) {
            $oCache->loadCachefile($sFile);
            if ($oCache->isExpired()) {
                echo "expired: $sFile - "
                    . ($oCache->delete() ? 'OK: deleted' : 'ERROR: deletion failed.')
                    . PHP_EOL;
            }
        }
    }
}

// ----------------------------------------------------------------------
// actions
// ----------------------------------------------------------------------

switch ($sAction) {
    case 'delete':
        echo "delete:<br>";
        actDeleteOutdated();
        break;;;
    case 'makeInvalid':
        if ($sModule) {
            $oCache = new AhCacheAdmin($sModule);
            $oCache->removefileTouch();
        }
        break;;;
    case 'deleteModule':
        if ($sModule) {
            echo "deleting module [$sModule] ... ";
            $oCache = new AhCacheAdmin($sModule);
            if ($oCache->deleteModule(true)) {
                $sModule = false;
                echo 'OK';
            } else {
                echo 'failed.';
            }
            echo '<br>';
        }
        break;;;
    default:
        echo $sAction ? "unhandled action: $sAction<br>" : '';;;
}

// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------


?><html>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css" integrity="sha512-1k7mWiTNoyx2XtmI96o+hdjP8nn0f3Z2N4oF/9ZZRgijyV4omsKOXEnqL1gKQNPy2MTSP9rIEWGcH/CInulptA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js" integrity="sha512-BkpSL20WETFylMrcirBahHfSnY++H2O1W+UnEEO4yNIl+jI2+zowyoGJpbtk6bx97fBXf++WJHSSK2MV4ghPcg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        body {
            background: #eee;
            background: linear-gradient(-20deg, #eee, #fff) fixed;
            border-top: 4px solid #bbc;
            color: #333;
            font-family: verdana, arial;
            font-size: 1.0em;
            margin: 0em;
            padding: 1em;
        }

        button {
            padding: 1em;
            font-size: 1em;
            border-radius: 0.3em;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        a {
            color: #669;
        }

        h1 {
            margin: 0 0 1em;
            border-bottom: 1px solid #ccc;
        }

        h1 a {
            color: #557;
            text-decoration: none;
        }

        h2 {
            margin-top: 0;
            color: #889;
        }

        footer {
            background: rgba(0, 0, 0, 0.05);
            border-top-left-radius: 1em;
            position: fixed;
            bottom: 1em;
            padding: 1em;
            right: 1em;
        }

        nav {
            float: left;
            margin-right: 1em;
            min-width: 15%;
            border-right: 0px dashed #ccc;
            border-bottom: 0px dashed #ccc;
            box-shadow: 0.3em 0.3em 0.7em #ddd;
        }

        nav ul li a {
            padding: 0.5em;
        }

        pre {
            background: #f0f4f8;
        }

        table {
            border: 1px solid #ccc;
        }

        table.right {
            float: right;
            margin-left: 5em;
            padding: 0.2em;
            background: #e4e0f0;
            background: none;
            border: none;
        }

        table.dataTable thead {
            background: #bbc;
        }

        td {
            vertical-align: top;
        }

        ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        ul li a {
            text-decoration: none;
            color: #666;
            display: block;
            padding: 0;
        }

        ul li a:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .ok,
        .ok a {
            color: #080;
        }

        .less30,
        .less30 a {
            background: #fec;
            color: #c80;
        }

        .outdated,
        .outdated a {
            background: #edd !important;
            color: #a66;
        }

        .active,
        .active a {
            background: #d62 !important;
            color: #fff;
        }

        #details {
            opacity: 0.97;
            overflow: scroll;
            position: fixed;
            top: 5%;
            left: 5%;
            width: 90%;
            height: 85%;
            background: #fff;
            padding: 1em;
            border: 3px solid;
            box-shadow: 0 0 3em #000;
        }

        #maincontent {
            float: left;
            border: 0px solid;
            min-width: 70%;
            max-width: 90%;
            padding-left: 1em;
            margin-bottom: 8em;
        }

        #navigation {
            float: left;
            border: 0px solid;
        }

        .button {
            background: #ccc;
            color: #333;
            border-radius: 0.3em;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.5em;
            text-decoration: none;
        }

        .delete {
            background: #e0dcdc;
            background: #f0ecec;
            border: 0px;
            color: #611;
        }

        .delete:hover {
            background: #ecc;
        }

        .bar {
            border: 1px solid rgba(0, 0, 0, 0.05);
            display: block;
            float: left;
            width: 100px;
        }

        .counter {
            font-size: 180%;
            color: #bbc;
            padding-left: 1em;
        }

        .bar .left {
            background: #9b9;
            height: 1em;
        }

        .dataTables_wrapper {
            box-shadow: 0 0 3em #ddd;
            padding: 1em;
        }

        .less30 .bar .left {
            background: #da8;
        }

        .less30,
        .less30 a {
            background: #fec;
            color: #c80;
        }

        .ok,
        .ok a {
            color: #080;
        }

        .outdated,
        .outdated a {
            background: #edd !important;
            color: #a66;
        }

        .active,
        .active a {
            background: #556 !important;
            color: #fff;
        }
    </style>

</head>

<body>

    <h1><a href="?">ahCache Admin</a></h1>

    <div id="navigation"><?php echo getNav(); ?></div>
    <div id="maincontent"><?php echo getItems(); ?></div>
    <?php echo getDetails(); ?>

    <div style="clear: both"></div>
    <footer><a href="https://github.com/axelhahn/ahcache">github.com: axelhahn/ahcache</a></footer>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                "lengthMenu": [
                    [10, 50, 100, -1],
                    [10, 50, 100, "..."]
                ],
                "order": [3, 'asc'],
                stateSave: true
            });
        });
    </script>
</body>

</html>