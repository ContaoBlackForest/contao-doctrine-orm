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
 * System configuration
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{doctrine_orm_legend:hide},doctrineDevMode';
$GLOBALS['TL_DCA']['tl_settings']['fields']['doctrineDevMode'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_settings']['doctrineDevMode'],
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50 m12')
);
