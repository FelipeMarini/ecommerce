<?php


namespace Hcode\Model;

// "\" = a partir do diretório raiz
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;


// fazer uma lógica para não poder cadastrar dois usuários com o mesmo email


class User extends Model {

	const SESSION = "User";

	const SECRET = "HcodePhp7_secret";


	public static function login($login, $password)
	{

		$sql = new Sql(); 

		// :LOGIN para evitar SQL injection
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if(count($results) === 0)
		{

			// "\" aqui é necessária na frente de Exception pois ela está no namespace principal e não no namespace Hcode\Model usado aqui
			throw new \Exception("Usuário inexistente ou senha inválida!");	

		}

		$data = $results[0];

		// $data["despassword"]) = hash criptografado
		if (password_verify($password, $data["despassword"]) === true)
		{

			$user = new User();

			$user->setData($data);

			// exit para não ter redirect
			// var_dump($user);
			// exit;

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} 
		
		else 
		{

			throw new \Exception("Usuário inexistente ou senha inválida");	

		}


	}


	public static function verifyLogin($inadmin = true)
	{

		// !(int)$_SESSION[User::SESSION]["iduser"] -> se iduser for null, a conversão para int retorna o valor 0 (por isso essa verificação neste caso) 
		// !(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin -> verifica se o usuário é do tipo admnistrador ($inadmin = true)
		
		if (!isset($_SESSION[User::SESSION]) || !$_SESSION[User::SESSION] || !(int)$_SESSION[User::SESSION]["iduser"] > 0 || 
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin) 
		{
			header("Location: /admin/login");
			exit;
		}

	}


	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}


	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	}

	
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(

			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()

		));


		$this->setData($results[0]);


	}


	public function get($iduser)
	{

		$sql = new Sql();


		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(


			":iduser"=>$iduser

		));


		$this->setData($results[0]);

	}


	public function update()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(

			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()

		));


		$this->setData($results[0]);

	}


	public function delete()
	{

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(

			":iduser"=>$this->getiduser()

		));

	}


	public static function getForgot($email)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email;", array(

			// bind do parâmetro do array -> prevenir sql injection
			":email"=>$email

		));

		
		if(count($results) === 0)
		{

			throw new \Exception("Não foi possível recuperar a senha");
			
		}

		else
		{

			$data = $results[0];

			// var_dump($data);

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip);", array(

				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER['REMOTE_ADDR']

			));

			if(count($results2) === 0)
			{
				throw new \Exception("Não foi possível recuperar a senha");
				
			}
			
			else
			{

				$dataRecovery = $results2[0];


				// var_dump($dataRecovery);
		       
		        
		        // iv - método de criptografia AES-256-CBC espera um vetor de inicialização único (iv) de 16 bytes(caracteres)
		        // função hash() gera um hash de acordo com o padrão sha256(algoritmo de funções para gerar um hash)
		        $secret_iv = "xxxxxxxxxxxxxxxxxxxxxxxxx";  
		        $iv = substr(hash('sha256', $secret_iv), 0, 16);
		        
		        // openssl_encrypt($data, $encrypt_method, $key, $options, $iv):
		        
		        // $data = dados a serem criptografados(idrecovery do banco) = $dataRecovery["idrecovery"]
		        // $encrypt_method = método de criptografia(constante) = "AES-256-CBC"
		        // $key = chave de criptografia (constante de 16 caracteres) = const Secret= "HcodePhp7_secret" = User::SECRET
		        // $options = int que desabilita (0) OPENSSL_RAW_DATA e OPENSSL_ZERO_PADDING = 0
		        // $iv = vetor de inicialização não nulo único para ter mais segurança 
		        
		        // para fazer o inverso -> openssl_decrypt(base64_decode(data), $encrypt_method, $key, 0, $iv);
				
				//*************************************************************************************************-
				// $code = base64_encode(openssl_encrypt($dataRecovery["idrecovery"], "AES-256-CBC", User::SECRET, 0, $iv));

				$code = ($dataRecovery["idrecovery"]);


				// // rota tipo "get" passa os parâmetros pela URL (após a ?) definindo a variável code como $code
				$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";


				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir a senha da Hcode Store", "forgot", array(

					"name"=>$data["desperson"],
					"link"=>$link
				));


				$mailer->send();


				return $data;


			}


		}

	}


	// tempo de validade do link para redefinir a senha é de 1 hora (de acordo como foi definido na query do método logo abaixo)
	public static function validForgotDecrypt($code)
	{

		$secret_iv = "xxxxxxxxxxxxxxxxxxxxxxxxx";  
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		$idrecovery = openssl_decrypt(base64_decode($code), "AES-256-CBC", User::SECRET, 0, $iv);

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a
			   INNER JOIN tb_users b USING(iduser)
			   INNER JOIN tb_persons c USING (idperson)
			   WHERE a.idrecovery = :idrecovery AND 
			   a.dtrecovery IS NULL AND 
			   DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", array(
				
			   		":idrecovery"=>$idrecovery

			));

		if (count($results) === 0) 
		{
			
			throw new \Exception("Não foi possível recuperar a senha");
			
		} 
		
		else 
		{
		
			return $results[0];

		}
		
	}


	public static function setForgotUsed($idrecovery)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery;", array(

			":idrecovery"=>$idrecovery

		));

	}


	public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser;", array(

			":password"=>$password,
			":iduser"=>$this->getiduser()

		));


	}



}



?>