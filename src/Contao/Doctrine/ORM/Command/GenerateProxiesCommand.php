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

class GenerateProxiesCommand extends Command
{
	protected function configure()
	{
		$this
			->setName('doctrine:orm:generate-proxies')
			->setDescription('Generates proxy classes for entity classes.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		global $container;

		/** @var string $cacheDir */
		$cacheDir = $container['doctrine.orm.proxiesCacheDir'];

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
            // Generating Proxies
            $entityManager->getProxyFactory()->generateProxyClasses($metadatas, $cacheDir);

            // Outputting information message
            $output->write(PHP_EOL . sprintf('Proxy classes generated to "<info>%s</INFO>"', $cacheDir) . PHP_EOL);
        } else {
            $output->write('No Metadata Classes to process.' . PHP_EOL);
        }
	}
}
