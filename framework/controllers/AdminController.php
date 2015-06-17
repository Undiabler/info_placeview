<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class AdminController extends CController
{

    public function initialize() {
        $this->tag->setTitle(' | Admin Panel');
        //$this->session->get("auth")
        //$this->user->getRole();


    }

    public function indexAction() {
        if (!$this->user->isAdmin()) {
            return $this->response->redirect(array(
                "for" => "admin_login"
            ));
        }

        $this->tag->prependTitle($this->trans->_('Dashboard'));
    }

    public function loginAction() {
        if ($this->user->isAdmin()) {
            return $this->response->redirect(array(
                "for" => "admin"
            ));
        }

        $this->tag->prependTitle($this->trans->_('Login'));

        if ($this->request->isPost()) {
            //Проверка переменных отрпвленых через POST
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            $password = sha1($password);

            if ($username == 'admin' && $password == sha1('admin')) {
                //$this->flash->success('Welcome ' . $username);
                $this->user->setAttr(1, 'Admins', new User());

                return $this->response->redirect(array(
                    "for" => "admin"
                ));
            } else {
                $this->view->setVar('message','Wrong username/password');
                //$this->flash->error('Wrong username/password');
            }
        }
    }

    public function logoutAction() {
        if ($this->user->isAdmin()) {
            $this->user->signOut();
        }

        return $this->response->redirect(array(
            "for" => "admin_login"
        ));
    }
}
