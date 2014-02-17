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

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class EntityGenerator extends \Doctrine\ORM\Tools\EntityGenerator
{
	/**
	 * @var string
	 */
	static protected $constantsTemplate = '/**
 * The name the table this entity is stored in.
 */
const TABLE_NAME = \'<tableName>\';

/**
 * The names of the primary key fields.
 */
const PRIMARY_KEY = \'<key>\';
';

	/**
	 * @var string
	 */
	static protected $saveCallbacksTemplate = '/**
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
		self::$classTemplate = '<?php

<namespace>

<entityAnnotation>
<entityClassName> implements \Contao\Doctrine\ORM\EntityInterface
{
<constants>
<entityBody>

	/**
	 * {@inheritdoc}
	 */
	static public function entityTableName()
	{
		return static::TABLE_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	static public function entityPrimaryKeyNames()
	{
		return explode(\',\', static::PRIMARY_KEY);
	}
}';

		self::$getMethodTemplate = '/**
 * <description>
 *
 * @return <variableType>
 */
public function <methodName>()
{
<spaces>return \Contao\Doctrine\ORM\EntityHelper::callGetterCallbacks($this, self::TABLE_NAME, \'<variableName>\', $this-><fieldName>);
}';

		self::$setMethodTemplate = '/**
 * <description>
 *
 * @param <variableType>$<variableName>
 * @return <entity>
 */
public function <methodName>(<methodTypeHint>$<variableName><variableDefault>)
{
<spaces>$this-><fieldName> = \Contao\Doctrine\ORM\EntityHelper::callSetterCallbacks($this, self::TABLE_NAME, \'<variableName>\', $<variableName>);

<spaces>return $this;
}';

	}

	/**
	 * {@inheritdoc}
	 */
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
				'<constants>',
			),
			array(
				$prefixCodeWithSpacesMethod->invoke($this, $this->generateConstant($metadata)),
			),
			$code
		);

		return str_replace('<spaces>', $spacesProperty->getValue($this), $code);
	}

	protected function generateConstant(ClassMetadataInfo $metadata)
	{
		return str_replace(
			array(
				'<tableName>',
				'<key>',
			),
			array(
				$metadata->getTableName(),
				implode(',', $metadata->getIdentifierFieldNames())
			),
			static::$constantsTemplate
		);
	}

	/**
	 * {@inheritdoc}
	 */
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
			else if (isset($GLOBALS['TL_DCA'][$metadata->getTableName(
			)]['fields'][$fieldMapping['fieldName']]['default'])
			) {
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

	/**
	 * {@inheritdoc}
	 */
	protected function generateEntityStubMethod(
		ClassMetadataInfo $metadata,
		$type,
		$fieldName,
		$typeHint = null,
		$defaultValue = null
	) {
		$methodName = $type . Inflector::classify($fieldName);
		if (in_array($type, array("add", "remove"))) {
			$methodName = Inflector::singularize($methodName);
		}

		if ($this->hasMethod($methodName, $metadata)) {
			return '';
		}
		$this->staticReflection[$metadata->name]['methods'][] = $methodName;

		$var      = sprintf('%sMethodTemplate', $type);
		$template = self::$$var;

		$methodTypeHint = null;
		$types          = Type::getTypesMap();
		$variableType   = $typeHint ? $this->getType($typeHint) . ' ' : null;

		if ($typeHint && !isset($types[$typeHint])) {
			$variableType   = '\\' . ltrim($variableType, '\\');
			$methodTypeHint = '\\' . $typeHint . ' ';
		}
		else if ($variableType[0] == '\\') {
			$variableType   = '\\' . ltrim($variableType, '\\');
			$methodTypeHint = '\\' . ltrim($variableType, '\\');
		}

		if ($metadata->hasField($fieldName) && $metadata->isNullable($fieldName)) {
			$nullable = true;
		}
		else {
			$nullable = false;
		}

		$replacements = array(
			'<description>'     => ucfirst($type) . ' ' . $fieldName,
			'<methodTypeHint>'  => $methodTypeHint,
			'<variableType>'    => $variableType,
			'<variableName>'    => Inflector::camelize($fieldName),
			'<methodName>'      => $methodName,
			'<fieldName>'       => $fieldName,
			'<variableDefault>' => ($defaultValue !== null) ? (' = ' . $defaultValue) : ($nullable ? ' = null' : ''),
			'<entity>'          => $this->getClassName($metadata)
		);

		$method = str_replace(
			array_keys($replacements),
			array_values($replacements),
			$template
		);

		return $this->prefixCodeWithSpaces($method);
	}
}
