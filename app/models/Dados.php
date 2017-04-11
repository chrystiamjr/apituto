<?php

use Phalcon\Mvc\Model\Message;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class Dados extends \Phalcon\Mvc\Model
{

	/**
	 *
	 * @var integer
	 * @Primary
	 * @Identity
	 * @Column(type="integer", length=11, nullable=false)
	 */
	public $id;

	/**
	 *
	 * @var string
	 * @Column(type="string", length=80, nullable=false)
	 */
	public $nome;

	/**
	 *
	 * @var string
	 * @Column(type="string", nullable=false)
	 */
	public $criado;

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSchema("api_teste");
	}

	/**
	 * Returns table name mapped in the model.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return 'dados';
	}

	public function validation()
	{
		$validator = new Validation();
		$validator->add(
			'nome',
			new Uniqueness(
				[
					"message" => "Nome n&atilde;o pode ser repetido",
				]
			)
		);
		return $this->validate($validator);
	}

	/**
	 * Allows to query a set of records that match the specified conditions
	 *
	 * @param mixed $parameters
	 * @return Dados[]|Dados
	 */
	public static function find($parameters = null)
	{
		return parent::find($parameters);
	}

	/**
	 * Allows to query the first record that match the specified conditions
	 *
	 * @param mixed $parameters
	 * @return Dados
	 */
	public static function findFirst($parameters = null)
	{
		return parent::findFirst($parameters);
	}

}
