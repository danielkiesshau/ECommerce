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
            array_push($html,'<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
        }
        
        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode("",$html));
            
        
    }

    public function getProducts($related = true){
        $sql = new Sql();
        if($related == true){
            $rs = $sql->select("
                SELECT * FROM tb_products WHERE idproduct IN(
                    SELECT a.idproduct FROM  tb_products a 
                    INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                    WHERE b.idcategory = :idcategory);
            ",[":idcategory"=>$this->getidcategory()]);
            
        }else{
            $rs = $sql->select("
                SELECT * FROM tb_products WHERE idproduct NOT IN(
                    SELECT a.idproduct FROM  tb_products a 
                    INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                    WHERE b.idcategory = :idcategory);
            ",[":idcategory"=>$this->getidcategory()]);
        }
        
        return $rs;
    }
    
    public function getProductsPage($page = 1, $itemsPerPage = 8){
        $start = ($page-1)*$itemsPerPage;
        
        $sql = new Sql();
        $rs = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS * FROM tb_products a
            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
            INNER JOIN tb_categories c ON  c.idcategory = b.idcategory
            WHERE c.idcategory = :idcategory 
            LIMIT $start, $itemsPerPage;
        ",[
            ":idcategory"=>$this->getidcategory()
        ]);
        $total = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
                'data'=>Product::checkList($rs),
                'total'=>(int)$total[0]["nrtotal"],
                'pages'=>ceil($total[0]["nrtotal"]/ $itemsPerPage)
               ];
    }
    
    public function addProduct(Product $product){
        $sql = new Sql();
        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);
    }

    public function removeProduct(Product $product){
        $sql = new Sql();
        $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);
    }

}


?>