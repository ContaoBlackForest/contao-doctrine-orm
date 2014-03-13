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

use Contao\Doctrine\ORM\Exception\UnknownPropertyException;
use Doctrine\ORM\Proxy\Proxy;

class EntityAccessor
{
	/**
	 * Get the primary key as array of properties from entity.
	 *
	 * @param mixed|EntityInterface $entity
	 *
	 * @return array|mixed[]
	 */
	public function getPrimaryKeyValues($entity)
	{
		$class = new \ReflectionClass($entity);

		if ($class->isSubclassOf('Contao\Doctrine\ORM\EntityInterface')) {
			$keyNames = $entity->entityPrimaryKeyNames();
		}
		else {
			$keyNames = array('id');
		}

		$self = $this;

		$keyValues = $this->getRawProperties($entity, $keyNames);
		$keyValues = array_map(
			function ($keyValue) use ($self) {
				if (is_object($keyValue)) {
					$keyValue = $self->getPrimaryKeyValues($keyValue);
				}
				return $keyValue;
			},
			$keyValues
		);

		return $keyValues;
	}

	/**
	 * Get the primary key from entity.
	 *
	 * @param mixed|EntityInterface $entity
	 *
	 * @return string
	 */
	public function getPrimaryKey($entity)
	{
		$class = new \ReflectionClass($entity);

		if ($class->isSubclassOf('Contao\Doctrine\ORM\EntityInterface')) {
			$keyNames = $entity->entityPrimaryKeyNames();
		}
		else {
			$keyNames = array('id');
		}

		$self = $this;

		$keyValues = $this->getRawProperties($entity, $keyNames);
		$keyValues = array_map(
			function ($keyValue) use ($self) {
				if (is_object($keyValue)) {
					$keyValue = $self->getPrimaryKey($keyValue);
				}
				return $keyValue;
			},
			$keyValues
		);

		return implode('|', $keyValues);
	}

	/**
	 * @param mixed|EntityInterface $entity
	 * @param mixed  $id
	 * @param mixed  $_
	 */
	public function setPrimaryKey($entity, $id)
	{
		$class = new \ReflectionClass($entity);

		if ($class->isSubclassOf('Contao\Doctrine\ORM\EntityInterface')) {
			$keyNames = $entity->entityPrimaryKeyNames();
		}
		else {
			$keyNames = array('id');
		}

		$keyCount = count($keyNames);

		$keyValues = (array) $id;

		if ($keyCount != count($keyValues)) {
			throw new \RuntimeException(
				sprintf(
					'The entity %s has %d primary key values [%s]',
					get_class($entity),
					$keyCount,
					implode(', ', $keyNames)
				)
			);
		}

		$keys = array_combine(
			$keyNames,
			$keyValues
		);

		$this->setRawProperties($entity, $keys);

		return $this;
	}

	/**
	 * Determine if the entity contains an accessible object property.
	 *
	 * @param object $entity
	 * @param string $propertyName
	 *
	 * @return bool
	 */
	public function hasRawProperty($entity, $propertyName)
	{
		$class = new \ReflectionClass($entity);

		return $class->hasProperty($propertyName);
	}

	/**
	 * Get a property from an entity by accessible object property.
	 *
	 * @param object $entity
	 * @param string $propertyName
	 *
	 * @return mixed
	 * @throws Exception\UnknownPropertyException
	 */
	public function getRawProperty($entity, $propertyName)
	{
		$class = new \ReflectionClass($entity);

		if ($class->hasProperty($propertyName)) {
			$propertyName = $class->getProperty($propertyName);
			$propertyName->setAccessible(true);
			return $propertyName->getValue($entity);
		}

		throw new UnknownPropertyException($entity, $propertyName);
	}

	/**
	 * Set a property of an entity by accessible object property.
	 *
	 * @param      $entity
	 * @param      $propertyName
	 * @param      $propertyValue
	 */
	public function setRawProperty($entity, $propertyName, $propertyValue)
	{
		$class = new \ReflectionClass($entity);

		if ($class->hasProperty($propertyName)) {
			$propertyName = $class->getProperty($propertyName);
			$propertyName->setAccessible(true);
			$propertyName->setValue($entity, $propertyValue);
			return $this;
		}

		throw new UnknownPropertyException($entity, $propertyName);
	}

