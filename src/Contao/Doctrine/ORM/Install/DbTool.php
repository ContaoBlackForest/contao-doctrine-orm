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
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\ORM\Tools\SchemaTool;

class DbTool extends \Controller
{
	public function hookSqlCompileCommands($return)
	{
		$this->loadLanguageFile('doctrine');

		// auto-generate and update entities here
		$GLOBALS['TL_CONFIG']['doctrineDevMode'] = true;

		$hasMigrations = false;

		$return = $this->generateMigrationSql($return, $hasMigrations);

		if ($hasMigrations) {
			$_SESSION['TL_INFO'][] = $GLOBALS['TL_LANG']['doctrine']['migrationRequired'];
		}
		else {
			$return = $this->generateSchemaSql($return);
		}

		return $return;
	}

	public function generateMigrationSql($return, &$hasMigrations)
	{
		$config  = \Config::getInstance();
		$modules = $config->getActiveModules();

		$connection = $GLOBALS['container']['doctrine.connection.default'];
		$output     = new OutputWriter();

		foreach ($modules as $module) {
			$path = sprintf('%s/system/modules/%s/migrations', TL_ROOT, $module);

			if (is_dir($path)) {
				$namespace = preg_split('~[\-_]~', $module);
				$namespace = array_map('ucfirst', $namespace);
				$namespace = implode('', $namespace);

				$configuration = new Configuration($connection, $output);
				$configuration->setName($module);
				$configuration->setMigrationsTableName('doctrine_migration_versions__' . str_replace('-', '_', standardize($module)));
				$configuration->setMigrationsNamespace('DoctrineMigrations\\' . $namespace);
				$configuration->setMigrationsDirectory($path);
				$configuration->registerMigrationsFromDirectory($path);

				$migration = new Migration($configuration);
				$versions  = $migration->getSql();

				if (count($versions)) {
					foreach ($versions as $version => $queries) {
						if (count($queries)) {
							$_SESSION['TL_CONFIRM'][] = sprintf($GLOBALS['TL_LANG']['doctrine']['migration'], $module, $version);

							$hasMigrations = true;
							$return        = $this->appendQueries($return, $queries);
						}
					}
				}
			}
		}

		return $return;
	}

	public function generateSchemaSql($return)
	{
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
		$queries   = $tool->getUpdateSchemaSql($metadatas);

		$filter = array_map('preg_quote', $GLOBALS['DOCTRINE_MANAGED_TABLE']);
		$filter = implode('|', $filter);
		$filter = sprintf('~^(CREATE|ALTER|DROP) TABLE (%s)~', $filter);

		$queries = array_filter(
			$queries,
			function ($query) use ($filter) {
				return preg_match($filter, $query);
			}
		);

		return $this->appendQueries($return, $queries);
	}

	protected function appendQueries(array $return, array $queries)
	{
		foreach ($queries as $query) {
			if (strpos($query, 'CREATE TABLE') !== false) {
				$return['CREATE'][] = $this->formatSql($query);
			}
			else if (strpos($query, 'DROP TABLE') !== false) {
				$return['DROP'][] = $this->formatSql($query);
			}
			else if (strpos($query, 'ALTER TABLE') !== false) {
				$return['ALTER_CHANGE'][] = $this->formatSql($query);
			}
			else {
				$return['ALTER_CHANGE'][] = $this->formatSql($query);
			}
		}

		return $return;
	}

	protected function formatSql($sql)
	{
		return \SqlFormatter::format($sql, false);
	}
}
