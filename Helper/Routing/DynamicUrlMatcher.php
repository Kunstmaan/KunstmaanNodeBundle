<?php

namespace Kunstmaan\NodeBundle\Helper\Routing;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;

/**
 * Custom UrlMatcher which holds a copy of all the routes, this is needed for the DynamicRouting
 *
 * @todo Check if there's a better solution then this ugly hack...
 */
class DynamicUrlMatcher extends UrlMatcher
{
    /**
     * @var RouteCollection
     */
    private $routesCopy;

    /**
     * @param RouteCollection $routes  The route collection
     * @param RequestContext  $context The request context
     */
    public function __construct(RouteCollection $routes, RequestContext $context)
    {
        parent::__construct($routes, $context);
        $this->routesCopy = $routes;
    }

    /**
     * Check if url exists
     *
     * @param string $pathInfo
     *
     * @return bool
     */
    public function match($pathInfo)
    {
        if ($ret = $this->matchCollection($pathInfo, $this->routesCopy)) {
            return $ret;
        }

        return false;
    }

}
