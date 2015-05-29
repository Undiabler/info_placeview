<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\Email as EmailValidator;
use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;

class Tasks extends Model
{

	public $id;

	public $owner_id;

	public $name;

	public $is_finished;
	
	public $is_expired;

	public $is_important;

	public $date;
	public $time;
	
	public $repeat_type;

	public function validation()
    {

    }

}