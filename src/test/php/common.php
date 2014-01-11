<?php

date_default_timezone_set('Europe/London');
$dir = realpath(dirname(__FILE__) . '/../main/php/');
set_include_path(get_include_path() . PATH_SEPARATOR . "../main/php/");

echo 'Include path for phpunit tests: ' . var_dump($dir);

?>
