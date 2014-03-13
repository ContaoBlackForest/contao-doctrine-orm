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

class OptionsSaveResolver
{
	static protected $instance;

	static public function getInstance()
	{
		if (static::$instance === null) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	static protected $entityClassNames;

	static public function create($entityClassName)
	{
		do {
			$methodName = uniqid('func_');
		}
		while (isset(static::$entityClassNames[$methodName]));

		static::$entityClassNames[$methodName] = $entityClassName;

		return array(
			'Contao\Doctrine\ORM\OptionsSaveResolver',
			$methodName
		);
	}

	/**
	 * @param string $methodName
	 * @param array  $args
	 */
	public function __call($methodName, array $args)
	{
		if (isset(static::$entityClassNames[$methodName])) {
			$className  = static::$entityClassNames[$methodName];
			$repository = EntityHelper::getRepository($className);
			$entities   = array();
			$ids        = $args[0];

			foreach ($ids as $id) {
				$id         = EntityHelper::parseCombinedId($className, $id);
				$entities[] = $repository->find($id);
			}

			return $entities;
		}

		throw new \RuntimeException('No save options resolver class found for ' . $methodName);
	}
}
