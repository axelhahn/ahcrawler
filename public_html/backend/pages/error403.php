<?php

http_response_code(403);

return '<h3>' . $this->lB('error.403') . '</h3>'
        . '<p>' . $this->lB('error.403.description') . '</p>'
        .'<hr>'
        . $this->_getButton([
            'href' => 'javascript:history.back();',
            // 'class' => 'button-secondary',
            'popup' => false,
            'label' => 'button.back'
        ])
        ;
