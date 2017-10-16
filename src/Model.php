<?php

namespace Opportus\Orm;

/**
 * The model...
 *
 * @version 0.0.1
 * @package Opportus\Orm
 * @author  Clément Cazaud <opportus@gmail.com>
 */
class Model
{
	/**
	 * @var array $properties
	 */
	protected $properties;

	/**
	 * @var array $propertyValidationCallbacks
	 */
	protected $propertyValidationCallbacks;

	/**
	 * Constructor.
	 *
	 * @param array $properties
	 * @param array $propertyValidationCallbacks
	 * @param array $data                        Default:array()
	 */
	public function __construct(array $properties, array $propertyValidationCallbacks, array $data = array())
	{
		$this->properties                  = $properties;
		$this->propertyValidationCallbacks = $propertyValidationCallbacks;

		$this->hydrate($data);
	}

	/**
	 * Hydrates the model with the passed data.
	 *
	 * @param  array $data
	 * @return array
	 */
	public function hydrate(array $data)
	{
		if (empty($data)) {
			return;
		}
	
		$invalidProperties = array();

		foreach($data as $property => $value) {
			if (array_key_exists($property, $this->properties)) {
				if ( false === $this->set($property, $value)) {
					$invalidProperties[] = $property;
				}
			}
		}

		return $invalidProperties;
	}

	/**
	 * Sets a property.
	 *
	 * @param  string $property
	 * @param  mixed  $value
	 * @return bool
	 */
	public function set(string $property, $value)
	{
		if ($validationCallback = $this->propertyValidationCallbacks[$property]) {
			if (call_user_func($validationCallback, $value)) {
				$this->properties[$property] = $value;

				return true;
			}

			return false;
		}
	}

	/**
	 * Gets a property.
	 *
	 * @param  string $property
	 * @return mixed
	 */
	public function get(string $property)
	{
		return ($property === 'data') ? $this->properties : $this->properties[$property];
	}
}

