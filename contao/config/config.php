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


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['initializeConsole'][]  = array('Contao\Doctrine\ORM\InitializeConsole', 'hookInitializeConsole');
$GLOBALS['TL_HOOKS']['sqlCompileCommands'][] = array('Contao\Doctrine\ORM\Install\DbTool', 'hookSqlCompileCommands');


/**
 * Console commands
 */
$GLOBALS['CONSOLE_CMD'][] = 'Contao\Doctrine\ORM\Command\GenerateEntitiesCommand';
$GLOBALS['CONSOLE_CMD'][] = 'Contao\Doctrine\ORM\Command\GenerateRepositoriesCommand';
$GLOBALS['CONSOLE_CMD'][] = 'Contao\Doctrine\ORM\Command\GenerateProxiesCommand';
$GLOBALS['CONSOLE_CMD'][] = 'Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand';
$GLOBALS['CONSOLE_CMD'][] = 'Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand';
$GLOBALS['CONSOLE_CMD'][] = 'Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand';
$GLOBALS['CONSOLE_CMD'][] = 'Doctrine\ORM\Tools\Console\Command\RunDqlCommand';
$GLOBALS['CONSOLE_CMD'][] = 'Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand';
$GLOBALS['CONSOLE_CMD'][] = 'Doctrine\ORM\Tools\Console\Command\InfoCommand';


/**
 * Field types
 */
$GLOBALS['DOCTRINE_TYPE_MAP']['text']               = array('type' => 'string');
$GLOBALS['DOCTRINE_TYPE_MAP']['text_multiple']      = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['password']           = array('type' => 'string');
$GLOBALS['DOCTRINE_TYPE_MAP']['password_multiple']  = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['textStore']          = array('type' => 'string');
$GLOBALS['DOCTRINE_TYPE_MAP']['textStore_multiple'] = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['textarea']           = array('type' => 'text');
$GLOBALS['DOCTRINE_TYPE_MAP']['select']             = array('type' => 'string');
$GLOBALS['DOCTRINE_TYPE_MAP']['select_multiple']    = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['select_foreignKey']  = array('type' => 'integer');
$GLOBALS['DOCTRINE_TYPE_MAP']['checkbox']           = array('type' => 'contaoBoolean');
$GLOBALS['DOCTRINE_TYPE_MAP']['checkbox_multiple']  = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['checkboxWizard']     = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['radio']              = array('type' => 'string');
$GLOBALS['DOCTRINE_TYPE_MAP']['radio_multiple']     = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['radio_foreignKey']   = array('type' => 'integer');
$GLOBALS['DOCTRINE_TYPE_MAP']['radioTable']         = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['inputUnit']          = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['trbl']               = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['chmod']              = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['pageTree']           = array('type' => 'integer');
$GLOBALS['DOCTRINE_TYPE_MAP']['pageTree_multiple']  = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['fileTree']           = array('type' => 'text');
$GLOBALS['DOCTRINE_TYPE_MAP']['fileTree_multiple']  = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['tableWizard']        = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['listWizard']         = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['optionWizard']       = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['moduleWizard']       = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['keyValueWizard']     = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['imageSize']          = array('type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['timePeriod']         = array('type' => 'serialized', 'length' => 65532);


/**
 * Entity parent class
 */
$GLOBALS['DOCTRINE_ENTITY_CLASS'][''] = 'Contao\Doctrine\ORM\Entity';


/**
 * Entities
 */
$GLOBALS['DOCTRINE_ENTITY_NAMESPACE_ALIAS']['ORM']       = 'ORM\Entity';
$GLOBALS['DOCTRINE_ENTITY_NAMESPACE_MAP']['orm_version'] = 'ORM\Entity\Version';
$GLOBALS['DOCTRINE_ENTITIES'][]                          = 'orm_version';


/**
 * Ignored tables
 */
$GLOBALS['DOCTRINE_IGNORE_TABLE'][]  = 'tl_';
$GLOBALS['DOCTRINE_IGNORE_TABLE'][]  = 'mm_';
