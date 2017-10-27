<?php

namespace Opportus\Orm;

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
	 * @var array $services
	 */
	protected $services = array();

	/**
	 * Registers an entity.
	 *
	 * @param array $entityDefinition Structured as such:
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
	public function registerEntity(array $entityDefinition, GatewayInterface $gateway)
	{
		$properties          = array_fill_keys(array_keys($entityDefinition['properties']), null);
		$validationCallbacks = array();
		$maps                = array();

		foreach ($entityDefinition['properties'] as $property => $propertyDefinition) {
			$validationCallbacks[$property] = $propertyDefinition['validationCallback'];
			$maps[$property]['table']       = $propertyDefinition['table'];
			$maps[$property]['column']      = $propertyDefinition['column'];
		}

		$mapper     = new Mapper($gateway, $maps);
		$factory    = new Factory($properties, $validationCallbacks);
		$repository = new Repository($mapper, $factory);

		$this->services[$entityDefinition['name']] = array(
			'mapper'     => $mapper,
			'factory'    => $factory,
			'repository' => $repository
		);
	}

	/**
	 * Getter overload.
	 *
	 * @param  string $name
	 * @param  string $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		$entities = array_map('ucfirst', array_keys($this->services));

		if (preg_match('/^get(' . implode('|', $entities) . ')(.+)$/', $name, $matches)) {
			$matches = array_map('strtolower', $matches);

			if (array_key_exists($matches[2], $this->services[$matches[1]])) {
				return $this->services[$matches[1]][$matches[2]];
			}
		}
	}
}

