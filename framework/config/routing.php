<?php  

$router = new \Phalcon\Mvc\Router(false);

$router->removeExtraSlashes(true); // router must remove the extra slashes in the handled routes

$router->add("/{language:(ru|en)}", [
	"controller" => "category",
	"action" => "list"
])->setName('home');

$router->add('/{language:(ru|en)}/{slug:[a-z0-9\-]+}', [
    "controller" => "category",
    "action" => "view"
])->setName('category');

$router->add("/{language:(ru|en)}/admin", [
	"controller" => "admin",
	"action" => "index"
])->setName('admin');

$router->add("/{language:(ru|en)}/admin/login", [
	"controller" => "admin",
	"action" => "login"
])->setName('admin_login');

$router->add("/{language:(ru|en)}/admin/logout", [
	"controller" => "admin",
	"action" => "logout"
])->setName('admin_logout');

$router->add("/{language:(ru|en)}/admin/category/:action/:params", [
	"controller" => "admin_category",
	"action" => 2,
	"params" => 3
]);

    //$router->setDefaultController('index');
    //$router->setDefaultAction('index');

    /*$router->add(
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
		'/{language:[a-z]{2}}/category/{slug:[a-z0-9\-]+}',
		[	"controller" => "index", "action" => "category", ]
	)->setName('category');

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
    */

	$router->notFound(array(
	    "controller" => "error",
	    "action" => "error404"
	));


	return $router;