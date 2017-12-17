<?php
/**
 * Exception returned if one of reserved properties is being set/unset
 */
class Controller_Exception extends Exception {}

class Controller
{
    protected $_request = null;
    protected $_response = null;
    protected $view = null;

    /**
     * Init the controller class
     *
     * The constructor must init the view
     *
     * @param Request $request
     */
    public function __construct(Request $request, Response $response)
    {
        $config = Config::getInstance();

        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();
        $viewExtension = $config->getOption('view/extension');

        $this->_request = $request;
        $this->_response = $response;

        $this->view = new View($controllerName.DIRECTORY_SEPARATOR.$actionName . $viewExtension);
    }

    /**
     * Init function called just after the constructor
     */
    public function init() {}

    public function getRequest()
    {
        return $this->_request;
    }

    public function getView()
    {
        return $this->view;
    }

    /**
     * Ensure that the user cannot set value of our reserved properties
     *
     * @param string $name
     * @param string $value
     * @throws Controller_Exception
     */
    public function __set($name, $value)
    {
        if ($name[0] == '_')
            throw new Controller_Exception('trying to set reserved property');

        $this->$name = $value;
    }

    /**
     * Ensure that the user cannot unset our reserved properties
     *
     * @param string $name
     * @throws Controller_Exception
     */
    public function __unset($name)
    {
        if ($name[0] == '_')
            throw new Controller_Exception('trying to unset reserved property');

        unset($this->$name);
    }

    /**
     * Function called after the action, it's main purpose is to render
     * the view into the response's content
     */
    public function render()
    {
        $this->view->render($this->_response);
    }
}