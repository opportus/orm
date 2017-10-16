<?php

namespace Opportus\Orm;

use \Exception;

/**
 * The entity manager...
 *
 * @version 0.0.1
 * @package Opportus\Orm
 * @author  Clément Cazaud <opportus@gmail.com>
 */
class EntityManager
{
	/**
	 * @var array $entities
	 */
	protected $entities = array();

	/**
	 * Registers an entity.
	 *
	 * @param array $entity Structured as such:
	 *    array(
	 *         'name'       => '',
	 *         'properties' => array(
	 *             'id' => array(
	 *                 'validationCallback' => '',
	 *                 'table'              => '',
	 *                 'column'             => ''
	 *             ),
	 *         ),
	 *     );
	 *
	 * @param GatewayInterface $gateway
	 */
	public function register(array $entity, GatewayInterface $gateway)
	{
		$entityName          = $entity['name'];
		$properties          = array_fill_keys(array_keys($entity['properties']), null);
		$validationCallbacks = array();
		$maps                = array();

		foreach ($entity['properties'] as $property => $definition) {
			$validationCallbacks[$property] = $definition['validationCallback'];
			$maps[$property]['table']       = $definition['table'];
			$maps[$property]['column']      = $definition['column'];
		}

		$mapper     = new Mapper($gateway, $maps);
		$factory    = new Factory($properties, $validationCallbacks);
		$repository = new Repository($mapper, $factory);

		$this->entities[$entityName] = new Entity($entityName, $mapper, $factory, $repository);
	}

	/**
	 * Gets an entity.
	 *
	 * @param  string $entity
	 * @return Entity
	 * @throws Exception If entity is not registered
	 */
	public function get($entity)
	{
		if (! isset($this->entities[$entity])) {
			throw new Exception('Entity not registered');
		}

		return $this->entities[$entity];
	}
}
