<?php
/**
 * page about
 */
$sReturn = '';

$sReturn.='<h3>' . $this->aAbout['product'] . ' ' . $this->aAbout['version'] . '</h3>'
        . '<p>' . $this->lB('about.info') . '</p>'
        . $this->_getSimpleHtmlTable(
                array(
                    array($this->lB('about.url.project'), '<a href="' . $this->aAbout['urlHome'] . '">' . $this->aAbout['urlHome'] . '</a>'),
                    array($this->lB('about.url.docs'), '<a href="' . $this->aAbout['urlDocs'] . '">' . $this->aAbout['urlDocs'] . '</a>'),
                    array($this->lB('about.url.source'), '<a href="' . $this->aAbout['urlSource'] . '">' . $this->aAbout['urlSource'] . '</a>'),
                )
        )
        . '<h3>' . $this->lB('about.thanks') . '</h3>'
        . '<p>' . $this->lB('about.thanks-text') . '</p>'
        . $this->_getSimpleHtmlTable(
                array(
                    array($this->lB('about.thanks.chartjs'), '<a href="https://www.chartjs.org/">https://www.chartjs.org/</a>'),
                    array($this->lB('about.thanks.datatables'), '<a href="https://datatables.net/">https://datatables.net/</a>'),
                    array($this->lB('about.thanks.fontawesome'), '<a href="https://fontawesome.io/">https://fontawesome.io/</a>'),
                    array($this->lB('about.thanks.jquery'), '<a href="https://jquery.com/">https://jquery.com/</a>'),
                    array($this->lB('about.thanks.medoo'), '<a href="https://medoo.in/">https://medoo.in/</a>'),
                    array($this->lB('about.thanks.rollingcurl'), '<a href="https://github.com/chuyskywalker/rolling-curl">https://github.com/chuyskywalker/rolling-curl</a>'),
                    array($this->lB('about.thanks.pure'), '<a href="https://purecss.io/">https://purecss.io/</a>'),
                )
        );
return $sReturn;
