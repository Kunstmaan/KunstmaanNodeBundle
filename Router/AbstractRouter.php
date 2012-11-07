<?php

namespace Kunstmaan\NodeBundle\Router;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;


/**
 * The abstract router can be re-used to create generic routers
 * based on the type of the node
 */
class AbstractRouter implements RouterInterface {

    /** @var  RequestContext */
    private $context;

    /** @var RouteCollection */
    private $routeCollection;

    /** @var UrlGenerator */
    private $urlGenerator;

    /** @var ContainerInterface */
    private $container;

    /** @var string */
    private $pageType;

    /**
     * The constructor for this service
     * This adds a few very generic routes
     *
     * @param $container
     * @param $pageType
     * @param $controllerAction
     */
    public function __construct($container, $pageType, $controllerAction) {
        $this->pageType         = $pageType;
        $this->container        = $container;
        $this->routeCollection  = new RouteCollection();

        $multilanguage      = $this->container->getParameter('multilanguage');
        $defaultlocale      = $this->container->getParameter('defaultlocale');

        $routename = "_abstract_".strtolower($pageType);

        if($multilanguage) { // the website is multilingual so the language is the first parameter
            $requiredLocales    = $this->container->getParameter('requiredlocales');

            $this->routeCollection->add($routename, new Route(
                '/{_locale}/{url}',
                array(
                    '_controller'       => $controllerAction,
                    'url'               => '',
                    '_locale'           => $defaultlocale,
                    'nodetranslation'   => null,
                ),
                array('_locale' => $requiredLocales, 'url' => "[a-zA-Z1-9\-_\/]+") // override default validation of url to accept /, - and _
            ));
        } else { // the website is not multiligual, _locale must do a fallback to the default locale
            $this->routeCollection->add($routename, new Route(
                '/{url}',
                array(
                    '_controller'       => $controllerAction,
                    'url'               => '',
                    '_locale'           => $defaultlocale,
                    'nodetranslation'   => null,
                ),
                array('url' => "[a-zA-Z1-9\-_\/]+") // override default validation of url to accept /, - and _
            ));
        }
    }


    /**
     * Match given urls via the context to the routes we defined. If the route matches see if it also matches
     * the nodetype and if it does, forward it to the controller
     *
     * @param string $pathinfo
     *
     * @return array
     */
    public function match($pathinfo)
    {
        $urlMatcher = new UrlMatcher($this->routeCollection, $this->getContext());
        $matchedRoute = $urlMatcher->match($pathinfo);

        if(!isset($matchedRoute['url'])) {
            throw new ResourceNotFoundException();
        }

        $nodeTranslationRepo = $this->container->get('doctrine')->getManager()->getRepository('KunstmaanNodeBundle:NodeTranslation');
        $matchedNodeTranslation = $nodeTranslationRepo->getNodeTranslationForUrl($matchedRoute['url'], $matchedRoute['_locale'], $this->pageType);

        if(is_null($matchedNodeTranslation)) {
            throw new ResourceNotFoundException();

        }

        $matchedRoute['nodetranslation'] = $matchedNodeTranslation;

        return $matchedRoute;
    }


    /**
     * Generate an url for a supplied route
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     *
     * @return null|string
     */
    public function generate($name, $parameters = array(), $absolute = false) {
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
        if(!isset($this->context)) {
            $this->context = new RequestContext();
            $this->context->fromRequest($this->container->get('request'));
        }
        return $this->context;
    }


    /**
     * Getter for routeCollection
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRouteCollection() {
        return $this->routeCollection;
    }
}