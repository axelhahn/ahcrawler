<?php
/**
 * page about
 */
$sReturn = '';

/*
require_once __DIR__.'/../../classes/ahwi-updatecheck.class.php';
$oUpdate=new ahwiupdatecheck(array(
        'product'=>$this->aAbout['product'],
        'version'=>$this->aAbout['version'],
        'baseurl'=>'https://c58.axel-hahn.de/versions/',
        'tmpdir'=>__DIR__.'/../../tmp/',
        'ttl'=>10,
        // 'ttl'=>86400,     // 1 day
));
// echo "getUpdateInfos : </pre>" . print_r($oUpdate->getUpdateInfos(), 1).'</pre>';

*/
$oRenderer=new ressourcesrenderer();
$sReturn.='<h3>' . $this->aAbout['product'] . ' ' . $this->aAbout['version'] . ' ('.$this->aAbout['date'].')</h3>'

        // update info
        . '<p>' . $this->lB('about.info') . '</p>'
        . $this->_getSimpleHtmlTable(
                array(
                    array($this->lB('about.url.project'), '<a href="' . $this->aAbout['urlHome'] . '">' . $this->aAbout['urlHome'] . '</a>'),
                    array($this->lB('about.url.docs'), '<a href="' . $this->aAbout['urlDocs'] . '">' . $this->aAbout['urlDocs'] . '</a>'),
                    array($this->lB('about.url.source'), '<a href="' . $this->aAbout['urlSource'] . '">' . $this->aAbout['urlSource'] . '</a>'),
                )
        )
        . $oRenderer->renderBookmarklet()
        . '<h3>' . $this->lB('about.thanks') . '</h3>'
        . '<p>' . $this->lB('about.thanks-text') . '</p>'
        . $this->_getSimpleHtmlTable(
                array(
                    array($this->lB('about.thanks.chartjs'), '<a href="https://www.chartjs.org/">https://www.chartjs.org/</a>'),
                    array($this->lB('about.thanks.datatables'), '<a href="https://datatables.net/">https://datatables.net/</a>'),
                    array($this->lB('about.thanks.fontawesome'), '<a href="https://fontawesome.com/">https://fontawesome.com/</a>'),
                    array($this->lB('about.thanks.jquery'), '<a href="https://jquery.com/">https://jquery.com/</a>'),
                    array($this->lB('about.thanks.medoo'), '<a href="https://medoo.in/">https://medoo.in/</a>'),
                    array($this->lB('about.thanks.rollingcurl'), '<a href="https://github.com/chuyskywalker/rolling-curl">https://github.com/chuyskywalker/rolling-curl</a>'),
                    array($this->lB('about.thanks.pure'), '<a href="https://purecss.io/">https://purecss.io/</a>'),
                )
        );
return $sReturn;
