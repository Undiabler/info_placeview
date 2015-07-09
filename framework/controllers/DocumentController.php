<?php

use Phalcon\Mvc\View;

class DocumentController extends CController
{

    public function initialize() {
        $this->tag->setTitle(' | Placeview');
    }

    public function viewAction($lang, $cat_slug, $doc_slug) {
        //$locale = Locale::acceptFromHttp($_SERVER["HTTP_ACCEPT_LANGUAGE"]);

        $cats = $this->extra->getSql("SELECT c.id, c.slug, ct.name, ct.description FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE ct.lang = ? ORDER BY c.created_at",
            [$lang]);

        $q = $this->db->query("SELECT c.id, c.slug, ct.name, ct.description FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE c.slug = ? AND ct.lang = ?",
            [$cat_slug, $lang]);
        $q->setFetchMode(Phalcon\Db::FETCH_ASSOC);
        $cat = $q->fetch();

        if (!$cat) return $this->dispatcher->forward([
            'controller' => 'error',
            'action' => 'error404'
        ]);

        $q = $this->db->query("SELECT * FROM document d JOIN document_translate dt ON d.id = dt.document_id WHERE d.slug = ? AND dt.lang = ?",
            [$doc_slug, $lang]);
        $q->setFetchMode(Phalcon\Db::FETCH_ASSOC);
        $doc = $q->fetch();

        if (!$doc) return $this->dispatcher->forward([
            'controller' => 'error',
            'action' => 'error404'
        ]);

        $this->tag->prependTitle($cat['name']);

        $this->view->setVars([
            'cats' => $cats,
            'cat' => $cat,
            'doc' => $doc
        ]);
    }


}
