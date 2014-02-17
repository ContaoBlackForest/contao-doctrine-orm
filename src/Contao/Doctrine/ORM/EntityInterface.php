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

use Contao\Doctrine\ORM\Event\DuplicateEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Proxy\Proxy;
use Symfony\Component\EventDispatcher\EventDispatcher;

interface EntityInterface
{
	const REF_IGNORE = 'ignore';

	const REF_ID = 'id';

	const REF_INCLUDE = 'include';

	const REF_ARRAY = 'array';

	/**
	 * Return the name of the table the entity is stored in.
	 *
	 * @return string
	 */
	static public function entityTableName();

	/**
	 * Return a list of property names, that used as primary key.
	 *
	 * @return array
	 */
	static public function entityPrimaryKeyNames();
}
