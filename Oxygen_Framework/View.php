<?php
class View_Exception extends Exception {}
class Helper_Exception extends Exception {}

class View
{
    CONST RENDER_PARTIAL = 1;
    CONST RENDER_LAYOUTS = 2;
    CONST RENDER_MAIN_LAYOUT = 4;

    CONST RENDER_ALL = 7;

    protected $_content = '';

    protected $_viewFilename = '';

    protected $_layout = array();

    protected $_viewVars = array();
    protected $_enableRender = array(
        self::RENDER_PARTIAL => true,
        self::RENDER_LAYOUTS => true,
        self::RENDER_MAIN_LAYOUT => true
    );

    function __construct($viewFilename = '')
    {
        $this->_viewFilename = $viewFilename;
        ob_start();
    }

    public function __call($method, $args)
    {
        $config = Config::getInstance();
        $helpers = $config->getOption('helpers');

        $helperClass = isset($helpers[$method]) ? $helpers[$method] : null;

        if (!empty($helperClass) && class_exists($helperClass) && is_subclass_of($helperClass, 'Helper'))
        {
            $helper = new $helperClass($this);
            return call_user_func_array(array($helper, $method), $args);
        }
        else
            throw new Helper_Exception('Helper "'.$method.'" not found');
    }

    public function __get($name)
    {
        return isset($this->_viewVars[$name]) ? $this->_viewVars[$name] : null;
    }

    public function __set($name, $value)
    {
        if ($name[0] == '_')
            throw new View_Exception('trying to set reserved property');

        $this->_viewVars[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->_viewVars[$name]);
    }

    public function __unset($name)
    {
        if ($name[0] == '_')
            throw new View_Exception('trying to unset reserved property');

        unset($this->_viewVars[$name]);
    }

    private function _setRender($type, $canRender)
    {
        foreach ($this->_enableRender as $renderType => $renderValue)
        {
            if ($renderType & $type)
                $this->_enableRender[$renderType] = $canRender;
        }
    }

    public function disableRender($type = self::RENDER_ALL)
    {
        $this->_setRender($type, false);
        return $this;
    }

    public function enableRender($type = self::RENDER_ALL)
    {
        $this->_setRender($type, true);
        return $this;
    }

    public function canRender($type = self::RENDER_ALL)
    {
        return isset($this->_enableRender[$type]) ? $this->_enableRender[$type] : null;
    }

    public function addLayout($layoutFile)
    {
        array_push($this->_layout, $layoutFile);
    }

    public function setViewFile($viewFile = '')
    {
        $this->_viewFilename = $viewFile;
    }

    /**
     * Render the view and send the response
     *
     * @param Response $response
     */
    public function render($response)
    {
        $config = Config::getInstance();

        // Save any output done before render to append it after partial rendering
        $ob_render = ob_get_clean();

        ob_start();
        try
        {
            Project::callPluginAction('beforeAddingLayout', array(&$this));

            if ($this->canRender(self::RENDER_PARTIAL))
            {
                if (!empty($this->_viewFilename))
                    $this->_content = $this->partial($this->_viewFilename);
            }

            $this->_content .= $ob_render;

            if ($this->canRender(self::RENDER_LAYOUTS))
            {
                foreach ($this->_layout as $layoutFile) {
                    $this->_content = $this->partial($layoutFile, array('_content' => $this->_content));
                }
            }

            if ($this->canRender(self::RENDER_MAIN_LAYOUT))
            {
                $mainLayout = $config->getOption('view/mainLayout');

                if ($mainLayout)
                    $this->_content = $this->partial($mainLayout, array('_content' => $this->_content));
            }
            ob_get_clean();

            $response->appendContent($this->_content, '_content');
        }
        catch (Exception $e)
        {
            ob_get_clean();
            throw $e;
        }

    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    /**
     * Render a view file using parameters
     *
     * $template string : template file
     * $parameters array : view vars as an array(variable => value)
     * */
    public function partial($template, array $parameters = array())
    {
        $config = Config::getInstance();
        $project = Project::getInstance();

        $viewsFolder = $project->getAppFolder() . $config->getOption('view/folder');

        $this->_viewVars = array_merge($this->_viewVars, $parameters);

        $viewFilename = $viewsFolder . DIRECTORY_SEPARATOR . $template;

        if (!is_readable($viewFilename))
            throw new View_Exception('Partial file "'.$viewFilename.'" cannot be found');

        // Include file in buffer and return it
        ob_start();
        include $viewFilename;
        return ob_get_clean();
    }

    /**
     * Simple page template parse using tags like {BRACKET_TAGS}
     * that will be replaced by their values
     * Perfect fit as final users' templates
     * (because it doesn't require any PHP knowledge)
     *
     * $template string : template file
     * $parameters array : view vars as an array(variable => value)
     * $deleteNotFound bool : if true, clear unused tags
     * */
    public function pparse($template, array $parameters = array(), $deleteNotFound = true)
    {
        $config = Config::getInstance();
        $project = Project::getInstance();

        $viewsFolder = $project->getAppFolder() . $config->getOption('view/folder');

        array_merge($this->_viewVars, $parameters);

        $viewFilename = $viewsFolder . DIRECTORY_SEPARATOR . $template;

        if (!is_readable($viewFilename))
            throw new View_Exception('Parsed view "'.$viewFilename.'" cannot be found');

        $contents = file_get_contents($viewFilename);

        foreach($parameters as $parameter => $value)
        {
            $contents = str_replace('{'.$parameter.'}', $value, $contents);
        }

        if ($deleteNotFound)
            $contents = preg_replace('/{\w+}/', '', $contents);

        return $contents;
    }
}