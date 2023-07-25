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
    return \libAllure\DatabaseFactory::getInstance();
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
    if (!headers_sent()) {
        header('Content-Type: text/plain');
    }

    foreach (func_get_args() as $i => $arg) {
        echo "Arg " . $i . ":\n";
        var_dump($arg);
        echo "\n\n";
    }

    exit;
}
