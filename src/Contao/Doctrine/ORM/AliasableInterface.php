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

interface AliasableInterface
{
	/**
	 * Return the alias parent field value, to generate the alias from.
	 *
	 * @return string
	 */
	public function getAliasParentValue();
}
