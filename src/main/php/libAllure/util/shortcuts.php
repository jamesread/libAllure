<?php

namespace libAllure\util;

// These shortcuts just save some typing for default configurations.

function san()
{
    global $san;

    if (!isset($san)) {
        $san = new \libAllure\Sanitizer();
    }

    return $san;
}

function db()
{
    global $db;

    if (!isset($db)) {
        $db = new \libAllure\Database();
    }

    return $db;
}

function stmt($sql)
{
    $stmt = db()->prepare($sql);

    return $stmt;
}

function stmtPrepExec($sql)
{
    $stmt = stmt($sql);
    $stmt->prepare();
    $stmt->execute();

    return $stmt;
}

function tpl($name)
{
    return new \libAllure\Template($name);
}

function errorHandler()
{
    return new \libAllure\ErrorHandler();
}

function filterStrings()
{
    $ret = array();
    $san = \libAllure\Sanitizer::getInstance();
    $san->filterAllowUndefined = false;

    foreach (func_get_args() as $argname) {
        $ret[$argname] = $san->filterString($argname);
    }

    return $ret;
}

function filterUints()
{
    $ret = array();
    $san = \libAllure\Sanitizer::getInstance();
    $san->filterAllowUndefined = false;

    foreach (func_get_args() as $argname) {
        $ret[$argname] = $san->filterUint($argname);
    }

    return $ret;
}

function vde()
{
    var_dump(func_get_args());
    exit;
}
