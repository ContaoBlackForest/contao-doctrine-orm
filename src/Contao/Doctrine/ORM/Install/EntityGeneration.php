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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EntityGeneration
{
	static public function generate(OutputInterface $output = null, &$reload = false)
	{
		global $container;

		/** @var string $entitiesCacheDir */
		$entitiesCacheDir = $container['doctrine.orm.entitiesCacheDir'];
		/** @var string $proxiesCacheDir */
		$proxiesCacheDir = $container['doctrine.orm.proxiesCacheDir'];
		/** @var string $repositoriesCacheDir */
		$repositoriesCacheDir = $container['doctrine.orm.repositoriesCacheDir'];

		/** @var LoggerInterface $logger */
		$logger = $container['doctrine.orm.logger'];

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($entitiesCacheDir, \FilesystemIterator::SKIP_DOTS),
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

		// load "disconnected" metadata (without reflection class) information
		$classMetadataFactory = new DisconnectedClassMetadataFactory();
		$classMetadataFactory->setEntityManager($entityManager);
		$metadatas = $classMetadataFactory->getAllMetadata();

		if (count($metadatas)) {
			$generated = array();

			// Create EntityGenerator
			/** @var EntityGenerator $entityGenerator */
			$entityGenerator = $container['doctrine.orm.entityGeneratorFactory'](true);

			foreach ($metadatas as $metadata) {
				static::generateEntity($metadata, $entitiesCacheDir, $entityGenerator, $output);
				$generated[] = $metadata->name;
			}

			if ($output) {
				$output->write(
					   PHP_EOL . sprintf('Entity classes generated to "<info>%s</info>"', $entitiesCacheDir) . PHP_EOL
				);
			}
			$logger->info(sprintf('Entity classes generated to "%s"', $entitiesCacheDir));

			// (re)load "connected" metadata information
			$classMetadataFactory = $entityManager->getMetadataFactory();

			try {
				$metadatas            = $classMetadataFactory->getAllMetadata();

				$entityManager
					->getProxyFactory()
					->generateProxyClasses($metadatas, $proxiesCacheDir);

				if ($output) {
					$output->write(
						   PHP_EOL . sprintf('Entity proxies generated to "<info>%s</info>"', $proxiesCacheDir) . PHP_EOL
					);
				}
				$logger->info(sprintf('Entity proxies generated to "%s"', $proxiesCacheDir));

				$repositoryGenerator = new EntityRepositoryGenerator();
				foreach ($metadatas as $metadata) {
					if ($metadata->customRepositoryClassName) {
						if ($output) {
							$output->write(
								   sprintf(
									   'Processing repository "<info>%s</info>"',
									   $metadata->customRepositoryClassName
								   ) . PHP_EOL
							);
						}

						$repositoryGenerator->writeEntityRepositoryClass(
											$metadata->customRepositoryClassName,
												$repositoriesCacheDir
						);
					}
				}
			}
			catch (\ReflectionException $e) {
				// silently ignore and reload
				$reload = true;
			}

			if ($output) {
				$output->write(
					   PHP_EOL . sprintf(
						   'Entity repositories generated to "<info>%s</info>"',
						   $repositoriesCacheDir
					   ) . PHP_EOL
				);
			}
			$logger->info(sprintf('Entity repositories generated to "%s"', $repositoriesCacheDir));

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
			$entityGenerator = $container['doctrine.orm.entityGeneratorFactory'](
				$GLOBALS['TL_CONFIG']['debugMode'] || $GLOBALS['TL_CONFIG']['doctrineDevMode']
			);
		}

		if ($output) {
			$output->write(
				   sprintf('Processing entity "<info>%s</info>"', $metadata->name) . PHP_EOL
			);
		}

		$entityGenerator->setClassToExtend(false);

		$classPath = explode('\\', $metadata->getName());
		while (count($classPath)) {
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
