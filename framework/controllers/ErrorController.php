<?php

class ErrorController extends CController
{
	public function initialize()
    {
        $this->view->pick("error/index");
    }

	public function error404Action()
	{
        $this->tag->prependTitle($this->trans->_('page_not_found'));
		$this->response->setStatusCode(404);
		$this->view->setVar("error", 404);
		$this->view->setVar("message", $this->trans->_('page_not_found_desc'));
	}
	public function error401Action()
	{
        $this->tag->prependTitle($this->trans->_('page_unauthorized'));
		$this->response->setStatusCode(401);
		$this->view->setVar("error", 401);
		$this->view->setVar("message", $this->trans->_('page_unauthorized_desc'));
	}
	public function error500Action()
	{
        $this->tag->prependTitle($this->trans->_('internal_error'));
		$this->response->setStatusCode(500);
		$this->view->setVar("error", 500);
		$this->view->setVar("message", $this->trans->_('internal_error'));
	}

}
