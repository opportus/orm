<?php

namespace Opportus\Orm;

/**
 * The entity...
 *
 * @version 0.0.1
 * @package Opportus\Orm
 * @author  Clément Cazaud <opportus@gmail.com>
 */
class Entity
{
	/**
	 * @var string $name
	 */
	protected $name;

	/**
	 * @var Mapper $mapper
	 */
	protected $mapper;

	/**
	 * @var Factory $factory
	 */
	protected $factory;

	/**
	 * @var Repository $repository
	 */
	protected $repository;

	/**
	 * Constructor.
	 *
	 * @param string     $name
	 * @param Mapper     $mapper
	 * @param Factory    $factory
	 * @param Repository $repository
	 */
	public function __construct(string $name, Mapper $mapper, Factory $factory, Repository $repository)
	{
		$this->name       = $name;
		$this->mapper     = $mapper;
		$this->factory    = $factory;
		$this->repository = $repository;
	}

	/**
	 * Gets.
	 *
	 * @param  string $property
	 * @return mixed
	 */
	public function get($property)
	{
		return $this->{$property};
	}
}

