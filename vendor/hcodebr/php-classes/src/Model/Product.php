<?php
namespace Hcode\Model;
// Contra barra inicial indica para começar da root do projeto
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model{

    
    public static function listAll(){
        $sql = new Sql();
        return  $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
    }
   
    public function save(){
        $sql = new Sql();
        $rs = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllenght, :vlweight, :desurl)" , array(
            ":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllenght"=>$this->getvllenght(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
        ));

        $this->setData($rs[0]);
        
    }
    
    public function get($idproduct){
        $sql = new Sql();
        $rs = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(":idproduct"=>$idproduct));
        $this->setData($rs[0]);
    }
    
    public function delete(){
        $sql = new Sql();
        $sql->query("DELETE FROM tb_products  WHERE idproducts = :idproducts", array(":idproducts"=>$this->getidproducts()));
    }
    
}


?>