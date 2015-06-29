<?php

use Phalcon\Mvc\View;

class AdminCategoryController extends CController
{
    public function initialize() {
        if (!$this->user->isAdmin()) {
            return $this->response->redirect(array(
                "for" => "admin_login",
                "language" => $this->config->lang
            ));
        }

        $this->tag->setTitle(' | Админ Панель');
        $this->view->setTemplateAfter('admin');
    }

    public function listAction() {
        $this->tag->prependTitle($this->trans->_('Список категорий'));

        $maxCats = 5;

        $params = $this->dispatcher->getParams();
        $page = isset($params[0]) ? $params[0] : 1;

        $countOfCats = $this->db->fetchColumn("SELECT COUNT(*) FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE ct.lang = ? ORDER BY c.created_at", [$this->config->lang]);

        $maxPages = ceil ($countOfCats / 5);

        $cats = $this->extra->getSql("SELECT c.id, c.created_at, c.slug, ct.name FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE ct.lang = ? ORDER BY c.created_at LIMIT " . (($page - 1) * $maxCats) . ", " . $maxCats, [$this->config->lang]);

        $this->view->setVar("cats", $cats);
        $this->view->setVar("currentPage", $page);
        $this->view->setVar("maxPages", $maxPages);
        $this->view->setVar("pages", range(1, $maxPages));
        $this->view->setVar("countOfCats", $countOfCats);
        $this->view->setVar("maxCats", $maxCats);
    }

    public function createAction() {
        $this->tag->prependTitle($this->trans->_('Создать категорию'));

        $cat = [];

        if ($this->request->isPost()) {
            $cat = $this->request->getPost('cat');

            $errors = false;

            $success = $this->db->query("INSERT INTO category (slug) VALUES (:slug)", [
                'slug' => $cat['slug']
            ]);

            $cat['id'] = null;

            if ($success) {
                $cat['id'] = $this->db->lastInsertId();

                foreach ($cat['translation'] as $lang => $catTranslation) {
                    $success = $this->db->query("INSERT INTO category_translate (category_id, lang, name, description) VALUES (:category_id, :lang, :name, :description)", [
                        'name' => $catTranslation['name'],
                        'description' => $catTranslation['description'],
                        'category_id' => $cat['id'],
                        'lang' => $lang
                    ]);

                    if ($success) {
                        $cat['translation'][$lang]['id'] = $this->db->lastInsertId();
                    } else {
                        //$this->flash->error('Insert into category_translate was failed! Category ID #' . $cat['id']);
                        $errors = true;
                    }
                }


            } else {
                //$this->flash->error('Category "' . $cat['translation'][$this->config->lang]['name'] . '" was not created!');
                $errors = true;
            }

            if (!$errors) {
                $this->flash->success('Категория "' . $cat['translation'][$this->config->lang]['name'] . '" создана!');

                return $this->response->redirect('/' . $this->config->lang . "/admin/category/edit/" . $cat['id']);
            } else {
                $this->flash->error('Категория "' . $cat['translation'][$this->config->lang]['name'] . '" не была создана!');
            }
        }

        $this->view->setVar("cat", $cat);
    }

    public function editAction($categoryId) {

        if ($this->request->isPost()) {
            $cat = $this->request->getPost('cat');

            $errors = false;

            $this->db->query("UPDATE category SET slug = :slug WHERE id = :category_id", [
                'slug' => $cat['slug'],
                'category_id' => $categoryId
            ]);

            $updated = true;

            foreach ($cat['translation'] as $lang => $catTranslation) {
                if ($catTranslation['id']) {
                    $result = $this->db->fetchColumn("SELECT COUNT(*) FROM category_translate WHERE id = ?", [$catTranslation['id']]);

                    if ((int)$result > 0) {
                        $this->db->query("UPDATE category_translate SET name = :name, description = :description WHERE category_id = :category_id AND lang = :lang", [
                            'name' => $catTranslation['name'],
                            'description' => $catTranslation['description'],
                            'category_id' => $categoryId,
                            'lang' => $lang
                        ]);
                    } else {
                        $updated = false;
                    }

                } else {
                    $updated = false;
                }

                if (!$updated) {
                    $success = $this->db->query("INSERT INTO category_translate (category_id, lang, name, description) VALUES (:category_id, :lang, :name, :description)", [
                        'name' => $catTranslation['name'],
                        'description' => $catTranslation['description'],
                        'category_id' => $categoryId,
                        'lang' => $lang
                    ]);

                    if ($success) {
                        $cat['translation'][$lang]['id'] = $this->db->lastInsertId();
                    } else {
                        //$this->flash->error('Insert into category_translate was failed! Category ID #' . $categoryId);
                        $errors = true;
                    }
                }
            }

            if (!$errors) {
                $this->flash->success('Категория "' . $cat['translation'][$this->config->lang]['name'] . '" обновлена!');
            } else {
                $this->flash->error('Категория "' . $cat['translation'][$this->config->lang]['name'] . '" не была обновлена!');
            }
        } else {
            $q = $this->db->query("SELECT * FROM category WHERE id = ?", [$categoryId]);
            $q->setFetchMode(Phalcon\Db::FETCH_ASSOC);
            $cat = $q->fetch();

            $q = $this->db->query("SELECT * FROM category c JOIN category_translate ct ON c.id = ct.category_id WHERE c.id = ?", [$cat['id']]);
            $q->setFetchMode(Phalcon\Db::FETCH_ASSOC);
            $catTranslations = $q->fetchAll();

            foreach ($catTranslations as $catTranslation) {
                $cat['translation'][$catTranslation['lang']] = $catTranslation;
            }

            foreach($this->config['langs'] as $lang) {
                if (!isset($cat['translation'][$lang])) {
                    $cat['translation'][$lang] = null;
                }
            }
        }

        $this->view->setVar("cat", $cat);

        $this->tag->prependTitle($this->trans->_('Изменить "' . $cat['translation'][$this->config->lang]['name'] . '"'));
    }

    public function deleteAction($categoryId) {

        $result = $this->db->fetchColumn("SELECT COUNT(*) FROM category WHERE id = ?", [$categoryId]);

        $errors = false;

        if ((int)$result > 0) {
            $success = $this->db->query("DELETE FROM category WHERE id = :category_id", [
                'category_id' => $categoryId
            ]);

            if (!$success) {
                //$this->flash->error('Категория ID #' . $categoryId . ' не была удалена!');
                $errors = true;
            }
        } else {
            //$this->flash->error('Категория ID #' . $categoryId . ' не была удалена, данной категории не существует!');
            $errors = true;
        }

        if (!$errors) {
            $this->flash->success('Категория #' . $categoryId . ' удалена!');
        } else {
            $this->flash->error('Категория #' . $categoryId . ' не была удалена!');
        }

        return $this->response->redirect('/' . $this->config->lang . "/admin/category/list");
    }
}
