<?php

namespace Opportus\Orm;

use Opportus\Orm\GatewayInterface;

use \PDO;
use \PDOException;
use \Exception;
use \RunTimeException;

/**
 * The default data gateway depending on PDO_MYSQL...
 *
 * @version 0.0.1
 * @package Opportus\Orm
 * @author  Clément Cazaud <opportus@gmail.com>
 */
class PdoMySqlGateway implements GatewayInterface
{
	/**
	 * @var array $config
	 */
	protected $config;

	/**
	 * @var array $connections
	 */
	protected $connections = array();

	/**
	 * @var PDOStatement $statement
	 */
	protected $statement;

	/**
	 * Constructor.
	 *
	 * @param array $config Respect the following array structure and keys:
	 *
	 * array(
	 *     'database1' => array(
	 *         'dbHost'    => 'localhost',
	 *         'dbName'    => '',
	 *         'dbUser'    => '',
	 *         'dbPass'    => '',
	 *         'dbOptions' => array(
	 *             'PDO:ATTR_PERSISTENT' => false,
	 *         )
	 *     ),    
	 * );
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * Connects to the given database.
	 *
	 * @param  string $database Default:'default'
	 * @throws RunTimeException(PDOException::getMessage, PDOException::errorInfo[0])
	 */
	public function connect(string $database = 'default')
	{
		if (isset($this->connections[$database])) {
			return;
		}

		try {
			$pdo = new PDO(
				'mysql:host=' . $this->config[$database]['dbHost'] . ';dbname=' . $this->config[$database]['dbName'],
				$this->config[$database]['dbUser'],
				$this->config[$database]['dbPass'],
				$this->config[$database]['dbOptions']
			);

			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			$this->connections[$database] = $pdo;

		} catch (PDOException $e) {
			throw new RunTimeException($e->getMessage(), (int) $e->errorInfo[0]);
		}
	}

	/**
	 * Disconnects from the given database.
	 *
	 * @param string $database Default:'default'
	 */
	public function disconnect(string $database = 'default')
	{
		if (isset($this->connections[$database])) {
			$this->connections[$database] = null;
		}
	}

	/**
	 * Gets last insert ID.
	 *
	 * @param  string|null $name     Default:null
	 * @param  string      $database Default:'database'
	 * @return string
	 * @throws RunTimeException(PDOException::getMessage, PDOException::errorInfo[0])
	 */
	public function getLastInsertId($name = null, string $database = 'default')
	{
		$this->connect($database);

		try {
			return $this->connections[$database]->lastInsertId($name);

		} catch (PDOException $e) {
			throw new RunTimeException($e->getMessage(), (int) $e->errorInfo[0]);
		}
	}

	/**
	 * Prepares the statement.
	 *
	 * @param  string          $statement
	 * @param  string          $database      Default:'default'
	 * @param  array           $driverOptions Default:array()
	 * @return PdoMySqlGateway $this
	 * @throws RunTimeException(PDOException::getMessage, PDOException::errorInfo[0])
	 */
	protected function prepare(string $statement, string $database = 'default', array $driverOptions = array())
	{
		$this->connect($database);

		try {
			$this->statement = $this->connections[$database]->prepare($statement, $driverOptions);

			return $this;

		} catch (PDOException $e) {
			throw new RunTimeException($e->getMessage(), (int) $e->errorInfo[0]);
		}
	}

	/**
	 * Binds value to the statement.
	 *
	 * @param  mixed           $parameter
	 * @param  mixed           $value
	 * @param  int|null        $dataType  Default: null
	 * @return PdoMySqlGateway $this
	 * @throws RunTimeException(PDOException::getMessage, PDOException::errorInfo[0])
	 */
	protected function bindValue($parameter, $value, $dataType = null)
	{
		try {
			if (null === $dataType) {
				if (is_string($value)) {
					$dataType = PDO::PARAM_STR;

				} elseif (is_int($value)) {
					$dataType = PDO::PARAM_INT;

				} elseif (is_null($value)) {
					$dataType = PDO::PARAM_NULL;
				}
			}

			$this->statement->bindValue($parameter, $value, $dataType);

			return $this;

		} catch (PDOException $e) {
			throw new RunTimeException($e->getMessage(), (int) $e->errorInfo[0]);
		}
	}

