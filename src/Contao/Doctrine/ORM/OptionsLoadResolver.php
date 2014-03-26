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

/**
 *
 */
class OptionsLoadResolver
{
	static protected $instance;

	static public function getInstance()
	{
		if (static::$instance === null) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	static public function create()
	{
		return array(
			'Contao\Doctrine\ORM\OptionsLoadResolver',
			'load'
		);
	}

	/**
	 * @param string $methodName
	 * @param array  $args
	 */
	public function load($entities)
	{
		$entityAccessor = EntityHelper::getEntityAccessor();
		$ids            = array();

		if (is_array($entities) || $entities instanceof \Traversable) {
			foreach ($entities as $entity) {
				$ids[] = $entityAccessor->getPrimaryKey($entity);
			}
		}

		return $ids;
	}
}
