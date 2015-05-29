<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\Email as EmailValidator;
use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;

class Spheres extends Model
{

	public $id;

	public $owner_id;

	public $name;

	public $color;
	
	public function validation()
    {

    }

}