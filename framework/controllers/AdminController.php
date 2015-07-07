<?php

use Phalcon\Mvc\View;

class AdminController extends CController
{

    public function initialize() {
        $this->tag->setTitle(' | ' . $this->trans->_('admin_panel'));
    }

    public function indexAction() {
        if (!$this->user->isAdmin()) {
            return $this->response->redirect(array(
                "for" => "admin_login",
                "language" => $this->config->lang
            ));
        }

        $this->tag->prependTitle($this->trans->_('home'));
    }

    public function loginAction() {
        if ($this->user->isAdmin()) {
            return $this->response->redirect(array(
                "for" => "admin",
                "language" => $this->config->lang
            ));
        }

        $this->tag->prependTitle($this->trans->_('auth'));

        if ($this->request->isPost()) {
            //Проверка переменных отрпвленых через POST
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            $password = sha1($password);

            if ($username == 'admin' && $password == sha1('admin')) {
                $this->flash->success($this->trans->_('auth_welcome', [
                    'username' => $username
                ]));
                $this->user->setAttr(1, 'Admins', new User());

                return $this->response->redirect(array(
                    "for" => "admin",
                    "language" => $this->config->lang
                ));
            } else {
                $this->flash->error($this->trans->_('wrong_credentials'));
            }
        }
    }

    public function logoutAction() {
        if ($this->user->isAdmin()) {
            $this->user->signOut();
        }

        return $this->response->redirect(array(
            "for" => "admin_login",
            "language" => $this->config->lang
        ));
    }
}
