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

namespace Core\Infrastructure\Common\Api;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Override symfony router to generate base URI
 */
class Router implements RouterInterface, RequestMatcherInterface, WarmableInterface, UrlGeneratorInterface
{
    use HttpUrlTrait;

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
     *
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
     * {@inheritDoc}
     *
     * @param string $name
     * @param array<string,mixed> $parameters
     * @param int $referenceType
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH)
    {
        $parameters['base_uri'] = trim($this->getBaseUri(), '/');
        $parameters['base_uri'] = empty($parameters['base_uri'])
            ? $parameters['base_uri']
            : $parameters['base_uri'] . '/';

        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * {@inheritDoc}
     *
     * @param RequestContext $context
     * @return void
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * @inheritDoc
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * @inheritDoc
     */
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string,mixed>
     */
    public function match(string $pathinfo)
    {
        return $this->router->match($pathinfo);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string,mixed>
     */
    public function matchRequest(Request $request)
    {
        return $this->requestMatcher->matchRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function warmUp(string $cacheDir)
    {
        return [];
    }
}
