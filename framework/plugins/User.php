<?php

use Phalcon\Mvc\User\Component;
use Phalcon\Mvc\User\Plugin;

class User extends Component
{

	private $id=0;
	private $role='Guests';
	private $model=null;

	public function __construct(){
		$this->load();
	}

	public function setAttr($id,$role,$model){
		$this->id=$id;
		$this->role=$role;
		$this->model=$model;

		$this->save();
	}

	private function save(){
		$this->session->set('auth', array(
				    'id' => $this->id,
				    'role'=> $this->role,
				    'model' => $this->model,
		));
	}

	private function load(){
		$auth = $this->session->get('auth');
		if ($auth) {
			$this->role  = isset($auth['role'])  ? $auth['role']  : $this->role;
			$this->id    = isset($auth['id'])    ? $auth['id']    : $this->id;
			$this->model = isset($auth['model'])  ? $auth['model']  : $this->model;
			// var_dump($this->model);
			// exit();
		}
	}

	public function getRole(){
		return $this->role;
	}

	public function getModel(){
		return $this->model;
	}

	public function getId(){
		return $this->id;
	}

	public function notify($message){
		$this->db->execute("INSERT INTO notifies(user_id,message) VALUES(?,?)",[$this->id,$message]);
	}

	public function isGuest(){
		return $this->id==0;
	}

	public function isAdmin(){
		return $this->role=='Admins';
	}

	public function signOut() {
		$this->setAttr(0, 'Guests', null);
	}


}