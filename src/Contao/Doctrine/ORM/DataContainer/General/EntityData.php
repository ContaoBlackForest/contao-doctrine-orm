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

namespace Contao\Doctrine\ORM\DataContainer\General;

use Contao\Doctrine\ORM\Entity;
use Contao\Doctrine\ORM\EntityHelper;
use Contao\Doctrine\ORM\Mapping\Driver\ContaoDcaDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use GeneralCollectionDefault;
use GeneralDataConfigDefault;
use InterfaceGeneralCollection;
use InterfaceGeneralDataConfig;
use InterfaceGeneralModel;
use Module;

class EntityData implements \InterfaceGeneralData
{
	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var string
	 */
	protected $entityClassName;

	/**
	 * @var \ReflectionClass
	 */
	protected $entityClass;

	/**
	 * @var EntityManager
	 */
	protected $entityManager;

	/**
	 * @var EntityRepository
	 */
	protected $entityRepository;

	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		if (!$this->entityManager) {
			$this->entityManager = EntityHelper::getEntityManager();
		}
		return $this->entityManager;
	}

	/**
	 * @return EntityRepository
	 */
	public function getEntityRepository()
	{
		if (!$this->entityRepository) {
			$this->entityRepository = EntityHelper::getRepository($this->entityClassName);
		}
		return $this->entityRepository;
	}

	/**
	 * @param Entity[] $entities
	 *
	 * @return EntityModel[]
	 */
	public function mapEntities($entities)
	{
		$collection = new GeneralCollectionDefault();
		foreach ($entities as $entity) {
			$collection->add($this->mapEntity($entity));
		}
		return $collection;
	}

	/**
	 * @param Entity $entity
	 *
	 * @return EntityModel
	 */
	public function mapEntity($entity)
	{
		return new EntityModel($entity);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setBaseConfig(array $config)
	{
		// Check configuration.
		if (!isset($config["source"])) {
			throw new \Exception("Missing entity table name.");
		}

		// fetch entity manager to register entity class loader
		$GLOBALS['container']['doctrine.orm.entityManager'];

		$this->tableName       = $config['source'];
		$this->entityClassName = ContaoDcaDriver::tableToClassName($this->tableName);
		$this->entityClass     = new \ReflectionClass($this->entityClassName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyConfig()
	{
		return GeneralDataConfigDefault::init();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyModel()
	{
		return new EntityModel($this->entityClass->newInstance());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyCollection()
	{
		return new GeneralCollectionDefault();
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch(InterfaceGeneralDataConfig $config)
	{
		$repository = $this->getEntityRepository();
		return $this->mapEntity($repository->find($config->getId()));
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetchAll(InterfaceGeneralDataConfig $config)
	{
		$repository = $this->getEntityRepository();
		return $this->mapEntities($repository->findAll());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCount(InterfaceGeneralDataConfig $config)
	{
		$entityManager = $this->getEntityManager();
		return $entityManager
			->createQueryBuilder()
			->select('COUNT(e)')
			->from($this->entityClassName, 'e')
			->getQuery()
			->getResult(Query::HYDRATE_SINGLE_SCALAR);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(InterfaceGeneralModel $item, $recursive = false)
	{
		$entityManager = $this->getEntityManager();
		$entityManager->persist($item->getEntity());
		$entityManager->flush($item->getEntity());
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveEach(InterfaceGeneralCollection $items, $recursive = false)
	{
		$entityManager = $this->getEntityManager();
		foreach ($items as $item) {
			$entityManager->persist($item->getEntity());
			$entityManager->flush($item->getEntity());
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($item)
	{
		$entityManager = $this->getEntityManager();
		$entityManager->remove($item->getEntity());
		$entityManager->flush($item->getEntity());
	}

	/**
	 * Save a new Version of a record
	 *
	 * @param int    $intID      ID of current record
	 * @param string $strVersion Version number
	 *
	 * @return void
	 */
	public function saveVersion(InterfaceGeneralModel $objModel, $strUsername)
	{
		// TODO: Implement saveVersion() method.
	}

	/**
	 * Return a model based of the version information
	 *
	 * @param mix $mixID      The ID of record
	 * @param mix $mixVersion The ID of the Version
	 *
	 * @return InterfaceGeneralModel
	 */
	public function getVersion($mixID, $mixVersion)
	{
		// TODO: Implement getVersion() method.
	}

	/**
	 * Return a list with all versions for this row
	 *
	 * @param mixed $mixID The ID of record
	 *
	 * @return InterfaceGeneralCollection
	 */
	public function getVersions($mixID, $blnOnlyActive = false)
	{
		// TODO: Implement getVersions() method.
	}

	/**
	 * Set a Version as active.
	 *
	 * @param mix $mixID      The ID of record
	 * @param mix $mixVersion The ID of the Version
	 */
	public function setVersionActive($mixID, $mixVersion)
	{
		// TODO: Implement setVersionActive() method.
	}

	/**
	 * Return the active version from a record
	 *
	 * @param mix $mixID The ID of record
	 *
	 * @return mix Version ID
	 */
	public function getActiveVersion($mixID)
	{
		// TODO: Implement getActiveVersion() method.
	}

	/**
	 * Reste the fallback field
	 *
	 * Documentation:
	 *      Evaluation - fallback => If true the field can only be assigned once per table.
	 *
	 * @return void
	 */
	public function resetFallback($strField)
	{
		// TODO: Implement resetFallback() method.
	}

	/**
	 * {@inheritdoc}
	 */
	public function isUniqueValue($field, $value, $id = null)
	{
		$keys     = explode(',', $this->entityClass->getConstant('KEY'));
		$idValues = explode($this->entityClass->getConstant('KEY_SEPARATOR'), $id);

		if (count($keys) != count($idValues)) {
			throw new \RuntimeException('Key count of ' . count($keys) . ' does not match id values count ' . count(
				$idValues
			));
		}

		$entityManager = $this->getEntityManager();
		$queryBuilder  = $entityManager->createQueryBuilder();
		$queryBuilder
			->select('COUNT(e.' . $keys[0] . ')')
			->from($this->entityClassName, 'e')
			->where(
				$queryBuilder
					->expr()
					->eq('e.' . $field, ':value')
			)
			->setParameter(':value', $value);
		foreach ($keys as $index => $key) {
			$queryBuilder
				->andWhere(
				$queryBuilder
					->expr()
					->neq('e.' . $key, ':key' . $index)
				)
				->setParameter(':key' . $index, $idValues[$index]);
		}
		$query = $queryBuilder->getQuery();
		return !$query->getResult(Query::HYDRATE_SINGLE_SCALAR);
	}

	/**
	 * {@inheritdoc}
	 */
	public function fieldExists($strField)
	{
		return $this->entityClass->hasProperty($strField);
	}

	/**
	 * {@inheritdoc}
	 */
	public function sameModels($objModel1, $objModel2)
	{
		if ($objModel1 instanceof EntityModel &&
			$objModel2 instanceof EntityModel &&
			get_class($objModel1->getEntity()) == get_class($objModel2->getEntity())
		) {
			$data1 = $objModel1
				->getEntity()
				->toArray();
			$data2 = $objModel2
				->getEntity()
				->toArray();

			foreach ($data1 as $key => $value) {
				if ($data2[$key] != $value) {
					return false;
				}
			}

			return true;
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilterOptions(InterfaceGeneralDataConfig $config)
	{
		$properties = $config->getFields();
		$property   = $properties[0];

		if (count($properties) <> 1) {
			throw new \Exception('Config must contain exactly one property to be retrieved.');
		}

		$entityManager = $this->getEntityManager();
		$queryBuilder  = $entityManager->createQueryBuilder();
		return $queryBuilder
			->select('DISTINCT e.' . $property)
			->from($this->entityClassName, 'e')
			->orderBy('e.' . $property)
			->getQuery()
			->getResult();
	}
}
