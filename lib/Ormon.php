<?php

class Ormon {
    private static $mongo;
    private static $defaultDatabaseName;

    public static function setMongo($mongo) {
        self::$mongo = $mongo;
    }

    public static function setDefaultDatabaseName($dbName) {
        self::$defaultDatabaseName = $dbName;
    }

    public static function getDefaultDatabase() {
        return self::$mongo->selectDB(self::$defaultDatabaseName);
    }

    public static function collectionize($s) {
        $arr = preg_split("/_|(?=[A-Z])/", $s, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
        $len = count($arr);
        return self::pluralize(implode('_', array_map(function($v){ return strtolower($v); }, $arr)));
    }

    public static function pluralize($s) {
        return "{$s}s";
    }
}