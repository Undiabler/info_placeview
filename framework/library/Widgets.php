<?php

use Phalcon\Mvc\User\Component;

class Widgets extends Component
{
	public function __construct(){
		// parent::__construct();
		$this->view=new Phalcon\Mvc\View\Simple();
		$this->view->setViewsDir('app/views/');
	}

    public function getMenu($name)
    {
    	// $view = new Phalcon\Mvc\View\Simple();
        echo $this->view->render('widgets/menu',['name'=>$name]);
        // echo('lol');
    }

    public function getFiltertable($rows,$count,$grouped=false,$cols_names)
    {
        echo $this->view->render('widgets/filtertable',['rows'=>$rows,'grouped'=>$grouped,'count'=>$count,'cols'=>$cols_names]);
    }

    public function getOptimizeChart()
    {
        echo $this->view->render('widgets/optimize_chart',[]);
    }

}