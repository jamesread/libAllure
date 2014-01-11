<?php

date_default_timezone_set('Europe/London');
$dir = realpath(dirname(__FILE__) . '/../main/php/');
$travisPath = '/home/travis/build/jamesread/libAllure/src/main/php/';
set_include_path(get_include_path() . PATH_SEPARATOR . "../main/php/" . PATH_SEPARATOR . $travisPath);

?>
