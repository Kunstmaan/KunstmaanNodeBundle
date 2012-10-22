<?php

namespace Kunstmaan\NodeBundle\AdminList;

use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM\DateFilterType;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM\BooleanFilterType;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM\StringFilterType;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionDefinition;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AbstractDoctrineORMAdminListConfigurator;
use Kunstmaan\NodeBundle\Entity\Node;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * NodeAdminListConfigurator
 */
class NodeAdminListConfigurator extends AbstractDoctrineORMAdminListConfigurator
{

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $permission;

    /**
     * @param EntityManager $em         The entity manager
     * @param AclHelper     $aclHelper  The ACL helper
     * @param string        $locale     The current locale
     * @param string        $permission The permission
     */
    public function __construct(EntityManager $em, AclHelper $aclHelper, $locale, $permission)
    {
        parent::__construct($em, $aclHelper);
        $this->locale = $locale;
        $this->setPermissionDefinition(
            new PermissionDefinition(array($permission), 'Kunstmaan\NodeBundle\Entity\Node', 'n')
        );
    }

    /**
     * Configure filters
     */
    public function buildFilters()
    {
        $this->addFilter('title', new StringFilterType('title'), 'Title')
            ->addFilter('online', new BooleanFilterType('online'), 'Online')
            ->addFilter('created', new DateFilterType('created'), 'Created At')
            ->addFilter('updated', new DateFilterType('updated'), 'Updated At');
    }

    /**
     * Configure the visible columns
     */
    public function buildFields()
    {
        $this->addField('title', 'Title', true)
            ->addField('created', 'Created At', true)
            ->addField('updated', 'Updated At', true)
            ->addField('online', 'Online', true);
    }

    /**
     * @param mixed $item
     *
     * @return array
     */
    public function getEditUrlFor($item)
    {
        /* @var Node $node */
        $node = $item->getNode();

        return array(
            'path'   => 'KunstmaanNodeBundle_nodes_edit',
            'params' => array('id' => $node->getId())
        );
    }

    /**
     * @return bool
     */
    public function canAdd()
    {
        return false;
    }

    /**
     * Return if current user can delete the specified item
     *
     * @param array|object $item
     *
     * @return bool
     */
    public function canDelete($item)
    {
        return false;
    }

    /**
     * @param object $item
     *
     * @return array
     */
    public function getDeleteUrlFor($item)
    {
        return array();
    }

    /**
     * @return string
     */
    public function getBundleName()
    {
        return 'KunstmaanNodeBundle';
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return 'NodeTranslation';
    }

    /**
     * Override path convention (because settings is a virtual admin subtree)
     *
     * @param string $suffix
     *
     * @return string
     */
    public function getPathByConvention($suffix = null)
    {
        if (empty($suffix)) {
            return sprintf('%s_pages', $this->getBundleName());
        }

        return sprintf('%s_pages_%s', $this->getBundleName(), $suffix);
    }

    /**
     * Override controller path (because actions for different entities are defined in a single Settings controller).
     *
     * @return string
     */
    public function getControllerPath()
    {
        return 'KunstmaanNodeBundle:NodeAdmin';
    }

    /**
     * @param QueryBuilder $queryBuilder The query builder
     */
    public function adaptQueryBuilder(QueryBuilder $queryBuilder)
    {
        parent::adaptQueryBuilder($queryBuilder);

        $queryBuilder->innerJoin('b.node', 'n', 'WITH', 'b.node = n.id');
        $queryBuilder->andWhere('b.lang = :lang');
        $queryBuilder->andWhere('n.deleted = 0');
        $queryBuilder->setParameter('lang', $this->locale);
    }

}
