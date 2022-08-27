<?php


namespace Hcode;


class Model {

	// array que tem os valores dos campos dos objetos, para fazer os getters e setters de forma dinâmica
	private $values = [];


	// método mágico que roda toda vez que um método é chamado
	public function __call($name, $args)
	{

		// no parâmetro $name traga os 3 caracteres a partir da posição 0 (0, 1 e 2), para saber se o tipo do método (Exemplo: "get")
		$method = substr($name, 0, 3);

		// descobrir o campo que está sendo passado da mesma forma, a partir da posição 3 (Exemplo: "get IDUSUARIO")
		$field = substr($name, 3, strlen($name));

		
		// var_dump($method, $field);
		// exit para não dar redirect
		// exit;

		// args é o valor que foi passado para o atributo, Exemplo: id = CINCO
		switch ($method) {
			
			case 'get':
				return $this->values[$field];
			break;

			case 'set':
				$this->values[$field] = $args[0];
			break;
		
		}

	}


	public function setData($data = array())
	{

		// faz um set dinâmico para todos os campos da tabela que vierem do banco de dados
		foreach ($data as $key => $value) {
			
			$this->{"set".$key}($value);

		}

	}


	public function getValues()
	{

		// existe esse método pois o array values é private, sempre bom não acessar diretamente (questão de segurança)
		return $this->values;

	}




}



?>