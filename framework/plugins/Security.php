<?php

use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;

/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class Security extends Plugin
{

	private function likeRest($controller,$action){

		$likeRestResources = array(
				// 'index'		=> ['flush_cache']
		);

		// var_dump($controller);
		// var_dump($action);
		// in_array(needle, haystack)
		$b=( array_key_exists($controller,$likeRestResources) && ( in_array($action,$likeRestResources[$controller]) || in_array('*',$likeRestResources[$controller]) ) );

 		// var_dump($b);

		return $b;

	}

	/**
	 * Returns an existing or new access control list
	 *
	 * @returns AclList
	 */
	public function getAcl()
	{

		//throw new \Exception("something");

		if (!isset($this->persistent->acl)) {

			$acl = new AclList();

			$acl->setDefaultAction(Acl::DENY);

			//Register roles
			$roles = array(
				'guests' => new Role('Guests'),
				'users'  => new Role('Users'),
				'admins' => new Role('Admins'),
			);
			foreach ($roles as $role) {
				$acl->addRole($role);
			}

			//Паблик но ложим куку

			$publicResources = array(
				'login'      => array('*'),
				'index'      => array('*'),
				'error'     => array('*'),
				'auth'     => array('*'),
			);


			//закрытые
			$privateResources = array(
				'index'      => array('planing','focus'),
			);

			// TODO:пройтись и прописать отдельно права для каждого модуля/контроллера

			//закрытые админские
			$adminResources = array(
				'adminpanel'      => array('*'),
			);

			foreach ($publicResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}

			foreach ($privateResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}

			foreach ($adminResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}

			//по умолчанию всем даем 
			foreach ($roles as $role) {
				foreach ($publicResources as $resource => $actions) {
					foreach ($actions as $action){
						$acl->allow($role->getName(), $resource, $action);
					}
				}
			}

			//Grant acess to private area to role Users
			foreach ($privateResources as $resource => $actions) {
				foreach ($actions as $action){
					$acl->allow('Users', $resource, $action);
					$acl->allow('Admins', $resource, $action);
				}
			}

			foreach ($adminResources as $resource => $actions) {
				foreach ($actions as $action){
					$acl->allow('Admins', $resource, $action);
				}
			}

			$this->persistent->acl = $acl;
		
		}

		return $this->persistent->acl;
	}


	public function lang($dispatcher){
		$lang = $dispatcher->getParam("language");		
		if (!$lang) {
			if (preg_match('/^\/[a-z]{2}/',$_SERVER['REQUEST_URI'])) return;
			$new_lang=substr($this->request->getBestLanguage(),0,2);
			if (file_exists(__DIR__."/../messages/".$new_lang.".php")){
				$this->config->lang=$new_lang;
				$server = $_SERVER['REQUEST_URI'];
				if ($server=='/') $server='/docs';
				header("Location: /".$this->config->lang.$server);
				exit();
			}
		} else {
			$this->config->lang=$lang;
		}
	}

	/**
	 * This action is executed before execute any action in the application
	 *
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 */
	public function beforeDispatch(Event $event, Dispatcher $dispatcher)
	{

		$this->lang($dispatcher);

		$controller = $dispatcher->getControllerName();
		$action = $dispatcher->getActionName();

		// if ($this->likeRest($controller,$action)) return true;

		$role=$this->user->getRole();

		// var_dump($role);
		// var_dump($controller);
		// var_dump($action);

		if ($role=='Admins') return true;
	
		$acl = $this->getAcl();

		if ( $acl->isAllowed($role, $controller, $action) != Acl::ALLOW) {

			$dispatcher->forward(array(
				'controller' => 'error',
				'action'     => 'error401'
			));

			return false;
		}
	}
}
