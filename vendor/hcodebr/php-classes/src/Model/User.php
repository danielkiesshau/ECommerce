<?php
namespace Hcode\Model;
// Contra barra inicial indica para começar da root do projeto
use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{
    const SESSION="user";
    public static function login($login,$password){
        $sql = new Sql();
        
        $rs = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(":LOGIN"=>$login));
        if(count($rs) == 0){
            //Contra barra no Exception devido ao namespace atual ser o Model
            throw new \Exception("Usuário inexistente e senha inválido",1);
        }
        $data = $rs[0];
       if(password_verify($password, $data['despassword']) == true){
            
            $user = new User();

            $user->setData($data);
            //The data from the user will be used in the session 
            $_SESSION[User::SESSION] = $user->getValues();
           
            return $user;
        }else{
            throw new \Exception("Usuário inexistente e senha inválido",1);
        }
    }
    
    public static function verifyLogin($inadmin = true){
        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            || 
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        ){
            header("Location: /admin/login");
            exit;
        }
    }
    
    public static function logout(){
        $_SESSION[User::SESSION] = NULL;
    }
    
    public static function listAll(){
        $sql = new Sql();
        return  $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }
    
    public function save(){
        $sql = new Sql();
        $rs = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)" , array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));
        
        $this->setData($rs[0]);
    }
    
    public function get($iduser){
        $sql = new Sql();
        $rs = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser=:iduser", array(
            ":iduser"=>$iduser
        ));
        
        
        $this->setData($rs[0]);
    }
    
    public function update(){
        $sql = new Sql();
        $rs = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)" , array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));
        
        $this->setData($rs[0]);
    }
    
    public function delete(){
        $sql = new Sql();
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()   
        ));
         
    }
    
}


?>