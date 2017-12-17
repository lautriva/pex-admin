<?php
class Oxygen_Utils
{
    /**
     * Pretty print a variable
     *
     * @param $var mixed the var to dump
     * @param $exit boolean exit the script execution after the dump
     */
    public static function dump($var, $exit = false)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';

        if ($exit)
            exit;
    }

    /**
     * Tell if the application is on development or production server
     *
     * @return boolean true if we are on development mode (set by env)
     */
    public static function isDev()
    {
        $env = getenv('APPLICATION_ENV') ?: 'production';
        return $env == 'development';
    }

    /**
     * Redirect client to an url
     *
     * @param $url string the url to redirect
     */
    public static function redirect($url)
    {
        header('Location: '.$url);
        exit;
    }

    /**
     * Get an parameterized URL from the router
     *
     * @param $routeName string the route name
     * @param $params array route parameters (if any)
     */
    public static function url($routeName, $params = array())
    {
        $project = Project::getInstance();
        return $project->getUrlByRoute($routeName, $params);
    }

    /**
     * Check if the string $haystack starts with the $needle string
     */
    public static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0)
            return true;

        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * Check if the string $haystack starts end the $needle string
     */
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0)
            return true;

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * Check if the array has string keys (== is associative)
     *
     * @param array $array
     * @return boolean
     */
    public static function isAssociativeArray($array)
    {
        return is_array($array) && count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Join a text separated by specific separators by uppercasing surrouding letter
     * The final text could be surrounded by prefix and suffix
     * e.g. 'some-action-name' become '[Prefix]someActionName[Suffix]'
     *
     * @param string $text
     * @param string $prefix
     * @param string $suffix
     * @param array $separators
     * @return string
     */
    public static function convertSeparatorToUcLetters($text, $prefix = '', $suffix = '', $separators = ['-', '_', ' '])
    {
        return $prefix.lcfirst(str_replace($separators, '', ucwords($text, implode('', $separators)))).$suffix;
    }
}