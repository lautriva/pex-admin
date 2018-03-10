<?php
/** @var $router Router **/

// General routes
$router->addRoute('home', array(
        'url' => '/',
        'controller' => 'index',
        'action' => 'index'
    )
);

$router->addRoute('logout', array(
        'url' => 'logout',
        'controller' => 'authentication',
        'action' => 'logout'
    )
);

// Groups related routes
$router->addRoute('group-index', array(
        'url' => 'groups',
        'controller' => 'groups',
        'action' => 'index'
    )
);

$router->addRoute('group-list', array(
        'url' => 'groups/list',
        'controller' => 'groups',
        'action' => 'list'
    )
);

$router->addRoute('group-list-ajax', array(
        'url' => 'groups/ajax',
        'controller' => 'groups',
        'action' => 'list-ajax'
    )
);

$router->addRoute('group-edit', array(
        'url' => 'groups/edit/:name',
        'controller' => 'groups',
        'action' => 'edit'
    )
);

$router->addRoute('group-clone', array(
        'url' => 'groups/clone',
        'controller' => 'groups',
        'action' => 'clone'
    )
);

$router->addRoute('group-delete', array(
        'url' => 'groups/delete',
        'controller' => 'groups',
        'action' => 'delete'
    )
);

// Players related routes
$router->addRoute('player-index', array(
        'url' => 'players',
        'controller' => 'players',
        'action' => 'index'
    )
);

$router->addRoute('player-list', array(
        'url' => 'players/list',
        'controller' => 'players',
        'action' => 'list'
    )
);

$router->addRoute('player-edit', array(
        'url' => 'players/edit/:name',
        'controller' => 'players',
        'action' => 'edit'
    )
);

$router->addRoute('player-clone', array(
        'url' => 'player/clone',
        'controller' => 'players',
        'action' => 'clone'
    )
);

$router->addRoute('player-delete', array(
        'url' => 'player/delete',
        'controller' => 'players',
        'action' => 'delete'
    )
);

// Permissions related routes
$router->addRoute('permission-index', array(
        'url' => 'permissions',
        'controller' => 'permissions',
        'action' => 'index'
    )
);

$router->addRoute('permission-list', array(
        'url' => 'permissions/list',
        'controller' => 'permissions',
        'action' => 'list'
    )
);

$router->addRoute('permission-edit', array(
        'url' => 'permission/edit/:name/:type',
        'controller' => 'permissions',
        'action' => 'edit'
    )
);

// Tools routes
$router->addRoute('tools-index', array(
        'url' => 'tools/index/',
        'controller' => 'tools',
        'action' => 'index'
    )
);

$router->addRoute('get-offline-uuid', array(
        'url' => 'tools/generate-uuid/:name',
        'controller' => 'tools',
        'action' => 'generate-uuid'
    )
);

$router->addRoute('get-mojang-uuid', array(
        'url' => 'tools/get-uuid/:name',
        'controller' => 'tools',
        'action' => 'get-mojang-uuid'
    )
);

$router->addRoute('preview-chat', array(
        'url' => 'tools/preview-chat',
        'controller' => 'tools',
        'action' => 'preview-chat'
    )
);