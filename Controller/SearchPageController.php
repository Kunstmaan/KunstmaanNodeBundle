<?php

namespace Kunstmaan\ViewBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Kunstmaan\AdminBundle\Helper\ClassLookup;

class SearchPageController extends Controller
{
    /**
     * @Route("/{slug}/searchredirect", requirements={"slug" = ".+"}, name="_search")
     * @Template()
     */
    public function searchAction($slug)
    {
        $query = $this->getRequest()->get("query");

        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $locale = $request->getLocale();
        $nodeTranslation = $em->getRepository('KunstmaanAdminNodeBundle:NodeTranslation')->getNodeTranslationForSlug($slug, null);
        if ($nodeTranslation) {
            $page = $nodeTranslation->getPublicNodeVersion()->getRef($em);
            $node = $nodeTranslation->getNode();
        } else {
            throw $this->createNotFoundException('No page found for slug ' . $slug);
        }

        $homepage = $this->getHomepage($node);
        $children = $homepage->getChildren();
           foreach ($children as $child) {
            if($child->getNodeTranslation($request->getLocale()) && ClassLookup::getClassName($child->getNodeTranslation($request->getSession()->getLocale())->getRef($em)) == "SearchPage") $searchpage = $child;
           }

           if ($searchpage) {
               return $this->redirect($this->generateUrl('_slug', array('url' => $searchpage->getNodeTranslation($request->getLocale())->getUrl(), 'query' => $query)));
           } else {
               throw $this->createNotFoundException('No searchpage found');
           }
    }

    public function getHomepage($page)
    {
        if ($page->getParent()) {
            $homepage = $this->getHomepage($page->getParent());
        }else $homepage = $page;

        return $homepage;
    }
}
