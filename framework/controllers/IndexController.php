<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class IndexController extends CController
{

	public function initialize() {
		//$this->tag->prependTitle('PlaceView — Конструктор интерактивных витруальныx туров.');
		$this->tag->setTitle(' | Placeview');
	}

	public function indexAction() {
	}

	public function planingAction() {
	  // $this->view->setVar('days',$this->getTasks());
		$this->view->setVar('days',[]);
	}

	public function categoryAction($lang, $slug) {

		$q = $this->db->query("SELECT c.id, c.slug, ct.name, ct.description FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE c.slug = ? AND ct.lang = ?",
			[$slug, $lang]);
		$q->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$cat = $q->fetch();

		$this->tag->prependTitle($cat['name']);

		$q = $this->db->query("SELECT * FROM document d JOIN document_translate dt ON d.id = dt.document_id WHERE d.category_id = ? AND dt.lang = ?",
			[$cat['id'], $lang]);
		$q->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$docs = $q->fetchAll();

		$this->view->setVar("cat", $cat);
		$this->view->setVar("docs", $docs);
	}

	public function infoAction() {
		
	}
}
