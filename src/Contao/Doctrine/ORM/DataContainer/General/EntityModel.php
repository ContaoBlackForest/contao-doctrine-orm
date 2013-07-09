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

namespace Contao\Doctrine\ORM\DataContainer\General;

use Contao\Doctrine\ORM\Entity;
use InterfaceGeneralModel;

class EntityModel extends \AbstractGeneralModel
{
	protected $entity;

	function __construct($entity)
	{
		$this->entity = $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
		$class = new \ReflectionClass($this->entity);
		$this->entity = $class->newInstance();
	}

	/**
	 * @return Entity
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getID()
	{
		$entity = $this->getEntity();

		if ($entity) {
			return $entity->id();
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setID($mixID)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entity->id($mixID);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProperty($strPropertyName)
	{
		$entity = $this->getEntity();

		if ($entity) {
			return $entity->$strPropertyName;
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setProperty($strPropertyName, $varValue)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entity->$strPropertyName = $varValue;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPropertiesAsArray()
	{
		$entity = $this->getEntity();

		if ($entity) {
			return $entity->toArray();
		}

		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPropertiesAsArray($arrProperties)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entity->fromArray($arrProperties);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasProperties()
	{
		return (bool) $this->getEntity();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProviderName()
	{
		$entity = $this->getEntity();
		$entity::TABLE_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->getEntity()->toArray());
	}
}
