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

use Contao\Doctrine\ORM\Entity;
use DcGeneral\Data\DefaultModel;
use InterfaceGeneralModel;
use ORM\Entity\Version;

class VersionModel extends DefaultModel
{
	/**
	 * @var Version
	 */
	protected $version;

	protected $active;

	function __construct($version, $active)
	{
		$this->version = $version;
		$this->active  = $active;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
	}

	/**
	 * @return Version
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getID()
	{
		$version = $this->getVersion();

		if ($version) {
			return $version->id();
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setID($mixID)
	{
		// unsupported for versions
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProperty($strPropertyName)
	{
		$version = $this->getVersion();

		if ($version) {
			switch ($strPropertyName) {
				case 'version':
					return $version->getId();

				case 'active':
					return $this->active;

				case 'tstamp':
					return $version->getCreatedAt()->getTimestamp();

				case 'username':
					return $version->getUsername();
			}
			return $version->$strPropertyName;
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setProperty($strPropertyName, $varValue)
	{
		// unsupported for versions
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPropertiesAsArray()
	{
		$version = $this->getVersion();

		if ($version) {
			return $version->toArray();
		}

		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPropertiesAsArray($arrProperties)
	{
		// unsupported for versions
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasProperties()
	{
		return (bool) $this->getVersion();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProviderName()
	{
		$version = $this->getVersion();
		return $version::TABLE_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->getVersion()->toArray());
	}
}
