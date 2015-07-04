<?php

use Phalcon\Mvc\View;

class AdminDocumentController extends CController
{
    public function initialize() {
        if (!$this->user->isAdmin()) {
            return $this->response->redirect(array(
                "for" => "admin_login",
                "language" => $this->config->lang
            ));
        }

        $this->tag->setTitle(' | ' . $this->trans->_('Админ Панель'));
        $this->view->setTemplateAfter('admin');
    }

    public function listAction() {
        $this->tag->prependTitle($this->trans->_('Список документов'));

        $maxDocs = 5;

        $params = $this->dispatcher->getParams();
        $page = isset($params[0]) ? $params[0] : 1;

        $countOfDocs = $this->db->fetchColumn("SELECT COUNT(*) FROM document d JOIN document_translate dt ON d.id = dt.document_id WHERE dt.lang = ? ORDER BY d.created_at", [$this->config->lang]);

        $maxPages = ceil ($countOfDocs / 5);

        $docs = $this->extra->getSql("SELECT d.id, d.created_at, d.slug, dt.name, ct.name as category_name FROM document d JOIN document_translate dt ON d.id = dt.document_id JOIN category_translate ct ON ct.category_id = d.category_id WHERE dt.lang = ? AND ct.lang = ? ORDER BY d.created_at LIMIT " . (($page - 1) * $maxDocs) . ", " . $maxDocs, [$this->config->lang, $this->config->lang]);

        $this->view->setVar("docs", $docs);
        $this->view->setVar("currentPage", $page);
        $this->view->setVar("maxPages", $maxPages);
        $this->view->setVar("pages", range(1, $maxPages));
        $this->view->setVar("countOfDocs", $countOfDocs);
        $this->view->setVar("maxDocs", $maxDocs);
    }

    public function createAction() {
        $this->createEditAction();
    }

