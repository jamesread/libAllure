<?php

date_default_timezone_set('Europe/London');
$dir = realpath(dirname(__FILE__) . '/../main/php/' . '/home/travis/build/jamesread/libAllure/src/main/php/');
set_include_path(get_include_path() . PATH_SEPARATOR . "../main/php/");

echo 'Include path for phpunit tests: ' . var_dump($dir) . ' and I am in ' . __DIR__ . ' and my cwd is ' . getcwd();

?>
