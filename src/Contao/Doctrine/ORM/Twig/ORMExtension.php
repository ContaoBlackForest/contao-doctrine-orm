<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace Contao\Doctrine\ORM\Twig;

use Contao\Doctrine\ORM\EntityAccessor;
use Contao\Doctrine\ORM\EntityInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Extension for twig template engine.
 */
class ORMExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'doctrine-orm';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('serializeEntityId', array($this, 'serializeEntityId')),
        );
    }

    /**
     * Serialize an entity and return its ID.
     *
     * @param EntityInterface $entity
     *
     * @return string
     */
    public function serializeEntityId(EntityInterface $entity)
    {
        /** @var EntityAccessor $entityAccessor */
        $entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

        $serializer = new IdSerializer();
        $serializer->setDataProviderName($entity->entityTableName());
        $serializer->setId($entityAccessor->getPrimaryKey($entity));
        return $serializer->getSerialized();
    }
}
