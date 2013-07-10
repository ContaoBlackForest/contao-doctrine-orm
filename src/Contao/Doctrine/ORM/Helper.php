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

use Contao\Doctrine\ORM\Entity;
use Contao\Doctrine\ORM\EntityHelper;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class Helper
{
	/**
	 * Extend a query with a where constraint on the id of an entity.
	 *
	 * @param QueryBuilder     $queryBuilder
	 * @param mixed            $id
	 * @param Entity           $entity
	 * @param bool             $standalone
	 * @param \ReflectionClass $entityClass
	 *
	 * @throws \RuntimeException
	 */
	static public function extendQueryWhereId(
		QueryBuilder $queryBuilder,
		Entity $entity,
		$standalone = false,
		\ReflectionClass $entityClass = null
	) {
		if (!$entityClass) {
			$entityClass = new \ReflectionClass($entity);
		}

		$keys = explode(',', $entityClass->getConstant('KEY'));

		foreach ($keys as $index => $key) {
			$value = $entity->__get($key);

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
	 * @param string $alias
	 * @param Entity $entity
	 *
	 * @return string
	 * @throws Exception
	 */
	static public function generateAlias($alias, Entity $entity)
	{
		// Generate alias if there is none
		if (!strlen($alias)) {
			if ($entity->__has('title')) {
				$alias = standardize($entity->getTitle());
			}
			else if ($entity->__has('name')) {
				$alias = standardize($entity->getName());
			}
			else {
				return '';
			}
		}

		$entityClass = new \ReflectionClass($entity);
		$keys        = explode(',', $entityClass->getConstant('KEY'));

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
