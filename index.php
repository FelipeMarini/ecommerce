<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;


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


$app->run();


 ?>