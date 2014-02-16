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

use Contao\Doctrine\ORM\EntityAccessor;
use Contao\Doctrine\ORM\EntityInterface;
use DcGeneral\Data\AbstractModel;
use DcGeneral\Data\PropertyValueBagInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

class EntityModel extends AbstractModel
{
	/**
	 * @var EntityInterface
	 */
	protected $entity;

	/**
	 * @var \ReflectionClass
	 */
	protected $reflectionClass;

	/**
	 * @var EntityAccessor
	 */
	protected $entityAccessor;

	/**
	 * @param object $entity
	 */
	function __construct($entity)
	{
		$this->entity = $entity;
	}

	/**
	 * @return EntityInterface
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * @return \ReflectionClass
	 */
	protected function getReflectionClass()
	{
		if (!$this->reflectionClass) {
			$this->reflectionClass = new \ReflectionClass($this->entity);
		}
		return $this->reflectionClass;
	}

	/**
	 * @param \Contao\Doctrine\ORM\EntityAccessor $entityAccessor
	 */
	public function setEntityAccessor($entityAccessor)
	{
		$this->entityAccessor = $entityAccessor;
		return $this;
	}

	/**
	 * @return \Contao\Doctrine\ORM\EntityAccessor
	 */
	public function getEntityAccessor()
	{
		if (!$this->entityAccessor) {
			$this->entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];
		}
		return $this->entityAccessor;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getID()
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entityAccessor = $this->getEntityAccessor();
			return $entityAccessor->getPrimaryKey($entity);
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setID($id)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entityAccessor = $this->getEntityAccessor();
			$entityAccessor->setPrimaryKey($entity, $id);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProperty($propertyName)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entityAccessor = $this->getEntityAccessor();
			return $entityAccessor->getProperty($entity, $propertyName);
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setProperty($propertyName, $propertyValue)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entityAccessor = $this->getEntityAccessor();
			$entityAccessor->setProperty($entity, $propertyName, $propertyValue);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPropertiesAsArray()
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entityAccessor = $this->getEntityAccessor();
			return $entityAccessor->getProperties($entity);
		}

		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPropertiesAsArray($properties)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entityAccessor = $this->getEntityAccessor();
			$entityAccessor->setProperties($entity, $properties);
		}

		return $this;
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
		return $entity::TABLE_NAME;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws DcGeneralInvalidArgumentException When a property in the value bag has been marked as invalid.
	 */
	public function readFromPropertyValueBag(PropertyValueBagInterface $valueBag)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entityAccessor = $this->getEntityAccessor();
			$properties     = $entityAccessor->getProperties($entity);

			foreach ($properties as $name => $value) {
				if (!$valueBag->hasPropertyValue($name)) {
					continue;
				}

				if ($valueBag->isPropertyValueInvalid($name)) {
					throw new DcGeneralInvalidArgumentException('The value for property ' . $name . ' is invalid.');
				}

				$entityAccessor->setProperty(
					$entity,
					$name,
					$valueBag->getPropertyValue($value)
				);
			}
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function writeToPropertyValueBag(PropertyValueBagInterface $valueBag)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entityAccessor = $this->getEntityAccessor();
			$properties     = $entityAccessor->getProperties($entity);

			foreach ($properties as $name => $value) {
				if (!$valueBag->hasPropertyValue($name)) {
					continue;
				}

				$valueBag->setPropertyValue($name, $value);
			}
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entityAccessor = $this->getEntityAccessor();
			$properties     = $entityAccessor->getProperties($entity);
			return new \ArrayIterator($properties);
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entityClass    = $this->getReflectionClass();
			$entityAccessor = $this->getEntityAccessor();

			// get all properties
			$properties = $entityAccessor->getProperties($entity);

			// create a new entity
			$entity = $entityClass->newInstance();

			// set all properties
			$entityAccessor->setProperties($entity, $properties);

			$this->entity = $entity;
		}
	}
}
