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
}
