<?php
namespace Hcode\Model;
// Contra barra inicial indica para começar da root do projeto
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model{

    
    public static function listAll(){
        $sql = new Sql();
        return  $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }
   
    public function save(){
        $sql = new Sql();
        $rs = $sql->select("CALL sp_categories_save(:idcategory, :descategory)" , array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
        ));

        $this->setData($rs[0]);
        
        
        //Update the categores-menu categories's file
        Category::updateFile();
    }
    
    public function get($idcategory){
        $sql = new Sql();
        $rs = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(":idcategory"=>$idcategory));
        $this->setData($rs[0]);
    }
    
    public function delete(){
        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(":idcategory"=>$this->getidcategory()));
        
        Category::updateFile();
    }
    
    public static function updateFile(){
        $categories = Category::listAll();
        
        $html = array();
        
        foreach($categories as $row){
            array_push($html,'<li><a href=/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
        }
        
        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode("",$html));
            
        
    }
}


?>