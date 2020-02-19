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

use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Domain\Monitoring\Serializer\ResourceExclusionStrategy;
use Centreon\Domain\Monitoring\Resource;

/**
 * Resource APIs for the Unified View page
 *
 * @package Centreon\Application\Controller
 */
class MonitoringResourceController extends AbstractController
{
    /**
     * @var \Centreon\Domain\Monitoring\ResourceService
     */
    protected $resource;

    public function __construct(ResourceServiceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * List all the resources in real-time monitoring : hosts and services.
     *
     * @param \Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface $requestParameters
     * @return View
     */
    public function list(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();
        
        $context = (new Context())
            ->setGroups(Resource::contextGroupsForListing())
            ->enableMaxDepth();
        
        $context->addExclusionStrategy(new ResourceExclusionStrategy);

        return $this->view([
            'result' => $this->resource->filterByContact($this->getUser())
                ->findResources(),
            'meta' => $requestParameters->toArray(),
        ])->setContext($context);
    }
}
