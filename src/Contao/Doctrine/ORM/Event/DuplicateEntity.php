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

use Contao\Doctrine\ORM\Entity;
use Symfony\Component\EventDispatcher\Event;

class DuplicateEntity extends Event
{
	const EVENT_NAME = 'contao-orm-entity-duplicate';

	/**
	 * @var Entity
	 */
	protected $entity;

	/**
	 * @var bool
	 */
	protected $withoutKeys;

	/**
	 * @param Entity $entity
	 * @param bool   $withoutKeys
	 */
	function __construct(Entity $entity, $withoutKeys)
	{
		$this->entity      = $entity;
		$this->withoutKeys = (bool) $withoutKeys;
	}

	/**
	 * @return Entity
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
