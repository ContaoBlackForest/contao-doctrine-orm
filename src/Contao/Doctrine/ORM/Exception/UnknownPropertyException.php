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

namespace Contao\Doctrine\ORM\Exception;

class UnknownPropertyException extends \RuntimeException
{
	protected $entity;

	protected $propertyName;

	public function __construct($entity, $propertyName)
	{
		parent::__construct(
			sprintf(
				'Unknown property %s of class %s',
				$propertyName,
				get_class($entity)
			)
		);
		$this->entity       = $entity;
		$this->propertyName = $propertyName;
	}

	/**
	 * @return string
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * @return string
	 */
	public function getPropertyName()
	{
		return $this->propertyName;
	}
}
