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
 * Entity orm_version
 */
$GLOBALS['TL_DCA']['orm_version'] = array(
	// Entity
	'entity' => array(
		'idGenerator' => \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_UUID
	),
	// Fields
	'fields' => array
	(
		'id'          => array(
			'field' => array(
				'id'   => true,
				'type' => 'string',
			)
		),
		'entityClass' => array
		(
			'field' => array(
				'type' => 'string',
			)
		),
		'entityId'    => array(
			'field' => array(
				'type' => 'string',
			)
		),
		'entityHash'        => array
		(
			'field' => array(
				'type'    => 'string',
				'length'  => '32',
				'options' => array('fixed' => true),
			)
		),
		'action'      => array
		(
			'field' => array(
				'type'    => 'string',
				'length'  => 6,
				'options' => array('fixed' => true),
			)
		),
		'createdAt'   => array(
			'field' => array(
				'type'          => 'datetime',
				'timestampable' => array('on' => 'create')
			)
		),
		'previous'    => array
		(
			'field' => array(
				'type'     => 'string',
				'length'   => '36',
				'options'  => array('fixed' => true),
				'nullable' => true,
			)
		),
		'data'        => array
		(
			'field' => array(
				'type' => 'text'
			)
		),
	)
);