	/**
	 * Get all properties from an entity by getters or accessible object properties.
	 *
	 * @param object $entity
	 *
	 * @return array
	 */
	public function getRawProperties($entity, array $propertyNames = array())
	{
		$propertyValues = array();

		$class = new \ReflectionClass($entity);

		if ($entity instanceof Proxy) {
			$class = $class->getParentClass();
		}

		// collect all properties
		if (empty($propertyNames)) {
			$properties = $class->getProperties(
				\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PUBLIC
			);
			foreach ($properties as $property) {
				$property->setAccessible(true);

				$propertyName  = $property->getName();
				$propertyValue = $property->getValue($entity);

				$propertyValues[$propertyName] = $propertyValue;
			}
		}
		// collect selected properties
		else {
			foreach ($propertyNames as $propertyName) {
				if (!$class->hasProperty($propertyName)) {
					throw new UnknownPropertyException($entity, $propertyName);
				}

				$property = $class->getProperty($propertyName);
				$property->setAccessible(true);
				$propertyValue = $property->getValue($entity);

				$propertyValues[$propertyName] = $propertyValue;
			}
		}

		return $propertyValues;
	}

	/**
	 * Set all properties of an entity using setters or accessible object properties.
	 *
	 * @param object $entity
	 * @param array  $properties
	 *
	 * @throws Exception\UnknownPropertyException
	 */
	public function setRawProperties($entity, $properties)
	{
		$class = new \ReflectionClass($entity);

		foreach ($properties as $propertyName => $propertyValue) {
			if ($class->hasProperty($propertyName)) {
				$property = $class->getProperty($propertyName);
				$property->setAccessible(true);
				$property->setValue($entity, $propertyValue);
				return $this;
			}

			throw new UnknownPropertyException($entity, $propertyName);
		}
	}

	/**
	 * Determine if the entity contains an getter or accessible object property.
	 *
	 * @param object $entity
	 * @param string $propertyName
	 *
	 * @return bool
	 */
	public function hasProperty($entity, $propertyName)
	{
		$class = new \ReflectionClass($entity);

		$getterName = explode('_', $propertyName);
		$getterName = array_map('ucfirst', $getterName);
		$getterName = implode('', $getterName);
		$getterName = 'get' . $getterName;

		return $class->hasMethod($getterName) || $class->hasProperty($propertyName);
	}

	/**
	 * Get a property from an entity by getter or accessible object property.
	 *
	 * @param object $entity
	 * @param string $propertyName
	 *
	 * @return mixed
	 * @throws Exception\UnknownPropertyException
	 */
	public function getProperty($entity, $propertyName)
	{
		$class = new \ReflectionClass($entity);

		$getterName = explode('_', $propertyName);
		$getterName = array_map('ucfirst', $getterName);
		$getterName = implode('', $getterName);
		$getterName = 'get' . $getterName;

		if ($class->hasMethod($getterName)) {
			$getterMethod = $class->getMethod($getterName);
			$getterMethod->setAccessible(true);
			return $getterMethod->invoke($entity);
		}

		if ($class->hasProperty($propertyName)) {
			$property = $class->getProperty($propertyName);
			$property->setAccessible(true);
			return $property->getValue($entity);
		}

		throw new UnknownPropertyException($entity, $propertyName);
	}

