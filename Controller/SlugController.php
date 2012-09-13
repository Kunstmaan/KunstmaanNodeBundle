<?php

namespace Kunstmaan\ViewBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Kunstmaan\AdminBundle\Entity\DynamicRoutingPageInterface;
use Kunstmaan\AdminBundle\Component\Security\Acl\Permission\PermissionMap;
use Kunstmaan\AdminNodeBundle\Modules\NodeMenu;
use Kunstmaan\ViewBundle\Helper\RenderContext;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SlugController extends Controller
{

    /**
     * @Route("/")
     * @Route("/draft/{url}", requirements={"url" = ".+"}, defaults={"preview" = true, "draft" = true}, name="_slug_draft")
     * @Route("/preview/{url}", requirements={"url" = ".+"}, defaults={"preview" = true}, name="_slug_preview")
     * @Route("/{url}", requirements={"url" = ".+"}, name="_slug")
     * @Template()
     */
    public function slugAction($url = null, $preview = false, $draft = false)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $locale = $request->getLocale();

        if (empty($locale)) {
            $locale = $request->getLocale();
        }

        $requiredLocales = $this->container->getParameter('requiredlocales');

        $localesArray = explode('|', $requiredLocales);

        if (!empty($localesArray[0])) {
            $fallback = $localesArray[0];
        } else {
            $fallback = $this->container->getParameter('locale');
        }

        if (!in_array($locale, $localesArray)) {
            if (empty($url)) {
                $url = $locale;
            } else {
                $url = $locale . '/' . $url;
            }
            $locale = $fallback;
            if ($draft) {
                return $this->redirect($this->generateUrl('_slug_draft', array('url' => $url, '_locale' => $locale)));
            } else {
                return $this->redirect($this->generateUrl('_slug', array('url' => $url, '_locale' => $locale)));
            }
        }

        $nodeTranslation = $em->getRepository('KunstmaanAdminNodeBundle:NodeTranslation')->getNodeTranslationForUrl($url, $locale);
        $exactMatch = true;
        if (!$nodeTranslation) {
            // Lookup node by best match for url
            $nodeTranslation = $em->getRepository('KunstmaanAdminNodeBundle:NodeTranslation')->getBestMatchForUrl($url, $locale);
            $exactMatch = false;
        }
        if ($nodeTranslation) {
            if ($draft) {
                $version = $nodeTranslation->getNodeVersion('draft');
                if (is_null($version)) {
                    $version = $nodeTranslation->getPublicNodeVersion();
                }
                $page = $version->getRef($em);
            } else {
                $page = $nodeTranslation->getPublicNodeVersion()->getRef($em);
            }
            $node = $nodeTranslation->getNode();
        }

        // If no node translation or no exact match that is not a dynamic routing page -> 404
        if (!$nodeTranslation || (!$exactMatch && !($page instanceof DynamicRoutingPageInterface))) {
            throw $this->createNotFoundException('No page found for slug ' . $url);
        }

        // check if the requested node is online, else throw a 404 exception (only when not previewing!)
        if (!$preview && !$nodeTranslation->isOnline()) {
            throw $this->createNotFoundException("The requested page is not online");
        }

        $securityContext = $this->get('security.context');
        if (false === $securityContext->isGranted(PermissionMap::PERMISSION_VIEW, $node)) {
            throw new AccessDeniedHttpException('You do not have sufficient rights to access this page.');
        }

        $aclHelper  = $this->container->get('kunstmaan.acl.helper');
        $nodeMenu = new NodeMenu($em, $securityContext, $aclHelper, $locale, $node);

        if ($page instanceof DynamicRoutingPageInterface) {
            $page->setLocale($locale);
            $slugPart = substr($url, strlen($nodeTranslation->getUrl()));
            if (false === $slugPart) {
                $slugPart = '/';
            }
            $path = $page->match($slugPart);
            if ($path) {
                $path['nodeTranslationId'] = $nodeTranslation->getId();

                return $this->forward($path['_controller'], $path, $request->query->all());
            }
        }

        //render page
        $pageParts = array();
        if ($exactMatch && method_exists($page, 'getPagePartAdminConfigurations')) {
            foreach ($page->getPagePartAdminConfigurations() as $pagePartAdminConfiguration) {
                $context = $pagePartAdminConfiguration->getDefaultContext();
                $pageParts[$context] = $em->getRepository('KunstmaanPagePartBundle:PagePartRef')->getPageParts($page, $context);
            }
        }
        $renderContext = new RenderContext(
                array('nodetranslation' => $nodeTranslation, 'slug' => $url, 'page' => $page, 'resource' => $page, 'pageparts' => $pageParts, 'nodemenu' => $nodeMenu,
                        'locales' => $localesArray));
        $hasView = false;
        if (method_exists($page, 'getDefaultView')) {
            $renderContext->setView($page->getDefaultView());
            $hasView = true;
        }
        if (method_exists($page, 'service')) {
            $redirect = $page->service($this->container, $request, $renderContext);
            if (!empty($redirect)) {
                return $redirect;
            } elseif (!$exactMatch && !$hasView) {
                // If it was a dynamic routing page and no view and no service implementation -> 404
                throw $this->createNotFoundException('No page found for slug ' . $url);
            }
        }

        return $this->render($renderContext->getView(), (array) $renderContext);
    }
}
