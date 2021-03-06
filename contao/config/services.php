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

/** @var Pimple $container */

$container['doctrine.orm.entityGeneratorFactory'] = $container->protect(
	function ($regenerate = false) {
		$entityGenerator = new \Contao\Doctrine\ORM\Install\EntityGenerator();
		$entityGenerator->setGenerateStubMethods(true);
		$entityGenerator->setFieldVisibility('protected');
		$entityGenerator->setRegenerateEntityIfExists($regenerate);
		$entityGenerator->setUpdateEntityIfExists($regenerate);

		return $entityGenerator;
	}
);

$container['doctrine.cache.orm'] = function ($container) {
	return $container['doctrine.cache.default'];
};

$container['doctrine.orm.entitiesCacheDir'] = $container->share(
	function ($container) {
		$entitiesCacheDir = TL_ROOT . '/system/cache/doctrine/entities';
		if (!is_dir($entitiesCacheDir)) {
			mkdir($entitiesCacheDir, 0777, true);
		}

		$classLoader = new \Composer\Autoload\ClassLoader();
		$classLoader->add('', array($entitiesCacheDir), true);
		$classLoader->register(true);
		$container['doctrine.orm.entitiesClassLoader'] = $classLoader;

		return $entitiesCacheDir;
	}
);

$container['doctrine.orm.proxiesCacheDir'] = $container->share(
	function ($container) {
		$proxiesCacheDir = TL_ROOT . '/system/cache/doctrine/proxies';
		if (!is_dir($proxiesCacheDir)) {
			mkdir($proxiesCacheDir, 0777, true);
		}

		return $proxiesCacheDir;
	}
);

$container['doctrine.orm.repositoriesCacheDir'] = $container->share(
	function ($container) {
		$repositoriesCacheDir = TL_ROOT . '/system/cache/doctrine/repositories';
		if (!is_dir($repositoriesCacheDir)) {
			mkdir($repositoriesCacheDir, 0777, true);
		}

		return $repositoriesCacheDir;
	}
);

$container['doctrine.orm.entityManager'] = $container->share(
	function ($container) {
		$isDevMode = $GLOBALS['TL_CONFIG']['debugMode'] || $GLOBALS['TL_CONFIG']['doctrineDevMode'];

		// create entity cache dir
		$entitiesCacheDir = $container['doctrine.orm.entitiesCacheDir'];

		// create proxy cache dir
		$proxiesCacheDir = $container['doctrine.orm.proxiesCacheDir'];

		$config = \Doctrine\ORM\Tools\Setup::createConfiguration(
			$isDevMode,
			$proxiesCacheDir,
			$container['doctrine.cache.orm']
		);
		$config->setMetadataDriverImpl(new \Contao\Doctrine\ORM\Mapping\Driver\ContaoDcaDriver($entitiesCacheDir));

		foreach (
			array(
				'ACOS'          => 'Acos',
				'ASIN'          => 'Asin',
				'ATAN'          => 'Atan',
				'ATAN2'         => 'Atan2',
				'BINARY'        => 'Binary',
				'CHAR_LENGTH'   => 'CharLength',
				'CONCAT_WS'     => 'ConcatWs',
				'COS'           => 'Cos',
				'COT'           => 'Cot',
				'COUNTIF'       => 'CountIf',
				'CRC32'         => 'Crc32',
				'DATE'          => 'Date',
				'DATE_FORMAT'   => 'DateFormat',
				'DAY'           => 'Day',
				'DEGREES'       => 'Degrees',
				'FIELD'         => 'Field',
				'FIND_IN_SET'   => 'FindInSet',
				'GROUP_CONCAT'  => 'GroupConcat',
				'HOUR'          => 'Hour',
				'IF'            => 'IfElse',
				'IFNULL'        => 'IfNull',
				'MATCH'         => 'MatchAgainst',
				'MD5'           => 'Md5',
				'MONTH'         => 'Month',
				'NULLIF'        => 'NullIf',
				'PI'            => 'Pi',
				'RADIANS'       => 'Radians',
				'RAND'          => 'Rand',
				'REGEXP'        => 'Regexp',
				'ROUND'         => 'Round',
				'SHA1'          => 'Sha1',
				'SHA2'          => 'Sha2',
				'SIN'           => 'Sin',
				'STR_TO_DATE'   => 'StrToDate',
				'TAN'           => 'Tan',
				'TIMESTAMPDIFF' => 'TimestampDiff',
				'WEEK'          => 'Week',
				'YEAR'          => 'Year',
			)
			as $function => $className
		) {
			$config->addCustomStringFunction($function, 'DoctrineExtensions\\Query\\Mysql\\' . $className);
		}

		if (array_key_exists('DOCTRINE_ENTITY_NAMESPACE_ALIAS', $GLOBALS) &&
			is_array($GLOBALS['DOCTRINE_ENTITY_NAMESPACE_ALIAS'])
		) {
			foreach ($GLOBALS['DOCTRINE_ENTITY_NAMESPACE_ALIAS'] as $alias => $namespace) {
				$config->addEntityNamespace($alias, $namespace);
			}
		}

		if (array_key_exists('TL_HOOKS', $GLOBALS) &&
			array_key_exists('prepareDoctrineEntityManager', $GLOBALS['TL_HOOKS']) &&
			is_array($GLOBALS['TL_HOOKS']['prepareDoctrineEntityManager'])
		) {
			foreach ($GLOBALS['TL_HOOKS']['prepareDoctrineEntityManager'] as $callback) {
				$object = method_exists($callback[0], 'getInstance') ? call_user_func(
					array($callback[0], 'getInstance')
				) : new $callback[0];
				$object->$callback[1]($config);
			}
		}

		/** @var \Doctrine\DBAL\Connection $connection */
		$connection = $container['doctrine.connection.default'];

		/** @var \Doctrine\Common\EventManager $eventManager */
		$eventManager = $container['doctrine.eventManager'];

		// very late bind version manager
		$eventManager->addEventSubscriber(new \Contao\Doctrine\ORM\VersioningListener());

		return \Doctrine\ORM\EntityManager::create($connection, $config, $eventManager);
	}
);

