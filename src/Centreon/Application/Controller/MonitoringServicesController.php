<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Responsible for all routes under /monitoring/services/
 * @package Centreon\Application\Controller
 */
class MonitoringServicesController extends AbstractController
{
    /**
     * @var MonitoringServiceInterface
     */
    private $monitoring;

    /**
     * MonitoringController constructor.
     *
     * @param MonitoringServiceInterface $monitoringService
     */
    public function __construct(MonitoringServiceInterface $monitoringService)
    {
        $this->monitoring = $monitoringService;
    }

    /**
     * Entry point to get all real time services.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/services",
     *     condition="request.attributes.get('version.is_beta') == true")
     *
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getServices(RequestParametersInterface $requestParameters): View
    {
        $services = $this->monitoring
            ->filterByContact($this->getUser())
            ->findServices();

        $context = (new Context())
            ->setGroups(['service_main', 'service_with_host', 'host_min'])
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $services,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to get all real time services based on a service group
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/servicegroups",
     *     condition="request.attributes.get('version.is_beta') == true")
     *
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getServicesByServiceGroups(RequestParametersInterface $requestParameters): View
    {
        $withHost = $requestParameters->getExtraParameter('show_host') === 'true';
        $withServices = $requestParameters->getExtraParameter('show_service') === 'true';

        $contexts = ['sg_main'];

        $contextsWithHosts = ['sg_with_host', 'host_min'];
        $contextsWithService = ['host_with_services', 'service_min'];

        if ($withServices) {
            $withHost = true;
            $contexts = array_merge($contexts, $contextsWithService);
        }
        if ($withHost) {
            $contexts = array_merge($contexts, $contextsWithHosts);
        }

        $servicesByServiceGroups = $this->monitoring
            ->filterByContact($this->getUser())
            ->findServiceGroups($withHost, $withServices);

        $context = (new Context())
            ->setGroups($contexts)
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $servicesByServiceGroups,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }
}
