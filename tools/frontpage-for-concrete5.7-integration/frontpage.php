<?php
function fnmatch($pattern, $string) {
        return preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.'))."$#i", $string);
} // end
require_once("lib/services.php");           
require('concrete/dispatcher.php');
