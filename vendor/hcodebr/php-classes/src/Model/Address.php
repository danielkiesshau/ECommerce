<?php
namespace Hcode\Model;
// Contra barra inicial indica para começar da root do projeto
use \Hcode\DB\Sql;
use \Hcode\Model;


class Address extends Model{

    const SESSION_ERROR = 'AddressError';
    
    public static function getCEP($nrcep){
        
        $nrcep = str_replace("-", "", $nrcep);
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      
        
        $data = json_decode(curl_exec($ch), true);
    
        curl_close($ch);
        
        return $data;
        
        
        
        
    }

    public static function setMsgError($msg){
        
        $_SESSION[Address::SESSION_ERROR] = $msg;
        
    }
    
    public static function getMsgError(){
        
        $msg = (isset($_SESSION[Address::SESSION_ERROR]) ? $_SESSION[Address::SESSION_ERROR] : '' );
        
        Address::clearMsgError();
        
        return $msg;
        
    }

    public static function clearMsgError(){
        
        $_SESSION[Address::SESSION_ERROR] = null;
        
    }
    
    public function loadFromCEP($nrcep){
        $data = Address::getCEP($nrcep);
        
        if(isset($data['logradouro']) && $data['logradouro']){
         
            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescountry('Brasil');
            $this->setdeszipcode($nrcep);
        }
    }
    
    public function save(){
        $sql = new Sql();
        
        $rs = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)",[
            ':idaddress'=>$this->getidaddress(),
            ':idperson'=>$this->getidperson(),
            ':desaddress'=>utf8_decode($this->getdesaddress()),
            ':descomplement'=>utf8_decode($this->getdescomplement()),
            ':descity'=>utf8_decode($this->getdescity()),
            ':desstate'=>utf8_decode($this->getdesstate()),
            ':descountry'=>utf8_decode($this->getdescountry()),
            ':deszipcode'=>$this->getdeszipcode(),
            ':desdistrict'=>$this->getdesdistrict()
        ]);
        
        if(count($rs) > 0){
            $this->setData($rs[0]);
        }
    }
}


?>