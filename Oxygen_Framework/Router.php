<?php
class Router_Exception extends Exception {}

/**
 * Handle request URI and call associated controller / action
 *
 * URIs can use one of the following forms:
 * - / => We are requesting the default controller/action (index page)
 * - /foo => we are requesting the controller named 'foo' and default action
 * - /foo/bar => same as above but requesting also the action named 'bar'
 * - /foo/bar/ga/bu/zo/meu => same as above, except that everything after bar/ will be stored as request parameters
 *      e.g. 'ga' will be parameter 0, 'bu' will be parameter 1, and so on
 * - /my/:custom/uri => custom route with a parameter named 'custom'
 *
 */
class Router
{
    protected $routes = array();
    protected $basePath = '/';

    /**
     * Populate $request with controller and action names
     *
     * @param Request $request
     *      The request to compute controller and action
     */
    public function route($request)
    {
        $config = Config::getInstance();

        $controllerName = null;
        $actionName = null;

        $request_uri = explode('?', $request->getUri(true));
        $this->basePath = $request->getApplicationPath();

        $uri = $request_uri[0];

        // Don't bother with the begining and last '/'
        $uri = trim($uri, '/');

        $explodedUri = explode('/', $uri);

        // Sort routes by length desc
        uasort($this->routes, function ($a, $b) {
            return strcmp($b['url'], $a['url']) ;
        });

        $routeData = $this->getRouteByUri($uri);

        if(!empty($routeData['route']))
        {
            // Registered route
            $controllerName = $routeData['route']['controller'];
            $action = $routeData['route']['action'];

            $request->setAllParams($routeData['params']);
        }
        else if(!empty($explodedUri[0]))
        {
            // Handle requests like '/controller-name/action-name/param1/param2'
            $controllerName = strtolower($explodedUri[0]);

            // get action name
            if (!empty($explodedUri[1]))
            {
                $action = strtolower($explodedUri[1]);

                // if there are any params, we save them in the request
                if (count($explodedUri) > 2 )
                    $request->setAllParams(array_slice($explodedUri, 2));
            }
            else
                $action = $config->getOption('router/default/action');
        }
        else
        {
            // We are requesting the home page
            $controllerName = $config->getOption('router/default/controller');
            $action = $config->getOption('router/default/action');
        }

        // Convert get parameters to request parameters
        if ($config->getOption('router/convertGetAsParams') && !empty($request_uri[1]))
        {
            $params = array();
            parse_str($request_uri[1], $params);

            foreach ($params as $name => $value)
            {
                $request->setParam($name, $value);
            }
        }

        $request->setControllerName($controllerName);
        $request->setActionName($action);
    }

    /**
     * Dispatch the request (call the controller/action provided)
     *
     * Handles the controller class according to it's prefix / suffix (assumes the controller file is loaded)
     * Then call the init() controller's method then call the action's method in controller
     * Finally, renders the controller's view.
     *
     * @param Request $request
     *      The request to dispatch
     * @param Response $response
     *      The response result
     * @throws Router_Exception
     *      If the controller / action could not be loaded
     */
    public function dispatch($request, $response)
    {
        $config = Config::getInstance();

        $controllerPrefix = $config->getOption('router/prefix/controller');
        $actionPrefix = $config->getOption('router/prefix/action');

        $controllerSuffix = $config->getOption('router/suffix/controller');
        $actionSuffix = $config->getOption('router/suffix/action');

        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();

        // Format controller and action name
        $controllerClassName = ucfirst(Oxygen_Utils::convertSeparatorToUcLetters($controllerName, $controllerPrefix, $controllerSuffix));
        $actionMethod = Oxygen_Utils::convertSeparatorToUcLetters($actionName, $actionPrefix, $actionSuffix);

        // Make the dispatch
        if (!empty($controllerClassName) && class_exists($controllerClassName) && is_subclass_of($controllerClassName, 'Controller'))
        {
            $controller = new $controllerClassName($request, $response);

            $controller->init();

            if (!empty($actionName) && (method_exists($controller, $actionMethod) || method_exists($controller, '__call')))
                call_user_func(array($controller, $actionMethod));
            else
                throw new Router_Exception('Method "' . $controllerClassName. '->'. $actionMethod.'()' . '" not exists');

            $controller->render();
        }
        else
            throw new Router_Exception('Controller class "' . $controllerClassName . '" not exists or is not a controller');
    }

