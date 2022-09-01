<?php 

session_start();

// pesquisar para o que serve o "exit;"" na hora de fazer o redirecionamento (Ex: header("Location: (rota)"))
// truncate table no banco reseta os ids para não continuarem em ordem crescente
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdm;
use \Hcode\Model\User;
use \Hcode\Model\Category;


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


$app->get('/admin/forgot', function() {

	$page = new PageAdm([

		"header"=>false,
		"footer"=>false

	]);

	$page->setTpl("forgot");

});


$app->post('/admin/forgot', function() {

	// ["email"] é igual a name="email" (parâmetro name do input de email do formulário que tem method="post" em forgot.html)
	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");	
	exit;

});


$app->get('/admin/forgot/sent', function() {

	$page = new PageAdm([

		"header"=>false,
		"footer"=>false

	]);

	$page->setTpl("forgot-sent");

});


$app->get('/admin/forgot/reset', function() {


	$user = User::validForgotDecrypt($_GET["code"]);


	$page = new PageAdm([

		"header"=>false,
		"footer"=>false

	]);

	$page->setTpl("forgot-reset", array(

		"name"=>$user["desperson"],
		"code"=>$_GET["code"]

	));

});


$app->post('/admin/forgot/reset', function() {

	//******************************************************************************
	$forgot = User::validForgotDecrypt($_GET["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);


	$password = password_hash($_POST["password"], PASSWORD_DEFAULT,[

		// custo de processamento para gerar um novo hash (buscar equilíbrio para não derrubar o servidor)
		"cost"=>12

	]);


	// salva a nova senha redefinida pelo link recebido no email (com novo hash gerado) (dentro de 1 hora do envio pelo site) no banco
	$user->setPassword($password);

	$page = new PageAdm([

		"header"=>false,
		"footer"=>false

	]);

	$page->setTpl("forgot-reset-success");

});


$app->get('/admin/categories', function() {

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdm();

	$page->setTpl("categories", array(

		"categories"=>$categories

	));

});

$app->get('/admin/categories/create', function() {

	User::verifyLogin();

	$page = new PageAdm();

	$page->setTpl("categories-create");

});


$app->post('/admin/categories/create', function() {

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});


$app->get('/admin/categories/:idcategory/delete', function($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");
	exit;

});


$app->get('/admin/categories/:idcategory', function($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdm();

	$page->setTpl("categories-update", array(

		// "categories" é a variável presente no template (fazemos o bind aqui (array))
		"category"=>$category->getValues()

	));

});


$app->post('/admin/categories/:idcategory', function($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});


$app->get('/categories/:idcategory', function($idcategory) {

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [

		"category"=>$category->getValues(),
		"products"=>[]

	]);


});


$app->run();


 ?>