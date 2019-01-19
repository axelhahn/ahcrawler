<?php
/**
 * HOME
 */
$oRenderer=new ressourcesrenderer($this->_sTab);
if(!$this->oDB){
    $oRenderer=new ressourcesrenderer($this->_sTab);
    $sHtml=''
    . '<h3>' . $this->lB('home.welcome') . '</h3>'
    .$this->lB('home.welcome.introtext').'<br><br>'
    .$oRenderer->oHtml->getTag('a',array(
        'href' => '?page=setup',
        'class' => 'pure-button button-secondary',
        'title' => $this->lB('nav.setup.label.hint'),
        'label' => $this->lB('nav.setup.label'),
    ))
    ;
} else {
$sHtml=$this->_renderChildItems($this->_aMenu)
    // . '<h3>' . $this->lB('home.welcome') . '</h3>'
    /*
    . (!$this->_getUser() && (
            !array_key_exists('PHP_AUTH_USER', $_SERVER)
            || !$_SERVER['PHP_AUTH_USER']
            )
     ? '<br><br><div class="message message-warning">' . $oRenderer->renderShortInfo('warn') . $this->lB('home.cfg.unprotected') . '</div><br><br>' 
    : ''
    )
     */
    //. '<p>' . $this->lB('home.welcome-introtext') . '</p>'
;
}
return $sHtml;