$container['doctrine.orm.entitySerializer.eventSubscribers'] = new ArrayObject();

$container['doctrine.orm.entitySerializer.subscribingHandlers'] = new ArrayObject(
	array(
		'Contao\Doctrine\ORM\Serializer\EntitySubscribingHandler',
	)
);

$container['doctrine.orm.entitySerializer'] = $container->share(
	function ($container) {
		$builder = \JMS\Serializer\SerializerBuilder::create();
		$builder->setCacheDir(TL_ROOT . '/system/tmp');
		$builder->addDefaultHandlers();
		$builder->addDefaultListeners();
		$builder->addDefaultSerializationVisitors();
		$builder->addDefaultDeserializationVisitors();
		$builder->configureListeners(
			function (\JMS\Serializer\EventDispatcher\EventDispatcher $eventDispatcher) use ($container) {
				foreach (
					$container['doctrine.orm.entitySerializer.eventSubscribers'] as
					$eventSubscriberClassName
				) {
					$eventSubscriberClass = new ReflectionClass($eventSubscriberClassName);
					$eventSubscriber      = $eventSubscriberClass->newInstance();

					$eventDispatcher->addSubscriber($eventSubscriber);
				}
			}
		);
		$builder->configureHandlers(
			function (\JMS\Serializer\Handler\HandlerRegistry $registry) use ($container) {
				foreach (
					$container['doctrine.orm.entitySerializer.subscribingHandlers'] as
					$subscribingHandlerClassName
				) {
					$subscribingHandlerClass = new ReflectionClass($subscribingHandlerClassName);
					$subscribingHandler      = $subscribingHandlerClass->newInstance();

					$registry->registerSubscribingHandler($subscribingHandler);
				}
			}
		);
		return $builder->build();
	}
);

$container['doctrine.orm.entityAccessor'] = $container->share(
	function () {
		class_exists('Contao\Doctrine\ORM\Annotation\Accessor');

		$annotationReader = new \Doctrine\Common\Annotations\AnnotationReader();
		return new \Contao\Doctrine\ORM\EntityAccessor($annotationReader);
	}
);

$container['doctrine.orm.versionManager'] = $container->share(
	function () {
		return new \Contao\Doctrine\ORM\VersionManager();
	}
);

$container['doctrine.orm.logger.handler.general'] = function ($container) {
	$factory = $container['logger.factory.handler.group'];
	return $factory($container['logger.default.handlers']);
};

$container['doctrine.orm.logger.default.handlers'] = new ArrayObject(
	array('doctrine.orm.logger.handler.general')
);

$container['doctrine.orm.logger'] = function ($container) {
	$factory = $container['logger.factory'];
	$logger  = $factory('orm', $container['doctrine.orm.logger.default.handlers']);

	return $logger;
};
