<?php

namespace Kunstmaan\NodeBundle\Entity\Nodetype;

use Kunstmaan\NodeBundle\Annotations\NodeAction;

use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\NodeBundle\Entity\AbstractNodeItem;
use Kunstmaan\NodeBundle\Entity\HasNodeInterface;
use Kunstmaan\NodeBundle\Form\RedirectAdminType;

use Doctrine\ORM\Mapping as ORM;

/**
 * Node
 * @NodeAction(controller="KunstmaanNodeBundle:Redirect:redirect")
 *
 * @ORM\Entity(repositoryClass="Kunstmaan\NodeBundle\Repository\Nodetype\RedirectRepository")
 * @ORM\Table(name="kuma_nodetype_redirect")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Redirect extends AbstractNodeItem implements HasNodeInterface
{

    protected $type = "RedirectNode";

    /**
     * @ORM\Column(type="string", nullable=false, name="redirect_url")
     */
    protected $redirectUrl;

    /**
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new RedirectAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array();
    }

    public function getType()
    {
        return $this->type;
    }

    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}