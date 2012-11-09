<?php

namespace Kunstmaan\NodeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Kunstmaan\NodeBundle\Helper\NodeMenu;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Entity\HasNodeInterface;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * This controller is for showing frontend pages based on slugs
 */
class RedirectController extends Controller
{

    public function redirectAction($nodetranslation)
    {
        return $this->redirect($nodetranslation->getRef($this->getDoctrine()->getManager())->getRedirectUrl());
    }
}
