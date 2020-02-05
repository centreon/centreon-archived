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
 * @package Centreon\Application\Controller
 */
class MonitoringController extends AbstractFOSRestController
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
     * Entry point to get a real time service.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/hosts/{hostId}/services/{serviceId}",
     *     condition="request.attributes.get('version.is_beta') == true")
     *
     * @param int $serviceId Service id
     * @param int $hostId Host id
     * @return View
     * @throws \Exception
     */
    public function getOneService(int $serviceId, int $hostId): View
    {
        $service = $this->monitoring
            ->filterByContact($this->getUser())
            ->findOneService($hostId, $serviceId);

        if ($service !== null) {
            $context = (new Context())
                ->setGroups(['service_full'])
                ->enableMaxDepth();

            return $this->view($service)->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
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

    /**
     * Entry point to get all real time services based on a host group.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/hostgroups",
     *     condition="request.attributes.get('version.is_beta') == true")
     *
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getHostGroups(RequestParametersInterface $requestParameters)
    {
        $withHost = $requestParameters->getExtraParameter('show_host') === 'true';
        $withServices = $requestParameters->getExtraParameter('show_service') === 'true';

        $contexts = ['hg_main'];

        $contextsWithHosts = ['hg_with_host', 'host_min'];
        $contextsWithService = ['host_with_services', 'service_min'];

        if ($withServices) {
            $withHost = true;
            $contexts = array_merge($contexts, $contextsWithService);
        }
        if ($withHost) {
            $contexts = array_merge($contexts, $contextsWithHosts);
        }

        $hostGroups = $this->monitoring
            ->filterByContact($this->getUser())
            ->findHostGroups($withHost, $withServices);

        $context = (new Context())
            ->setGroups($contexts)
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $hostGroups,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to get all real time hosts.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/hosts",
     *     condition="request.attributes.get('version.is_beta') == true")
     *
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getHosts(RequestParametersInterface $requestParameters)
    {
        $withServices = $requestParameters->getExtraParameter('show_service') === 'true';
        $hosts = $this->monitoring
            ->filterByContact($this->getUser())
            ->findHosts($withServices);

        $parametersGroup = ['host_main'];

        if ($withServices) {
            $parametersGroup[] = 'host_with_services';
            $parametersGroup[] = 'service_min';
        }
        $context = (new Context())->setGroups($parametersGroup);

        return $this->view(
            [
                'result' => $hosts,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to get a real time host.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/hosts/{hostId}",
     *     requirements={"hostId"="\d+"},
     *     condition="request.attributes.get('version.is_beta') == true")
     *
     * @param int $hostId Host id
     * @return View
     * @throws \Exception
     */
    public function getOneHost(int $hostId)
    {
        $host = $this->monitoring
            ->filterByContact($this->getUser())
            ->findOneHost($hostId);

        if ($host !== null) {
            $context = (new Context())
                ->setGroups(['host_full', 'service_min'])
                ->enableMaxDepth();

            return $this->view($host)->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }

    /**
     * Entry point to get all real time services based on a host.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *      "/monitoring/hosts/{hostId}/services",
     *      condition="request.attributes.get('version.is_beta') == true")
     *
     * @param int $hostId Host id for which we want to get all services
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getServicesByHost(int $hostId, RequestParametersInterface $requestParameters)
    {
        $this->monitoring->filterByContact($this->getUser());

        if ($this->monitoring->isHostExists($hostId)) {
            $services = $this->monitoring->findServicesByHost($hostId);

            $context = (new Context())
                ->setGroups(['service_main'])
                ->enableMaxDepth();

            return $this->view(
                [
                    'result' => $services,
                    'meta' => $requestParameters->toArray()
                ]
            )->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }
}
