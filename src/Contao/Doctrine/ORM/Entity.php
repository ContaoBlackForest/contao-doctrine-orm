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

use Contao\Doctrine\ORM\Event\DuplicateEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Proxy\Proxy;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @deprecated
 */
abstract class Entity
{
    const TABLE_NAME = null;

    const PRIMARY_KEY = null;

    const PRIMARY_KEY_SEPARATOR = ';';

    const REF_IGNORE = 'ignore';

    const REF_ID = 'id';

    const REF_INCLUDE = 'include';

    const REF_ARRAY = 'array';

    /**
     * Get or set the ID of this entity.
     *
     * @param mixed $_
     *
     * @return string
     */
    public function id()
    {
        $args = func_get_args();

        $keys = explode(',', static::PRIMARY_KEY);
        if (count($args)) {
            if (count($args) == 1 && is_string($args[0])) {
                $args = explode(static::PRIMARY_KEY_SEPARATOR, $args[0]);
            }

            if (count($args) == count($keys)) {
                foreach ($keys as $index => $field) {
                    $this->$field = $args[$index];
                }
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Arguments count of %d does not match id field count of %d',
                        count($args),
                        count($keys)
                    )
                );
            }
        }

        /** @var EntityAccessor $entityAccessor */
        $entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

        $id = array();
        foreach ($keys as $field) {
            $value = $this->$field;
            if ($value instanceof EntityInterface) {
                $value = $entityAccessor->getPrimaryKey($value);
            }
            $id[] = $value;
        }

        return implode(static::PRIMARY_KEY_SEPARATOR, $id);
    }

    /**
     * Duplicate (clone) an entity with or without its primary key.
     *
     * @param bool $withoutPrimaryKey
     *
     * @return static
     */
    public function duplicate($withoutPrimaryKey = false)
    {
        if ($this instanceof Proxy) {
            $this->__load();
        }

        $entity = clone $this;

        // clean primary key
        if ($withoutPrimaryKey) {
            $keys = explode(',', static::PRIMARY_KEY);
            foreach ($keys as $key) {
                $entity->$key = null;
            }
        }

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $GLOBALS['container']['event-dispatcher'];
        $eventDispatcher->dispatch(EntityEvents::DUPLICATE_ENTITY, new DuplicateEntity($entity, $withoutPrimaryKey));

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $GLOBALS['container']['event-dispatcher'];
        $eventDispatcher->dispatch(EntityEvents::DUPLICATE_ENTITY, new DuplicateEntity($this, false));
    }
}
