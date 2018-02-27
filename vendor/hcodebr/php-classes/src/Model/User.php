<?php
namespace Hcode\Model;
// Contra barra inicial indica para começar da root do projeto
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{
    const SESSION = "user";
    const SECRET = "Hcode Store";
    
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
    
    public static function getForgot($email, $inadmin = true){
        $sql = new Sql();
        $rs = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail=:EMAIL;",array(":EMAIL"=>$email));
        if(count($rs) == 0){
            throw new \Exception("Não foi possível recuperar a senha");
        }else{
            $data = $rs[0];
            
            $rs2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)",array(
                ":iduser"=>$data['iduser'],
                ":desip"=>$_SERVER['REMOTE_ADDR']
            ));
            if(count($rs2) == 0){
                throw new \Exception("Não foi possível recuperar a senha");
            }else{
                $dataRecovery = $rs2[0];
  
                //Encrypting data for recovery
                $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                $code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);
                $result = base64_encode($iv.$code);
                
                if ($inadmin === true) {
                    $link = "http://e-commerce.com//admin/forgot/reset?code=$result";
                } else {
                    $link = "http://e-commerce.com//forgot/reset?code=$result";
                } 

                $mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir Senha! e-commerce", "forgot", 
                array(
                    "name"=>$data['desperson'],
                    "link"=>$link
                ));
                
                $mailer->send();
                
                return $data;
                
            }
        }
        
    }
    
    public static function validForgotDeCrypt($result){
        //Decrypting id recovery
        $result = base64_decode($result);
        $code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
        $iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');;
        $idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
        
        
        $sql = new Sql();
        
        //Inner join to retrieve name of the person to use it in the template
        //dtrecory must be NULL, that shows us that it has never been used, and in a interval below 1 hour
        $rs=  $sql->select('SELECT * FROM tb_userspasswordsrecoveries a 
        INNER JOIN tb_users USING(iduser) 
        INNER JOIN tb_persons USING(idperson) 
        WHERE a.idrecovery = :idrecovery AND a.dtrecovery IS NULL 
        AND DATE_ADD(a.dtregister, INTERVAL 24 HOUR) >= NOW()',array(":idrecovery"=>$idrecovery));
        
        if(count($rs) == 0){
            throw new \Exception("Não foi possível recuperar a senha");
        }else{
            return $rs[0];
        }
        
    }

    public static function setForgotUsed($idrecovery){
        $sql = new Sql();
        
        $sql->select("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery=:idrecovery",array(":idrecovery"=>$idrecovery));
    }
    
    public function setPassword($password){
        
        $sql = new Sql();
        
        $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
            ":password"=>$password,
            ":iduser"=>$this->getiduser()
        ));
    }
    
   
    
}


?>