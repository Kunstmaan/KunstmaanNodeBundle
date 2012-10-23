<?php

namespace Kunstmaan\NodeBundle\Helper\Menu;

use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\PageInterface;

use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;
use Kunstmaan\NodeBundle\Event\ConfigureActionMenuEvent;
use Kunstmaan\NodeBundle\Event\Events;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Knp\Menu\ItemInterface;
use Knp\Menu\FactoryInterface;

/**
 * ActionsMenuBuilder
 */
class ActionsMenuBuilder
{

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var NodeVersion
     */
    private $activeNodeVersion;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var SecurityContextInterface
     */
    private $context;

    /**
     * @param FactoryInterface         $factory    The factory
     * @param EntityManager            $em         The entity manager
     * @param RouterInterface          $router     The router
     * @param EventDispatcherInterface $dispatcher The event dispatcher
     * @param SecurityContextInterface $context    The security context
     */
    public function __construct(FactoryInterface $factory, EntityManager $em, RouterInterface $router, EventDispatcherInterface $dispatcher, SecurityContextInterface $context)
    {
        $this->factory = $factory;
        $this->em      = $em;
        $this->router  = $router;
        $this->dispatcher = $dispatcher;
        $this->context = $context;
    }

    /**
     * @param Request $request
     *
     * @return ItemInterface
     */
    public function createSubActionsMenu(/** @noinspection PhpUnusedParameterInspection */Request $request = null)
    {
        $activeNodeVersion = $this->getActiveNodeVersion();
        $menu              = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'sub_actions');

        if (!is_null($activeNodeVersion)) {
            $menu->addChild('subaction.versions', array('linkAttributes' => array('data-toggle' => 'modal', 'data-target' => '#versions')));
        }

        $this->dispatcher->dispatch(Events::CONFIGURE_SUB_ACTION_MENU, new ConfigureActionMenuEvent($this->factory, $menu, $activeNodeVersion));

        return $menu;
    }

    /**
     * @param Request $request
     *
     * @return ItemInterface
     */
    public function createActionsMenu(/** @noinspection PhpUnusedParameterInspection */Request $request = null)
    {
        $activeNodeVersion = $this->getActiveNodeVersion();
        $menu              = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'main_actions');

        if (!is_null($activeNodeVersion)) {
            $activeNodeTranslation = $activeNodeVersion->getNodeTranslation();
            $node = $activeNodeTranslation->getNode();
            $isFirst = true;
            if ('draft' == $activeNodeVersion->getType()) {
                if ($this->context->isGranted(PermissionMap::PERMISSION_EDIT, $node)) {
                    $menu->addChild('action.saveasdraft', array('linkAttributes' => array('type' => 'submit', 'onClick' => 'isEdited=false', 'class' => 'btn' . ($isFirst ? ' btn-primary' : ''), 'value' => 'save', 'name' => 'save'), 'extras' => array('renderType' => 'button')));
                    $isFirst = false;
                }
                if ($this->context->isGranted(PermissionMap::PERMISSION_PUBLISH, $node)) {
                    $menu->addChild('action.publish', array('linkAttributes' => array('type' => 'submit', 'class' => 'btn' . ($isFirst ? ' btn-primary' : ''), 'value' => 'saveandpublish', 'name' => 'saveandpublish'), 'extras' => array('renderType' => 'button')));
                }
                $menu->addChild('action.preview', array('uri' => $this->router->generate('_slug_preview', array('url' => $activeNodeTranslation->getUrl(), 'version' => $activeNodeVersion->getId())), 'linkAttributes' => array('target' => '_blank', 'class' => 'btn')));
            } else {
                if ($this->context->isGranted(PermissionMap::PERMISSION_EDIT, $node) && $this->context->isGranted(PermissionMap::PERMISSION_PUBLISH, $node)) {
                    $menu->addChild('action.save', array('linkAttributes' => array('type' => 'submit', 'onClick' => 'isEdited=false', 'class' => 'btn' . ($isFirst ? ' btn-primary' : ''), 'value' => 'save', 'name' => 'save'), 'extras' => array('renderType' => 'button')));
                    $isFirst = false;
                }
                if ($activeNodeTranslation->isOnline() &&  $this->context->isGranted(PermissionMap::PERMISSION_UNPUBLISH, $node)) {
                    $menu->addChild('action.unpublish', array('linkAttributes' => array('class' => 'btn', 'data-toggle' => 'modal', 'data-target' => '#unpub')));
                } elseif (!$activeNodeTranslation->isOnline() &&  $this->context->isGranted(PermissionMap::PERMISSION_PUBLISH, $node)) {
                    $menu->addChild('action.publish', array('linkAttributes' => array('class' => 'btn', 'data-toggle' => 'modal', 'data-target' => '#pub')));
                }
                if ($this->context->isGranted(PermissionMap::PERMISSION_EDIT, $node)) {
                    $menu->addChild('action.saveasdraft', array('linkAttributes' => array('type' => 'submit', 'class' => 'btn' . ($isFirst ? ' btn-primary' : ''), 'value' => 'saveasdraft', 'name' => 'saveasdraft'), 'extras' => array('renderType' => 'button')));
                }
                $menu->addChild('action.preview', array('uri' => $this->router->generate('_slug_preview', array('url' => $activeNodeTranslation->getUrl())), 'linkAttributes' => array('target' => '_blank', 'class' => 'btn')));
            }
            $page = $activeNodeVersion->getRef($this->em);
            if (!is_null($page) && $page instanceof PageInterface) {
                $possibleChildPages = $page->getPossibleChildTypes();
                if (!empty($possibleChildPages)) {
                    $menu->addChild('action.addsubpage', array('linkAttributes' => array('type' => 'button', 'class' => 'btn', 'data-toggle' => 'modal', 'data-target' => '#add-subpage-modal'), 'extras' => array('renderType' => 'button')));
                }
            }
            if ($this->context->isGranted(PermissionMap::PERMISSION_DELETE, $node)) {
                $menu->addChild('action.delete', array('linkAttributes' => array('type' => 'button', 'class' => 'btn', 'onClick' => 'oldEdited = isEdited; isEdited=false', 'data-toggle' => 'modal', 'data-target' => '#delete-page-modal'), 'extras' => array('renderType' => 'button')));
            }
        }

        $this->dispatcher->dispatch(Events::CONFIGURE_ACTION_MENU, new ConfigureActionMenuEvent($this->factory, $menu, $activeNodeVersion));

        return $menu;
    }

    /**
     * @param Request $request
     *
     * @return ItemInterface
     */
    public function createTopActionsMenu(Request $request = null)
    {
        $menu = $this->createActionsMenu($request);
        $menu->setChildrenAttribute('class', 'main_actions top');
        $menu->setChildrenAttribute('id', 'main_actions_top');

        return $menu;
    }

    /**
     * Set activeNodeVersion
     *
     * @param NodeVersion $activeNodeVersion
     *
     * @return ActionsMenuBuilder
     */
    public function setActiveNodeVersion(NodeVersion $activeNodeVersion)
    {
        $this->activeNodeVersion = $activeNodeVersion;

        return $this;
    }

    /**
     * Get activeNodeVersion
     *
     * @return NodeVersion
     */
    public function getActiveNodeVersion()
    {
        return $this->activeNodeVersion;
    }

}
