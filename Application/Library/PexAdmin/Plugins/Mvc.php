<?php
Class PexAdmin_Plugins_Mvc extends Plugin
{
    public function beforeBootstrap(Router $router)
    {
        // Add custom routes
        require APPLICATION_DIR.'/Config/router.php';

		// Add PEXAdmin custom configuration data
        $config = Config::getInstance();
        $config->loadConfig(Config::getArrayFromJSONFile(APPLICATION_DIR.'/Config/pexadmin.json'), true);
    }

    public function beforeAddingLayout(View $view)
    {
        $view->basedir = $this->_request->getApplicationPath();
    }
}