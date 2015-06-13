<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class IndexController extends CController
{

	public function initialize() {
		//$this->tag->prependTitle('PlaceView — Конструктор интерактивных витруальныx туров.');
		$this->tag->setTitle(' | Placeview');

		//



		//var_dump('index initialized');
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
		$this->tag->prependTitle($this->trans->_('All Categories'));

		$cats = $this->extra->getSql("SELECT c.id, c.slug, ct.name, ct.description FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE ct.lang = ? ORDER BY c.created_at",
			[$this->config->lang]);

		$this->view->setVar("cats", $cats);
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
