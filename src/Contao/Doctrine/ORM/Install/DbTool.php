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

class DbTool
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
		EntityGeneration::generate();

		$metadatas = $entityManager
			->getMetadataFactory()
			->getAllMetadata();
		$tool      = new SchemaTool($entityManager);
		$sqls      = $tool->getUpdateSchemaSql($metadatas);

		foreach ($sqls as $sql) {
			if (!preg_match('~^(CREATE|ALTER|DROP) TABLE orm_~', $sql)) {
				continue;
			}

			$sql = preg_replace('~orm_\w+ \(~', "\$0\n  ", $sql);
			$sql = preg_replace('~[^\d],~', "\$0\n ", $sql);
			$sql = str_replace(') DEFAULT CHARACTER SET', "\n) DEFAULT CHARACTER SET", $sql);

			if (strpos($sql, 'CREATE TABLE') !== false) {
				$return['CREATE'][] = $sql;
			}
			else if (strpos($sql, 'DROP TABLE') !== false) {
				$return['DROP'][] = $sql;
			}
			else if (strpos($sql, 'ALTER TABLE') !== false) {
				$return['ALTER_CHANGE'][] = $sql;
			}
			else {
				$return['ALTER_CHANGE'][] = $sql;
			}
		}

		return $return;
	}
}
