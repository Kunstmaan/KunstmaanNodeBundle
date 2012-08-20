<?php

namespace Kunstmaan\AdminNodeBundle\Helper\Menu;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Kunstmaan\AdminNodeBundle\Modules\NodeMenu;
use Symfony\Component\HttpFoundation\Request;
use Kunstmaan\AdminBundle\Helper\Menu\MenuBuilder;
use Kunstmaan\AdminBundle\Helper\Menu\MenuItem;
use Kunstmaan\AdminBundle\Helper\Menu\MenuAdaptorInterface;
use Kunstmaan\AdminBundle\Helper\Menu\TopMenuItem;
use Symfony\Component\Translation\Translator;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Knp\Menu\ItemInterface as KnpMenu;
use Kunstmaan\AdminNodeBundle\Entity\Node;
/**
 * The Page Menu Adaptor
 */
class PageMenuAdaptor implements MenuAdaptorInterface
{
    private $container;
    private $nodemenu;

    /**
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * In this method you can add children for a specific parent, but also remove and change the already created children
     *
     * @param MenuBuilder $menu      The MenuBuilder
     * @param MenuItem[]  &$children The current children
     * @param MenuItem    $parent    The parent Menu item
     * @param Request     $request   The Request
     */
    public function adaptChildren(MenuBuilder $menu, array &$children, MenuItem $parent = null, Request $request = null)
    {
        if (is_null($this->nodemenu)) {
            $node = null;
            if ($request->attributes->get('_route') == 'KunstmaanAdminNodeBundle_pages_edit') {
                $node = $this->container->get("doctrine")->getEntityManager()->getRepository('KunstmaanAdminNodeBundle:Node')->findOneById($request->attributes->get('id'));
            }
            $this->nodemenu = new NodeMenu($this->container, $request->getLocale(), $node, 'write', true, true);
        }
        if (is_null($parent)) {
            $menuitem = new TopMenuItem($menu);
            $menuitem->setRoute('KunstmaanAdminNodeBundle_pages');
            $menuitem->setInternalname("Pages");
            $menuitem->setParent($parent);
            if (stripos($request->attributes->get('_route'), $menuitem->getRoute()) === 0) {
                $menuitem->setActive(true);
            }
            $children[] = $menuitem;
        } else if ('KunstmaanAdminNodeBundle_pages' == $parent->getRoute()) {
            $topnodes = $this->nodemenu->getTopNodes();

            $currentId = $request->attributes->get('id');
            if (isset($currentId)) {
                $currentNode = $this->container->get("doctrine")->getEntityManager()->getRepository('KunstmaanAdminNodeBundle:Node')->findOneById($currentId);
                if (!is_null($currentNode)) {
                    $parentNodes = $currentNode->getParents();
                } else {
                    $parentNodes = array();
                }
            }

            foreach ($topnodes as $child) {
                $menuitem = new MenuItem($menu);
                $menuitem->setRoute('KunstmaanAdminNodeBundle_pages_edit');
                $menuitem->setRouteparams(array('id' => $child->getId()));
                $menuitem->setInternalname($child->getTitle());
                $menuitem->setParent($parent);
                $menuitem->setRole('page');
                $menuitem->setWeight($child->getNodeTranslation()->getWeight());

                if (isset($currentNode) && stripos($request->attributes->get('_route'), $menuitem->getRoute()) === 0) {
                    if ($currentNode->getId() == $child->getId()) {
                        $menuitem->setActive(true);
                    } else {
                        foreach ($parentNodes as $parentNode) {
                            if ($parentNode->getId() == $child->getId()) {
                                $menuitem->setActive(true);
                                break;
                            }
                        }
                    }
                }

                $children[] = $menuitem;
            }


        } else if ('KunstmaanAdminNodeBundle_pages_edit' == $parent->getRoute()) {
            $parentRouteParams = $parent->getRouteparams();
            $node = $this->container->get("doctrine")->getEntityManager()->getRepository('KunstmaanAdminNodeBundle:Node')->findOneById($parentRouteParams['id']);
            $nodemenu = new NodeMenu($this->container, $request->getLocale(), $node, 'write', true, true);

            $childNodes = $nodemenu->getCurrent()->getChildren();

            $currentId = $request->attributes->get('id');
            if (isset($currentId)) {
                $currentNode = $this->container->get("doctrine")->getEntityManager()->getRepository('KunstmaanAdminNodeBundle:Node')->findOneById($currentId);
                if (!is_null($currentNode)) {
                    $parentNodes = $currentNode->getParents();
                } else {
                    $parentNodes = array();
                }
            }
            foreach ($childNodes as $child) {
                $menuitem = new MenuItem($menu);
                $menuitem->setRoute('KunstmaanAdminNodeBundle_pages_edit');
                $menuitem->setRouteparams(array('id' => $child->getId()));
                $menuitem->setInternalname($child->getTitle());
                $menuitem->setParent($parent);
                $menuitem->setRole('page');
                $menuitem->setWeight($child->getNodeTranslation()->getWeight());

                if (isset($currentNode) && stripos($request->attributes->get('_route'), $menuitem->getRoute()) === 0) {
                    if ($currentNode->getId() == $child->getId()) {
                        $menuitem->setActive(true);
                    } else {
                        foreach ($parentNodes as $parentNode) {
                            if ($parentNode->getId() == $child->getId()) {
                                $menuitem->setActive(true);
                                break;
                            }
                        }
                    }
                }
                $children[] = $menuitem;
            }
        }
    }

}