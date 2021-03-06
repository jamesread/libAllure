<?php

// These shortcuts just save some typing for default configurations.

function san() {
	require_once 'libAllure/Sanitizer.php';

	global $san;

	if (!isset($san)) {
		$san = new libAllure\Sanitizer;
	}

	return $san;
}

function db() {
	require_once 'libAllure/Database.php';

	global $db;

	if (!isset($db)) {
		$db = new libAllure\Database;
	}

	return $db;
}

function stmt($sql) {
	return db()->prepare($sql);
}

function tpl($name) {
	require_once 'libAllure/Template.php';
	return new libAllure\Template($name);
}

function errorHandler() {
	require_once 'libAllure/ErrorHandler.php';
	return new libAllure\ErrorHandler();
}

function filterStrings() {
	require_once 'libAllure/Sanitizer.php';

	$ret = array();
	$san = \libAllure\Sanitizer::getInstance();
	$san->filterAllowUndefined = false;

	foreach (func_get_args() as $argname) {
		$ret[$argname] = $san->filterString($argname);
	}

	return $ret;
}

function filterUints() {
	require_once 'libAllure/Sanitizer.php';

	$ret = array();
	$san = \libAllure\Sanitizer::getInstance();
	$san->filterAllowUndefined = false;

	foreach (func_get_args() as $argname) {
		$ret[$argname] = $san->filterUint($argname);
	}

	return $ret;
}

function vde() {
	var_dump(func_get_args()); exit;
}

?>
