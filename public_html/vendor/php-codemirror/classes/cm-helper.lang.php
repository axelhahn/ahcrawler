<?php
/*
 * Mapping array for codemirror modes
 * This file is loaded in 'cm-helper.class.php'.
 * 
 * WORK IN PROGRESS 
 * These are just a few of the supported languages!
 * @see https://codemirror.net/5/mode/index.html
 * @see https://codemirror.net/5/mode/meta.js  
 * 
 * @var array
 */

declare(strict_types=1);

return [
        // default
        'text'=>      ['load'=>[],        'mode'=> ""],

        'c'=>         ['load'=>['clike'], 'mode'=> "text/x-csrc"],
        'cpp'=>       ['load'=>['clike'], 'mode'=> "text/x-c++src"],
        'csharp'=>    ['load'=>['clike'], 'mode'=> "text/x-csharp"],
        'objectivec'=>['load'=>['clike'], 'mode'=> "text/x-objectivec"],
        'ceylon'=>    ['load'=>['clike'], 'mode'=> "text/x-ceylon"],
        'java'=>      ['load'=>['clike'], 'mode'=> "text/x-java"],
        'kotlin'=>    ['load'=>['clike'], 'mode'=> "text/x-kotlin"],
        'scala'=>     ['load'=>['clike'], 'mode'=> "text/x-scala"],
        'squirrel'=>  ['load'=>['clike'], 'mode'=> "text/x-squirrel"],
        'vertex'=>    ['load'=>['clike'], 'mode'=> "text/x-vertex"],

        'css'=>       ['load'=>['css'],   'mode'=> "text/css"],
        'scss'=>      ['load'=>['css'],   'mode'=> "text/css"],
        'x-less'=>    ['load'=>['css'],   'mode'=> "text/css"],

        'javascript'=>['load'=>['javascript'], 'mode'=> "text/javascript"], // text/javascript, application/javascript, application/x-javascript, text/ecmascript, application/ecmascript, application/json, application/x-json, application/manifest+json, application/ld+json, text/typescript, application/typescript
        'json'=>      ['load'=>['javascript'], 'mode'=> "application/json"],

        // 'htmlmixed'=> ['load'=>['xml', 'javascript', 'css', 'htmlmixed'],                 'mode'=> "htmlmixed"],
        'htmlmixed'=> ['load'=>['htmlmixed'], 'mode'=> "htmlmixed"],

        'markdown' => ['load'=>['markdown'], 'mode'=> "text/x-markdown"],

        'php'=>       ['load'=>['xml', 'javascript', 'css', 'htmlmixed', 'clike', 'php'], 'mode'=> "application/x-httpd-php"],

        'shell'=>     ['load'=>['shell'], 'mode'=> "text/x-sh"], // text/x-sh, application/x-sh
        'sql'=>       ['load'=>['shell'], 'mode'=> "text/x-sql"],

        'xml'=>       ['load'=>['xml'],  'mode'=> "application/xml"],

        'yaml'=>      ['load'=>['yaml'],  'mode'=> "text/x-yaml"],

];
