<?php

namespace Opportus\Orm;

/**
 * The model factory...
 *
 * @version 0.0.1
 * @package Opportus\Orm
 * @author  Clément Cazaud <opportus@gmail.com>
 */
class Factory
{
	/**
	 * @var array $modelProperties
	 */
	protected $modelProperties;

	/**
	 * @var array $modelPropertyValidationCallbacks
	 */
	protected $modelPropertyValidationCallbacks;

	/**
	 * Constructor.
	 *
	 * @param array $modelProperties
	 * @param array $modelPropertyValidationCallbacks
	 */
	public function __construct(array $modelProperties, array $modelPropertyValidationCallbacks)
	{
		$this->modelProperties                  = $modelProperties;
		$this->modelPropertyValidationCallbacks = $modelPropertyValidationCallbacks;
	}

	/**
	 * Creates a model.
	 *
	 * @param  array $data Default:array()
	 * @return Model
	 */
	public function create(array $data = array())
	{
		return new Model($this->modelProperties, $this->modelPropertyValidationCallbacks, $data);
	}
}

