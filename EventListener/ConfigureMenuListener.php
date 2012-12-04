<?php

namespace Kunstmaan\NodeBundle\EventListener;

use Kunstmaan\AdminBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $factory = $event->getFactory();

        $menu->addChild($factory->createItem('Pages', array('route' => 'KunstmaanNodeBundle_pages')));
    }
}