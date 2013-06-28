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

namespace Contao\Doctrine\ORM\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\EntityGenerator;

class ContaoDcaDriver extends \Controller implements MappingDriver
{
	protected $entitiesCacheDir;

	public function __construct($entitiesCacheDir)
	{
		parent::__construct();
		$this->entitiesCacheDir = $entitiesCacheDir;
	}

	/**
	 * Loads the metadata for the specified class into the provided container.
	 *
	 * @param string                              $className
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata
	 */
	public function loadMetadataForClass($className, ClassMetadata $metadata)
	{
		$tableName = static::classToTableName($className);;
		$this->loadDataContainer($tableName);

		if (!array_key_exists('TL_DCA', $GLOBALS) || !array_key_exists($tableName, $GLOBALS['TL_DCA'])) {
			return;
		}

		$entityConfig = array();
		if (array_key_exists('entity', $GLOBALS['TL_DCA'][$tableName])) {
			$entityConfig = $GLOBALS['TL_DCA'][$tableName]['entity'];
		}

		// custom repository class
		if (array_key_exists('repositoryClass', $entityConfig)) {
			$metadata->setCustomRepositoryClass($entityConfig['repositoryClass']);
		}

		// id generator
		if (array_key_exists('idGenerator', $entityConfig)) {
			$metadata->setIdGeneratorType($entityConfig['idGenerator']);
		}

		$metadata->isMappedSuperclass = true;
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setPrimaryTable(
			array('name' => $tableName)
		);

		// add primary key field ID
		if (!array_key_exists('id', $GLOBALS['TL_DCA'][$tableName]['fields'])) {
			$GLOBALS['TL_DCA'][$tableName]['fields']['id']['field'] = array();
		}
		$GLOBALS['TL_DCA'][$tableName]['fields']['id']['field'] = array_merge(
			array(
				'id' => true,
				'type' => 'integer'
			),
			$GLOBALS['TL_DCA'][$tableName]['fields']['id']['field']
		);

		// add parent key field PID
		if (!empty($GLOBALS['TL_DCA'][$tableName]['config']['ptable'])) {
			if (!array_key_exists('pid', $GLOBALS['TL_DCA'][$tableName]['fields'])) {
				$GLOBALS['TL_DCA'][$tableName]['fields']['pid']['field'] = array();
			}
			$GLOBALS['TL_DCA'][$tableName]['fields']['pid']['field'] = array_merge(
				array(
					'type' => 'integer'
				),
				$GLOBALS['TL_DCA'][$tableName]['fields']['pid']['field']
			);
		}

		// add modification time field TSTAMP
		if (!array_key_exists('tstamp', $GLOBALS['TL_DCA'][$tableName]['fields'])) {
			$GLOBALS['TL_DCA'][$tableName]['fields']['tstamp']['field'] = array();
		}
		$GLOBALS['TL_DCA'][$tableName]['fields']['tstamp']['field'] = array_merge(
			array(
				'type' => 'integer'
			),
			$GLOBALS['TL_DCA'][$tableName]['fields']['tstamp']['field']
		);

		foreach ($GLOBALS['TL_DCA'][$tableName]['fields'] as $fieldName => $fieldConfig) {
			$fieldMapping = array();

			$inputTypes = array($fieldConfig['inputType']);
			$inputTypeOptions = array();

			if ($fieldConfig['foreignKey']) {
				$inputTypeOptions[] = 'foreignKey';
			}
			if ($fieldConfig['eval']['multiple']) {
				$inputTypeOptions[] = 'multiple';
			}
			for ($i=0; $i<count($inputTypeOptions); $i++) {
				$inputTypeOption = $fieldConfig['inputType'] . '_' . $inputTypeOptions[$i];
				array_unshift($inputTypes, $inputTypeOption);
				for ($j=$i+1; $j<count($inputTypeOptions); $j++) {
					$inputTypeOption .= '_' . $inputTypeOptions[$j];
					array_unshift($inputTypes, $inputTypeOption);
				}
			}
			$fieldMapping['type'] = 'text';
			foreach ($inputTypes as $inputType) {
				if (array_key_exists($inputType, $GLOBALS['DOCTRINE_TYPE_MAP'])) {
					$fieldMapping['type'] = $GLOBALS['DOCTRINE_TYPE_MAP'][$inputType];
					break;
				}
			}


			if ($fieldConfig['eval']['maxlength']) {
				$fieldMapping['length'] = (int) $fieldConfig['eval']['maxlength'];
			}
			if (isset($fieldConfig['eval']['unique'])) {
				$fieldMapping['unique'] = (bool) $fieldConfig['eval']['unique'];
			}

			if (array_key_exists('field', $fieldConfig)) {
				$fieldMapping = array_merge($fieldMapping, $fieldConfig['field']);
			}

			$fieldMapping['id'] = $fieldName == 'id';
			$fieldMapping['fieldName'] = $fieldName;

			$metadata->mapField($fieldMapping);
		}

		if (TL_MODE == 'BE') {
			global $container;

			/** @var string $cacheDir */
			$cacheDir = $container['doctrine.orm.entitiesCacheDir'];

			static $entityGenerator;
			if (!$entityGenerator) {
				$entityGenerator = new EntityGenerator();
				$entityGenerator->setGenerateStubMethods(true);
				$entityGenerator->setFieldVisibility('protected');
				$entityGenerator->setRegenerateEntityIfExists($GLOBALS['TL_CONFIG']['debugMode'] || $GLOBALS['TL_CONFIG']['doctrineDevMode']);
				$entityGenerator->setUpdateEntityIfExists($GLOBALS['TL_CONFIG']['debugMode'] || $GLOBALS['TL_CONFIG']['doctrineDevMode']);
			}
			$entityGenerator->generate(array($metadata), $cacheDir);
		}
	}

