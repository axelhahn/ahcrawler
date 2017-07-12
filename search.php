<?php

    $bMaintainanceSearch=false;
    require_once("classes/search.class.php");
    $o=new ahsearch(1);

	// TODO: handle these vars in the search class and 
	// create public methods to read them
	
    $sQuery=(array_key_exists('q', $_GET))?$_GET['q']:'';
    $sFrom=(array_key_exists('fromurl', $_GET))
            ?$_GET['fromurl']
            :array_key_exists('HTTP_REFERER', $_SERVER)
                ?$_SERVER['HTTP_REFERER']
                :'';
    
    $sSubdir=array_key_exists('subdir',$_GET)?$_GET['subdir']:'%';
    $sPrefUrl='//'.$_SERVER['SERVER_NAME'];
    
    $sRadios='';
    $aCat=$o->getSearchcategories();
    if ($aCat){
        foreach ($aCat as $sLabel=>$sUrl){
            $sSelect.='<option value="'.$sUrl.'" '.($sSubdir==$sUrl?'selected="selected"':'').' >'.$sLabel.'</option>';
        }
        $sSelect='<select name="subdir" class="form-control">'.$sSelect.'</select>';
    }
    // echo "sSubdir=$sSubdir<br>sFrom=$sFrom<br>";

    if ($bMaintainanceSearch){
    echo '
        <h3>Moment ... bei der Suche finden Wartungsarbeiten statt</h3>
        <p>
            Die Webseite wurde auf PHP 7 umgestellt... ich bin dabei, die Suche 
            zu aktualisieren.<br>
            :<br>
            :<br>
            :<br>
            :<br>
            :<br>
        </p>
        <hr>';
    }
?>


<form method="GET" action="?">
    <?php echo $o->lF('label.searchhelp'); ?><br>
    <br>
    <div class="col-sm-4">
        <label><?php echo $o->lF('label.searchwhere'); ?></label>
        <?php echo $sSelect; ?>
    </div>
    <div class="col-sm-5">
        <label for="eSearch2"><?php echo $o->lF('label.search'); ?></label>
        <div class="input-group">
            <input type="text" name="q" id="eSearch2" value="<?php echo $sQuery; ?>"
                   class="form-control"
                   title="<?php echo $o->lF('input.search.title'); ?>"
                   placeholder="<?php echo $o->lF('input.search.placeholder'); ?>"
                   pattern="^...*"
                   required="required"
                   >
            <span class="input-group-btn">

                <button class="btn btn-success" type="submit">
                    <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                    <?php echo $o->lF('btn.search.label'); ?>
                </button>
            </span>
        </div>
    </div>
</form>
<div style="clear: both; margin-bottom: 2em;"></div>
<?php

echo $o->renderSearchresults($sQuery, array('url'=>$sPrefUrl, 'subdir'=>$sSubdir));
