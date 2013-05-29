Doctrine ORM Bridge
===================

This extension provide [Doctrine ORM](http://www.doctrine-project.org) in the [Contao Open Source CMS](http://contao.org).
It provide an entity manager via the service `$container['doctrine.orm.entityManager']`.
To use the Doctrine Connection within the Contao Database Framework, use [bit3/contao-doctrine-dbal-driver](https://github.com/bit3/contao-doctrine-dbal-driver).

Register entities
-----------------

```php
$GLOBALS['DOCTRINE_ENTITIES']['MyEntityClassName'] = 'tl_entity_table';
```

Configure entities via DCA
--------------------------

```php
<?php

$GLOBALS['TL_DCA']['...'] = array(
	'entity' => array(
		// (optional) Repository class name
		'repositoryClass' => 'MyEntityRepositoryClassName',
		// (optional) ID generator type (AUTO, IDENTITY, UUID)
		'idGenerator'     => 'UUID',
	),
	'fields' => array(
		'...' => array(
			'field' => array(
				'type' => (string),
				// do not set fieldName!
				'
			),
		),
	),
);
```

Contao hooks
------------

`$GLOBALS['TL_HOOK']['prepareDoctrineEntityManager'] = function(\Doctrine\ORM\Configuration &$config) { ... }`
Called before the entity manager will be created.
