<?php

use Phalcon\Mvc\View;

class CategoryController extends CController
{

    public function initialize() {
        $this->tag->setTitle(' | Placeview');
    }

    public function listAction() {
        $this->tag->prependTitle($this->trans->_('All Categories'));

        $cats = $this->extra->getSql("SELECT c.id, c.slug, ct.name, ct.description FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE ct.lang = ? ORDER BY c.created_at",
            [$this->config->lang]);

        $this->view->setVar("cats", $cats);
    }

    public function viewAction($lang, $slug) {

        $q = $this->db->query("SELECT c.id, c.slug, ct.name, ct.description FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE c.slug = ? AND ct.lang = ?",
            [$slug, $lang]);
        $q->setFetchMode(Phalcon\Db::FETCH_ASSOC);
        $cat = $q->fetch();

        if (!$cat) return $this->dispatcher->forward([
            'controller' => 'error',
            'action' => 'error404'
        ]);

        $this->tag->prependTitle($cat['name']);

        $q = $this->db->query("SELECT * FROM document d JOIN document_translate dt ON d.id = dt.document_id WHERE d.category_id = ? AND dt.lang = ?",
            [$cat['id'], $lang]);
        $q->setFetchMode(Phalcon\Db::FETCH_ASSOC);
        $docs = $q->fetchAll();

        $cats = $this->extra->getSql("SELECT c.id, c.slug, ct.name FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE ct.lang = ? ORDER BY c.created_at",
            [$this->config->lang]);

        $this->view->setVars([
            'cats' => $cats,
            'docs' => $docs,
            'cat' => $cat
        ]);
    }
}
