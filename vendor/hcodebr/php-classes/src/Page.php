<?php
namespace Hcode;

use Rain\Tpl;

class Page{
    private $tpl;
    private $options = [];
    private $defaults = [
      "data" => []  
    ];
    
    public function __construct($opts = array()){
        //Overwrite the default options's values
        $this->options = array_merge($this->defaults,$opts);
        
        //Configure the tpl parameters for views and cache
        $config = array(
					"tpl_dir"       => $_SERVER['DOCUMENT_ROOT']."/views/",
					"cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views/cache/",
					"debug"         => false // set to false to improve the speed
        );
        Tpl::configure( $config );
        
        $this->tpl = new Tpl;
        
        $this->setData($this->options["data"]);
        
        $this->tpl->draw("header");
    }
    
    public function __destruct(){
        $this->tpl->draw("footer");
         
    }
    
    public function setTpl($name, $data = array(), $returnHTML = false){
        $this->setData($data);
        
        return $this->tpl->draw($name, $returnHTML);
    }
    
    private function setData($data = array()){
        //Assigning data
        foreach($data as $key => $value){
            $this->tpl->assign($key,$value);  
        }
        
    }
}


?>