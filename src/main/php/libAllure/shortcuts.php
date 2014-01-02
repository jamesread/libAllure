<?php

// These shortcuts just save some typing for default configurations.

function san() {
	require_once 'libAllure/Sanitizer';

	global $san;

	if (!isset($san)) {
		$san = new libAllure\Sanitizer;
	}

	return $san;
}

function db() {
	require_once 'libAllure/DatabaseFactory';

	global $db;

	if (!isset($db)) {
		$db = new libAllure\Database;
	}

	return $db;
}

?>
