<?php

namespace Kunstmaan\NodeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\HasNodeInterface;
use Kunstmaan\NodeBundle\Tabs\TabPane;

class AdaptFormEvent extends Event
{

    /**
     * @var TabPane
     */
    private $tabPane;

    /**
     * @var HasNodeInterface
     */
    private $page;

    /**
     * @var Node
     */
    private $node;

    /**
     * @var NodeTranslation
     */
    private $nodeTranslation;

    /**
     * @var NodeVersion
     */
    private $nodeVersion;

    /**
     * @param TabPane $tabPane
     * @param HasNodeInterface $page
     * @param Node $node
     * @param NodeTranslation $nodeTranslation
     * @param NodeVersion $nodeVersion
     */
    public function __construct(TabPane $tabPane, HasNodeInterface $page, Node $node, NodeTranslation $nodeTranslation, NodeVersion $nodeVersion)
    {

        $this->tabPane = $tabPane;
        $this->page = $page;
        $this->node = $node;
        $this->nodeTranslation = $nodeTranslation;
        $this->nodeVersion = $nodeVersion;
    }

    /**
     * @return Node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return NodeTranslation
     */
    public function getNodeTranslation()
    {
        return $this->nodeTranslation;
    }

    /**
     * @return NodeVersion
     */
    public function getNodeVersion()
    {
        return $this->nodeVersion;
    }

    /**
     * @return HasNodeInterface
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return TabPane
     */
    public function getTabPane()
    {
        return $this->tabPane;
    }


}