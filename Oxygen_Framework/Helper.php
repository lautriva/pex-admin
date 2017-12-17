<?php

/**
 * Abstract class for view helper
 *
 */
class Helper
{
    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
    }
}