<?php
namespace Hcode;
class Model{
    
    private $values = [];
    //The __call will be called when someone try to call a method to use Getters and Setters(this will create for every class that inherit of this one)
    public function __call($name, $args){
        //We will get the 
        $method = substr($name, 0, 3);
        $fieldName = substr($name, 3, strlen($name));
      
        switch($method){
            case "get":
                return $this->values['fieldName'];
            break;
            case "set":
                //First argument will be passed to the values
                $this->values[$fieldName] = $args[0];
            break;
        }
    }
    
    public function setData($data = array()){
        //This will create objects and set each field in that the DB encountered.
        foreach($data as $key=>$value){
            //use the {} brackets to set dynamic strings
            $this->{"set".$key}($value);
        }
    }
    
    public function getValues(){
        return $this->values;
    }
    
}


?>