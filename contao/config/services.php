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

$container['doctrine.cache.orm'] = function($container) {
	return $container['doctrine.cache.default'];
};

$container['doctrine.orm.entitiesCacheDir'] = $container->share(
	function($container) {
		$entitiesCacheDir = TL_ROOT . '/system/cache/doctrine/entities';
		if (!is_dir($entitiesCacheDir)) {
			mkdir($entitiesCacheDir, 0777, true);
		}
		return $entitiesCacheDir;
	}
);

$container['doctrine.orm.proxiesCacheDir'] = $container->share(
	function($container) {
		$proxiesCacheDir = TL_ROOT . '/system/cache/doctrine/proxies';
		if (!is_dir($proxiesCacheDir)) {
			mkdir($proxiesCacheDir, 0777, true);
		}
		return $proxiesCacheDir;
	}
);

$container['doctrine.orm.repositoriesCacheDir'] = $container->share(
	function($container) {
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

		if (array_key_exists('TL_HOOK', $GLOBALS) && array_key_exists('prepareDoctrineEntityManager', $GLOBALS['TL_HOOK']) && is_array($GLOBALS['prepareDoctrineEntityManager']['prepareDoctrineConnection'])) {
			foreach ($GLOBALS['TL_HOOK']['prepareDoctrineEntityManager'] as $callback) {
				$object = method_exists($callback[0], 'getInstance') ? call_user_func(array($callback[0], 'getInstance')) : new $callback[0];
				$object->$callback[1]($config);
			}
		}

		return \Doctrine\ORM\EntityManager::create($container['doctrine.connection.default'], $config);
	}
);
