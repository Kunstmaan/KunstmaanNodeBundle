<?php

namespace Kunstmaan\NodeBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
class SlugController extends Controller
{

    /**
     * Handle the page requests
     *
     * @param string $url     The url
     * @param bool   $preview Show in preview mode
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return Response|array
     */
    public function slugAction($url = null, $preview = false)
    {
        /* @var EntityManager $em */
        $em         = $this->getDoctrine()->getManager();
        $request    = $this->getRequest();
        $locale     = $request->getLocale();
        
        /* @var NodeTranslation $nodeTranslation */
        $nodeTranslation = $request->get('_nodeTranslation');
        
        // If no node translation -> 404
        if (!$nodeTranslation) {
            throw new ResourceNotFoundException('No page found for slug ' . $url);
        }

        // check if the requested node is online, else throw a 404 exception (only when not previewing!)
        if (!$preview && !$nodeTranslation->isOnline()) {
            throw $this->createNotFoundException("The requested page is not online");
        }

        /* @var HasNodeInterface $entity */
        $entity     = null;
        $node       = $nodeTranslation->getNode();
        if ($preview) {
            $version = $request->get('version');
            if (!empty($version) && is_numeric($version)) {
                $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')->find($version);
                if (!is_null($nodeVersion)) {
                    $entity = $nodeVersion->getRef($em);
                }
            }
        }
        if (is_null($entity)) {
            $entity = $nodeTranslation->getPublicNodeVersion()->getRef($em);
        }

        /* @var SecurityContextInterface $securityContext */
        $securityContext = $this->get('security.context');
        if (false === $securityContext->isGranted(PermissionMap::PERMISSION_VIEW, $node)) {
            throw new AccessDeniedHttpException('You do not have sufficient rights to access this page.');
        }

        /* @var AclHelper $aclHelper */
        $aclHelper = $this->container->get('kunstmaan_admin.acl.helper');
        $includeOffline = $preview;
        $nodeMenu = new NodeMenu($em, $securityContext, $aclHelper, $locale, $node, PermissionMap::PERMISSION_VIEW, $includeOffline);

        unset($securityContext);
        unset($aclHelper);

        //render page
        $renderContext = new RenderContext(
            array(
                'nodetranslation'   => $nodeTranslation,
                'slug'              => $url,
                'page'              => $entity,
                'resource'          => $entity,
                'nodemenu'          => $nodeMenu,
            )
        );
        if (method_exists($entity, 'getDefaultView')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $renderContext->setView($entity->getDefaultView());
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $entity->service($this->container, $request, $renderContext);

        if ($response instanceof Response) {
            return $response;
        }

        $view = $renderContext->getView();
        if (empty($view)) {
            throw $this->createNotFoundException('No page found for slug ' . $url);
        }

        return $this->render($view, $renderContext->getArrayCopy());
    }
}
