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
	public function offsetExists($offset)
	{
		return $this->__has($offset);
	}

	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	public function offsetSet($offset, $value)
	{
		return $this->__set($offset, $value);
	}

	public function offsetUnset($offset)
	{
		return $this->__unset($offset);
	}

	function __has($name)
	{
		static $reflection;
		if (!$reflection) {
			$reflection = new \ReflectionClass($this);
		}
		return $reflection->hasProperty($name);
	}

	function __isset($name)
	{
		return $this->__has($name) && $this->$name !== null;
	}

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

	function __unset($name)
	{
		if ($this->__has($name)) {
			$this->$name = null;
		}
		else {
			throw new \InvalidArgumentException('The entity ' . get_class($this) . ' does not have a property ' . $name);
		}
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
    protected function callLoadCallbacks($field, $value)
    {
        if (isset($GLOBALS['TL_DCA'][static::TABLE_NAME]['fields'][$field]['load_callback'])) {
            $callbacks = (array) $GLOBALS['TL_DCA'][static::TABLE_NAME]['fields'][$field]['load_callback'];
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    $value = call_user_func($callback, $value, $this);
                }
                else {
                    $object = (in_array('getInstance', get_class_methods($callback[0]))) ? call_user_func($callback[0], 'getInstance') : new $callback[0];
                    $value = $object->$callback[1]($value, $this);
                }
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
    protected function callSaveCallbacks($field, $value)
    {
        if (isset($GLOBALS['TL_DCA'][static::TABLE_NAME]['fields'][$field]['save_callback'])) {
            $callbacks = (array) $GLOBALS['TL_DCA'][static::TABLE_NAME]['fields'][$field]['save_callback'];
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    $value = call_user_func($callback, $value, $this);
                }
                else {
                    $object = (in_array('getInstance', get_class_methods($callback[0]))) ? call_user_func($callback[0], 'getInstance') : new $callback[0];
                    $value = $object->$callback[1]($value, $this);
                }
            }
        }
        return $value;
    }
}
