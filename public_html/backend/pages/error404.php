<?php

header('HTTP/1.1 404 Page not found');

return '<h3>' . $this->lB('error.404') . '</h3>'
        . '<p>' . $this->lB('error.404.description') . '</p>'
        .'<hr>'
        . $this->_getButton([
            'href' => 'javascript:history.back();',
            // 'class' => 'button-secondary',
            'popup' => false,
            'label' => 'button.back'
        ])
        ;
