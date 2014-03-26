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
 * Event subscribers
 */
$GLOBALS['TL_EVENT_SUBSCRIBERS'][] = 'Contao\Doctrine\ORM\Subscriber';


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
$GLOBALS['DOCTRINE_TYPE_MAP']['text']               = array('nullable' => true, 'type' => 'string');
$GLOBALS['DOCTRINE_TYPE_MAP']['text_multiple']      = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['password']           = array('nullable' => true, 'type' => 'string');
$GLOBALS['DOCTRINE_TYPE_MAP']['password_multiple']  = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['textStore']          = array('nullable' => true, 'type' => 'string');
$GLOBALS['DOCTRINE_TYPE_MAP']['textStore_multiple'] = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['textarea']           = array('nullable' => true, 'type' => 'text');
$GLOBALS['DOCTRINE_TYPE_MAP']['select']             = array('nullable' => true, 'type' => 'string');
$GLOBALS['DOCTRINE_TYPE_MAP']['select_multiple']    = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['select_foreignKey']  = array('nullable' => true, 'type' => 'integer');
$GLOBALS['DOCTRINE_TYPE_MAP']['checkbox']           = array('nullable' => true, 'type' => 'boolean');
$GLOBALS['DOCTRINE_TYPE_MAP']['checkbox_multiple']  = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['checkboxWizard']     = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['radio']              = array('nullable' => true, 'type' => 'string');
$GLOBALS['DOCTRINE_TYPE_MAP']['radio_multiple']     = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['radio_foreignKey']   = array('nullable' => true, 'type' => 'integer');
$GLOBALS['DOCTRINE_TYPE_MAP']['radioTable']         = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['inputUnit']          = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['trbl']               = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['chmod']              = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['pageTree']           = array('nullable' => true, 'type' => 'integer');
$GLOBALS['DOCTRINE_TYPE_MAP']['pageTree_multiple']  = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['fileTree']           = array('nullable' => true, 'type' => 'binaryString', 'length' => 512);
$GLOBALS['DOCTRINE_TYPE_MAP']['fileTree_multiple']  = array('nullable' => true, 'type' => 'serializedBinary', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['tableWizard']        = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['listWizard']         = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['optionWizard']       = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['moduleWizard']       = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['keyValueWizard']     = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['imageSize']          = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);
$GLOBALS['DOCTRINE_TYPE_MAP']['timePeriod']         = array('nullable' => true, 'type' => 'serialized', 'length' => 65532);


/**
 * Entities
 */
$GLOBALS['DOCTRINE_ENTITY_NAMESPACE_ALIAS']['ORM']       = 'ORM\Entity';
$GLOBALS['DOCTRINE_ENTITY_NAMESPACE_MAP']['orm_version'] = 'ORM\Entity\Version';
$GLOBALS['DOCTRINE_ENTITY_CLASS']['ORM\Entity\Version']  = 'Contao\Doctrine\ORM\Entity\AbstractVersion';
$GLOBALS['DOCTRINE_ENTITIES'][]                          = 'orm_version';


/**
 * Managed tables
 */
$GLOBALS['DOCTRINE_MANAGED_TABLE'][]  = 'orm_';
