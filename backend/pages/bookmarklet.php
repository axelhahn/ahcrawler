<?php
/**
 * page bookmarklet
 */
$sReturn = '';

$oRenderer=new ressourcesrenderer();
$sReturn.=$oRenderer->renderBookmarklet();

return $sReturn;
