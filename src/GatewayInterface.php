<?php

namespace Opportus\Orm;

/**
 * The data gateway interface...
 *
 * @version 0.0.1
 * @package Opportus\Orm
 * @author  Clément Cazaud <opportus@gmail.com>
 */
interface GatewayInterface
{
	/**
	 * Connects to the database.
	 *
	 * @param string $database Default:'default'
	 */
	public function connect(string $database = 'default');

	/**
	 * Disconnects from the database.
	 *
	 * @param string $database Default:'default'
	 */
	public function disconnect(string $database = 'default');

	/**
	 * Gets last insert ID.
	 *
	 * @param  string|null $name     Default:null
	 * @param  string      $database Default:'default'
	 * @return string
	 */
	public function getLastInsertId($name = null, string $database = 'default');

	/**
	 * Creates.
	 *
	 * @param  array            $params
	 * @return GatewayInterface
	 */
	public function create(array $params);

	/**
	 * Reads.
	 *
	 * @param  array $params
	 * @return array
	 */
	public function read(array $params);

	/**
	 * Updates.
	 *
	 * @param  array            $params
	 * @return GatewayInterface
	 */
	public function update(array $params);

	/**
	 * Deletes.
	 *
	 * @param  array            $params
	 * @return GatewayInterface
	 */
	public function delete(array $params);
}

