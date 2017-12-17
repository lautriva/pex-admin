<?php

// Load Oxygen Framework
define('FRAMEWORK_DIR', __DIR__ . '/../Oxygen_Framework');
require FRAMEWORK_DIR . '/Project.php';

// Define where are all application files (Models, views, controllers...)
define('APPLICATION_DIR', dirname(__DIR__).'/Application');

// Create the project instance
$project = Project::create(
    APPLICATION_DIR,
    Config::getArrayFromJSONFile(APPLICATION_DIR.'/Config/pex.json')
);

// Load Db adapters
if (file_exists(APPLICATION_DIR.'/Config/db.json'))
    Oxygen_Db::loadAdapters(Config::getArrayFromJSONFile(APPLICATION_DIR.'/Config/db.json'));

// Here we go !
$project->run();