    /**
     * Add a route to custom route lists
     *
     * @param string $name
     *      Unique route identifier
     * @param array $routeData
     *      An array containing route data as following :
     *      array(
     *          'controller' => 'foo',
     *          'action' => 'bar',
     *          'url' => '/my/own/url/:foo',
     *          'values' => array(
     *              'foo' => 'something' // 'something' is the default 'foo' parameter's value
     *          )
     *      )
     *
     * @throws Router_Exception
     *      If one of mandatory fields (controller/action/url) isn't set
     */
    public function addRoute($name, $routeData)
    {
        if (isset($this->routes[$name]))
            throw new Router_Exception('Route "'.$name.'" already exists');
        else if (!isset($routeData['url'], $routeData['controller'], $routeData['action']))
            throw new Router_Exception('Route "'.$name.'" is missing one of mandatory fields: "url", "controller" or "action"');
        else
            $this->routes[$name] = $routeData;
    }

    /**
     * Find a route according to the passed URI (and populate parameters if any)
     *
     * @param string $uri
     * @return Array
     *      array(
     *       'route' => The route array ['controller' => ..., 'action' => ..., ]
     *       'params' => The parameter values found (if any)
     *   )
     */
    public function getRouteByUri($uri)
    {
        $result = false;
        $params = array();
        $foundRoute = false;

        $explodedUri = explode('/', $uri);

        foreach ($this->routes as $name => $route)
        {
            // Reset parameters at each iteration
            $params = array();

            // Check if route is a regex one
            if (!empty($route['regex']) && preg_match($route['regex'], $uri, $matches))
            {
                // Remove the 'full match'
                array_shift($matches);

                $result = $route;
                $params = $matches;

                // We found the route, get out !
                $foundRoute = true;
                break;
            }
            else
            {
                // Don't bother with the begining and last '/'
                $routeUrl = trim($route['url'], '/');

                $explodedRoute = explode('/', $routeUrl);

                // Route and URI have different length => it is not this route
                if (count($explodedRoute) < count($explodedUri))
                    continue;

                // Consider we have probably found the route
                $foundRoute = true;

                $params = isset($route['values']) ? $route['values'] : array();

                foreach ($explodedRoute as $i => $part)
                {
                    $explodedRoutePart = $explodedRoute[$i];
                    $explodedUriPart = isset($explodedUri[$i]) ? $explodedUri[$i] : '';

                    if ($explodedRoutePart != $explodedUriPart && (strlen($explodedRoutePart) == 0 || $explodedRoutePart[0] != ':'))
                    {
                        // Wrong route, remove params and treat next
                        $foundRoute = false;
                        $params = array();
                        break;
                    }
                    else if (strlen($explodedRoutePart) > 0 && $explodedRoutePart[0] == ':')
                    {
                        $paramName = substr($explodedRoutePart, 1);

                        // Fill parameter with (in order of presence) : provided value, default or null
                        $params[$paramName] = !empty($explodedUriPart)
                            ? $explodedUriPart
                            : (isset($params[$paramName]) ? $params[$paramName] : null)
                        ;
                    }
                }
            }

            // If we found the route, get out
            if ($foundRoute)
            {
                $result = $route;
                break;
            }
        }

        return array(
            'route' => $result,
            'params' => $params,
        );
    }

    /**
     * Compute an URI from route name and parameters
     *
     * @param string $routeName
     *      Name of the route to compute an URI
     * @param Array $params
     *      Parameters list
     * @return string
     *      The final URI
     */
    public function getUrlByRoute($routeName, $params)
    {
        $result = '';

        if (isset($this->routes[$routeName]))
        {
            $route = $this->routes[$routeName];

            $result = preg_replace_callback(
                '/:([\w]+)/',
                function ($matches) use ($params, $route) {
                    $paramName = ltrim($matches[1], ':');

                    // fill parameter with (in order of presence) : provided value, default or null
                    return isset($params[$paramName])
                        ? $params[$paramName]
                        : (isset($route['values'][$paramName]) ? $route['values'][$paramName] : null)
                    ;
                },
                ltrim($route['url'], '/')
            );
        }
        else
            throw new Router_Exception('Unknown route "'.$routeName.'"');

        return $this->basePath.'/'.$result;
    }
}