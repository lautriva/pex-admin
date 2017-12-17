<?php
class Oxygen_Session
{
    private static $SESSION_STORAGE_SPACE = '_OXYGEN_FRAMEWORK';

    private static $session_started = false;

    public function __construct()
    {
        if (!self::$session_started)
        {
            session_start();
            self::$session_started = true;
        }
    }

    public function destroy()
    {
        session_destroy();
        self::$session_started = false;
    }

    public function __get($name)
    {
        return isset($_SESSION[self::$SESSION_STORAGE_SPACE][$name])
            ? unserialize($_SESSION[self::$SESSION_STORAGE_SPACE][$name])
            : null
        ;
    }

    public function __set($name, $value)
    {
        $_SESSION[self::$SESSION_STORAGE_SPACE][$name] = serialize($value);
    }

    public function __isset($name)
    {
        return isset($_SESSION[self::$SESSION_STORAGE_SPACE][$name]);
    }

    public function __unset($name)
    {
        unset($_SESSION[self::$SESSION_STORAGE_SPACE][$name]);
    }

    public static function getInstance()
    {
        return new self();
    }

    /**
     * Ensure singleton by preventing cloning
     */
    private function __clone() { }

    public static function setCookie($name, $value = null, $expire = null)
    {
        setCookie($name, $value, $expire);
    }

    public static function getCookie($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }
}