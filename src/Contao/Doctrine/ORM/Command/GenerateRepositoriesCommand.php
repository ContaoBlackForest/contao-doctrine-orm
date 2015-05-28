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
use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateRepositoriesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('doctrine:orm:generate-repositories')
            ->setDescription('Generate repository classes from your mapping information.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $container;

        /** @var string $cacheDir */
        $cacheDir = $container['doctrine.orm.repositoriesCacheDir'];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($cacheDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file);
            } else {
                unlink($file);
            }
        }

        /** @var EntityManager $entityManager */
        $entityManager = $container['doctrine.orm.entityManager'];

        $classMetadataFactory = new DisconnectedClassMetadataFactory();
        $classMetadataFactory->setEntityManager($entityManager);
        $metadatas = $classMetadataFactory->getAllMetadata();

        if (count($metadatas)) {
            $numRepositories = 0;
            $generator = new EntityRepositoryGenerator();

            foreach ($metadatas as $metadata) {
                if ($metadata->customRepositoryClassName) {
                    $output->write(
                        sprintf('Processing repository "<info>%s</info>"', $metadata->customRepositoryClassName)
                        . PHP_EOL
                    );

                    $generator->writeEntityRepositoryClass($metadata->customRepositoryClassName, $cacheDir);

                    $numRepositories++;
                }
            }

            if ($numRepositories) {
                // Outputting information message
                $output->write(
                    PHP_EOL
                    . sprintf('Repository classes generated to "<info>%s</INFO>"', $cacheDir)
                    . PHP_EOL
                );
            } else {
                $output->write('No Repository classes were found to be processed.' . PHP_EOL);
            }
        } else {
            $output->write('No Metadata Classes to process.' . PHP_EOL);
        }
    }
}
