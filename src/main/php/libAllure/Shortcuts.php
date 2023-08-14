<?php

namespace libAllure;

abstract class Shortcuts
{
    public static function san(): \libAllure\Sanitizer
    {
        return \libAllure\Sanitizer::getInstance();
    }

    public static function db(): \libAllure\Database
    {
        return \libAllure\DatabaseFactory::getInstance();
    }

    public static function stmt($sql)
    {
        $stmt = self::db()->prepare($sql);

        return $stmt;
    }

    public static function stmtPrepExec($sql)
    {
        $stmt = self::stmt($sql);
        $stmt->prepare();
        $stmt->execute();

        return $stmt;
    }

    public static function filterStrings()
    {
        $ret = array();
        $san = \libAllure\Sanitizer::getInstance();
        $san->filterAllowUndefined = false;

        foreach (func_get_args() as $argname) {
            $ret[$argname] = $san->filterString($argname);
        }

        return $ret;
    }

    public static function filterUints()
    {
        $ret = array();
        $san = \libAllure\Sanitizer::getInstance();
        $san->filterAllowUndefined = false;

        foreach (func_get_args() as $argname) {
            $ret[$argname] = $san->filterUint($argname);
        }

        return $ret;
    }

    public static function vde()
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
}
