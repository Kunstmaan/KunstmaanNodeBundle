<?php

namespace Kunstmaan\NodeBundle\Controller;

use Doctrine\ORM\EntityManager;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;
use Kunstmaan\NodeBundle\Entity\HasNodeInterface;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Event\Events;
use Kunstmaan\NodeBundle\Event\SlugEvent;
use Kunstmaan\NodeBundle\Event\SlugSecurityEvent;
use Kunstmaan\NodeBundle\Helper\NodeMenu;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This controller is for showing frontend pages based on slugs
 */
class SlugController extends Controller
{

    /**
     * Handle the page requests
     *
     * @param Request $request The request
     * @param string  $url     The url
     * @param bool    $preview Show in preview mode
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     *
     * @return Response|array
     */
    public function slugAction(Request $request, $url = null, $preview = false)
    {
        /* @var EntityManager $em */
        $em     = $this->getDoctrine()->getManager();
        $locale = $request->getLocale();

        /* @var NodeTranslation $nodeTranslation */
        $nodeTranslation = $request->get('_nodeTranslation');
        if (!$nodeTranslation) {
            // When the SlugController is used from a different Routing or RouteLoader class, the _nodeTranslation is not set, so we need this fallback
            $nodeTranslation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')->getNodeTranslationForUrl($url, $locale);
        }

        // If no node translation -> 404
        if (!$nodeTranslation) {
            throw $this->createNotFoundException('No page found for slug ' . $url);
        }

        $entity = $this->getPageEntity(
            $request,
            $preview,
            $em,
            $nodeTranslation
        );

        $securityEvent = new SlugSecurityEvent();
        $securityEvent
            ->setNode($nodeTranslation->getNode())
            ->setEntity($entity)
            ->setRequest($request)
            ->setNodeTranslation($nodeTranslation);

        $eventDispatcher = $this->get('event_dispatcher');
        $eventDispatcher->dispatch(Events::SLUG_SECURITY, $securityEvent);

        //render page
        $renderContext = new RenderContext(
            array(
                'nodetranslation' => $nodeTranslation,
                'slug'            => $url,
                'page'            => $entity,
                'resource'        => $entity,
            )
        );
        if (method_exists($entity, 'getDefaultView')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $renderContext->setView($entity->getDefaultView());
        }
        $preEvent = new SlugEvent(null, $renderContext);
        $eventDispatcher->dispatch(Events::PRE_SLUG_ACTION, $preEvent);
        $renderContext = $preEvent->getRenderContext();

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $entity->service($this->container, $request, $renderContext);

        $postEvent = new SlugEvent($response, $renderContext);
        $eventDispatcher->dispatch(Events::POST_SLUG_ACTION, $postEvent);

        $response = $postEvent->getResponse();
        $renderContext = $postEvent->getRenderContext();

        if ($response instanceof Response){
            return $response;
        }

        $view = $renderContext->getView();
        if (empty($view)) {
            throw $this->createNotFoundException('No page found for slug ' . $url);
        }

        $request->attributes->set('_template', $view);

        return $renderContext->getArrayCopy();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param                                           $preview
     * @param                                           $em
     * @param                                           $nodeTranslation
     *
     * @return \Kunstmaan\NodeBundle\Entity\HasNodeInterface
     */
    private function getPageEntity(
        Request $request,
        $preview,
        $em,
        $nodeTranslation
    ) {
        /* @var HasNodeInterface $entity */
        $entity = null;
        if ($preview) {
            $version = $request->get('version');
            if (!empty($version) && is_numeric($version)) {
                $nodeVersion = $em->getRepository(
                    'KunstmaanNodeBundle:NodeVersion'
                )->find($version);
                if (!is_null($nodeVersion)) {
                    $entity = $nodeVersion->getRef($em);
                }
            }
        }
        if (is_null($entity)) {
            $entity = $nodeTranslation->getPublicNodeVersion()->getRef($em);

            return $entity;
        }

        return $entity;
    }
}
