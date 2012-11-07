<?php


use Kunstmaan\NodeBundle\Entity\HasNodeInterface;

use Doctrine\ORM\Mapping as ORM;

/**
 * Node
 *
 * @ORM\Entity(repositoryClass="Kunstmaan\NodeBundle\Repository\Nodetype\RedirectRepository")
 * @ORM\Table(name="kuma_nodetype_redirect")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Redirect implements HasNodeInterface {

    private $type = "RedirectNode";

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        // TODO: Implement getTitle() method.
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return HasNodeInterface
     */
    public function setTitle($title)
    {
        // TODO: Implement setTitle() method.
    }

    /**
     * @return HasNodeInterface
     */
    public function getParent()
    {
        // TODO: Implement getParent() method.
    }

    /**
     * @param HasNodeInterface $hasNode
     */
    public function setParent(HasNodeInterface $hasNode)
    {
        // TODO: Implement setParent() method.
    }

    /**
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        // TODO: Implement getDefaultAdminType() method.
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        // TODO: Implement getPossibleChildTypes() method.
    }

    public function getType()
    {
        return $this->type;
    }
}