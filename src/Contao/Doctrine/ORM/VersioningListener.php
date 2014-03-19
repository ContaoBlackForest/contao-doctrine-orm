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

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use JMS\Serializer\Serializer;
use ORM\Entity\Version;

class VersioningListener implements EventSubscriber
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
		if ($entity instanceof EntityInterface && !$entity instanceof Version) {
			$changeSet = $entityManager
				->getUnitOfWork()
				->getEntityChangeSet($entity);

			/** @var EntityAccessor $entityAccessor */
			$entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

			$originalData = $entityAccessor->getRawProperties($entity);
			// restore original values
			foreach ($changeSet as $field => $change) {
				$originalData[$field] = $change[0];
			}

			/** @var VersionManager $versionManager */
			$versionManager = $GLOBALS['container']['doctrine.orm.versionManager'];

			/** @var Version $previousVersion */
			$previousVersion = $versionManager->findVersion($entity, $originalData);

			/** @var Serializer $serializer */
			$serializer = $GLOBALS['container']['doctrine.orm.entitySerializer'];

			$version = new Version();
			$version->setEntityClass(Helper::createShortenEntityName($entity));
			$version->setEntityId($entityAccessor->getPrimaryKey($entity));
			$version->setEntityHash(VersionManager::calculateHash($entity));
			$version->setAction($action);
			$version->setPrevious($previousVersion ? $previousVersion->getId() : null);
			$version->setData($serializer->serialize($entity, 'json'));
			$version->setChanges($serializer->serialize($changeSet, 'json'));

			if (BE_USER_LOGGED_IN) {
				$user = \BackendUser::getInstance();
				$version->setUserId($user->id);
				$version->setUsername($user->username);
			}

			$this->versions[] = $version;
		}
	}
}
