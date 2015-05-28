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

namespace Contao\Doctrine\ORM\Serializer;

use Contao\Doctrine\ORM\EntityAccessor;
use Contao\Doctrine\ORM\EntityInterface;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\scalar;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

class EntitySubscribingHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'Contao\Doctrine\ORM\EntityInterface',
                'method'    => 'serializeEntity',
            ),
            array(
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'Contao\Doctrine\ORM\EntityInterface',
                'method'    => 'deserializeEntity',
            ),
        );
    }

    public function serializeEntity(
        JsonSerializationVisitor $visitor,
        EntityInterface $entity,
        array $type,
        Context $context
    ) {
        /** @var EntityAccessor $entityAccessor */
        $entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

        $properties              = $entityAccessor->getRawProperties($entity);
        $properties['__ENTITY_CLASS__'] = get_class($entity);
        $properties              = $visitor->visitArray($properties, $type, $context);

        return $properties;
    }

    public function deserializeEntity(JsonSerializationVisitor $visitor, $properties, array $type, Context $context)
    {
        /** @var EntityAccessor $entityAccessor */
        $entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

        $entityClassName = $properties['__ENTITY_CLASS__'];
        $entityClass     = new \ReflectionClass($entityClassName);
        $entity          = $entityClass->newInstance();

        unset($properties['__ENTITY_CLASS__']);
        $properties = $visitor->visitArray($properties, $type, $context);
        $entityAccessor->setRawProperties($entity, $properties);

        return $entity;
    }
}
