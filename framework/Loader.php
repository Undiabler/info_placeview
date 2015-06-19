<?php

	use Phalcon\Tag;
	use Phalcon\Mvc\Url;
	use Phalcon\Mvc\View;
	use Phalcon\Mvc\Application;
	use Phalcon\DI\FactoryDefault;
	use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

	use Phalcon\Logger,
    Phalcon\Events\Manager as EventsManager,
    Phalcon\Logger\Adapter\File as FileLogger;

	if (!class_exists('Phalcon\Loader')) die('Phalcon не установлен!');

class Loader {

	public static function init(){

		try {

		    // Register an autoloader
		    $loader = new Phalcon\Loader();
		    $loader->registerDirs(
		        array(
		            __DIR__ . '/controllers/',
		            __DIR__ . '/plugins/',
		            __DIR__ . '/library/',
		            __DIR__ . '/models/'
		        )
		    )->register();

		    $di = new FactoryDefault();

		    $config = include __DIR__ . '/config/application.php';
		    $di->set('config', $config);

		    $di['db'] = function() use ($di,$config) {

		    	$db=new DbAdapter($config->databases->db->toArray());
				
				$eventsManager = $di->getShared('eventsManager');

				$logger = new FileLogger((string)(realpath(dirname(__FILE__) . '/logs/db.log')));

				// Слушаем все события БД
				$eventsManager->attach('db', function($event, $connection) use ($logger) {
				    if ($event->getType() == 'beforeQuery') {
				        $logger->log($connection->getSQLStatement(), Logger::INFO);
				    }
				});

				$db->setEventsManager($eventsManager);
		        
		        return $db;
		    };

		// \Phalcon\Mvc\Model::setup(array(    
		// 	'notNullValidations' => false
		// ));

		    $di['modelsMetadata'] = function() {

			    // Создать менеджер мета-данных с APC
			    if (strstr($_SERVER["HTTP_HOST"], $config->app->product)) {
			    	
				    $metaData = new \Phalcon\Mvc\Model\MetaData\Xcache(array(
				        "lifetime" => 86400,
				        "prefix"   => "my-prefix"
				    ));

			    } else {
			   
			    	 $metaData = new \Phalcon\Mvc\Model\MetaData\Memory(array(
				        "lifetime" => 86400,
				        "prefix"   => "my-prefix"
				    ));

			    }

			    return $metaData;
			};

			$di['session'] = function(){
			    $session = new Phalcon\Session\Adapter\Files();
			    $session->start();
			    return $session;
			};

			$di->set('user',  function(){
			    return new User();
			});

			$di->set('dispatcher', function() use ($di) {

			    $eventsManager = $di->getShared('eventsManager');

			    $security = new Security($di);

			    $eventsManager->attach('dispatch', $security);

			    $dispatcher = new Phalcon\Mvc\Dispatcher();

			    $eventsManager->attach("dispatch", function ($event, $dispatcher, $exception) use ($di)
	            {
	                if ($event->getType() == 'beforeNotFoundAction') {
	                    $dispatcher->forward(array(
	                        // 'module'     => 'site',
	                        'controller' => 'error',
	                        'action'     => 'error404'
	                    ));

	                    return false;
	                }

	                if ($event->getType() == 'beforeException') {
	                    switch ($exception->getCode()) {
	                        case \Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
	                        case \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
	                            $dispatcher->forward(array(
	                                // 'module'     => 'site',
	                                'controller' => 'error',
	                                'action'     => 'error404'
	                            ));

	                            return false;
	                    }
	                }
	            });
			    
			    $dispatcher->setEventsManager($eventsManager);

			    return $dispatcher;
			});


		    $di['view'] = function() {
		        $view = new View();
		        $view->setViewsDir(__DIR__.'/views/');
		        return $view;
		    };

		    $di['url'] = function() {
		        $url = new Url();
		        $url->setBaseUri('/');
		        $url->setBasePath(__DIR__.'/web/');
		        return $url;
		    };

		    $di['widget']= function(){
			    return new Widgets();
			};

			$di['extra']= function(){
			    return new Extra();
			};

		    // Setup the tag helpers
		    $di['tag'] = function() {
		        return new Tag();
		    };

		    $di->set('flash', function () {
				$flash = new \Phalcon\Flash\Session(array(
					'error'   => 'alert alert-danger',
					'success' => 'alert alert-success',
					'notice'  => 'alert alert-info',
					'warning' => 'alert alert-warning'
				));

				return $flash;
			});

			$di->set('router', function() {
				return include __DIR__ . '/config/routing.php';
			});

		    
		    $application = new Application($di);

		    echo $application->handle()->getContent();

		} catch (Exception $e) {
		     echo '<b>Exception:</b> Error on line '.$e->getLine().' in '.$e->getFile()
				 .': <b>'.$e->getMessage().'</b>';
		}

	}
}