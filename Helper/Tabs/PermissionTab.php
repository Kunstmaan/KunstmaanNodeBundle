<?php

namespace Kunstmaan\NodeBundle\Tabs;

use Doctrine\ORM\EntityManager;

use Kunstmaan\NodeBundle\Entity\HasNodeInterface;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionAdmin;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMapInterface;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

class PermissionTab implements TabInterface
{

    /**
     * @var PermissionAdmin
     */
    protected $permissionAdmin;

    /**
     * @var PermissionMapInterface
     */
    protected $permissionMap;

    /**
     * @var HasNodeInterface
     */
    protected $node;

    /**
     * @var string
     */
    protected $title;

    /**
     * @param string                 $title           The title
     * @param HasNodeInterface       $node            The node
     * @param PermissionAdmin        $permissionAdmin The permission admin
     * @param PermissionMapInterface $permissionMap   The permission map
     */
    function __construct($title, HasNodeInterface $node, PermissionAdmin $permissionAdmin, PermissionMapInterface $permissionMap)
    {
        $this->title = $title;
        $this->node = $node;
        $this->permissionAdmin = $permissionAdmin;
        $this->permissionMap = $permissionMap;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param FormBuilderInterface $builder The form builder
     * @param Request              $request The request
     */
    public function buildForm(FormBuilderInterface $builder, Request $request)
    {
        $this->permissionAdmin->initialize($this->node, $this->permissionMap);
    }

    /**
     * @param Request $request
     */
    public function bindRequest(Request $request)
    {
        $this->permissionAdmin->bindRequest($request);
    }

    /**
     * @param EntityManager $em
     * @param Request $request
     */
    public function persist(EntityManager $em, Request $request)
    {
    }

    /**
     * @param FormView $formView
     *
     * @return array
     */
    public function getFormErrors(FormView $formView)
    {
        return array();
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return 'KunstmaanNodeBundle:Tabs:permission_tab.html.twig';
    }
}