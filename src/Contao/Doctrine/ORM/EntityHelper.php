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

use Doctrine\ORM\EntityManager;

class EntityHelper
{
	/**
	 * @return EntityManager
	 */
	static public function getEntityManager()
	{
		return $GLOBALS['container']['doctrine.orm.entityManager'];
	}

	/**
	 * @param string $className
	 *
	 * @return \Doctrine\ORM\EntityRepository
	 */
	static public function getRepository($className)
	{
		$entityManager = static::getEntityManager();
		return $entityManager->getRepository($className);
	}

	/**
	 * Search an entity by an combined id, fetched by Entity::id()
	 *
	 * @param \ReflectionClass|string $class
	 * @param string                  $combinedId
	 *
	 * @return Entity|null
	 */
	static public function parseCombinedId($class, $combinedId)
	{
		if (($pos = strpos($class, ':')) !== false) {
			$alias = substr($class, 0, $pos);
			if (isset($GLOBALS['DOCTRINE_ENTITY_NAMESPACE_ALIAS'][$alias])) {
				$namespace = $GLOBALS['DOCTRINE_ENTITY_NAMESPACE_ALIAS'][$alias];
				$class     = $namespace . '\\' . substr($class, $pos + 1);
			}
		}

		if (!$class instanceof \ReflectionClass) {
			$class = new \ReflectionClass($class);
		}

		$keySeparator   = $class->getConstant('KEY_SEPARATOR');
		$keyDeclaration = $class->getConstant('KEY');
		$keys           = explode(',', $keyDeclaration);
		$ids            = explode($keySeparator, $combinedId);
		$criteria       = array_combine($keys, $ids);

		return $criteria;
	}

	/**
	 * Search an entity by an combined id, fetched by Entity::id()
	 *
	 * @param \ReflectionClass|string $class
	 * @param string                  $combinedId
	 *
	 * @return Entity|null
	 */
	static public function findByCombinedId($class, $combinedId)
	{
		$criteria = static::parseCombinedId($class, $combinedId);

		$repository = static::getRepository($class->getName());
		$entity     = $repository->findOneBy($criteria);

		return $entity;
	}
}
