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

use Contao\Doctrine\ORM\Install\EntityGeneration;
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
		EntityGeneration::generate($output);
	}
}
