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

namespace Contao\Doctrine\ORM\Event;

use Contao\Doctrine\ORM\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

class DuplicateEntity extends Event
{
	/**
	 * @var EntityInterface
	 */
	protected $entity;

	/**
	 * @var bool
	 */
	protected $withoutKeys;

	/**
	 * @param EntityInterface $entity
	 * @param bool            $withoutKeys
	 */
	function __construct($entity, $withoutKeys)
	{
		$this->entity      = $entity;
		$this->withoutKeys = (bool) $withoutKeys;
	}

	/**
	 * @return EntityInterface
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * @return bool
	 */
	public function getWithoutKeys()
	{
		return $this->withoutKeys;
	}
}
