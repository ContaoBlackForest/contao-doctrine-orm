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

class DateTimeNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function normalize($object, $format = null, array $context = array())
	{
		return $object->format('c');
	}

	/**
	 * {@inheritdoc}
	 */
	public function denormalize($data, $class, $format = null, array $context = array())
	{
		$datetime = \DateTime::createFromFormat('Y-m-d*H:i:sP', $data);

		if ($datetime) {
			return $datetime;
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsNormalization($data, $format = null)
	{
		return $data instanceof \DateTime;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsDenormalization($data, $type, $format = null)
	{
		return is_string($data) && ($type == 'DateTime' || preg_match('~^\d\d\d\d-\d\d-\d\dT\d\d:\d\d:\d\d+\d\d:\d\d$~', $data));
	}
}
