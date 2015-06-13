<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class CController extends Controller
{

	protected $trans;

	public function onConstruct() {
		$this->trans = $this->_getTranslation();
		$this->view->setVar("t", $this->trans);
	}

	protected function _getTranslation()
	{
		$messages = include __DIR__."/../messages/".$this->config->lang.".php";

		if (!is_array($messages)) $messages = [];

		return new \Phalcon\Translate\Adapter\NativeArray(array(
		   "content" => $messages
		));
	}

}
