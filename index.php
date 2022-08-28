<?php 

session_start();

// pesquisar para o que serve o "exit;"" na hora de fazer o redirecionamento (Ex: header("Location: (rota)"))

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




$app->get('/admin/users', function() {

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdm();

	$page->setTpl("users", array(

		"users"=>$users

	));

});



$app->get('/admin/users/create', function() {

	User::verifyLogin();

	$page = new PageAdm();

	$page->setTpl("users-create");
});


$app->get('/admin/users/:iduser/delete', function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});


$app->get('/admin/users/:iduser', function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdm();

	$page->setTpl("users-update", array(

		"user"=>$user->getValues()

	));

});



$app->post('/admin/users/create', function() {

	User::verifyLogin();

	$user = new User();

	// if ternário para determinar o valor do atributo inadmin (tipo de usuário admnistrador do sistema)
	$_POST['inadmin'] = (isset($_POST['inadmin'])) ? 1 : 0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;

});


$app->post('/admin/users/:iduser', function($iduser) {

	User::verifyLogin();

	$user = new User();

	$_POST['inadmin'] = (isset($_POST['inadmin'])) ? 1 : 0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;

});


$app->run();


 ?>