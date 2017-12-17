<?php
class Plugin_Exception extends Exception { }

/**
 * Base class for Plugin handler.
 * Your application's plugin(s) must extend this class !
 */
Class Plugin
{
    protected $_request = null;
    protected $_response = null;

    /**
     * Plugin constructor
     * Let you use your own Request / Response / Router objects implementation
     *
     * If you want to override this, please take care of calling this constructor
     * (e.g. Parent::__construct($request, $response, $router)) at the end of your own !
     *
     * @param Request $request
     * @param Response $response
     * @param Router $router
     */
    public function __construct(Request &$request, Response &$response, Router &$router)
    {
        if (!$request instanceof Request)
            throw new Plugin_Exception(get_class($request).' is not an instance of Request');

        if (!$response instanceof Response)
            throw new Plugin_Exception(get_class($response).' is not an instance of Response');

        if (!$router instanceof Router)
            throw new Plugin_Exception(get_class($router).' is not an instance of Router');

        $this->_request = $request;
        $this->_response = $response;
    }

    /**
     * Called before running the project.
     * You can register custom routes to the router.
     *
     * @param Router $router
     */
    public function beforeBootstrap(Router $router) {}

    /**
     * Called before dispatching the request.
     * You can choose which controller / action to use in router.
     *
     * @param Request $request
     */
    public function beforeDispatch(Request $request) {}

    /**
     * Called before adding layouts / partials to view
     * You can add edit anything in view
     *
     * @param View $view
     */
    public function beforeAddingLayout(View $view) {}

    /**
     * Called before sending the output to the browser
     * You can compress (gzip) the response or add custom headers
     *
     * @param Response $response
     */
    public function beforeRender(Response $response) {}
}