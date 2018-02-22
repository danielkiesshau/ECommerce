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
}


?>