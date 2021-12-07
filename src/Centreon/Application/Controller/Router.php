<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Centreon\Application\Controller;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * Override symfony router to generate base URI
 */
class Router implements RouterInterface, RequestMatcherInterface, WarmableInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestMatcherInterface
     */
    private $requestMatcher;

    /**
     * MyRouter constructor.
     * @param RouterInterface $router
     * @param RequestMatcherInterface $requestMatcher
     */
    public function __construct(RouterInterface $router, RequestMatcherInterface $requestMatcher)
    {
        $this->router = $router;
        $this->requestMatcher = $requestMatcher;
    }

    /**
     * Get router
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @inheritDoc
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        if (isset($_SERVER['REQUEST_URI']) && preg_match('/^(.+)\/api\/.+/', $_SERVER['REQUEST_URI'], $matches)) {
            $parameters['base_uri'] = trim($matches[1], '/') . '/';
        }

        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * @inheritdoc
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * @inheritdoc
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * @inheritdoc
     */
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }

    /**
     * @inheritdoc
     */
    public function match($pathinfo)
    {
        return $this->router->match($pathinfo);
    }

    /**
     * @inheritdoc
     */
    public function matchRequest(Request $request)
    {
        return $this->requestMatcher->matchRequest($request);
    }

    public function warmUp(string $cacheDir)
    {
    }
}
