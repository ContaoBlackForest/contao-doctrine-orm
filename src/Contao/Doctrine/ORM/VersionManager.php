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

use Contao\Doctrine\ORM\Entity;
use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use ORM\Entity\Version;

class VersionManager implements EventSubscriber
{
	protected $versions = array();

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			'onFlush',
		);
	}

	public function onFlush(OnFlushEventArgs $args)
	{
		$entityManager = $args->getEntityManager();
		$unitOfWork    = $entityManager->getUnitOfWork();

		foreach ($unitOfWork->getScheduledEntityInsertions() AS $entity) {
			$this->createVersion('insert', $entity, $args);
		}

		foreach ($unitOfWork->getScheduledEntityUpdates() AS $entity) {
			$this->createVersion('update', $entity, $args);
		}

		foreach ($unitOfWork->getScheduledEntityDeletions() AS $entity) {
			$this->createVersion('delete', $entity, $args);
		}

		if (count($this->versions)) {
			$metadata = $entityManager->getClassMetadata('ORM:Version');
			while (count($this->versions)) {
				$version = array_shift($this->versions);

				$entityManager->persist($version);
				$unitOfWork->computeChangeSet($metadata, $version);
			}
		}
	}

	protected function createVersion($action, $entity, OnFlushEventArgs $args)
	{
		$entityManager = $args->getEntityManager();
		if ($entity instanceof Entity && !$entity instanceof Version) {
			$changeSet = $entityManager
				->getUnitOfWork()
				->getEntityChangeSet($entity);

			$originalData = $entity->toArray();
			// restore original values
			foreach ($changeSet as $field => $change) {
				$originalData[$field] = $change[0];
			}

			$previousVersion = static::findVersion($entity, $originalData);

			$version = new Version();
			$version->setEntityClass(Helper::createShortenEntityName($entity));
			$version->setEntityId($entity->id());
			$version->setEntityHash(static::calculateHash($entity->toArray()));
			$version->setAction($action);
			$version->setPrevious($previousVersion ? $previousVersion->getId() : null);
			$version->setData(json_encode($entity->toArray()));
			$this->versions[] = $version;
		}
	}

	/**
	 * Calculate a hash from an entity to identify a version.
	 *
	 * @param Entity|array $entity
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	static public function calculateHash($entity)
	{
		if ($entity instanceof Entity) {
			$entityData = $entity->toArray();
		}
		else if (is_array($entity)) {
			$entityData = $entity;
		}
		else {
			throw new \RuntimeException('Illegal argument type ' . gettype(
				$entity
			) . ' for VersionManager::calculateHash');
		}

		ksort($entityData);
		$hash = array_map(
			function ($value) {
				if (is_array($value) || $value instanceof Entity) {
					return static::calculateHash($value);
				}
				else if (!is_object($value) || method_exists($value, '__toString')) {
					return (string) $value;
				}
				else if ($value instanceof \DateTime) {
					return $value->getTimestamp();
				}
				else {
					throw new \RuntimeException('Do not know how to hash object type ' . get_class($value));
				}
			},
			$entityData
		);
		$hash = implode('~', $hash);
		$hash = md5($hash);

		return $hash;
	}

	static public function findVersion(Entity $entity, $entityData = null)
	{
		$versionRepository = EntityHelper::getRepository('ORM:Version');

		$entityClassName = Helper::createShortenEntityName($entity);
		$entityId        = $entity->id();
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
}
