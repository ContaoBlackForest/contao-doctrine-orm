<?php

/**
 * Doctrine ORM bridge
 * Copyright (C) 2013 Tristan Lins
 *
 * PHP version 5
 *
 * @copyright  bit3 UG 2013
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @package    doctrine-orm
 * @license    LGPL
 * @filesource
 */

namespace Contao\Doctrine\ORM;

use Contao\Doctrine\ORM\Event\DuplicateEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Proxy\Proxy;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class Entity implements \ArrayAccess
{
	const TABLE_NAME = null;

	const KEY = null;

	const KEY_SEPARATOR = ';';

	const REF_IGNORE = 'ignore';

	const REF_ID = 'id';

	const REF_INCLUDE = 'include';

	const REF_ARRAY = 'array';

	/**
	 * Get or set the ID of this entity.
	 *
	 * @param mixed $_
	 *
	 * @return string
	 */
	public function id()
	{
		$args = func_get_args();

		$fields = explode(',', static::KEY);
		if (count($args)) {
			if (count($args) == 1 && is_string($args[0])) {
				$args = explode(static::KEY_SEPARATOR, $args[0]);
			}
			if (count($args) == count($fields)) {
				foreach ($fields as $index => $field) {
					$this->__set($field, $args[$index]);
				}
			}
			else {
				throw new \InvalidArgumentException(
					'Arguments count of ' . count($args) . ' does not match id field count of ' . count($fields)
				);
			}
		}
		else {
			$id = array();
			foreach ($fields as $field) {
				$value = $this->__get($field);
				if ($value instanceof Entity) {
					$value = $value->id();
				}
				$id[] = $value;
			}
		}
		return implode(static::KEY_SEPARATOR, $id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		return $this->__has($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		return $this->__set($offset, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		return $this->__unset($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	function __has($name)
	{
		static $reflection;
		if (!$reflection) {
			$reflection = new \ReflectionClass($this);
		}
		return $reflection->hasProperty($name);
	}

	/**
	 * {@inheritdoc}
	 */
	function __isset($name)
	{
		return $this->__has($name) && $this->$name !== null;
	}

	/**
	 * {@inheritdoc}
	 */
	function __get($name)
	{
		if ($this->__has($name)) {
			$getter = 'get' . ucfirst($name);

			if (method_exists($this, $getter)) {
				return $this->$getter();
			}
		}

		throw new \InvalidArgumentException('The entity ' . get_class($this) . ' does not have a property ' . $name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function __set($name, $value)
	{
		if ($this->__has($name)) {
			$setter = 'set' . ucfirst($name);

			if (method_exists($this, $setter)) {
				$this->$setter($value);
				return;
			}
		}

		throw new \InvalidArgumentException('The entity ' . get_class($this) . ' does not have a property ' . $name);
	}

	/**
	 * {@inheritdoc}
	 */
	function __unset($name)
	{
		$this->__set($name, null);
	}

	/**
	 * Update properties of this entity from an array.
	 *
	 * @param array $array
	 * @return Entity
	 */
	public function fromArray(array $array)
	{
		foreach ($array as $name => $value) {
			$this->__set($name, $value);
		}
		return $this;
	}

	/**
	 * Return array with all properties of this entity.
	 *
	 * @param string $references
	 * @param array $recursionPath Internal use only!
	 *
	 * @return array
	 */
	public function toArray($references = self::REF_ID, array $recursionPath = array())
	{
		// bc fallback
		if ($references === true) {
			$references = static::REF_IGNORE;
		}
		else if ($references === false) {
			$references = static::REF_ID;
		}

		if ($this instanceof Proxy) {
			$this->__load();
		}

		$hash = spl_object_hash($this);
		if (in_array($hash, $recursionPath)) {
			return '*recursion*';
		}
		$recursionPath[] = $hash;

		$data = array();
		foreach ($this as $key => $value) {
			if ($value instanceof Entity) {
				switch ($references) {
					case static::REF_ID:
						$data[$key] = $value->id();
						break;
					case static::REF_ARRAY:
						$data[$key] = $value->toArray($references, $recursionPath);
						break;
					case static::REF_INCLUDE:
						$data[$key] = $value;
						break;
				}
			}
			else if ($value instanceof Collection) {
				if (!$references) {
					$data[$key] = array();
					foreach ($value as $item) {
						switch ($references) {
							case static::REF_ID:
								$data[$key][] = $value->id();
								break;
							case static::REF_ARRAY:
								$data[$key][] = $value->toArray($references, $recursionPath);
								break;
							case static::REF_INCLUDE:
								$data[$key][] = $value;
								break;
						}
					}
				}
			}
			else {
				$data[$key] = $value;
			}
		}
		return $data;
	}

    /**
     * Call load callbacks
     *
     * @param string $field
     * @param mixed $value
     *
     * @return mixed
     */
    protected function callGetterCallbacks($field, $value)
    {
        if (isset($GLOBALS['TL_DCA'][static::TABLE_NAME]['fields'][$field]['getter_callback'])) {
            $callbacks = (array) $GLOBALS['TL_DCA'][static::TABLE_NAME]['fields'][$field]['getter_callback'];
            foreach ($callbacks as $callback) {
				$value = call_user_func($callback, $value, $this);
            }
        }
        return $value;
    }

    /**
     * Call save callbacks
     *
     * @param string $field
     * @param mixed $value
     *
     * @return mixed
     */
    protected function callSetterCallbacks($field, $value)
    {
        if (isset($GLOBALS['TL_DCA'][static::TABLE_NAME]['fields'][$field]['setter_callback'])) {
            $callbacks = (array) $GLOBALS['TL_DCA'][static::TABLE_NAME]['fields'][$field]['setter_callback'];
            foreach ($callbacks as $callback) {
				$value = call_user_func($callback, $value, $this);
            }
        }
        return $value;
    }

	/**
	 * Duplicate (clone) an entity with or without it keys.
	 *
	 * @param bool $withoutKeys
	 *
	 * @return static
	 */
	function duplicate($withoutKeys = false)
	{
		if ($this instanceof Proxy) {
			$this->__load();
		}

		$keys = explode(',', static::KEY);

		$data = array();
		foreach ($this as $key => $value) {
			if (!$withoutKeys || !in_array($key, $keys)) {
				if ($value instanceof Entity) {
					$data[$key] = $value->toArray();
				}
				else {
					$data[$key] = $value;
				}
			}
		}

		$entity = new static();
		$entity->fromArray($data);

		/** @var EventDispatcher $eventDispatcher */
		$eventDispatcher = $GLOBALS['container']['event-dispatcher'];
		$eventDispatcher->dispatch(DuplicateEntity::EVENT_NAME, new DuplicateEntity($entity, $withoutKeys));

		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	function __clone()
	{
		/** @var EventDispatcher $eventDispatcher */
		$eventDispatcher = $GLOBALS['container']['event-dispatcher'];
		$eventDispatcher->dispatch(DuplicateEntity::EVENT_NAME, new DuplicateEntity($this, false));
	}
}
