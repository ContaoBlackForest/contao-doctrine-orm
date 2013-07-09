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
use Contao\Doctrine\ORM\Mapping\Driver\ContaoDcaDriver;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Output\OutputInterface;

class EntityGeneration
{
	static public function generate(OutputInterface $output = null)
	{
		global $container;

		/** @var string $cacheDir */
		$cacheDir = $container['doctrine.orm.entitiesCacheDir'];

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($cacheDir, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);
		/** @var \SplFileInfo $file */
		foreach ($iterator as $file) {
			if ($file->isDir()) {
				rmdir($file);
			}
			else {
				unlink($file);
			}
		}

		/** @var EntityManager $entityManager */
		$entityManager = $container['doctrine.orm.entityManager'];

		$classMetadataFactory = new DisconnectedClassMetadataFactory();
		$classMetadataFactory->setEntityManager($entityManager);
		$metadatas = $classMetadataFactory->getAllMetadata();

		if (count($metadatas)) {
			$generated = array();

			// Create EntityGenerator
			/** @var EntityGenerator $entityGenerator */
			$entityGenerator = $container['doctrine.orm.entitiyGeneratorFactory'](true);

			foreach ($metadatas as $metadata) {
				static::generateEntity($metadata, $cacheDir, $entityGenerator, $output);
				$generated[] = $metadata->name;
			}

			// Outputting information message
			if ($output) {
				$output->write(PHP_EOL . sprintf('Entity classes generated to "<info>%s</INFO>"', $cacheDir) . PHP_EOL);
			}

			return $generated;
		}
		else {
			return false;
		}
	}

	/**
	 * @param ClassMetadataInfo $metadata
	 * @param string            $cacheDir
	 * @param EntityGenerator   $entityGenerator
	 * @param OutputInterface   $output
	 */
	static function generateEntity(
		ClassMetadataInfo $metadata,
		$cacheDir = null,
		EntityGenerator $entityGenerator = null,
		OutputInterface $output = null
	) {
		global $container;

		if (!$cacheDir) {
			$cacheDir = $container['doctrine.orm.entitiesCacheDir'];
		}

		if (!$entityGenerator) {
			$entityGenerator = $container['doctrine.orm.entitiyGeneratorFactory'](
				$GLOBALS['TL_CONFIG']['debugMode'] || $GLOBALS['TL_CONFIG']['doctrineDevMode']
			);
		}

		if ($output) {
			$output->write(
				sprintf('Processing entity "<info>%s</info>"', $metadata->name) . PHP_EOL
			);
		}

		$classPath = explode('\\', $metadata->getName());
		while (true) {
			$className = implode('\\', $classPath);

			if (isset($GLOBALS['DOCTRINE_ENTITY_CLASS'][$className])) {
				$entityGenerator->setClassToExtend($GLOBALS['DOCTRINE_ENTITY_CLASS'][$className]);
				break;
			}

			array_pop($classPath);
		}

		$entityGenerator->writeEntityClass($metadata, $cacheDir);

		// force load the new generated class
		if (!class_exists($metadata->getName(), false)) {
			$path = $cacheDir . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $metadata->name) . '.php';
			include($path);
		}
	}
}
