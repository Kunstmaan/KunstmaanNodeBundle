<?php

namespace Kunstmaan\NodeBundle\EventListener;

use Knp\Menu\ItemInterface;
use Doctrine\ORM\EntityManager;
use Kunstmaan\AdminBundle\Event\ConfigureTopMenuEvent;

class ConfigureTopMenuListener
{

    public function __construct()
    {

    }

    public function onTopMenuConfigure(ConfigureTopMenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild('Pages', array('route' => 'KunstmaanNodeBundle_pages'));
    }
}