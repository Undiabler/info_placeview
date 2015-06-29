<?php

class ErrorController extends CController
{
	public function initialize()
    {
        $this->view->pick("error/index");
    }

	public function error404Action()
	{
        $this->tag->prependTitle($this->trans->_('Страница отсуствует'));
		$this->response->setStatusCode(404);
		$this->view->setVar("error", 404);
		$this->view->setVar("message", "Такая страница отсуствует");
	}
	public function error401Action()
	{
        $this->tag->prependTitle($this->trans->_('Страница запрещена'));
		$this->response->setStatusCode(401);
		$this->view->setVar("error", 401);
		$this->view->setVar("message", "Страница не входит в список разрешенных для посещения");
	}
	public function error500Action()
	{
        $this->tag->prependTitle($this->trans->_('Внутренняя ошибка'));
		$this->response->setStatusCode(500);
		$this->view->setVar("error", 500);
		$this->view->setVar("message", "Внутренняя ошибка");
	}

}