	/**
	 * Gets the names of all mapped classes known to this driver.
	 *
	 * @return array The names of all mapped classes known to this driver.
	 */
	public function getAllClassNames()
	{
		if (array_key_exists('DOCTRINE_ENTITIES', $GLOBALS)) {
			return array_map(array($this, 'tableToClassName'), $GLOBALS['DOCTRINE_ENTITIES']);
		}
		return array();
	}

	/**
	 * Whether the class with the specified name should have its metadata loaded.
	 * This is only the case if it is either mapped as an Entity or a
	 * MappedSuperclass.
	 *
	 * @param string $className
	 *
	 * @return boolean
	 */
	public function isTransient($className)
	{
		if (array_key_exists('DOCTRINE_ENTITIES', $GLOBALS)) {
			$tableName = static::classToTableName($className);
			return in_array($tableName, $GLOBALS['DOCTRINE_ENTITIES']);
		}
		return false;
	}

	static public function tableToClassName($tableName)
	{
		$namespaceMap = array();
		if (array_key_exists('DOCTRINE_ENTITY_NAMESPACE_MAP', $GLOBALS)) {
			$namespaceMap = $GLOBALS['DOCTRINE_ENTITY_NAMESPACE_MAP'];
		}

		$parts = explode('_', $tableName);

		// remove tl_ prefix
		array_shift($parts);

		$partialTableName = 'tl';
		$className = '';

		foreach ($parts as $part) {
			$partialTableName .= '_' . $part;
			$className .= ucfirst($part);

			if (array_key_exists($partialTableName, $namespaceMap)) {
				$className = rtrim($namespaceMap[$partialTableName], '\\') . '\\';
			}
		}

		return $className;
	}

	static public function classToTableName($className)
	{
		$namespaceMap = array();
		if (array_key_exists('DOCTRINE_ENTITY_NAMESPACE_MAP', $GLOBALS)) {
			$namespaceMap = array_flip($GLOBALS['DOCTRINE_ENTITY_NAMESPACE_MAP']);
		}

		static $preg;
		if (!$preg) {
			$classes = array_keys($namespaceMap);
			$classes = array_map('preg_quote', $classes);
			$preg = '~^(' . implode('|', $classes) . ')\\\\(.*)$~s';
		}

		if (preg_match($preg, $className, $matches)) {
			$tableName = $namespaceMap[$matches[1]] . '_';
			$className = $matches[2];
		}
		else {
			$tableName = 'tl_';
		}

		$className = str_replace('\\', '', $className);
		preg_match_all('~[A-Z][a-z0-9_]*~', $className, $matches);
		$tableName .= implode('_', array_map('strtolower', $matches[0]));
		return $tableName;
	}
}
