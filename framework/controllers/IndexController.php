<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class IndexController extends CController
{

	public function initialize() {
		var_dump('index initialized');
	}

	public function adminpanelAction() {
		$this->view->disableLevel(array(
            View::LEVEL_LAYOUT => false,
            View::LEVEL_MAIN_LAYOUT => false
        ));	
	}

	public function planingAction() {
	  // $this->view->setVar('days',$this->getTasks());
		$this->view->setVar('days',[]);
	}

	public function indexAction() {
		exit();
	}

	public function emptyAction() {
		exit();
	}

	public function categoryAction() {
		// exit();
	}

	public function infoAction() {
		
	}

	public function alldocsAction() {

	}

	public function docsAction() {

		$article=$this->dispatcher->getParam("article");

		if (!$article) {
			$this->dispatcher->forward(array(
	            "controller" => "index",
	            "action" => "alldocs"
	        ));
		} else {

			$this->view->setVar('name',$article);

		}
		
	}

}
