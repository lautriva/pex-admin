<?php
/**
 * Oxygen Framework bootstrap file
 *
 */

require __DIR__ . '/Autoloader.php';
require __DIR__ . '/Router.php';
require __DIR__ . '/View.php';
require __DIR__ . '/Controller.php';
require __DIR__ . '/Helper.php';
require __DIR__ . '/Plugin.php';
require __DIR__ . '/Config.php';
require __DIR__ . '/Request.php';
require __DIR__ . '/Response.php';

class Project_Exception extends Exception { }

/**
 * Main application (== Project) handler
 *
 */
Class Project
{
    protected static $_instance = null;
    protected static $_plugins = null;

    protected $_appFolder;
    protected $_autoloader;

    protected $_router = null;
    protected $_request = null;
    protected $_response = null;

    /**
     * Instanciate a MVC project
     *
     * @param string $app_folder
     *      Folder where are all application files (Controllers / Models / Views)
     * @param array $projectOptions
     * @throws Project_Exception
     * @return Project
     */
    public static function create($app_folder, $projectOptions = array())
    {
        if (self::$_instance == null)
            self::$_instance = new Project($app_folder, $projectOptions);
        else
            throw new Project_Exception('Project already initialized, use Project::getInstance() instead');

        return self::$_instance;
    }

    public static function getInstance()
    {
        if (self::$_instance == null)
            throw new Project_Exception('Project not initialized, use Project::create($app_folder, $options = array()) instead');

        return self::$_instance;
    }

    private function __construct($app_folder, $projectOptions = array())
    {
        if (!empty($projectOptions))
        {
            $config = Config::getInstance();
            $config->loadConfig($projectOptions);
        }

        $this->_appFolder = $app_folder;

        $this->_autoloader = new Autoloader($app_folder);

        $this->_router = new Router();
        $this->_request = new Request();
        $this->_response = new Response();

        $this->registerAutoloader();
    }

    /**
     * Ensure singleton by preventing cloning
     */
    private function __clone() { }

    /**
     * Call a specific action plugin event
     *
     * @param string $action
     *      The plugin method to call
     * @param array $params
     * @throws Plugin_Exception
     */
    public static function callPluginAction($action, $params)
    {
        // First run, load plugins
        if (null === self::$_plugins)
        {
            self::$_plugins = array();
            $project = Project::getInstance();

            $config = Config::getInstance();
            $pluginClasses = $config->getOption('plugins');

            if (is_string($pluginClasses))
                $pluginClasses = [$pluginClasses];

            foreach ($pluginClasses as $pluginIndex => $pluginClass)
            {
                if (class_exists($pluginClass))
                {
                    $plugin = new $pluginClass(
                        $project->_request,
                        $project->_response,
                        $project->_router
                    );

                    if (!is_a($plugin, 'Plugin'))
                        throw new Plugin_Exception('Plugins class "' . $pluginClass . '" doesn\'t extend "Plugin" class');

                    self::$_plugins[$pluginIndex] = $plugin;
                }
                else
                    throw new Plugin_Exception('Plugin class "' . $pluginClass . '" Not found');
            }
        }

        $params = is_array($params) ? $params : array($params);

        foreach (self::$_plugins as $plugin)
        {
            call_user_func_array(
                array($plugin, $action),
                $params
            );
        }
    }

    /**
     * Get URI by route and parameters
     * @see Router::getUrlByRoute()
     *
     * @param string $routeName
     * @param array $params
     * @return string
     */
    public function getUrlByRoute($routeName, $params)
    {
        return $this->_router->getUrlByRoute($routeName, $params);
    }

    public function getAppFolder()
    {
        return $this->_appFolder;
    }

    public function getAutoloader()
    {
        return $this->_autoloader;
    }

    /**
     * Add our own __autoload implementation
     *
     * Register our autoloader and some namespaces (Oxygen + Helper + those registered in configuration)
     *
     * @return Project
     */
    protected function registerAutoloader()
    {
        $config = Config::getInstance();

        // Register helper namespaces
        $this->_autoloader->addClassType('Helper', $config->getOption('view/helpersFolder'));

        // Register configured namespaces
        if (!empty($config->getOption('namespaces')))
        {
            $classtypes = $config->getOption('namespaces');

            foreach ($classtypes as $prefix => $folder)
            {
                $this->_autoloader->addClassType($prefix, $folder);
            }
        }

        // aka run spl_autoload_register
        $this->_autoloader->register();

        return $this;
    }

    protected function handleDispatch()
    {
        // Before dispatch, call plugin's 'beforeDispatch' handler
        self::callPluginAction('beforeDispatch', array($this->_request));

        // Load controller class
        $this->_autoloader->loadControllerClass($this->_request->getControllerName());

        // Make the actual dispatch
        $this->_router->dispatch($this->_request, $this->_response);
    }

    /**
     * Main project function
     *
     * @throws Exception
     */
    public function run()
    {
        // Let the user play with the router before the actual run (e.g. to add custom routes)
        self::callPluginAction('beforeBootstrap', array(&$this->_router));

        $this->_router->route($this->_request);

        try
        {
            $this->handleDispatch();
        }
        catch (Exception $e)
        {
            $config = Config::getInstance();

            $errorControllerName = $config->getOption('router/error/controller');
            $errorActionName = $config->getOption('router/error/action');

            // If error controller / action are defined, let it handle the exception
            if (!empty($errorControllerName) && !empty($errorActionName))
            {
                // Save old request route
                $requestData = array(
                    'controllerName' => $this->_request->getControllerName(),
                    'actionName' => $this->_request->getActionName(),
                    'params' => $this->_request->getAllParams()
                );

                // Store the exception data in the request and go to the error handler
                $this->_request->setParam('exception', $e)->setParam('request', $requestData)
                    ->setControllerName($errorControllerName)
                    ->setActionName($errorActionName);

                $this->handleDispatch();
            }
            else
                throw $e; // No error handler found, so we throw back the exception
        }

        Project::callPluginAction('beforeRender', array(&$this->_response));
        $this->_response->render();
    }
}