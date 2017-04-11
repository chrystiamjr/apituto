<?php
use Phalcon\Di\FactoryDefault;

use Phalcon\Mvc\Micro;
use Phalcon\Http\Response;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

	$di = new FactoryDefault();

	include APP_PATH . '/config/router.php';

	include APP_PATH . '/config/services.php';

	$config = $di->getConfig();

	include APP_PATH . '/config/loader.php';

	$app = new Micro($di);

	$app->get(
		"/api/dados",
		function () use ($app) {
			$phql = "SELECT * FROM dados";
			$dados = $app->modelsManager->executeQuery($phql);
			$data = [];
			foreach ($dados as $dado){
				$data[] = [
					"id"			=> $dado->id,
					"nome"  	=> $dado->nome,
					"criado"  => $dado->criado
				];
			}
			echo json_encode($data);
		}
	);

	$app->get(
		"/api/dados/{id:[0-9]+}",
		function ($id) use ($app) {
			$phql = "SELECT * FROM dados WHERE dados.id = ".$id;
			$dados = $app->modelsManager->executeQuery($phql);
			$data = [];
			foreach ($dados as $dado){
				$data[] = [
					"id"			=> $dado->id,
					"nome"  	=> $dado->nome,
					"criado"  => $dado->criado
				];
			}
			echo json_encode($data);
		}
	);

	$app->post(
		"/api/dados",
		function () use ($app) {
			$insert = [
				'nome' 	 => $app->request->getPost('nome'),
				'criado' => ( ($app->request->getPost('criado')) ? $app->request->getPost('criado') : date('Y-m-d H:i:s') )
			];

			$phql = "INSERT INTO dados (nome, criado) VALUES ('".$insert['nome']."', '".$insert['criado']."')";
			echo $phql;
			$status = $app->modelsManager->executeQuery($phql);

			// cria response
			$response = new Response();
			if($status->success() === true){
				$response->setStatusCode(201, "Criado");
				$dados = Dados::findFirstBynome($insert['nome']);
				$response->setJsonContent(
					[
						'status' => "ok",
						'data' => $dados
					]
				);
			} else {
				$response->setStatusCode(409, "Conflito");

				$erros = [];

				foreach ($status->getMessages() as $msg){
					$erros[] = $msg->getMessage();
				}

				$response->setJsonContent(
					[
						'status' => "Erro",
						'messages' => $erros
					]
				);
			}
			return $response;
		}
	);

	$app->put(
		"/api/dados/{id:[0-9]+}",
		function ($id) use ($app) {
			$update = [
				'id'		 => $id,
				'nome' 	 => $app->request->getPut('nome'),
				'criado' => $app->request->getPut('criado')
			];

			$phql = "UPDATE dados.dados SET dados.nome = '".$update['nome']."', dados.criado = '".$update['criado']."' WHERE dados.id = ".$id;
			$status = $app->modelsManager->executeQuery($phql);

			// cria response
			$response = new Response();
			if($status->success() === true){
				$response->setStatusCode(201, "Criado");
				$dados = $dados = Dados::findFirst($update['id']);
				$response->setJsonContent(
					[
						'status' => "ok",
						'data' => $dados
					]
				);
			} else {
				$response->setStatusCode(409, "Conflito");

				$erros = [];

				foreach ($status->getMessages() as $msg){
					$erros[] = $msg->getMessage();
				}

				$response->setJsonContent(
					[
						'status' => "Erro",
						'messages' => $erros
					]
				);
			}
			return $response;
		}
	);

	$app->delete(
		"/api/dados/{id:[0-9]+}",
		function () {
			$phql = "DELETE FROM dados.dados WHERE dados.id = ".$id;
			$status = $app->modelsManager->executeQuery($phql);

			// cria response
			$response = new Response();
			if($status->success() === true){
				$response->setJsonContent(
					[
						'status' => "ok"
					]
				);
			} else {
				$response->setStatusCode(409, "Conflito");
				$erros = [];
				foreach ($status->getMessages() as $msg){
					$erros[] = $msg->getMessage();
				}
				$response->setJsonContent(
					[
						'status' => "Erro",
						'messages' => $erros
					]
				);
			}
			return $response;
		}
	);

	$app->handle();


} catch (\Exception $e) {
	echo $e->getMessage() . '<br>';
	echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