	/**
	 * Executes the statement.
	 *
	 * @param  array|null      $inputParameters Default: null
	 * @return PdoMySqlGateway $this
	 * @throws RunTimeException(PDOException::getMessage, PDOException::errorInfo[0])
	 */
	protected function execute(array $inputParameters = null)
	{
		try {
			$this->statement->execute($inputParameters);

			return $this;

		} catch (PDOException $e) {
			throw new RunTimeException($e->getMessage(), (int) $e->errorInfo[0]);
		}
	}

	/**
	 * Fetches all results from the statement.
	 *
	 * @param  int   $fetchStyle    Default: PDO::FETCH_ASSOC
	 * @param  mixed $fetchArgument Default: 0
	 * @param  array $ctorArgs      Default: array()
	 * @return array
	 * @throws RunTimeException(PDOException::getMessage, PDOException::errorInfo[0])
	 */
	protected function fetchAll(int $fetchStyle = PDO::FETCH_ASSOC, $fetchArgument = 0, array $ctorArgs = array())
	{
		try {
			switch ($fetchStyle) {
				case PDO::FETCH_COLUMN:
					return $this->statement->fetchAll($fetchStyle, $fetchArgument);
				case PDO::FETCH_FUNC:
					return $this->statement->fetchAll($fetchStyle, $fetchArgument);
				case PDO::FETCH_CLASS:
					return $this->statement->fetchAll($fetchStyle, $fetchArgument, $ctorArgs);
				default:
					return $this->statement->fetchAll($fetchStyle);
			}

		} catch (PDOException $e) {
			throw new RunTimeException($e->getMessage(), (int) $e->errorInfo[0]);
		}
	}

	//-----------------------------------------------------------------------------------//
	//--------------------------------|  CRUD METHODS  |---------------------------------//
	//-----------------------------------------------------------------------------------//

	/**
	 * Creates.
	 *
	 * @param  array           $params
	 * @return PdoMySqlGateway
	 */
	public function create(array $params)
	{
		$defaultParams = array(
			// The data array MUST be structured as follow: 'column_name' => $value
			'data'     => array(),
			'table'    => '',
			'database' => 'default',
		);

		$columns    = array();
		$values     = array();
		$bindParams = array();
		$params     = array_merge($defaultParams, $params);

		foreach ($params['data'] as $column => $value) {
			$columns[]    = $column;
			$values[]     = $value;
			$bindParams[] = '?';
		}

		$sql  = 'INSERT INTO ' . $params['table'];
		$sql .= ' (' . implode(', ', $columns) . ')';
		$sql .= ' VALUES (' . implode(', ', $bindParams) . ')';

		$this->prepare($sql, $params['database']);

		foreach ($values as $valueNumber => $value) {
			$this->bindValue($valueNumber + 1, $value);
		}

		return $this->execute();
	}

