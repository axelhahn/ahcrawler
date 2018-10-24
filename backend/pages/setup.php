<?php
/**
 * SETTINGS
 */
$sCfg=file_get_contents($this->_getOptionsfile());
$sReturn='
    <!--
        <link rel="stylesheet" href="../vendor/codemirror/lib/codemirror.css">
        <script src="../vendor/codemirror/lib/codemirror.js"></script>
        <script src="../vendor/codemirror/addon/edit/matchbrackets.js"></script>
        <script src="../vendor/codemirror/addon/comment/continuecomment.js"></script>
        <script src="../vendor/codemirror/addon/comment/comment.js"></script>
        <script src="../vendor/codemirror/mode/javascript/javascript.js"></script>
    -->
        <form>
            <textarea id="taconfig" name="config" cols="120" rows="20">'.$sCfg.'</textarea>

        </form>
    <!--
        <script>
          var editor = CodeMirror.fromTextArea(document.getElementById("taconfig"), {
            matchBrackets: true,
            autoCloseBrackets: true,
            mode: "application/ld+json",
            lineNumbers: true,
            lineWrapping: true
          });
        </script>
    -->
';
return $sReturn;