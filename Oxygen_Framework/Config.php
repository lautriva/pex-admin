<?php
/**
 * Managing the application's configuration
 */

class Config_Exception extends Exception {}

class Config
{
    CONST CONFIG_NODES_SEPARATOR = '/';

    protected static $_instance;

    // Options list, here is default config
    protected $_options = [
        'plugins' => 'Plugin',
        'router' => [
            'default' => [
                'controller' => 'index',
                'action' => 'index'
            ],
            'suffix' => [
                'controller' => 'Controller',
                'action' => 'Action'
            ],
            'convertGetAsParams' => true,
            'controllersFolder' => '/Controllers'
        ],
        'view' => [
            'extension' => '.phtml',
            'folder' => '/Views',
            'helpersFolder' => '/Helpers'
        ]
    ];

    /**
     * Get main config instance
     *
     * @return Config
     */
    public static function getInstance()
    {
        if (self::$_instance == null)
            self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * Load config from an array
     *
     * @param array $options
     * @param bool $merge
     *      merge (true) or replace (false) existing configuration
     */
    public function loadConfig($options, $merge = true)
    {
        $this->_options = $merge
            ? self::array_merge_recursive($this->_options, $options)
            : $options;
    }

    /**
     * Get JSON data from a file and decode it as an associative array
     *
     * @param string $filename
     * @throws Config_Exception
     * @return array
     */
    public static function getArrayFromJSONFile($filename)
    {
        if (!file_exists($filename))
            throw new Config_Exception('"'.$filename.'" not found');

        $config = @json_decode(file_get_contents($filename), true);

        if (json_last_error() != JSON_ERROR_NONE)
            throw new Config_Exception('Error while loading "'.$filename.'". JSON Error: '.json_last_error_msg());

        return $config;
    }

    /**
     * Load configuration data from a file
     *
     * @param string $filename
     * @return Config
     */
    public function loadFromJSONFile($filename, $merge = true)
    {
        $this->loadConfig(self::getArrayFromJSONFile($filename), $merge);
        return $this;
    }

    /**
     * Get config value from node name
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getOption($name, $defaultValue = null)
    {
        $path = explode(self::CONFIG_NODES_SEPARATOR, $name);
        $result = $defaultValue;
        $node = $this->_options;

        if (!empty($path))
        {
            $nodeFound = true;

            foreach ($path as $field)
            {
                if (isset($node[$field]))
                    $node = $node[$field];
                else
                {
                    $nodeFound = false;
                    break;
                }
            }

            if ($nodeFound)
                $result = $node;
        }

        return $result;
    }

    /**
     * Set config value
     *
     * @param string $name
     * @param mixed $value
     */
    public function setOption($name, $value)
    {
        $path = explode(self::CONFIG_NODES_SEPARATOR, $name);

        $temp = &$this->_options;
        foreach($path as $key) {
            $temp = &$temp[$key];
        }
        $temp = $value;
        unset($temp);
    }

    /**
     * Merge two multi-dimmensional arrays.
     * On duplicate value it takes one from the second array
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private static function array_merge_recursive(array $array1, array $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => & $value)
        {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
                $merged[$key] = self::array_merge_recursive($merged[$key], $value);
            else if (is_numeric($key) && !in_array($value, $merged))
                $merged[] = $value;
            else
                $merged[$key] = $value;
        }

        return $merged;
    }
}