	/**
	 * Set a property on an entity by setter or accessible object property.
	 *
	 * @param object $entity
	 * @param string $propertyName
	 * @param mixed  $propertyValue
	 *
	 * @throws Exception\UnknownPropertyException
	 */
	public function setProperty($entity, $propertyName, $propertyValue)
	{
		$class = new \ReflectionClass($entity);

		$setterName = explode('_', $propertyName);
		$setterName = array_map('ucfirst', $setterName);
		$setterName = implode('', $setterName);
		$setterName = 'set' . $setterName;

		if ($class->hasMethod($setterName)) {
			$setterMethod = $class->getMethod($setterName);

			if (
				$setterMethod->getNumberOfParameters() > 0 &&
				$setterMethod->getNumberOfRequiredParameters() <= 1
			) {
				$parameters = $setterMethod->getParameters();
				$firstParameter = $parameters[0];
				$typeClass = $firstParameter->getClass();

				$propertyValue = $this->guessValue($typeClass, $propertyValue);

				$setterMethod->setAccessible(true);
				$setterMethod->invoke($entity, $propertyValue);
				return $this;
			}
		}

		if ($class->hasProperty($propertyName)) {
			$property = $class->getProperty($propertyName);
			$property->setAccessible(true);
			$property->setValue($entity, $propertyValue);
			return $this;
		}

		throw new UnknownPropertyException($entity, $propertyName);
	}

	/**
	 * Get all properties from an entity by getters or accessible object properties.
	 *
	 * @param object $entity
	 *
	 * @return array
	 */
	public function getProperties($entity, array $propertyNames = array())
	{
		$propertyValues = array();

		$class = new \ReflectionClass($entity);

		if ($entity instanceof Proxy) {
			$class = $class->getParentClass();
		}

		// collect all properties
		if (empty($propertyNames)) {
			// collect getters
			$methods = $class->getMethods(
				\ReflectionMethod::IS_PRIVATE | \ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC
			);
			foreach ($methods as $method) {
				if (preg_match('~^get([A-Z])$~', $method->getName(), $matches)) {
					$method->setAccessible(true);

					$propertyName  = lcfirst($matches[1]);
					$propertyValue = $method->invoke($entity);

					$propertyValues[$propertyName] = $propertyValue;
				}
			}

			// collect properties
			$properties = $class->getProperties(
				\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PUBLIC
			);
			foreach ($properties as $property) {
				// skip if the value is already provided by a getter
				if (array_key_exists($property->getName(), $propertyValues)) {
					continue;
				}

				$property->setAccessible(true);

				$propertyName  = $property->getName();
				$propertyValue = $property->getValue($entity);

				$propertyValues[$propertyName] = $propertyValue;
			}
		}

		// collect selected properties
		else {
			foreach ($propertyNames as $propertyName) {
				$getterName = explode('_', $propertyName);
				$getterName = array_map('ucfirst', $getterName);
				$getterName = implode('', $getterName);
				$getterName = 'get' . $getterName;

				if ($class->hasMethod($getterName)) {
					$getterMethod = $class->getMethod($getterName);
					$getterMethod->setAccessible(true);
					$propertyValue = $getterMethod->invoke($entity);

					$propertyValues[$propertyName] = $propertyValue;
					continue;
				}

				if ($class->hasProperty($propertyName)) {
					$property = $class->getProperty($propertyName);
					$property->setAccessible(true);
					$propertyValue = $property->getValue($entity);

					$propertyValues[$propertyName] = $propertyValue;
					continue;
				}

				throw new UnknownPropertyException($entity, $propertyName);
			}
		}

		return $propertyValues;
	}

	/**
	 * Set all properties of an entity using setters or accessible object properties.
	 *
	 * @param object $entity
	 * @param array  $properties
	 *
	 * @throws Exception\UnknownPropertyException
	 */
	public function setProperties($entity, $properties)
	{
		$class = new \ReflectionClass($entity);

		foreach ($properties as $propertyName => $propertyValue) {
			$setterName = explode('_', $propertyName);
			$setterName = array_map('ucfirst', $setterName);
			$setterName = implode('', $setterName);
			$setterName = 'set' . $setterName;

			if ($class->hasMethod($setterName)) {
				$setterMethod = $class->getMethod($setterName);

				if (
					$setterMethod->getNumberOfParameters() > 0 &&
					$setterMethod->getNumberOfRequiredParameters() <= 1
				) {
					$parameters = $setterMethod->getParameters();
					$firstParameter = $parameters[0];
					$typeClass = $firstParameter->getClass();

					$propertyValue = $this->guessValue($typeClass, $propertyValue);

					$setterMethod->setAccessible(true);
					$setterMethod->invoke($entity, $propertyValue);
					continue;
				}
			}

			if ($class->hasProperty($propertyName)) {
				$property = $class->getProperty($propertyName);
				$property->setAccessible(true);
				$property->setValue($entity, $propertyValue);
				continue;
			}

			throw new UnknownPropertyException($entity, $propertyName);
		}
	}

