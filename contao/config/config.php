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
$GLOBALS['TL_HOOKS']['initializeConsole'][] = array('Contao\Doctrine\ORM\InitializeConsole', 'hookInitializeConsole');
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
$GLOBALS['DOCTRINE_TYPE_MAP']['text']               = 'string';
$GLOBALS['DOCTRINE_TYPE_MAP']['text_multiple']      = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['password']           = 'string';
$GLOBALS['DOCTRINE_TYPE_MAP']['password_multiple']  = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['textStore']          = 'string';
$GLOBALS['DOCTRINE_TYPE_MAP']['textStore_multiple'] = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['textarea']           = 'text';
$GLOBALS['DOCTRINE_TYPE_MAP']['select']             = 'text';
$GLOBALS['DOCTRINE_TYPE_MAP']['select_multiple']    = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['select_foreignKey']  = 'integer';
$GLOBALS['DOCTRINE_TYPE_MAP']['checkbox']           = 'contaoBoolean';
$GLOBALS['DOCTRINE_TYPE_MAP']['checkbox_multiple']  = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['checkboxWizard']     = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['radio']              = 'text';
$GLOBALS['DOCTRINE_TYPE_MAP']['radio_multiple']     = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['radio_foreignKey']   = 'integer';
$GLOBALS['DOCTRINE_TYPE_MAP']['radioTable']         = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['inputUnit']          = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['trbl']               = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['chmod']              = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['pageTree']           = 'integer';
$GLOBALS['DOCTRINE_TYPE_MAP']['pageTree_multiple']  = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['fileTree']           = 'text';
$GLOBALS['DOCTRINE_TYPE_MAP']['fileTree_multiple']  = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['tableWizard']        = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['listWizard']         = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['optionWizard']       = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['moduleWizard']       = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['keyValueWizard']     = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['imageSize']          = 'serialized';
$GLOBALS['DOCTRINE_TYPE_MAP']['timePeriod']         = 'serialized';
