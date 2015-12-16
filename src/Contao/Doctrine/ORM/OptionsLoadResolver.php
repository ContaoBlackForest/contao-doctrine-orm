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

use Contao\Doctrine\ORM\DataContainer\General\EntityDataProvider;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;

/**
 *
 */
class OptionsLoadResolver
{
    static protected $instance;

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function create()
    {
        return array(
            'Contao\Doctrine\ORM\OptionsLoadResolver',
            'load'
        );
    }

    /**
     * @param string $methodName
     * @param array  $args
     */
    public function load($entities, DcCompat $compat)
    {
        $providerName = $compat->getModel()->getProviderName();
        $enviroment   = $compat->getEnvironment();
        /** @var EntityDataProvider $dataProvider */
        $dataProvider = $enviroment->getDataProvider($providerName);

        $entityManager    = $dataProvider->getEntityManager();
        $metaFactory      = $entityManager->getMetadataFactory();
        $metaData         = $metaFactory->getMetadataFor($dataProvider->getEntityRepository()->getClassName());
        $associationNames = $metaData->getAssociationNames();

        $entityAccessor = EntityHelper::getEntityAccessor();
        $ids            = array();

        if (is_array($entities) || $entities instanceof \Traversable) {
            foreach ($entities as $entity) {
                if (is_object($entity)) {
                    $ids[] = $entityAccessor->getPrimaryKey($entity);

                    continue;
                }

                if (!empty($associationNames)
                    && in_array($compat->getPropertyName(), $associationNames)
                ) {
                    $ids[] = $entity;
                }
            }
        }

        return $ids;
    }
}
