<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Order extends Model{
    
    public function save(){
        $sql = new Sql();
        
        $rs = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [
            ':idorder'=>$this->getidorder(), 
            ':idcart'=>$this->getidcart(), 
            ':iduser'=>$this->getiduser(),
            ':idstatus'=>$this->getidstatus(),
            ':idaddress'=>$this->getidaddress(), 
            ':vltotal'=>$this->getvltotal()
        ]);
        
        if($rs > 0){
            $this->setData($rs[0]);
        }
        
    }
    
    public function get($idorder){
        $sql = new Sql();
        
        $rs = $sql->select("
            SELECT * FROM tb_orders a
            INNER JOIN tb_ordersstatus b USING(idstatus)
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress) 
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            WHERE a.idorder = :idorder
        ",[
            ':idorder'=>$idorder
        ]);
        
        if(count($rs) > 0){
            $this->setData($rs[0]);
        }
    }
}

?>