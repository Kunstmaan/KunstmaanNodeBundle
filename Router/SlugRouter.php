<?php

namespace Kunstmaan\NodeBundle\Router;

use Kunstmaan\NodeBundle\Repository\NodeTranslationRepository;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;

/**
 * The SlugRouter takes care of routing the paths for slugs. It should have the lowest priority as it's a
 * catch-all router that routes (almost) all requests to the SlugController
 */
class SlugRouter implements RouterInterface
{

    /** @var  RequestContext */
    private $context;

    /** @var RouteCollection */
    private $routeCollection;

    /** @var UrlGenerator */
    private $urlGenerator;

    /** @var ContainerInterface */
    private $container;

    /**
     * The constructor for this service
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->routeCollection = new RouteCollection();

        $multilanguage      = $this->container->getParameter('multilanguage');
        $defaultlocale      = $this->container->getParameter('defaultlocale');

        if ($multilanguage) {
            // the website is multilingual so the language is the first parameter
            $requiredLocales    = $this->container->getParameter('requiredlocales');

            $this->routeCollection->add(
                '_slug_preview',
                new Route(
                    '/{_locale}/preview/{url}',
                    array(
                            '_controller'   => 'KunstmaanNodeBundle:Slug:slug',
                            'preview'       => true,
                            'url'           => ''
                ),
                array('_locale' => $requiredLocales, 'url' => "[a-zA-Z0-9\-_\/]*") // override default validation of url to accept /, - and _
            ));
            $this->routeCollection->add(
                '_slug',
                new Route(
                    '/{_locale}/{url}',
                    array(
                        '_controller'   => 'KunstmaanNodeBundle:Slug:slug',
                        'preview'       => false,
                        'url'           => ''
                    ),
                    array('_locale' => $requiredLocales, 'url' => "[a-zA-Z0-9\-_\/]*")
                )
            );
        } else {
            // the website is not multiligual, _locale must do a fallback to the default locale
            $this->routeCollection->add(
                '_slug_preview',
                new Route(
                    '/preview/{url}',
                    array(
                            '_controller'   => 'KunstmaanNodeBundle:Slug:slug',
                            'preview'       => true,
                            'url'           => '',
                            '_locale'       => $defaultlocale
                        ),
                    array('url' => "[a-zA-Z0-9\-_\/]*")
                )
            );
            $this->routeCollection->add(
                '_slug',
                new Route(
                    '/{url}',
                    array(
                        '_controller'   => 'KunstmaanNodeBundle:Slug:slug',
                        'preview'       => false,
                        'url'           => '',
                        '_locale'       => $defaultlocale
                    ),
                    array('url' => "[a-zA-Z0-9\-_\/]*")
                )
            );
        }
    }


    /**
     * Match given urls via the context to the routes we defined.
     * This functionality re-uses the default Symfony way of routing and its components
     *
     * @param string $pathinfo
     *
     * @return array
     */
    public function match($pathinfo)
    {
        $urlMatcher = new UrlMatcher($this->routeCollection, $this->getContext());

        $result = $urlMatcher->match($pathinfo);
        if (!empty($result)) {
            // The route matches, now check if it actually exists
            $em = $this->container->get('doctrine.orm.entity_manager');

            /* @var NodeTranslationRepository $nodeTranslationRepo */
            $nodeTranslationRepo = $em->getRepository('KunstmaanNodeBundle:NodeTranslation');
            /* @var NodeTranslation $nodeTranslation */
            $nodeTranslation = $nodeTranslationRepo->getNodeTranslationForUrl($result['url'], $result['_locale']);

            if (is_null($nodeTranslation)) {
                throw new ResourceNotFoundException('No page found for slug ' . $pathinfo);
            }
        }

        return $result;
    }


    /**
     * Generate an url for a supplied route
     *
     * @param string $name       The path
     * @param array  $parameters The route parameters
     * @param bool   $absolute   Absolute url or not
     *
     * @return null|string
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        $this->urlGenerator = new UrlGenerator($this->routeCollection, $this->context);

        return $this->urlGenerator->generate($name, $parameters, $absolute);
    }


    /**
     * Sets the request context.
     *
     * @param RequestContext $context The context
     *
     * @api
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }


    /**
     * Gets the request context.
     *
     * @return RequestContext The context
     *
     * @api
     */
    public function getContext()
    {
        if (!isset($this->context)) {
            /* @var Request $request */
            $request = $this->container->get('request');

            $this->context = new RequestContext();
            $this->context->fromRequest($request);
        }

        return $this->context;
    }


    /**
     * Getter for routeCollection
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routeCollection;
    }
}
