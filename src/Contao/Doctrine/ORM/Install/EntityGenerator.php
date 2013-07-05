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

namespace Contao\Doctrine\ORM\Install;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class EntityGenerator extends \Doctrine\ORM\Tools\EntityGenerator
{
	/**
	 * @var string
	 */
	static protected $tableNameTemplate =
'/**
 * The table name this entity is stored in
 */
const TABLE_NAME = \'<tableName>\';
';

	/**
	 * @var string
	 */
	static protected $saveCallbacksTemplate =
'/**
 * The table name this entity is stored in
 */
protected static $__saveCallbacks = <saveCallbacks>;
';

	public function __construct()
	{
		parent::__construct();

		$this->typeAlias['timestamp']      = '\DateTime';
		$this->typeAlias['contao-boolean'] = 'bool';

		// hack until https://github.com/doctrine/doctrine2/pull/719 is merged
		self::$classTemplate =
'<?php

<namespace>

use Doctrine\ORM\Mapping as ORM;

<entityAnnotation>
<entityClassName>
{
<tableName>
<entityBody>
}';

		self::$getMethodTemplate =
'/**
 * <description>
 *
 * @return <variableType>
 */
public function <methodName>()
{
<spaces>return $this->callLoadCallbacks(\'<variableName>\', $this-><fieldName>);
}';

		self::$setMethodTemplate =
'/**
 * <description>
 *
 * @param <variableType>$<variableName>
 * @return <entity>
 */
public function <methodName>(<methodTypeHint>$<variableName><variableDefault>)
{
<spaces>$this-><fieldName> = $this->callSaveCallbacks(\'<variableName>\', $<variableName>);

<spaces>return $this;
}';

	}

	public function generateEntityClass(ClassMetadataInfo $metadata)
	{
		$code = parent::generateEntityClass($metadata);

		$class = new \ReflectionClass('Doctrine\ORM\Tools\EntityGenerator');

		$spacesProperty = $class->getProperty('spaces');
		$spacesProperty->setAccessible(true);

		$prefixCodeWithSpacesMethod = $class->getMethod('prefixCodeWithSpaces');
		$prefixCodeWithSpacesMethod->setAccessible(true);

		$code = str_replace(
			array(
				 '<tableName>',
			),
			array(
				 $prefixCodeWithSpacesMethod->invoke($this, $this->generateTableNameConstant($metadata)),
			),
			$code
		);

		return str_replace('<spaces>', $spacesProperty->getValue($this), $code);
	}

	protected function generateTableNameConstant(ClassMetadataInfo $metadata)
	{
		return str_replace(
			array(
				 '<tableName>',
			),
			array(
				 $metadata->getTableName(),
			),
			static::$tableNameTemplate
		);
	}

	protected function generateEntityFieldMappingProperties(ClassMetadataInfo $metadata)
	{
		$lines = array();

		foreach ($metadata->fieldMappings as $fieldMapping) {
			if ($this->hasProperty($fieldMapping['fieldName'], $metadata) ||
				$metadata->isInheritedField($fieldMapping['fieldName'])
			) {
				continue;
			}

			if (isset($fieldMapping['default'])) {
				$default = ' = ' . var_export($fieldMapping['default'], true);
			}
			else if (isset($GLOBALS['TL_DCA'][$metadata->getTableName()]['fields'][$fieldMapping['fieldName']]['default'])) {
				$default = ' = ' . var_export(
						$GLOBALS['TL_DCA'][$metadata->getTableName()]['fields'][$fieldMapping['fieldName']]['default'],
						true
					);
			}
			else {
				$default = '';
			}

			$lines[] = $this->generateFieldMappingPropertyDocBlock($fieldMapping, $metadata);
			$lines[] = $this->spaces . $this->fieldVisibility . ' $' . $fieldMapping['fieldName']
				. $default . ";\n";
		}

		return implode("\n", $lines);
	}
}