	/**
	 * Reads.
	 *
	 * @param  array $params
	 * @return array
	 *
	 * @todo Implement joins...
	 */
	public function read(array $params)
	{
		$defaultParams = array(
			'columns'  => array('*'),
			'table'    => '',
			'where'    => array(
				0 => array(
					'condition' => '',
					'column'    => 'id',
					'operator'  => '=',
					'value'     => '',
				),
			),
			'orderby'  => '',
			'order'    => 'ASC',
			'limit'    => 0,
			'database' => 'default',
		);

		$params = array_merge($defaultParams, $params);

		foreach ($params['where'] as $clauseNumber => $clause) {
			$params['where'][$clauseNumber] = array_merge($defaultParams['where'][$clauseNumber], $params['where'][$clauseNumber]);
		}

		$sql  = 'SELECT ' . implode(', ', $params['columns']);
		$sql .= ' FROM ' . $params['table'];

		if ('' !== $params['where'][0]['value']) {
			$sql .= ' WHERE ';

			foreach ($params['where'] as $clauseNumber => $clause) {
				$sql .= $clause['condition'] ? $clause['condition'] . ' ' : '';
			   	$sql .= $clause['column'] . ' ' . $clause['operator'] . ' ?';
			}
		}

		if ($params['orderby']) {
			$sql .= ' ORDER BY ' . $params['orderby'] . '  ' . $params['order'];
		}

		if ($params['limit']) {
			$sql .= ' LIMIT ' . $params['limit'];
		}

		$this->prepare($sql, $params['database']);

		if ('' !== $params['where'][0]['value']) {
			foreach ($params['where'] as $clauseNumber => $clause) {
				$this->bindValue($clauseNumber + 1, $clause['value']);
			}
		}

		$this->execute();

		return $this->fetchAll();
	}

	/**
	 * Updates.
	 *
	 * @param  array           $params
	 * @return PdoMySqlGateway
	 */
	public function update(array $params)
	{
		$defaultParams = array(
			// The data array MUST be structured as follow: 'column_name' => $value
			'data'     => array(),
			'table'    => '',
			'database' => 'default',
			'where'    => array(
				0 => array(
					'condition' => '',
					'column'    => 'id',
					'operator'  => '=',
					'value'     => '',
				),
			),
		);

		$bindValues = array();
		$params     = array_merge($defaultParams, $params);

		foreach ($params['where'] as $clauseNumber => $clause) {
			$params['where'][$clauseNumber] = array_merge($defaultParams['where'][$clauseNumber], $params['where'][$clauseNumber]);
		}

		$sql  = 'UPDATE ' . $params['table'];
		$sql .= ' SET ';

		foreach ($params['data'] as $column => $value) {
			$bindValues[] = $value;

			$sql .= $column . ' = ?';
			$sql .= count($bindValues) === count($params['data']) ? '' : ', ';
		}

		if ('' !== $params['where'][0]['value']) {
			$sql .= ' WHERE ';

			foreach ($params['where'] as $clauseNumber => $clause) {
				$bindValues[] = $clause['value'];

				$sql .= $clause['condition'] ? $clause['condition'] . ' ' : '';
			   	$sql .= $clause['column'] . ' ' . $clause['operator'] . ' ?';
			}
		}

		$this->prepare($sql, $params['database']);

		foreach ($bindValues as $valueNumber => $value) {
			$this->bindValue($valueNumber + 1, $value);
		}

		return $this->execute();
	}

	/**
	 * Deletes.
	 *
	 * @param  array           $params
	 * @return PdoMySqlGateway
	 */
	public function delete(array $params)
	{
		$defaultParams = array(
			'table'    => '',
			'database' => 'default',
			'where'    => array(
				0 => array(
					'condition' => '',
					'column'    => 'id',
					'operator'  => '=',
					'value'     => '',
				),
			),
		);

		$params = array_merge($defaultParams, $params);

		foreach ($params['where'] as $clauseNumber => $clause) {
			$params['where'][$clauseNumber] = array_merge($defaultParams['where'][$clauseNumber], $params['where'][$clauseNumber]);
		}

		$sql = 'DELETE FROM ' . $params['table'];

		if ('' !== $params['where'][0]['value']) {
			$sql .= ' WHERE ';

			foreach ($params['where'] as $clauseNumber => $clause) {
				$sql .= $clause['condition'] ? $clause['condition'] . ' ' : '';
			   	$sql .= $clause['column'] . ' ' . $clause['operator'] . ' ?';
			}
		}

		$this->prepare($sql, $params['database']);

		if ('' !== $params['where'][0]['value']) {
			foreach ($params['where'] as $clauseNumber => $clause) {
				$this->bindValue($clauseNumber + 1, $clause['value']);
			}
		}

		return $this->execute();
	}
}

