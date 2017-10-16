<?php

namespace Opportus\Orm;

/**
 * The mapper...
 *
 * @version 0.0.1
 * @package Opportus\Orm
 * @author  Clément Cazaud <opportus@gmail.com>
 *
 * @todo Handle advanced mapping
 */
class Mapper
{
	/**
	 * @var GatewayInterface $gateway
	 */
	protected $gateway;

	/**
	 * @var array $maps
	 */
	protected $maps;

	/**
	 * Constructor.
	 *
	 * @param GatewayInterface $gateway
	 * @param array            $maps
	 */
	public function __construct(GatewayInterface $gateway, array $maps = array())
	{
		$this->gateway = $gateway;
		$this->maps    = $maps; 
	}

	/**
	 * Creates.
	 *
	 * @param  array $params
	 * @return array
	 */
	public function create(array $params)
	{
		$params['table'] = $this->maps['id']['table'];
		$params['data']  = $this->convertPropertyToColumn($params['data']);

		if ($this->gateway->create($params)) {
			$params = array(
				'where' => array(
					0 => array(
						'column'   => 'id',
						'operator' => '=',
						'value'    => $this->gateway->getLastInsertId('id'),
					),
				),
			);

			return current($this->read($params));

		} else {
			return array();
		}
	}

	/**
	 * Reads.
	 *
	 * @param  array $params
	 * @return array
	 *
	 * @todo Implement joins to handle advanced mapping
	 */
	public function read(array $params)
	{
		$params['table'] = $this->maps['id']['table'];

		return $this->convertColumnToProperty($this->gateway->read($params));
	}

	/**
	 * Updates.
	 *
	 * @param  array $params
	 * @return bool
	 */
	public function update(array $params)
	{
		$params['table'] = $this->maps['id']['table'];
		$params['data']  = $this->convertPropertyToColumn($params['data']);

		return $this->gateway->update($params);
	}

	/**
	 * Deletes.
	 *
	 * @param  array $params
	 * @return bool
	 */
	public function delete(array $params)
	{
		$params['table'] = $this->maps['id']['table'];

		return $this->gateway->delete($params);
	}

	/**
	 * Converts column names to property names.
	 *
	 * @param  array $resultSet
	 * @return array $convertedResultSet
	 */
	protected function convertColumnToProperty($resultSet)
	{
		$convertedResultSet = array();
		$modelData          = array();

		foreach ($resultSet as $key => $result) {
			foreach ($result as $column => $value) {
				$modelProperty = lcfirst(str_replace('_', '', ucwords($column, '_')));
				$modelData[$modelProperty] = $value;
			}

			$convertedResultSet[$key] = $modelData;
		}

		return $convertedResultSet;
	}

	/**
	 * Converts property names to column names.
	 *
	 * @param  array $properties
	 * @return array $columns
	 */
	protected function convertPropertyToColumn($properties)
	{
		$columns = array();

		foreach ($properties as $property => $value) {
			$column    = strtolower(preg_replace('/\B[A-Z]/', '_$0', $property));
			$columns[$column] = $value;
		}

		return $columns;
	}
}

