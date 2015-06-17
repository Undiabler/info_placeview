<?php
use Phalcon\Mvc\Controller;

class ErrorController extends Controller
{
	public function initialize()
    {
        $this->view->pick("error/index");
    }

	public function error404Action()
	{
		$this->response->setStatusCode(404);
		$this->view->setVar("error", 404);
		$this->view->setVar("message", "Такая страница отсуствует");
	}
	public function error401Action()
	{
		$this->response->setStatusCode(401);
		$this->view->setVar("error", 401);
		$this->view->setVar("message", "Страница не входит в список разрешенных для посещения");
	}
	public function error500Action()
	{
		$this->response->setStatusCode(500);
		$this->view->setVar("error", 500);
		$this->view->setVar("message", "Внутренняя ошибка");
	}

}
