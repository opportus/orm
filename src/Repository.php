<?php

namespace Opportus\Orm;

use \InvalidArgumentException;

/**
 * The repository...
 *
 * @version 0.0.1
 * @package Opportus\Orm
 * @author  Clément Cazaud <opportus@gmail.com>
 */
class Repository
{
	/**
	 * @var Mapper $mapper
	 */
	protected $mapper;

	/**
	 * @var Factory $factory
	 */
	protected $factory;

	/**
	 * @var array $models
	 */
	protected $models = array();

	/**
	 * Constructor.
	 *
	 * @param Mapper  $mapper
	 * @param Factory $factory
	 */
	public function __construct(Mapper $mapper, Factory $factory)
	{
		$this->mapper  = $mapper;
		$this->factory = $factory;
	}

	/**
	 * Gets the model.
	 *
	 * @param  array|int $params Default:array()
	 * @return mixed
	 */
	public function get($params = array())
	{
		$single = false;
		$models = array();

		if (is_int($params)) {
			$single = true;
			$id     = $params;

			if (isset($this->models[$id])) {
				return $this->model[$id];
			}

			$params = array(
				'where' => array(
					0 => array(
						'column'   => 'id',
						'operator' => '=',
						'value'    => $id,
					),
				),
			);

		} elseif (is_array($params) && isset($params['where'])) {
			foreach ($params['where'] as $clauseNumber => $clause) {

				// The repository handles only requests based on the model/row ID...
				if (isset($clause['column']) && 'id' !== $clause['column']) {
					unset($params['where'][$clauseNumber]);
					continue;

				} elseif (isset($clause['value'])) {
					$id = $clause['value'];

					// If the model is already in the repository...
					if (isset($this->models[$id])) {
						$models[$id] = $this->models[$id];
						unset($params['where'][$clauseNumber]);
						continue;
					}
				}
			}

			// Reorders where clauses...
			$i = 0;
			foreach ($params['where'] as $clauseNumber => $clause) {
				if ($i = 0 && isset($clause['condition'])) {
					unset($clause['condition']);
				}

				$params['where'][$i] = $clause;

				$i++;
			}
		}

		if (! empty($data = $this->mapper->read($params))) {
			foreach ($data as $datum) {
				$model                           = $this->factory->create($datum);
				$this->models[$model->get('id')] = $model;
				$models[$model->get('id')]       = $model;
			}
		}

		$models = $single ? current($models) : $models;

		return $models;
	}

	/**
	 * Adds the model.
	 *
	 * @param  Model    $model
	 * @return int|null $model->id
	 */
	public function add(Model $model)
	{
		$params = array(
			'data' => $model->get('data'),
		);

		if (null !== $model->get('id')) {
			$this->models[$model->get('id')] = $model;

			$params['where'] = array(
				0 => array(
					'column'   => 'id',
					'operator' => '=',
					'value'    => $model->get('id'),
				),
			);

			$this->mapper->update($params);

		} elseif (! empty($data = $this->mapper->create($params))) {
			$model->hydrate($data);
			$this->models[$model->get('id')] = $model;
		}

		return $model->get('id');
	}

	/**
	 * Deletes the model.
	 *
	 * @param  int  $id
	 * @return bool
	 */
	public function delete($id)
	{
		$id = (int) $id;

		if (isset($this->models[$id])) {
			unset($this->models[$id]);
		}

		$params = array(
			'where' => array(
				0 => array(
					'column'   => 'id',
					'operator' => '=',
					'value'    => $id,
				),
			),
		);

		return $this->mapper->delete($params);
	}
}

