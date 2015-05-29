<?php  

$router = new \Phalcon\Mvc\Router(false);

    $router->setDefaultController('index');
    $router->setDefaultAction('index');

    $router->add(
	    '/{language:[a-z]{2}}/:controller/:action',
	    array(
	        'controller' => 2,
	        'action'     => 3
	    )
	);

	$router->add(
	    "/",
	    [	"controller" => "index", "action" => "index", ]
	);

	$router->add(
	    '/{language:[a-z]{2}}/:action',
	    [	"controller" => "index", "action" => 2, ]
	)->setName('index_act');

	$router->add(
	    '/{language:[a-z]{2}}/docs/{article:[a-z0-9\-]+}',
	    [	"controller" => "index", "action" => 'docs', ]
	)->setName('docs');

	$router->add(
	    '/:action',
	    [	"controller" => "index", "action" => 'empty', ]
	);

	// $router->add(
	//     "/:action",
	//     [	"controller" => "index", "action" => 1, ]
	// );
	
	// $router->add(
	//     "/:action",
	//     [	"controller" => "index", "action" => 1, ]
	// );

	$router->add(
	    "/sphere/:action",
	    [	"controller" => "sphere", "action" => 1, ]
	);

	$router->add(
	    "/tools/:action",
	    [	"controller" => "tools", "action" => 1, ]
	);


	$router->add(
	    "/logout",
	    [	"controller" => "login", "action" => "logout", ]
	);

	$router->add(
	    "/register",
	    [	"controller" => "login", "action" => "register", ]
	);

	$router->add(
	    "/planing",
	    [	"controller" => "index", "action" => "planing", ]
	)->setName('planing');

	$router->add(
	    "/focus",
	    [	"controller" => "index", "action" => "focus", ]
	)->setName('focus');

	$router->add(
	    "/cron",
	    [	"controller" => "index", "action" => "cron", ]
	);

	$router->add(
	    "/adminpanel",
	    [	"controller" => "adminpanel", "action" => "index", ]
	);

		$router->add(
	    "/adminpanel/:action",
	    [	"controller" => "adminpanel",   "action"     => 1, ]
	);

	$router->add("/auth/:action",
	    array(
	        "controller" => "auth",
	        "action"     => 1,
	    )
	);


	// $router->add(
	//     "/campaign/edit/{id:[0-9]+}",
	//     [	"controller" => "camps", "action" => "edit", ]
	// )->setName('camp-edit');


	$router->notFound(array(
	    "controller" => "errors",
	    "action" => "route404"
	));


	return $router;