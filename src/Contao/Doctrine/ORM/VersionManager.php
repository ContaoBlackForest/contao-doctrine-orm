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

use Doctrine\Common\Collections\Collection;
use ORM\Entity\Version;
use Symfony\Component\Serializer\Serializer;

class VersionManager
{
	/**
	 * Calculate a hash from an entity to identify a version.
	 *
	 * @param EntityInterface|array $entity
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	static public function calculateHash($entity)
	{
		if ($entity instanceof EntityInterface) {
			/** @var EntityAccessor $entityAccessor */
			$entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

			$entityData = $entityAccessor->getRawProperties($entity);
			ksort($entityData);
		}
		else if (is_array($entity)) {
			$entityData = $entity;
			ksort($entityData);
		}
		else if ($entity instanceof \Traversable) {
			$entityData = $entity;
		}
		else {
			throw new \RuntimeException('Illegal argument type ' . gettype(
				$entity
			) . ' for VersionManager::calculateHash');
		}

		$hash = array();
		foreach ($entityData as $value) {
			if (is_array($value)) {
				$hash[] = static::calculateHash($value);
			}
			else if (!is_object($value) || method_exists($value, '__toString')) {
				$hash[] = (string) $value;
			}
			else if ($value instanceof \DateTime) {
				$hash[] = $value->getTimestamp();
			}
			else if ($value instanceof EntityInterface || $value instanceof Collection) {
				// ignore references
			}
			else {
				throw new \RuntimeException('Do not know how to hash object type ' . get_class($value));
			}
		}
		$hash = implode('~', $hash);
		$hash = md5($hash);

		return $hash;
	}

	public function getVersion($versionId)
	{
		$versionRepository = EntityHelper::getRepository('ORM:Version');

		return $versionRepository->find($versionId);
	}

	public function findVersion(EntityInterface $entity, $entityData = null)
	{
		/** @var EntityAccessor $entityAccessor */
		$entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

		$versionRepository = EntityHelper::getRepository('ORM:Version');

		$entityClassName = Helper::createShortenEntityName($entity);
		$entityId        = $entityAccessor->getPrimaryKey($entity);
		$entityHash      = static::calculateHash($entityData ? : $entity);

		return $versionRepository->findOneBy(
			array(
				 'entityClass' => $entityClassName,
				 'entityId'    => $entityId,
				 'entityHash'  => $entityHash
			),
			array('createdAt' => 'DESC')
		);
	}

	public function findVersions(EntityInterface $entity)
	{
		/** @var EntityAccessor $entityAccessor */
		$entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

		$versionRepository = EntityHelper::getRepository('ORM:Version');

		$entityClassName = Helper::createShortenEntityName($entity);
		$entityId        = $entityAccessor->getPrimaryKey($entity);

		return $versionRepository->findBy(
			array(
				 'entityClass' => $entityClassName,
				 'entityId'    => $entityId,
			),
			array('createdAt' => 'DESC')
		);
	}

	public function getEntityVersion($version)
	{
		if (is_string($version)) {
			$version = $this->getVersion($version);
		}
		if ($version === null) {
			return null;
		}
		if (!$version instanceof Version) {
			throw new \RuntimeException('Version ID or entity is expected for VersionManager::getEntityVersion, got ' . gettype($version));
		}

		/** @var Serializer $serializer */
		$serializer = $GLOBALS['container']['doctrine.orm.entitySerializer'];

		$entityRepository = EntityHelper::getRepository($version->getEntityClass());

		/** @var EntityInterface $entity */
		$entity = $entityRepository->find($version->getEntityId());

		/** @var EntityInterface $entity */
		$previousEntity = $serializer->deserialize(
			$version->getData(),
			$entityRepository->getClassName(),
			'json'
		);

		$targetClass = new \ReflectionClass($entity);
		$sourceClass = new \ReflectionClass($previousEntity);

		foreach ($sourceClass->getProperties() as $sourceProperty) {
			$sourceValue = $sourceProperty->getValue($entity);
			if ($sourceValue instanceof EntityInterface || $sourceValue instanceof Collection) {
				// skip references
			}
			else {
				$targetProperty = $targetClass->getProperty($sourceProperty->getName());
				$sourceProperty->setAccessible(true);
				$targetProperty->setAccessible(true);
				$targetProperty->setValue($entity, $sourceProperty->getValue($previousEntity));
			}
		}

		return $entity;
	}
}
