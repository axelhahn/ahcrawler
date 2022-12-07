<?php

    $iCount=0;
    $iCountLocal=0;
    $iCountUnused=0;
    
    global $oCdn;
    require_once(__DIR__ . '/../../classes/cdnorlocal-admin.class.php');
    
    $oRenderer=new ressourcesrenderer();
    
    /*
    // $sVendorUrl=(strpos($_SERVER['REQUEST_URI'], '/admin/?') ? '.' : '') . './vendor/';
     * 
     */
    $sVendorUrl='/../vendor/cache/';
    $oCdnAdmin = new axelhahn\cdnorlocaladmin(array(
        'vendordir'=>__DIR__ . '/../../vendor/cache', 
        'vendorurl'=>$sVendorUrl, 
        'debug'=>0
    ));
    // $oCdnAdmin->setLibs($oCdn->getLibs());
    $oCdnAdmin->setLibs(array_keys($oCdn->getLibs()));
    
    // echo '<pre>'.print_r($oCdn->getLibs(), 1).'</pre>';
    // echo '<pre>'.print_r($oCdn->getFilteredLibs(array('islocal'=>1, 'isunused'=>1)), 1).'</pre>';
    // echo '<pre>'.print_r($oCdnAdmin->getLibs(), 1).'</pre>';
    // echo '<pre>'.print_r($oCdnAdmin->getLibs(true), 1).'</pre>';
    
    // --- donwload or delete a library?
    $sLib2download=(array_key_exists('download', $_GET))?$_GET['download']:'';
    $sLib2delete=(array_key_exists('delete', $_GET))?$_GET['delete']:'';
    $sVersion2delete=(array_key_exists('version', $_GET))?$_GET['version']:'';
    
    $aTable=array();    
    $sHtml='
            <p>' . $this->lB('vendor.hint') . '</p>'
            // . ($sLib2delete.$sLib2download ? '<a href="'. getNewQs(array('delete'=>'', 'download'=>'')).'" class="btn btn-default">OK</a>' : '')
            ;
    $aTable[]=array(
        $this->lB('vendor.lib'),
        $this->lB('vendor.version'),
        $this->lB('vendor.remote'),
        $this->lB('vendor.local'),
    );
    foreach($oCdnAdmin->getLibs(true) as $sLibname=>$aLib){
        
        // --- download
        if ($sLib2download && $aLib['lib']===$sLib2download && !$aLib['islocal']){
            // $sHtml.='downloading '.$sLib2download.'...<br>';
            $oCdnAdmin->downloadAssets($sLib2download, $aLib['version']);
            echo "<script>window.setTimeout('location.href=\"?&page=vendor\"', 20);</script>";
            // TODO re-enable $oCdn->setLibs($aEnv['vendor']);
        }
        // --- delete
        if ($sLib2delete && $aLib['lib']===$sLib2delete && $aLib['islocal']){
            // $sHtml.='deleting '.$sLib2delete.'...<br>';
            $oCdnAdmin->delete($sLib2delete, $sVersion2delete);
            echo "<script>window.setTimeout('location.href=\"?&page=vendor\"', 20);</script>";
            // TODO re-enable $oCdn->setLibs($aEnv['vendor']);
        }

        $aTable[]=array(
            '<strong>'.$aLib['lib'].'</strong><br>' 
                // . $oCdnAdmin->getLibraryDescription($aLib['lib']).'<br>'
                // . '<a href="'.$oCdnAdmin->getLibraryHomepage($aLib['lib']).'">'.$oCdnAdmin->getLibraryHomepage($aLib['lib']).'</a><br>'
                // . '('.$oCdnAdmin->getLibraryAuthor($aLib['lib']).')<br>'
                ,
            $aLib['version']
            .( (isset($aLib['isunused']) && $aLib['isunused'] && $aLib['isunused']) 
                ? $oRenderer->renderMessagebox($this->lB('vendor.unused'), 'warning')
                : ''
            )
            ,
            (!$aLib['islocal'] ? $this->_getButton(array(
                            'onclick' => 'location.href=\''. getNewQs(array('download'=>$aLib['lib'], 'version'=>$aLib['version'])).'\';',
                            'class' => 'button-secondary',
                            'label' => 'button.download'
                        ))
                :''),
            ($aLib['islocal'] 
                
                ? $this->_getButton(array(
                            'onclick' => 'location.href=\''. getNewQs(array('delete'=>$aLib['lib'], 'version'=>$aLib['version'])).'\';',
                            'class' => 'button-error',
                            'popup' => false,
                            'label' => 'button.delete'
                        ))

                :''),
        );
    }
    $iCount=count($oCdn->getLibs());
    $iCountLocal=count($oCdn->getFilteredLibs(array('islocal'=>1,'isunused'=>0)));
    $iCountUnused=count($oCdn->getFilteredLibs(array('islocal'=>1,'isunused'=>1)));
    
    return  (($iCount && $iCount===$iCountLocal)
            ? sprintf($this->lB('vendor.AllLocal'), $iCount)
            : sprintf($this->lB('vendor.Localinstallations'), $iCount, $iCountLocal)
    ).'<br>'
    . $sHtml
    . ($iCountUnused
            ? sprintf($this->lB('vendor.DeleteUnused'), $iCountUnused).'<br><br>'
            : ''
    )
    . $this->_getSimpleHtmlTable($aTable, true)
    ;
    // echo 'Libs:<br><pre>'. print_r($oCdn->getLibs(),1). '</pre>---<br>';    
