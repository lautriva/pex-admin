<?php
class Autoloader
{
    protected $appFolder = '';
    protected $namespaces = array(
        'Oxygen' => __DIR__ . '/Oxygen'
    );

    /**
     * Constructor
     *
     * @param string $rootDir
     *      Directory used as root classes folder
     */
    public function __construct($rootDir)
    {
        $this->appFolder = $rootDir;
    }

    /**
     * Main autoloader function
     *
     * @param string $className
     */
    public function autoload($className)
    {
        $classFile = $this->getClassFileFromClassName($className);

        if ($classFile)
            require_once $classFile;
    }

    /**
     * Add a new autoloaded class
     *
     * @param string $type
     *      Namespace of the class
     * @param string $folder
     *      Where are stored that class type
     */
    public function addClassType($type, $folder)
    {
        $this->namespaces[$type] = $this->appFolder.$folder;
    }

    /**
     * Get the folder (relative to application) of a specified namespace
     *
     * @param string $type
     * @return boolean|string
     */
    public function getClassFolder($type)
    {
        return isset($this->namespaces[$type]) ? $this->namespaces[$type] : false;
    }

    /**
     * Register our autoloader
     */
    public function register()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Load a controller class from the specified className
     *
     * @param string $controllerName
     */
    public function loadControllerClass($controllerName)
    {
        $config = Config::getInstance();

        $controllersFolder = $config->getOption('router/controllersFolder');

        $controllerPrefix = $config->getOption('router/prefix/controller');
        $controllerSuffix = $config->getOption('router/suffix/controller');

        $filePrefix = $config->getOption('router/prefix/controllerFile');
        $fileSuffix = $config->getOption('router/suffix/controllerFile');

        $controllerClassFile = ucfirst(Oxygen_Utils::convertSeparatorToUcLetters($controllerName, $filePrefix, $fileSuffix));

        $classFile = $this->appFolder . $controllersFolder . DIRECTORY_SEPARATOR . $controllerClassFile . '.php';

        if (!empty ($classFile) && file_exists($classFile))
            require_once $classFile;
    }

    /**
     * Get the class file from a class name
     * 'Namespace_Class_Name' to 'Namespace/ClassName'
     *
     * Dry run will output all file proposals (without extension)
     * e.g. 'Namespace_Class_Name' to ['Namespace/Class_Name.php', 'Namespace/Class/Name']
     *
     * @param string $className
     *      The class to load
     * @param bool $dryRun
     *      if true, $result will contain all proposals
     * @return boolean|array
     */
    public function getClassFileFromClassName($className, $dryRun = false)
    {
        $classFile = false;
        $classFileList = [];

        $config = Config::getInstance();

        foreach ($this->namespaces as $namespace => $namespaceFolder)
        {
            if (substr($className, 0, strlen($namespace)) === $namespace)
            {
                $classFileList = [];

                // Remove the namespace and starting underscore in class name
                $classNameWithoutNamespace = substr($className, strlen($namespace));

                $classNameWithoutNamespace = $classNameWithoutNamespace[0] == '_'
                    ? substr($classNameWithoutNamespace, 1)
                    : $classNameWithoutNamespace;

                do
                {
                    $pos = strpos($classNameWithoutNamespace, '_');

                    // Try the to load class file at the namespace root folder
                    $classFile = $namespaceFolder . DIRECTORY_SEPARATOR . $classNameWithoutNamespace;

                    if (!$dryRun && file_exists($classFile.'.php'))
                    {
                        $classFile .= '.php';
                            break 2;
                    }
                    elseif ($dryRun)
                        $classFileList[] = $classFile;

                    if ($pos != false)
                        $classNameWithoutNamespace = substr_replace($classNameWithoutNamespace, DIRECTORY_SEPARATOR, $pos, 1);

                } while ($pos !== false);
            }

            // This is not the wanted namespace, continue...
            $classFile = false;
        }

        return !$dryRun ? $classFile : $classFileList;
    }
}