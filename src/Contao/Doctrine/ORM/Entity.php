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

use Doctrine\ORM\Proxy\Proxy;

abstract class Entity implements \ArrayAccess
{
	const KEY_SEPARATOR = '-';

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
				$id[] = $this->__get($field);
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
			return $this->$getter();
		}
		else {
			throw new \InvalidArgumentException('The entity ' . get_class($this) . ' does not have a property ' . $name);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function __set($name, $value)
	{
		if ($this->__has($name)) {
			$setter = 'set' . ucfirst($name);
			$this->$setter($value);
		}
		else {
			throw new \InvalidArgumentException('The entity ' . get_class($this) . ' does not have a property ' . $name);
		}
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
	 * @return array
	 */
	public function toArray()
	{
		if ($this instanceof Proxy) {
			$this->__load();
		}

		$data = array();
		foreach ($this as $key => $value) {
			if ($value instanceof Entity) {
				$data[$key] = $value->toArray();
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
}
