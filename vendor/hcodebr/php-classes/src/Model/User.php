<?php


namespace Hcode\Model;

// "\" = a partir do diretório raiz
use \Hcode\DB\Sql;
use \Hcode\Model;


class User extends Model {

	const SESSION = "User";


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



}



?>