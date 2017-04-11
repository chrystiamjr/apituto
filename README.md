# APITUTO

***

#### TUTORIAL DE API REST UTILIZANDO PHALCON MICRO APPLICATION

***

- Para desenvolvimento desta aplicação simples, utilizo o banco de dados de teste localizado dentro da aplicação (`api_teste.sql`), utilizo as configurações existentes dentro de `app/config/` para configurações do banco de dados e para permissão de uso facilitado de **Models e ModelsManager**;
- A aplicação principal se encontra dentro de `public/index.php` (ou o **Bootstrap** da aplicação), substituindo a forma padrão de aplicação eu utilizo a aplicação `Micro()` que possibilita uma aplicação de rápida performace e também permite o uso de *API's*;

***

#### Estrutura de métodos:

***

|      | GET | GET($id) | POST | PUT | DELETE |
| ---- | --- | -------- | ---- | --- | ------ |
|DADOS | Retorna TODOS | Retorna UM | Insere novos dados | Altera dados existente | Remove dados existentes|

***

#### Modo de desenvolvimento da aplicação:

***

Após realizar a criação do projeto devemos seguir os seguintes passos:
1. Realizar a alteração do `public/index.php` importanto `use Phalcon\Mvc\Micro;` e referenciando a aplicação como uma aplicação micro, `$app = new Micro($di);`;
2. Devemos alterar nossas configurações do banco de dados localizado no arquivo `app/config/config.php`;
3. Utilizar o **Phalcon-CLI** para criação dos models a serem utilizados;
4. Dentro de `index.php` devemos utilizar o seguinte esqueleto para desenvolvimento:
```
$app = new Micro($di);

// Utiliza a API e através do método GET, RETORNA TODOS OS DADOS.
$app->get(
	"/api/dados",
	function () use ($app) {

	}
);

/*
* Utiliza a API e através do método GET, RETORNA APENAS UM DADO.
* Nota: Ele receberá como GET um ID que terá um tratamento através do ':[0-9]+' 
* permitindo assim que o mesmo seja somente do tipo INTEIRO.
*/
$app->get(
	"/api/dados/{id:[0-9]+}",
	function ($id) use ($app) {

	}
);

// Utiliza a API e através do método POST, INSERE UM NOVO DADO.
$app->post(
	"/api/dados",
	function () use ($app) {

	}
);

// Utiliza a API e através do método PUT, ALTERA UM DADO EXISTENTE respeitando o tratamento do ID.
$app->put(
	"/api/dados/{id:[0-9]+}",
	function ($id) use ($app) {

	}
);

// Utiliza a API e através do método DELETE, REMOVE UM DADO EXISTENTE respeitando o tratamento do ID.
$app->delete(
	"/api/dados/{id:[0-9]+}",
	function () {

	}
);

$app->handle();
```
5. Por partes iremos inserir as buscas do banco e a atibuição dos dados para envio através da *API*. 
6. Iniciaremos pelo primeiro **GET**, inserindo o **PHQL** que será utilizado, utillizando o `modelsManager` que foi criado junto com a `Micro Application` devemos utilizar o **PHQL** criado para ser interpretado, após os dados serem retornados utilizaremos um `Array` de dados para armazenar todos os dados encontrados na consulta e por fim devemos codificar o nosso `Array` para enviar em forma de `JSON`.
```
// Utiliza a API e através do método GET, RETORNA TODOS OS DADOS.
$app->get(
	"/api/dados",
	function () use ($app) {
		$phql = "SELECT * FROM dados";
		$dados = $app->modelsManager->executeQuery($phql);
		$data = [];
		foreach($dados as $dado){
			$data[] = [
				'id'      => $dado->id,
				'nome'    => $dado->nome,
				'criado'  => $dado->criado
			];
		}
		echo json_encode($data);
	}
);
```
7. Ainda trabalhando com o **GET** criaremos a mesma estrutura do anterior, porém, recebendo uma variável `$id` que será passada junto da **URL** da **API**. A variável recebida sofre por um tratamento utilizando o `{id:[0-9]+}`, onde determinamos o seu nome seguido do tipo de dado que ela poderá receber, no caso limitando os dados somente para dados do tipo inteiro, deste modo podemos filtrar nossa consulta **PHQL** para que a mesma retorne somente um `Array` de dados específico.
```
/*
* Utiliza a API e através do método GET, RETORNA APENAS UM DADO.
* Nota: Ele receberá como GET um ID que terá um tratamento através do ':[0-9]+' 
* permitindo assim que o mesmo seja somente do tipo INTEIRO.
*/
$app->get(
	"/api/dados/{id:[0-9]+}",
	function ($id) use ($app) {
		$phql = "SELECT * FROM dados WHERE id = ".$id;
		$dados = $app->modelsManager->executeQuery($phql);
		$data = [];
		foreach($dados as $dado){
			$data[] = [
				'id'      => $dado->id,
				'nome'    => $dado->nome,
				'criado'  => $dado->criado
			];
		}
		echo json_encode($data);
	}
);
```
8. Mudando agora para **POST** criaremos uma estrutura parecida com a anterior, porém, ao invés de retornar dados iremos utilizar o método `RESPONSE()` para enviar o status da aplicação e também retornar os dados que foram salvos. Para isso devemos, criar um novo `Array` para realizar nossa inserção, onde o mesmo receberá os dados de **nome** e **criado** enviados via **POST**.
9. Após realizarmos a busca no banco de dados, iremos iniciar uma nova instancia do método `Response()`, que nos permite enviar códigos de status (tais como *201, 404, 500, 401, 409*).
10. Após instanciarmos a variável de resposta, verificaremos se o status da nossa pesquisa no banco retornou sucesso, se sim, determinaremos uma response com status *201*, retornaremos do nosso model os dados criados e enviaremos uma resposta através de conteúdo `JSON` passando o nosso status e os dados retornados do nosso model.
11. Caso nossa inserção tenha sido sem sucesso, enviaremos um código de status *409*, armazenaremos todas as mensagens de erro encontradas dentro de um `Array` e enviaremos uma resposta através de conteúdo `JSON` passando o nosso status e os todos os erros encontrados.
```
// Utiliza a API e através do método POST, INSERE UM NOVO DADO.
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
```
12. No nosso **PUT** criaremos uma estrutura totalmente parecida com a anterior, porém, em nossa **URL** enviamos uma variável `$id` tratada para ser utilizada para modificar um dado específico e ao enviar nossa mensagem de sucesso não enviaremos os dados alterados, sendo somente enviadas as mensagens de status.
```
// Utiliza a API e através do método PUT, ALTERA UM DADO EXISTENTE respeitando o tratamento do ID.
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
```
13. Por fim, nosso **DELETE** irá ter uma estrutura igual a do **PUT**, diferenciando apenas a **QUERY PHQL** utilizada.
```
// Utiliza a API e através do método DELETE, REMOVE UM DADO EXISTENTE respeitando o tratamento do ID.
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
```
14. Nossa *API* está criada com sucesso, como estaremos rodando ela via **localhost**, devemos seguir a seguinte estrutura:
    * `http://localhost/NOME_DO_PROJETO/api/NOME_DA_TABELA`.

***

#### Recomendações:

***

* Como recomendação de testes de *API*, recomendo utilizar o [POSTMAN](https://www.getpostman.com/) para o trabalho.
* Caso deseja integrar esta aplicação **REST** em seu sistema, lembre-se de alterar os dados do seu arquivo `.htaccess` localizado na raiz do projeto para:
```apache
RewriteRule  ^$ NOME_DO_MICRO_APP/api/       [L]
RewriteRule  (.*) NOME_DO_MICRO_APP/api/$1   [L]
```
