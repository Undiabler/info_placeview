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

$router->add('/{language:(ru|en)}/{cat_slug:[a-z0-9\-]+}/{doc_slug:[a-z0-9\-]+}', [
    "controller" => "document",
    "action" => "view"
])->setName('doc');

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

$router->add("/{language:(ru|en)}/admin/document/:action/:params", [
    "controller" => "admin_document",
    "action" => 2,
    "params" => 3
]);

$router->notFound(array(
    "controller" => "error",
    "action" => "error404"
));


return $router;