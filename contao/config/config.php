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
 * Console commands
 */
$GLOBALS['CONSOLE_CMD'][] = 'Contao\Doctrine\ORM\Command\GenerateEntitiesCommand';
$GLOBALS['CONSOLE_CMD'][] = 'Contao\Doctrine\ORM\Command\GenerateRepositoriesCommand';
$GLOBALS['CONSOLE_CMD'][] = 'Contao\Doctrine\ORM\Command\GenerateProxiesCommand';


/**
 * Field types
 */
$GLOBALS['DOCTRINE_TYPE_MAP']['text']               = 'string';
$GLOBALS['DOCTRINE_TYPE_MAP']['text_multiple']      = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['password']           = 'string';
$GLOBALS['DOCTRINE_TYPE_MAP']['password_multiple']  = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['textStore']          = 'string';
$GLOBALS['DOCTRINE_TYPE_MAP']['textStore_multiple'] = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['textarea']           = 'text';
$GLOBALS['DOCTRINE_TYPE_MAP']['select']             = 'text';
$GLOBALS['DOCTRINE_TYPE_MAP']['select_multiple']    = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['select_foreignKey']  = 'integer';
$GLOBALS['DOCTRINE_TYPE_MAP']['checkbox']           = 'boolean';
$GLOBALS['DOCTRINE_TYPE_MAP']['checkbox_multiple']  = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['checkboxWizard']     = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['radio']              = 'text';
$GLOBALS['DOCTRINE_TYPE_MAP']['radio_multiple']     = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['radio_foreignKey']   = 'integer';
$GLOBALS['DOCTRINE_TYPE_MAP']['radioTable']         = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['inputUnit']          = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['trbl']               = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['chmod']              = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['pageTree']           = 'integer';
$GLOBALS['DOCTRINE_TYPE_MAP']['pageTree_multiple']  = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['fileTree']           = 'text';
$GLOBALS['DOCTRINE_TYPE_MAP']['fileTree_multiple']  = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['tableWizard']        = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['listWizard']         = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['optionWizard']       = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['moduleWizard']       = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['keyValueWizard']     = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['imageSize']          = 'array';
$GLOBALS['DOCTRINE_TYPE_MAP']['timePeriod']         = 'array';
