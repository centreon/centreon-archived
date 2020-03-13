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
use Centreon\Domain\Monitoring\Entity\AckEventObject;
use Centreon\Domain\Monitoring\Entity\CommentEventObject;
use Centreon\Domain\Monitoring\Entity\DowntimeEventObject;
use Centreon\Domain\Monitoring\Entity\LogEventObject;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Model\Log;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\ServiceGroup;
use Centreon\Domain\Monitoring\TimelineEvent;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
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
            ->setGroups([
                Service::SERIALIZER_GROUP_MAIN,
                Service::SERIALIZER_GROUP_WITH_HOST,
                Host::SERIALIZER_GROUP_MIN
            ])
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
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getServicesByServiceGroups(RequestParametersInterface $requestParameters): View
    {
        $withHost = $requestParameters->getExtraParameter('show_host') === 'true';
        $withServices = $requestParameters->getExtraParameter('show_service') === 'true';

        $contexts = [ServiceGroup::SERIALIZER_GROUP_MAIN];

        $contextsWithHosts = [ServiceGroup::SERIALIZER_GROUP_WITH_HOST, Host::SERIALIZER_GROUP_MIN];
        $contextsWithService = [Host::SERIALIZER_GROUP_WITH_SERVICES, Service::SERIALIZER_GROUP_MIN];

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
     * Entry point to get all servicegroups attached to host-service
     *
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getServiceGroupsByHostAndService(
        int $hostId,
        int $serviceId,
        RequestParametersInterface $requestParameters
    ): View {
        $this->monitoring->filterByContact($this->getUser());

        if ($this->monitoring->isServiceExists($hostId, $serviceId)) {
            $serviceGroups = $this->monitoring->findServiceGroupsByHostAndService($hostId, $serviceId);

            $context = (new Context())
                ->setGroups([ServiceGroup::SERIALIZER_GROUP_MAIN])
                ->enableMaxDepth();

            return $this->view(
                [
                    'result' => $serviceGroups,
                    'meta' => $requestParameters->toArray()
                ]
            )->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }

    /**
     * Entry point to get timeline for a host-service
     * @param int $hostId id of host
     * @param int $serviceId id of service
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws \Exception
     */
    public function getServiceTimeline(
        int $hostId,
        int $serviceId,
        RequestParametersInterface $requestParameters
    ): View {

        $this->monitoring->filterByContact($this->getUser());

        if ($this->monitoring->isServiceExists($hostId, $serviceId)) {
            $timeline = $this->monitoring->findTimelineEvents($hostId, $serviceId);

            $context = (new Context())
                ->setGroups([
                    LogEventObject::SERIALIZER_GROUP_LIST,
                    CommentEventObject::SERIALIZER_GROUP_LIST,
                    DowntimeEventObject::SERIALIZER_GROUP_LIST,
                    AckEventObject::SERIALIZER_GROUP_LIST,
                    TimelineEvent::SERIALIZER_GROUP_LIST,
                ])
                ->enableMaxDepth();

            return $this->view(
                [
                    'result' => $timeline,
                    'meta' => $requestParameters->toArray()
                ]
            )->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }
}
