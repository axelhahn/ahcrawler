<?php
/**
 * HOME
 */
$sHtml=$this->_renderChildItems($this->_aMenu)
    // . '<h3>' . $this->lB('home.welcome') . '</h3>'
    . (!$this->_getUser() && (
            !array_key_exists('PHP_AUTH_USER', $_SERVER)
            || !$_SERVER['PHP_AUTH_USER']
            )
     ? '<div class="warning">' . $this->lB('home.cfg.unprotected') . '</div><br><br>' 
    : ''
    )
    //. '<p>' . $this->lB('home.welcome-introtext') . '</p>'
;
return $sHtml;