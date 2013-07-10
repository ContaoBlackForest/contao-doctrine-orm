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

namespace Contao\Doctrine\ORM\Serializer;

use Contao\Doctrine\ORM\Entity;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\scalar;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

class EntityNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function normalize($object, $format = null, array $context = array())
	{
		$attributes = $object->toArray();

		foreach ($attributes as $key => $value) {
			if (null !== $value && !is_scalar($value)) {
				$attributes[$key] = $this->serializer->normalize($value, $format);
			}
		}

		return $attributes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function denormalize($data, $class, $format = null, array $context = array())
	{
		$reflectionClass = new \ReflectionClass($class);
		$entity = $reflectionClass->newInstance();

		foreach ($data as $key => $value) {
			$setter = 'set' . ucfirst($key);
			if ($reflectionClass->hasMethod($setter)) {
				$setter = $reflectionClass->getMethod($setter);

				$parameters = $setter->getParameters();
				$parameter = $parameters[0];

				$type = $parameter->getClass();
				if ($type) {
					$value = $this->serializer->denormalize($value, $type->getName(), $format, $context);
				}

				$setter->invoke($entity, $value);
			}
			else if ($reflectionClass->hasProperty($key)) {
				$property = $reflectionClass->getProperty($key);
				$property->setAccessible(true);
				$property->setValue($entity, $value);
			}
			// otherwise ignore, we expect that a previous field does not exists anymore
		}

		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsNormalization($data, $format = null)
	{
		return $data instanceof Entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsDenormalization($data, $type, $format = null)
	{
		try {
			$class = new \ReflectionClass($type);
			return $class->isSubclassOf('Contao\Doctrine\ORM\Entity');
		}
		catch (\ReflectionException $e) {
			return false;
		}
	}
}
