<?php

class ErrorController extends Controller
{
    public function errorAction()
    {
        $exception = $this->getRequest()->getParam('exception', null);
        $request = $this->getRequest()->getParam('request', null);

        if (!$exception)
        {
            $exception = new Exception('No error');
            $request = $this->getRequest();
        }

        $exceptionClass = get_class($exception);

        switch (get_class($exception))
        {
            case 'Router_Exception':
                header("HTTP/1.0 404 Not Found");

                // In production, display a 404 page
                if (!Oxygen_Utils::isDev())
                {
                    // If we try to get get an asset file, disable entirely the render
                    // to avoid useless parsing of the 404 page
                    $assetsExts = ['js', 'css', 'jpg', 'png'];
                    $fileWanted = $this->getRequest()->getUri();

                    if (in_array(strtolower(pathinfo($fileWanted, PATHINFO_EXTENSION)), $assetsExts))
                        $this->view->disableRender();
                    else
                        $this->view->setViewFile('error/404.phtml');
                }
            break;

            default:
                header("HTTP/1.0 500 Internal Server Error");

                // Show nothing in production
                if (!Oxygen_Utils::isDev())
                {
                    $this->view->disableRender();
                }
            break;
        }

        // In dev, print more details about the error
        if (Oxygen_Utils::isDev())
        {
            $this->view->title = get_class($exception);

            $this->view->exception = $exception;
            $this->view->request = $request;
        }
    }
}