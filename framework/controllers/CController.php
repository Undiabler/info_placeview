<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class CController extends Controller
{

	public function onConstruct() {
		$this->view->setVar("t", $this->_getTranslation());
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