	/**
	 * Get a public property from an entity by getter.
	 *
	 * @param object $entity
	 * @param string $propertyName
	 *
	 * @return mixed
	 * @throws Exception\UnknownPropertyException
	 */
	public function getPublicProperty($entity, $propertyName)
	{
		$class = new \ReflectionClass($entity);

		$getterName = explode('_', $propertyName);
		$getterName = array_map('ucfirst', $getterName);
		$getterName = implode('', $getterName);
		$getterName = 'get' . $getterName;

		if ($class->hasMethod($getterName)) {
			$getterMethod = $class->getMethod($getterName);

			if ($getterMethod->isPublic()) {
				return $getterMethod->invoke($entity);
			}
		}

		throw new UnknownPropertyException($entity, $propertyName);
	}

	/**
	 * Get all public properties from an entity by getters.
	 *
	 * @param object $entity
	 *
	 * @return array
	 */
	public function getPublicProperties($entity, $includePrimaryKey = false, array $propertyNames = array())
	{
		$propertyValues = array();

		$class = new \ReflectionClass($entity);

		if ($entity instanceof Proxy) {
			$class = $class->getParentClass();
		}

		// collect all properties
		if (empty($propertyNames)) {
			// collect getters
			$methods = $class->getMethods(
				\ReflectionMethod::IS_PUBLIC
			);
			foreach ($methods as $method) {
				if (preg_match('~^get([A-Z])$~', $method->getName(), $matches)) {
					$propertyName  = lcfirst($matches[1]);
					$propertyValue = $method->invoke($entity);

					$propertyValues[$propertyName] = $propertyValue;
				}
			}
		}

		// collect selected properties
		else {
			foreach ($propertyNames as $propertyName) {
				$getterName = explode('_', $propertyName);
				$getterName = array_map('ucfirst', $getterName);
				$getterName = implode('', $getterName);
				$getterName = 'get' . $getterName;

				if ($class->hasMethod($getterName)) {
					$getterMethod = $class->getMethod($getterName);

					if ($getterMethod->isPublic()) {
						$propertyValue = $getterMethod->invoke($entity);

						$propertyValues[$propertyName] = $propertyValue;
						continue;
					}
				}

				throw new UnknownPropertyException($entity, $propertyName);
			}
		}

		if ($includePrimaryKey) {
			if ($class->isSubclassOf('Contao\Doctrine\ORM\EntityInterface')) {
				$keyNames = $entity->entityPrimaryKeyNames();
			}
			else {
				$keyNames = array('id');
			}

			$self = $this;

			$keyValues = $this->getRawProperties($entity, $keyNames);
			$keyValues = array_map(
				function ($keyValue) use ($self) {
					if (is_object($keyValue)) {
						$keyValue = $self->getPrimaryKey($self);
					}
					return $keyValue;
				},
				$keyValues
			);

			foreach ($keyValues as $keyName => $keyValue) {
				$propertyValues[$keyName] = $keyValue;
			}
		}

		return $propertyValues;
	}

	public function guessValue(\ReflectionClass $targetType = null, $currentValue)
	{
		if (
			is_object($currentValue) ||
			!$targetType ||
			!$targetType->isSubclassOf('Contao\Doctrine\ORM\EntityInterface')
		) {
			return $currentValue;
		}

		$className = $targetType->getName();

		$repository = EntityHelper::getRepository($className);
		$primaryKey = EntityHelper::parseCombinedId($className, $currentValue);

		return $repository->find($primaryKey);
	}
}
