<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdm;
use \Hcode\Model\User;


$app = new Slim();

// mostra os erros explicados
$app->config('debug', true);


// rota principal (home)
$app->get('/', function() {
    
    // teste para mostrar a conexão com o banco mysql e mostrar a tabela de users (formato JSON) na tela home
	
	// $sql = new Hcode\DB\Sql();

	// $results = $sql->select("SELECT * FROM tb_users");

	// echo json_encode($results);

	// construct chama o header
	$page = new Page();

	// método setTpl() chama o conteúdo (index)
	$page->setTpl("index");

	// no final o destruct chama o footer

});


$app->get('/admin', function() {

	User::verifyLogin();

	$page = new PageAdm();

	$page->setTpl("index");

});

$app->get('/admin/login', function() {

	$page = new PageAdm([

		"header"=>false,
		"footer"=>false

	]);

	$page->setTpl("login");

});


$app->post('/admin/login', function() {

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;
});


$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;
});



$app->run();


 ?>