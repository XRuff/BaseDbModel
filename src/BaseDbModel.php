<?php

namespace XRuff\App\Model;

use Nette;
use Nette\Database\Context;
use Nette\Utils\Strings;

/**
 * Base repository class
 *
 * @property-read Nette\Database\Table\Selection $table
 */
abstract class BaseDbModel
{
    use Nette\Smart<;

	/** @var string */
	const TABLE_NAME_SEPARATOR = '_';

	/** @var \Nette\Database\Context */
	protected $db;

	/** @var string $tableName */
	protected $tableName;

	/** @var array event occurs before item is saved */
	public $onBeforeSave = [];

	/** @var $cache CacheStorage */
	public $cache;

	/**
	 * @param Nette\Database\Context $db
	 * @param string $tableName
	 *
	 * @return BaseDbModel
	 */
	public function __construct(Context $db, $tableName = null)
	{
		$this->db = $db;

		if (isset($tableName)) {
			if (!is_string($tableName)) {
				throw new Nette\InvalidArgumentException("Invalid table name, '" . gettype($tableName) . "'given!");
			}
			$this->tableName = $tableName;
		} else {
			$this->tableName = $this->formatTableNameFromClass();
		}
	}

	/**
	 * Formats db table name by class' upper characters, by default TableName => table_name
	 *
	 * @return string
	 */
	protected function formatTableNameFromClass()
	{
        $shorName = substr(strrchr(get_class($this), "\\"), 1);
        $name = substr($shorName, 0, strrpos($shorName, 'Repository'));
		return strtolower(Strings::replace($name, '#(?<!^)([A-Z])#', self::TABLE_NAME_SEPARATOR . '\\1'));
	}

	/**
	 * @return \Nette\Database\Table\Selection
	 */
	public function getTable()
	{
		return $this->db->table($this->tableName);
	}

	/**
	 * @return \Nette\Database\Table\Selection
	 */
	public function findAll()
	{
		return $this->table;
	}

	/**
	 * @param array $condition [columnName => value]
	 * @return Nette\Database\Table\Selection
	 */
	public function findBy(array $condition)
	{
		return $this->findAll()->where($condition);
	}

	/**
	 * @param int $id
	 * @return \Nette\Database\Table\IRow|FALSE
	 */
	public function get($id)
	{
		return $this->findAll()->get($id);
	}

	/**
	 * @param int $id
	 * @return Nette\Database\Table\IRow|FALSE
	 */
	public function find($id)
	{
		return $this->get($id);
	}

	/**
	 * @param array $condition [columnName => value]
	 * @return Nette\Database\Table\ActiveRow|FALSE
	 */
	public function getOneBy(array $condition)
	{
		return $this->findBy($condition)->limit(1)->fetch();
	}

	/**
	 * @param string $value
	 * @param string $key
	 * @return array
	 */
	public function getAllAsArray($value = 'title', $key = 'id') {
		return $this->findAll()->order($value)->fetchPairs($key, $value);
	}

	/**
	 * @param array $condition
	 * @param string $value
	 * @param string $key
	 * @return array
	 */
	public function getAllAsArrayBy(array $condition, $value = 'title', $key = 'id') {
		return $this->findBy($condition)->order($value)->fetchPairs($key, $value);
	}

	/**
	 * Returns all active items
	 * @param string $key Column name for active rows
	 * @return \Nette\Database\Table\Selection
	 */
	public function getAllActive($key = 'active') {
		return $this->findAll()->where($key, 1);
	}

	/**
	 * Inserts or updates row
	 *
	 * @param Nette\Utils\ArrayHash|array $values
	 * @param string $key
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function save($values, $key = 'id')
	{
		try {
			$this->onBeforeSave($values);

			if ($this->isPersistent((array) $values, $key)) {
				$this->get($values[$key])->update($values);
				return $this->get($values[$key]);
			} else {
				return $this->table->insert($values);
			}
		} catch (\Exception $e) {

			if (is_array($e->errorInfo) && $e->errorInfo[1] === 1062) {
				throw new DuplicateEntryException($e->getMessage(), $e->errorInfo[1], $e);
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Checks if item with same ID/key already exists in table
	 *
	 * @param array $values
	 * @param string $key Optional key
	 * @return bool
	 */
	protected function isPersistent(array $values, $key = 'id')
	{
		return (array_key_exists($key, $values) && $this->get($values[$key]));
	}

	public function setCacheStorage($cache) {
		$this->cache = $cache;
	}
}
