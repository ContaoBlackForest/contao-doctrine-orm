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

namespace Contao\Doctrine\ORM\Install;

use Contao\Doctrine\ORM\EntityHelper;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\ORM\Tools\SchemaTool;

class DbTool extends \Controller
{
	public function hookSqlCompileCommands($return)
	{
		// auto-generate and update entities here
		$GLOBALS['TL_CONFIG']['doctrineDevMode'] = true;

		$entityManager = EntityHelper::getEntityManager();

		$cacheDriver = $entityManager
			->getConfiguration()
			->getMetadataCacheImpl();
		if ($cacheDriver && !$cacheDriver instanceof ApcCache) {
			$cacheDriver->deleteAll();
		}

		// force "disconnected" generation of entities
		$reload = false;
		EntityGeneration::generate(null, $reload);

		if ($reload) {
			$this->reload();
		}

		$metadatas = $entityManager
			->getMetadataFactory()
			->getAllMetadata();
		$tool      = new SchemaTool($entityManager);
		$sqls      = $tool->getUpdateSchemaSql($metadatas);

		$filter = array_map('preg_quote', $GLOBALS['DOCTRINE_MANAGED_TABLE']);
		$filter = implode('|', $filter);
		$filter = sprintf('~^(CREATE|ALTER|DROP) TABLE (%s)~', $filter);

		foreach ($sqls as $sql) {
			if (!preg_match($filter, $sql)) {
				continue;
			}

			if (strpos($sql, 'CREATE TABLE') !== false) {
				$return['CREATE'][] = $this->formatCreate($sql);
			}
			else if (strpos($sql, 'DROP TABLE') !== false) {
				$return['DROP'][] = $this->formatDrop($sql);
			}
			else if (strpos($sql, 'ALTER TABLE') !== false) {
				$return['ALTER_CHANGE'][] = $this->formatAlter($sql);
			}
			else {
				$return['ALTER_CHANGE'][] = $this->formatSql($sql);
			}
		}

		return $return;
	}

	protected function formatCreate($sql)
	{
		$sql = preg_replace(
			'~orm_\w+ \(~',
			"\$0\n  ",
			$sql
		);
		$sql = preg_replace(
			'~[^\s]+ [^\s]+ [^\s]+,~',
			"\$0\n ",
			$sql
		);
		$sql = str_replace(
			') DEFAULT CHARACTER SET',
			"\n) DEFAULT CHARACTER SET",
			$sql
		);

		return $sql;
	}

	protected function formatDrop($sql)
	{
		return $sql;
	}

	protected function formatAlter($sql)
	{
		$sql = preg_replace(
			'#(ADD|DROP|CHANGE)#',
			"\n  $1",
			$sql
		);
		$sql = preg_replace(
			'#(FOREIGN KEY|REFERENCES)#',
			"\n      $1",
			$sql
		);

		return $sql;
	}

	protected function formatSql($sql)
	{
		return $sql;
	}
}
