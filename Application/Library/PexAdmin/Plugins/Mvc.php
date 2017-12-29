<?php
Class PexAdmin_Plugins_Mvc extends Plugin
{
    public function beforeBootstrap(Router $router)
    {
        // Add custom routes
        require APPLICATION_DIR.'/Config/router.php';
    }

    public function beforeAddingLayout(View $view)
    {
        $view->basedir = $this->_request->getApplicationPath();
    }
}