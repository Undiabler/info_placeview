<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\Email as EmailValidator;
use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;

class Users extends BaseModel
{

	public $id;

            public $firstName;
            public $lastName;

	public $email;

	public $hash;
	
	public $role;

	public $status;

       public function initialize()
    {
        $this->allowEmptyString(array('lastName'));
       $this->skipAttributesOnCreate(array('registrationDate','lastLogin'));
    }

	public function validation()
            {

                $this->validate(new UniquenessValidator(
                    array(
                        "field"   => "email",
                        "message" => "Пользователь с таким email уже существует"
                    )
                ));

                $this->validate(new EmailValidator(array(
                            'field' => 'email'
                )));

                return $this->validationHasFailed() != true;
            }

}