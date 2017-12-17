<?php
Class PexAdmin_Plugins_Authentication extends Plugin
{
    public function beforeDispatch(Request $request)
    {
        // If user is not yet connected redirect him to login page
        if (!Oxygen_Auth::getIdentity())
        {
            $request->setControllerName('authentication')
                ->setActionName('index');
        }
    }
}