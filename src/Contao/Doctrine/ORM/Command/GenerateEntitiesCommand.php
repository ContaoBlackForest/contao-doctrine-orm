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

namespace Contao\Doctrine\ORM\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEntitiesCommand extends Command
{
	protected function configure()
	{
		$this
			->setName('doctrine:orm:generate-entities')
			->setDescription('Generate entity classes from your mapping information.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
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
            // Create EntityGenerator
            $entityGenerator = new EntityGenerator();

			$entityGenerator->setGenerateStubMethods(true);
			$entityGenerator->setFieldVisibility('protected');
            $entityGenerator->setRegenerateEntityIfExists(true);
            $entityGenerator->setUpdateEntityIfExists(true);

            foreach ($metadatas as $metadata) {
                $output->write(
                    sprintf('Processing entity "<info>%s</info>"', $metadata->name) . PHP_EOL
                );
            }

            // Generating Entities
            $entityGenerator->generate($metadatas, $cacheDir);

            // Outputting information message
            $output->write(PHP_EOL . sprintf('Entity classes generated to "<info>%s</INFO>"', $cacheDir) . PHP_EOL);
        } else {
            $output->write('No Metadata Classes to process.' . PHP_EOL);
        }
	}
}
