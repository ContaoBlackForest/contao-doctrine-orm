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

use Contao\Doctrine\ORM\EntityAccessor;
use Contao\Doctrine\ORM\EntityInterface;
use DcGeneral\Data\DefaultModel;
use ORM\Entity\Version;

class VersionModel extends EntityModel
{
	protected $active;

	function __construct(Version $versionEntity, $active)
	{
		parent::__construct($versionEntity);
		$this->active = $active;
	}

	/**
	 * @return EntityInterface|Version
	 */
	public function getEntity()
	{
		return parent::getEntity();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProperty($strPropertyName)
	{
		switch ($strPropertyName) {
			case 'version':
				return $this->getVersion();

			case 'active':
				return $this->isCurrent();

			case 'tstamp':
				return $this->getDateTime();

			case 'username':
				return $this->getAuthorName();
		}

		return parent::getProperty($strPropertyName);
	}

	public function getVersion()
	{
		$entity = $this->getEntity();
		return $entity->getId();
	}

	public function getDateTime()
	{
		$entity = $this->getEntity();
		return $entity->getCreatedAt();
	}

	public function isCurrent()
	{
		return $this->active;
	}

	public function getAuthorName()
	{
		$entity = $this->getEntity();
		return $entity->getUsername();
	}

	public function getAuthorUsername()
	{
		$entity = $this->getEntity();
		return $entity->getUsername();
	}
}
