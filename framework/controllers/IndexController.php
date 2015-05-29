<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class IndexController extends CController
{

	public function initialize() {
		var_dump('index initialized');
	}

	public function photographersAction() {

		$c=$this->extra->getSql('SELECT * from user_main');
		// var_dump($c);
		// exit();

	}
	
	public function cabinetAction() {

	}
	public function playerAction() {
		$this->view->disableLevel(array(
            View::LEVEL_LAYOUT => false,
            View::LEVEL_MAIN_LAYOUT => false
        ));
	}

	public function not_foundAction() {
		$this->view->disableLevel(array(
            View::LEVEL_LAYOUT => false,
            View::LEVEL_MAIN_LAYOUT => false
        ));
	}

	public function sign_upAction() {
		$this->view->disableLevel(array(
            View::LEVEL_LAYOUT => false,
            View::LEVEL_MAIN_LAYOUT => false
        ));
	}

	public function sign_inAction() {
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
