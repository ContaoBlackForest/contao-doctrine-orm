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

use Contao\Doctrine\ORM\AliasableInterface;
use Contao\Doctrine\ORM\Entity;
use Contao\Doctrine\ORM\EntityHelper;
use DcGeneral\DC_General;
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
	 * @param bool             $singleton
	 * @param \ReflectionClass $entityClass
	 *
	 * @throws \RuntimeException
	 */
	static public function extendQueryWhereId(
		QueryBuilder $queryBuilder,
		$id,
		Entity $entity,
		$singleton = false,
		\ReflectionClass $entityClass = null
	) {
		if (!$entityClass) {
			$entityClass = new \ReflectionClass($entity);
		}

		$keys     = explode(',', $entityClass->getConstant('KEY'));
		$idValues = is_array($id) ? $id : explode($entityClass->getConstant('KEY_SEPARATOR'), $id);

		if (count($keys) != count($idValues)) {
			throw new \RuntimeException(
				sprintf(
					'Key count of %d does not match id values count %d',
					count($keys),
					count($idValues)
				)
			);
		}

		foreach ($keys as $index => $key) {
			$where = $queryBuilder
				->expr()
				->neq('e.' . $key, ':key' . $index);

			if ($index > 0 || !$singleton) {
				$queryBuilder->andWhere($where);
			}
			else {
				$queryBuilder->where($where);
			}

			$queryBuilder->setParameter(':key' . $index, $idValues[$index]);
		}
	}

	/**
	 * Auto-Generate an alias for an entity.
	 *
	 * @param string      $alias
	 * @param \DC_General $dc
	 *
	 * @return string
	 * @throws Exception
	 */
	static public function generateAlias($alias, DC_General $dc)
	{
		/** @var Entity $entity */
		$entity    = $dc
			->getEnvironment()
			->getCurrentModel()
			->getEntity();

		return \Contao\Doctrine\ORM\Helper::generateAlias($alias, $entity);
	}
}