    private function createEditAction($documentId = null, $isNew = true) {
        if ($this->request->isPost()) {
            $doc = $this->request->getPost('doc');

            if ($isNew) {
                // create a slug from document name
                $doc['slug'] = $this->extra->urlSlug($doc['translation'][$this->config->lang]['name'], ['transliterate' => true]);
            }

            $errors = false;

            // Validation
            if (!$doc['slug']) {
                $errors = true;
            } else {
                foreach ($doc['translation'] as $lang => $docTranslation) {
                    if (!$docTranslation['name'] || !$docTranslation['text']) {
                        $errors = true;
                    }
                }
            }

            if (!$errors) {
                if ($isNew) {
                    $success = $this->db->query("INSERT INTO document (slug, category_id, preview_image, discuss_url) VALUES (:slug, :category_id, :preview_image, :discuss_url)", [
                        'slug' => $doc['slug'],
                        'category_id' => $doc['category_id'],
                        'preview_image' => $doc['preview_image'] ?: null,
                        'discuss_url' => $doc['discuss_url'] ?: null
                    ]);

                    $doc['id'] = null;

                    if ($success) {
                        $doc['id'] = $this->db->lastInsertId();

                        foreach ($doc['translation'] as $lang => $docTranslation) {
                            $success = $this->db->query("INSERT INTO document_translate (document_id, lang, name, text) VALUES (:document_id, :lang, :name, :text)", [
                                'name' => $docTranslation['name'],
                                'text' => $docTranslation['text'],
                                'document_id' => $doc['id'],
                                'lang' => $lang
                            ]);

                            if ($success) {
                                $doc['translation'][$lang]['id'] = $this->db->lastInsertId();
                            } else {
                                $errors = true;
                            }
                        }

                    } else {
                        $errors = true;
                    }
                } else {
                    $this->db->query("UPDATE document SET slug = :slug, category_id = :category_id, preview_image = :preview_image, discuss_url = :discuss_url WHERE id = :id", [
                        'slug' => $doc['slug'],
                        'category_id' => $doc['category_id'],
                        'preview_image' => $doc['preview_image'] ?: null,
                        'discuss_url' => $doc['discuss_url'] ?: null,
                        'id' => $doc['id']
                    ]);

                    $updated = true;

                    foreach ($doc['translation'] as $lang => $docTranslation) {
                        if ($docTranslation['id']) {
                            $result = $this->db->fetchColumn("SELECT COUNT(*) FROM document_translate WHERE id = ?", [$docTranslation['id']]);

                            if ((int)$result > 0) {
                                $this->db->query("UPDATE document_translate SET name = :name, text = :text WHERE document_id = :document_id AND lang = :lang", [
                                    'name' => $docTranslation['name'],
                                    'text' => $docTranslation['text'],
                                    'document_id' => $doc['id'],
                                    'lang' => $lang
                                ]);
                            } else {
                                $updated = false;
                            }

                        } else {
                            $updated = false;
                        }

                        if (!$updated) {
                            $success = $this->db->query("INSERT INTO document_translate (document_id, lang, name, text) VALUES (:document_id, :lang, :name, :text)", [
                                'name' => $docTranslation['name'],
                                'text' => $docTranslation['text'],
                                'document_id' => $doc['id'],
                                'lang' => $lang
                            ]);

                            if ($success) {
                                $cat['translation'][$lang]['id'] = $this->db->lastInsertId();
                            } else {
                                $errors = true;
                            }
                        }
                    }
                }
            }

            if (!$errors) {
                $this->flash->success($this->trans->_($isNew ? 'doc_created' : 'doc_edited', [
                    'doc_name' => $doc['translation'][$this->config->lang]['name']
                ]));

                if ($isNew) {
                    return $this->response->redirect('/' . $this->config->lang . "/admin/document/edit/" . $doc['id']);
                }
            } else {
                $this->flash->error($this->trans->_($isNew ? 'doc_not_created' : 'doc_not_edited', [
                    'doc_name' => $doc['translation'][$this->config->lang]['name']
                ]));
            }
        } elseif(!$isNew) {
            $q = $this->db->query("SELECT * FROM document WHERE id = ?", [$documentId]);
            $q->setFetchMode(Phalcon\Db::FETCH_ASSOC);
            $doc = $q->fetch();

            if (!$doc) return $this->dispatcher->forward([
                'controller' => 'error',
                'action' => 'error404'
            ]);

            $q = $this->db->query("SELECT * FROM document d JOIN document_translate dt ON d.id = dt.document_id WHERE d.id = ?", [$documentId]);
            $q->setFetchMode(Phalcon\Db::FETCH_ASSOC);
            $docTranslations = $q->fetchAll();

            foreach ($docTranslations as $docTranslation) {
                $doc['translation'][$docTranslation['lang']] = $docTranslation;
            }

            foreach($this->config['langs'] as $lang) {
                if (!isset($doc['translation'][$lang])) {
                    $doc['translation'][$lang] = null;
                }
            }
        }

        $cats = $this->extra->getSql("SELECT c.id, ct.name FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE ct.lang = ? ORDER BY c.created_at", [$this->config->lang]);

        $this->view->setVars([
            'doc' => $doc,
            'cats' => $cats,
            'isNew' => $isNew
        ]);

        $this->tag->prependTitle($this->trans->_($isNew ? 'doc_create' : 'doc_edit', [
            'doc_name' => $doc['translation'][$this->config->lang]['name']
        ]));

        $this->view->pick('admin_document/edit');
    }

    public function editAction($documentId) {
        $this->createEditAction($documentId, false);
    }

    public function deleteAction($documentId) {

        $result = $this->db->fetchColumn("SELECT COUNT(*) FROM document WHERE id = ?", [$documentId]);

        $errors = false;

        if ((int)$result > 0) {
            $success = $this->db->query("DELETE FROM document WHERE id = :document_id", [
                'document_id' => $documentId
            ]);

            if (!$success) {
                $errors = true;
            }
        } else {
            $errors = true;
        }

        if (!$errors) {
            $this->flash->success('Документ #' . $documentId . ' удален!');
        } else {
            $this->flash->error('Документ #' . $documentId . ' не был удален!');
        }

        return $this->response->redirect('/' . $this->config->lang . "/admin/document/list");
    }
}
