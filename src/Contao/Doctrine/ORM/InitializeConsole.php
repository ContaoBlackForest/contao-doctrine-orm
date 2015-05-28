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

namespace Contao\Doctrine\ORM;

use Composer\Console\Application;
use Contao\Doctrine\ORM\EntityHelper;
use Symfony\Component\Console\Helper\Helper;

class InitializeConsole extends Helper
{
    /**
     * @param Application $application
     */
    public function hookInitializeConsole($application)
    {
        $application->getHelperSet()->set($this);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'em';
    }

    public function getEntityManager()
    {
        return $GLOBALS['container']['doctrine.orm.entityManager'];
    }
}
