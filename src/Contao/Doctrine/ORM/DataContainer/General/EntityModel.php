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
use Contao\Doctrine\ORM\EntityHelper;
use DcGeneral\Data\AbstractModel;
use Doctrine\Common\Collections\Collection;
use InterfaceGeneralModel;
use Psr\Log\LoggerInterface;

class EntityModel extends AbstractModel
{
	/**
	 * @var Entity
	 */
	protected $entity;

	protected $reflectionClass;

	function __construct($entity)
	{
		$this->entity = $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
		$this->entity = $this->entity->duplicate(true);
	}

	/**
	 * @return Entity
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
	public function setID($id)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$entity->id($id);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProperty($propertyName)
	{
		$entity = $this->getEntity();

		if ($entity) {
			try {
				$value = $entity->$propertyName;

				if ($value instanceof Entity) {
					$value = $value->id();
				}
				else if ($value instanceof Collection) {
					$ids = array();
					foreach ($value as $item) {
						$ids[] = $item->id();
					}
					$value = $ids;
				}

				return $value;
			}
			catch (\InvalidArgumentException $e) {
				/** @var LoggerInterface $logger */
				$logger = $GLOBALS['container']['doctrine.orm.logger'];
				$logger->warning($e->getMessage());
			}
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setProperty($propertyName, $value)
	{
		$entity = $this->getEntity();

		if ($entity) {
			$reflectionClass = $this->getReflectionClass();

			$setter = 'set' . ucfirst($propertyName);
			if ($reflectionClass->hasMethod($setter)) {
				$setter = $reflectionClass->getMethod($setter);
				$reflectionParameters = $setter->getParameters();
				$reflectionParameter = $reflectionParameters[0];
				$reflectionParameterClass = $reflectionParameter->getClass();

				if ($value !== null && $reflectionParameterClass && $reflectionParameterClass->isSubclassOf('Contao\Doctrine\ORM\Entity')) {
					$value = EntityHelper::findByCombinedId($reflectionParameterClass, $value);
				}
				else if ($reflectionParameterClass && $reflectionParameterClass->getName() == 'DateTime' && !$value instanceof \DateTime) {
					$datetime = new \DateTime();
					$datetime->setTimestamp($value);
					$value = $datetime;
				}

				$setter->invoke($entity, $value);
			}
			else {
				$getter = 'get' . ucfirst($propertyName);
				if ($reflectionClass->hasMethod($getter)) {
					$getter = $reflectionClass->getMethod($getter);
					$currentValue = $getter->invoke($entity);
				}
				else if ($reflectionClass->hasProperty($propertyName)) {
					$reflectionProperty = $reflectionClass->getProperty($propertyName);
					$reflectionProperty->setAccessible(true);
					$currentValue = $reflectionProperty->getValue($entity);
				}
				else {
					throw new \RuntimeException('Could not find property ' . $propertyName . ' on entity ' . get_class($entity));
				}

				if ($currentValue instanceof Collection) {
					$adder = 'add' . ucfirst(rtrim($propertyName, 's'));
					$adder = $reflectionClass->getMethod($adder);
					$reflectionParameters = $adder->getParameters();
					$reflectionParameter = $reflectionParameters[0];
					$reflectionParameterClass = $reflectionParameter->getClass();

					$currentValue->clear();
					foreach (((array) $value) as $id) {
						$item = EntityHelper::findByCombinedId($reflectionParameterClass, $id);
						$adder->invoke($entity, $item);
					}
				}
				else {
					$entity->$propertyName = $value;
				}
			}
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
	public function setPropertiesAsArray($properties)
	{
		$entity = $this->getEntity();

		if ($entity) {
			foreach ($properties as $propertyName => $value) {
				$this->setProperty($propertyName, $value);
			}
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
		return $entity::TABLE_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->getEntity()->toArray());
	}
}
