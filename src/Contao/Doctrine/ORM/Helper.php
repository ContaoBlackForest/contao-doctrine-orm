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

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class Helper
{
	/**
	 * Extend a query with a where constraint on the id of an entity.
	 *
	 * @param QueryBuilder     $queryBuilder
	 * @param mixed            $id
	 * @param EntityInterface  $entity
	 * @param bool             $standalone
	 * @param \ReflectionClass $entityClass
	 *
	 * @throws \RuntimeException
	 */
	static public function extendQueryWhereId(
		QueryBuilder $queryBuilder,
		EntityInterface $entity,
		$standalone = false,
		\ReflectionClass $entityClass = null
	) {
		/** @var EntityAccessor $entityAccessor */
		$entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

		if (!$entityClass) {
			$entityClass = new \ReflectionClass($entity);
		}

		if ($entityClass->isSubclassOf('Contao\Doctrine\ORM\EntityInterface')) {
			$keys = $entityClass
				->getMethod('entityPrimaryKeyNames')
				->invoke(null);
		}
		else {
			$keys = array('id');
		}

		foreach ($keys as $index => $key) {
			$value = $entityAccessor->getProperty($entity, $key);

			if ($value !== null) {
				$where = $queryBuilder
					->expr()
					->neq('e.' . $key, ':key' . $index);
				$queryBuilder->setParameter(':key' . $index, $value);
			}
			else {
				$where = $queryBuilder
					->expr()
					->isNotNull('e.' . $key);
			}

			if ($index > 0 || !$standalone) {
				$queryBuilder->andWhere($where);
			}
			else {
				$queryBuilder->where($where);
			}
		}
	}

	/**
	 * Auto-Generate an alias for an entity.
	 *
	 * @param string          $alias
	 * @param EntityInterface $entity
	 *
	 * @return string
	 * @throws \Exception
	 */
	static public function generateAlias($alias, $entity, $baseField = false)
	{
		// Generate alias if there is none
		if (!strlen($alias)) {
			/** @var EntityAccessor $entityAccessor */
			$entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

			if ($baseField) {
				$alias = standardize($entityAccessor->getProperty($entity, $baseField));
			}
			else if ($entity instanceof AliasableInterface) {
				$alias = standardize($entity->getAliasParentValue());
			}
			else if ($entityAccessor->hasProperty($entity, 'title')) {
				$alias = standardize($entityAccessor->getProperty($entity, 'title'));
			}
			else if ($entityAccessor->hasProperty($entity, 'name')) {
				$alias = standardize($entityAccessor->getProperty($entity, 'name'));
			}
			else {
				throw new \RuntimeException('Cannot generate alias, do not know which field should used!');
			}
		}

		$entityClass = new \ReflectionClass($entity);

		if ($entityClass->hasConstant('PRIMARY_KEY')) {
			$keys = explode(',', $entityClass->getConstant('PRIMARY_KEY'));
		}
		else {
			$keys = array('id');
		}

		$entityManager = EntityHelper::getEntityManager();
		$queryBuilder  = $entityManager->createQueryBuilder();
		$queryBuilder
			->select('COUNT(e.' . $keys[0] . ')')
			->from($entityClass->getName(), 'e')
			->where(
				$queryBuilder
					->expr()
					->eq('e.alias', ':alias')
			)
			->setParameter(':alias', $alias);
		static::extendQueryWhereId(
			$queryBuilder,
			$entity
		);
		$query          = $queryBuilder->getQuery();
		$duplicateCount = $query->getResult(Query::HYDRATE_SINGLE_SCALAR);

		// Check whether the news alias exists
		if ($duplicateCount) {
			throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $alias));
		}

		return $alias;
	}

	static public function createShortenEntityName($entity)
	{
		static $namespaceMap;
		if (!$namespaceMap) {
			if (array_key_exists('DOCTRINE_ENTITY_NAMESPACE_ALIAS', $GLOBALS)) {
				$namespaceMap = array_flip($GLOBALS['DOCTRINE_ENTITY_NAMESPACE_ALIAS']);
			}
			else {
				$namespaceMap = array();
			}
		}

		static $preg;
		if (!$preg) {
			$classes = array_keys($namespaceMap);
			$classes = array_map('preg_quote', $classes);
			$preg    = '~^(' . implode('|', $classes) . ')\\\\(.*)$~s';
		}

		$className = is_object($entity) ? get_class($entity) : (string) $entity;

		if (preg_match($preg, $className, $matches)) {
			$className = $namespaceMap[$matches[1]] . ':' . $matches[2];
		}

		return $className;
	}
}
