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

use Doctrine\ORM\EntityManager;

class EntityHelper
{
    /**
     * @return EntityAccessor
     */
    public static function getEntityAccessor()
    {
        return $GLOBALS['container']['doctrine.orm.entityAccessor'];
    }

    /**
     * @return EntityManager
     */
    public static function getEntityManager()
    {
        return $GLOBALS['container']['doctrine.orm.entityManager'];
    }

    /**
     * @param string $className
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public static function getRepository($className)
    {
        $entityManager = static::getEntityManager();
        return $entityManager->getRepository($className);
    }

    /**
     * Search an entity by an combined id, fetched by Entity::id()
     *
     * @param \ReflectionClass|string $class
     * @param string                  $combinedId
     *
     * @return EntityInterface|null
     */
    public static function parseCombinedId($class, $combinedId)
    {
        if (($pos = strpos($class, ':')) !== false) {
            $alias = substr($class, 0, $pos);
            if (isset($GLOBALS['DOCTRINE_ENTITY_NAMESPACE_ALIAS'][$alias])) {
                $namespace = $GLOBALS['DOCTRINE_ENTITY_NAMESPACE_ALIAS'][$alias];
                $class     = $namespace . '\\' . substr($class, $pos + 1);
            }
        }

        if (!$class instanceof \ReflectionClass) {
            $class = new \ReflectionClass($class);
        }

        if ($class->isSubclassOf('Contao\Doctrine\ORM\EntityInterface')) {
            $keys = $class
                ->getMethod('entityPrimaryKeyNames')
                ->invoke(null);
        } else {
            $keys = array('id');
        }

        $ids      = explode('|', $combinedId);
        $criteria = array_combine($keys, $ids);

        return $criteria;
    }

    /**
     * Search an entity by an combined id, fetched by Entity::id()
     *
     * @param \ReflectionClass|string $class
     * @param string                  $combinedId
     *
     * @return EntityInterface|null
     */
    public static function findByCombinedId($class, $combinedId)
    {
        $criteria = static::parseCombinedId($class, $combinedId);

        $repository = static::getRepository($class->getName());
        $entity     = $repository->findOneBy($criteria);

        return $entity;
    }

    /**
     * Call load callbacks
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return mixed
     */
    public static function callGetterCallbacks($entity, $table, $field, $value)
    {
        if (isset($GLOBALS['TL_DCA'][$table]['fields'][$field]['getter_callback'])) {
            $callbacks = (array) $GLOBALS['TL_DCA'][$table]['fields'][$field]['getter_callback'];
            foreach ($callbacks as $callback) {
                $value = call_user_func($callback, $value, $entity);
            }
        }
        return $value;
    }

    /**
     * Call save callbacks
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return mixed
     */
    public static function callSetterCallbacks($entity, $table, $field, $value)
    {
        if (isset($GLOBALS['TL_DCA'][$table]['fields'][$field]['setter_callback'])) {
            $callbacks = (array) $GLOBALS['TL_DCA'][$table]['fields'][$field]['setter_callback'];
            foreach ($callbacks as $callback) {
                $value = call_user_func($callback, $value, $entity);
            }
        }
        return $value;
    }
}
