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

use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\ServiceGroup;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\HostGroup;

/**
 * @package Centreon\Application\Controller
 */
class MonitoringHostsController extends AbstractController
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

        if ($service === null) {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }

        $groups = [
            Service::SERIALIZER_GROUP_FULL,
            Acknowledgement::SERIALIZER_GROUP_FULL
        ];
        $context = (new Context())
            ->setGroups(array_merge($groups, Downtime::SERIALIZER_GROUPS_SERVICE))
            ->enableMaxDepth();

        return $this->view($service)->setContext($context);
    }

    /**
     * Entry point to get all real time services.
     *
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getServices(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $services = $this->monitoring
            ->filterByContact($this->getUser())
            ->findServices();

        $context = (new Context())
            ->setGroups([
                Service::SERIALIZER_GROUP_MAIN,
                Service::SERIALIZER_GROUP_WITH_HOST,
                Host::SERIALIZER_GROUP_MIN,
            ])
            ->enableMaxDepth();

        return $this->view([
            'result' => $services,
            'meta' => $requestParameters->toArray()
        ])->setContext($context);
    }

    /**
     * Entry point to get all real time services based on a service group
     *
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getServicesByServiceGroups(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $withHost = $requestParameters->getExtraParameter('show_host') === 'true';
        $withServices = $requestParameters->getExtraParameter('show_service') === 'true';

        $contexts = [
            ServiceGroup::SERIALIZER_GROUP_MAIN,
        ];

        if ($withServices) {
            $withHost = true;
            $contexts = array_merge($contexts, [
                Host::SERIALIZER_GROUP_WITH_SERVICES,
                Service::SERIALIZER_GROUP_MIN,
            ]);
        }

        if ($withHost) {
            $contexts = array_merge($contexts, [
                ServiceGroup::SERIALIZER_GROUP_WITH_HOST,
                Host::SERIALIZER_GROUP_MIN,
            ]);
        }

        $servicesByServiceGroups = $this->monitoring
            ->filterByContact($this->getUser())
            ->findServiceGroups($withHost, $withServices);

        $context = (new Context())
            ->setGroups($contexts)
            ->enableMaxDepth();

        return $this->view([
            'result' => $servicesByServiceGroups,
            'meta' => $requestParameters->toArray()
        ])->setContext($context);
    }

    /**
     * Entry point to get all real time services based on a host group.
     *
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getHostGroups(RequestParametersInterface $requestParameters)
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $withHost = $requestParameters->getExtraParameter('show_host') === 'true';
        $withServices = $requestParameters->getExtraParameter('show_service') === 'true';

        $contexts = [
            HostGroup::SERIALIZER_GROUP_MAIN,
        ];

        if ($withServices) {
            $withHost = true;
            $contexts = array_merge($contexts, [
                Host::SERIALIZER_GROUP_WITH_SERVICES,
                Service::SERIALIZER_GROUP_MIN,
            ]);
        }
        if ($withHost) {
            $contexts = array_merge($contexts, [
                HostGroup::SERIALIZER_GROUP_WITH_HOST,
                Host::SERIALIZER_GROUP_MIN,
            ]);
        }

        $hostGroups = $this->monitoring
            ->filterByContact($this->getUser())
            ->findHostGroups($withHost, $withServices);

        $context = (new Context())
            ->setGroups($contexts)
            ->enableMaxDepth();

        return $this->view([
            'result' => $hostGroups,
            'meta' => $requestParameters->toArray()
        ])->setContext($context);
    }

    /**
     * Entry point to get all real time hosts.
     *
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getHosts(RequestParametersInterface $requestParameters)
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $withServices = $requestParameters->getExtraParameter('show_service') === 'true';
        $hosts = $this->monitoring
            ->filterByContact($this->getUser())
            ->findHosts($withServices);

        $contexts = [
            Host::SERIALIZER_GROUP_MAIN
        ];

        if ($withServices) {
            $contexts = array_merge($contexts, [
                Host::SERIALIZER_GROUP_WITH_SERVICES,
                Service::SERIALIZER_GROUP_MIN,
            ]);
        }

        return $this->view([
            'result' => $hosts,
            'meta' => $requestParameters->toArray()
        ])->setContext((new Context())->setGroups($contexts));
    }

    /**
     * Entry point to get a real time host.
     *
     * @param int $hostId Host id
     * @return View
     * @throws \Exception
     */
    public function getOneHost(int $hostId)
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $host = $this->monitoring
            ->filterByContact($this->getUser())
            ->findOneHost($hostId);

        if ($host === null) {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }

        $groups = [
            Host::SERIALIZER_GROUP_FULL,
            Service::SERIALIZER_GROUP_MIN,
            Acknowledgement::SERIALIZER_GROUP_FULL
        ];

        $context = (new Context())
            ->setGroups(
                array_merge(
                    $groups,
                    Downtime::SERIALIZER_GROUPS_MAIN
                )
            )
            ->enableMaxDepth();

        return $this->view($host)->setContext($context);
    }

    /**
     * Entry point to get all real time services based on a host.
     *
     * @param int $hostId Host id for which we want to get all services
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getServicesByHost(int $hostId, RequestParametersInterface $requestParameters)
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $this->monitoring->filterByContact($this->getUser());

        if (!$this->monitoring->isHostExists($hostId)) {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }

        $context = (new Context())
            ->setGroups([
                Service::SERIALIZER_GROUP_MAIN,
            ])
            ->enableMaxDepth();

        return $this->view([
            'result' => $this->monitoring->findServicesByHost($hostId),
            'meta' => $requestParameters->toArray()
        ])->setContext($context);
    }

    /**
     * Entry point to get all hostgroups.
     *
     * @param int $hostId Id of host to search hostgroups for
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return \FOS\RestBundle\View\View
     * @throws \Exception
     */
    public function getHostGroupsByHost(int $hostId, RequestParametersInterface $requestParameters)
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $this->monitoring->filterByContact($this->getUser());

        if (!$this->monitoring->isHostExists($hostId)) {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }

        $contexts = [
            HostGroup::SERIALIZER_GROUP_MAIN,
        ];

        $hostGroups = $this->monitoring
            ->filterByContact($this->getUser())
            ->findHostGroups(true, false, $hostId);

        $context = (new Context())
            ->setGroups($contexts)
            ->enableMaxDepth();

        return $this->view([
            'result' => $hostGroups,
            'meta' => $requestParameters->toArray()
        ])->setContext($context);
    }
}
