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
	public $username;

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
                        "field"   => "username",
                        "message" => "Пользователь с таким username уже существует"
                    )
                ));

                $this->validate(new UniquenessValidator(array(
                    'field' => 'username',
                    'message' => 'Sorry, That username is already taken'
                )));

                $this->validate(new EmailValidator(array(
                            'field' => 'email'
                )));

                return $this->validationHasFailed() != true;
            }

}