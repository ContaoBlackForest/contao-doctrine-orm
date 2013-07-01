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

abstract class Entity implements \JsonSerializable
{
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
			return $this->$name;
		}
		else {
			throw new \InvalidArgumentException('The entity ' . get_class($this) . ' does not have a property ' . $name);
		}
	}

	public function __set($name, $value)
	{
		if ($this->__has($name)) {
			$this->$name = $value;
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
			if ($value instanceof \JsonSerializable) {
				$data[$key] = $value->jsonSerialize();
			}
			else {
				$data[$key] = $value;
			}
		}
		return $data;
	}

	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Specify data which should be serialized to JSON
	 *
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 *       which is a value of any type other than a resource.
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}
}
