<?php

namespace Kunstmaan\NodeBundle\EventListener;

use Kunstmaan\AdminBundle\Event\ConfigureMenuChildrenEvent;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;
use Kunstmaan\NodeBundle\Helper\NodeMenu;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityManager;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfigureMenuChildrenListener
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var \SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @param Request                   $request
     * @param EntityManager             $em
     * @param SecurityContextInterface  $securityContext
     * @param AclHelper                 $aclHelper
     */
    public function __construct(Request $request, EntityManager $em, SecurityContextInterface $securityContext, AclHelper $aclHelper)
    {
        $this->request = $request;
        $this->em = $em;
        $this->securityContext = $securityContext;
        $this->aclHelper       = $aclHelper;
    }

    /**
     * @param ConfigureMenuChildrenEvent $event
     */
    public function onMenuChildrenConfigure(ConfigureMenuChildrenEvent $event)
    {
        $menuParentNames = array();
        foreach($this->getParents($event->getMenu()) as $parent){
            $menuParentNames[] = $parent->getName();
        }
        if ('Pages' == $event->getMenu()->getName() or in_array('Pages', $menuParentNames)) {
            if ('Pages' == $event->getMenu()->getName()) {
                $nodeMenu = new NodeMenu($this->em, $this->securityContext, $this->aclHelper, $this->request->getLocale(), null, PermissionMap::PERMISSION_EDIT, true, true);
                foreach ($nodeMenu->getTopNodes() as $child) {
                    $childMenu = $event->getMenu()->addChild($event->getFactory()->createItem($child->getTitle(), array('route' => 'KunstmaanNodeBundle_nodes_edit', 'routeParameters' => array('id' =>  $child->getId()))));
                    $childMenu->setExtra('id', $child->getId());
                    $childMenu->setAttribute('rel', 'page');
                }
            } else if(in_array('Pages', $menuParentNames)){
                $menuNode = $this->em->getRepository('KunstmaanNodeBundle:Node')->findOneById($event->getMenu()->getExtra('id'));
                $children = $this->em->getRepository('KunstmaanNodeBundle:Node')->getChildNodes($menuNode->getId(), $this->request->getLocale(), PermissionMap::PERMISSION_EDIT, $this->aclHelper, true);
                foreach ($children as $child) {
                    $nodeTranslation = $child->getNodeTranslation($this->request->getLocale(), true);
                    if (!is_null($nodeTranslation)) {
                        $childMenu = $event->getMenu()->addChild($event->getFactory()->createItem($nodeTranslation->getTitle(), array('route' => 'KunstmaanNodeBundle_nodes_edit', 'routeParameters' => array('id' =>  $child->getId()))));
                        $childMenu->setExtra('id', $child->getId());
                        $childMenu->setAttribute('rel', 'page');
                    }
                }
            }
        }
    }

    /**
     * Fills the array with the parents of the given ItemInterface
     * @param ItemInterface $menu
     *
     * @return array
     */
    public function getParents(ItemInterface $menu){
        $parent  = $menu->getParent();
        $parents = array();
        while ($parent != null) {
            $parents[] = $parent;
            $parent    = $parent->getParent();
        }

        return array_reverse($parents);
    }
}