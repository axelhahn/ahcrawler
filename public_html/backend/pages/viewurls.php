<?php
/**
 * page viewurls
 * 
 * Show current content of some special urls
 */

$aList=[
    'favicon.ico',
    'human.txt',
    'robots.txt',
    'sitemap.xml',
];

$sHtml='';
$sSelector='';
$sDetails='';

$sRequest=$this->_getRequestParam('relurl');
$iProfileId=$this->_getTab();
$this->setSiteId($iProfileId);

$oRenderer=new ressourcesrenderer();
$oHeader=new httpheader();


$sHtml=''
    .$this->_getNavi2($this->_getProfiles(), false, '')
    . ($sRequest
        ? '<h3>'.sprintf($this->lB('viewurls.head.show'), $sRequest).'</h3>'
        : '<h3>'.$this->lB('viewurls.head.select').'</h3>'
    )
    .'<p>'.$this->lB('viewurls.head.hint').'</p>'
;

$this->setSiteId($this->_sTab); // to load the profile into $this->aProfile
$sFirstUrl=$this->aProfileSaved['searchindex']['urls2crawl'][0]??false;
$sFirstDomain=preg_replace('#^(.*://[^/]*)/.*#', "$1", $sFirstUrl);


if(!$sFirstDomain){
    $sHtml.=$oRenderer->renderMessagebox($this->lB('viewurls.nostarturl'), 'warning');
    return $sHtml;
}

if($sRequest){

    $sUrl="$sFirstDomain/$sRequest";

    $aResponse = $this->httpGet($sUrl);
    $sResponse=$aResponse['response'];

    $sHeader=explode("\r\n\r\n", $sResponse)[0]??'';
    $sBody=explode("\r\n\r\n", $sResponse)[1]??'';

    $oHeader->setHeaderAsString($sHeader);
    $iStatus=$oHeader->getHttpStatuscode();

    $sDetails.=$oRenderer->renderValue('http_code', $iStatus) . " <strong>$sUrl</strong> "
        . '<a href="' . $sUrl . '" target="_blank" class="pure-button" title="'.$this->lB('ressources.link-to-url').'">'. $oRenderer->_getIcon('link-to-url').'</a><br><br>'
;

    if (!isset($aResponse['error'])) {

        if($iStatus==200){


            $sDetails.=""
                .$oHeader->getContentType()."<br><br>"
                // . $oRenderer->renderValue('http_code', $i).'<br>'
                . $oRenderer->renderToggledContent($this->lB('httpheader.plain'), '<pre>' . htmlentities(print_r($sHeader, 1)) . '</pre>', false)
                .'<br>'
                ;

            if(strstr($oHeader->getContentType(), "image")){
                $sDetails.="<img src=\"$sUrl\" width=\"128\"/>";
            } else {
                $sDetails.="<pre>".htmlentities($sBody, 1)."</pre>";
            }
        } else {
            $sDetails.=$oRenderer->renderToggledContent($this->lB('httpheader.plain'), '<pre>' . htmlentities(print_r($sHeader, 1)) . '</pre>', false)
            ;
        }
    }

} else {
    $sDetails='';
}

foreach($aList as $sLabel){
    $sSelector.='<a href="?page=viewurls&siteid='.$iProfileId.'&relurl='.$sLabel.'" 
        class="pure-button button-small '.($sRequest == $sLabel ? ' button-secondary' : '').'" 
        style="width: 100%; padding: 0.5em">'.$sLabel.'</a><br>';
}

// ---------- complete output

$sHtml.='<table class="pure-table pure-table-horizontal">
<thead></thead>
<tbody>
    <tr><td valign="top">'.$sSelector.'</td><td valign=top>'.$sDetails.'</td></tr>
<tbody></table>';
return $sHtml;
