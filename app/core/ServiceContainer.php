<?php
class ServiceContainer
{
    private static $services = [];

    public static function get($class)
    {
        if (!isset(self::$services[$class])) {
            require_once __DIR__ . "/../services/{$class}.php";
            self::$services[$class] = new $class();
        }
        return self::$services[$class];
    }
}
