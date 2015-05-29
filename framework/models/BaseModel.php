<?php	

use Phalcon\Mvc\Model;
use \Phalcon\Mvc\Model\Message as Message;

class BaseModel extends Model
{
	public function getAttributes()
	{
	    $metaData = $this->getModelsMetaData();
	    return $metaData->getAttributes($this);
	}

	private $_allowed_empty_strings=array();

	public function allowEmptyString($array){
		$this->_allowed_empty_strings=$array;
	}

	public function beforeValidation()
	{
	    // $notNullAttributes = $this->getModelsMetaData()->getNotNullAttributes($this);
	    // var_dump($notNullAttributes);
	    // exit();
	    // foreach ($notNullAttributes as $field) {
	    //     if (!isset($this->$field) || $this->$field === null) {
	    //         $this->appendMessage(new Message($field . ' is required'));
	    //         // throw new Message($field . ' is required');
	    //         // var_dump('is required');
	    //         // exit();
	    //         return false;
	    //     }
	    // }
	    // return true;
	    // var_dump($this->_allowed_empty_strings);
	    
	    foreach ($this->_allowed_empty_strings as $key) {
	    	if (isset($this->$key) && $this->$key===''){
	    		// var_dump($key);
	    		$this->$key = new Phalcon\Db\RawValue('""');
	    		// var_dump($this->$key);
	    	}
	    }
	    // exit();
	}

}