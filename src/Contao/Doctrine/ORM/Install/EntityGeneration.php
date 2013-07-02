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
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Output\OutputInterface;

class EntityGeneration
{
	static public function generate(OutputInterface $output = null)
	{
		global $container;

		/** @var EntityManager $entityManager */
		$entityManager = $container['doctrine.orm.entityManager'];

        $classMetadataFactory = new DisconnectedClassMetadataFactory();
        $classMetadataFactory->setEntityManager($entityManager);
        $metadatas = $classMetadataFactory->getAllMetadata();

        if (count($metadatas)) {
			$generated = array();

			/** @var string $cacheDir */
			$cacheDir = $container['doctrine.orm.entitiesCacheDir'];

            // Create EntityGenerator
            $entityGenerator = $container['doctrine.orm.entitiyGeneratorFactory'](true);

            foreach ($metadatas as $metadata) {
				if ($output) {
					$output->write(
						sprintf('Processing entity "<info>%s</info>"', $metadata->name) . PHP_EOL
					);
				}
				$generated[] = $metadata->name;
            }

            // Generating Entities
            $entityGenerator->generate($metadatas, $cacheDir);

            // Outputting information message
			if ($output) {
            	$output->write(PHP_EOL . sprintf('Entity classes generated to "<info>%s</INFO>"', $cacheDir) . PHP_EOL);
			}

			return $generated;
        } else {
            return false;
        }
	}
}
