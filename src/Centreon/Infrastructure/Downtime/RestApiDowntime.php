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
 *Controller
 */
declare(strict_types=1);

namespace Centreon\Infrastructure\Downtime;

use Centreon\Domain\Downtime\Interfaces\DowntimeServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;

/**
 * Use to manage all downtime requests.
 *
 * @package Centreon\Infrastructure\Downtime\Controller
 */
class RestApiDowntime extends AbstractFOSRestController
{

    /**
     * @var DowntimeServiceInterface
     */
    private $downtimeService;
    /**
     * @var MonitoringServiceInterface
     */
    private $monitoringService;

    public function __construct(
        DowntimeServiceInterface $downtimeService,
        MonitoringServiceInterface $monitoringService
    ) {
        $this->downtimeService = $downtimeService;
        $this->monitoringService = $monitoringService;
    }

    /**
     * Entry point to find the last hosts downtimes.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/hosts/downtimes",
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.downtime.findHostDowntime")
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findHostDowntime(RequestParametersInterface $requestParameters): View
    {
        $hostsDowntime = $this->downtimeService
            ->filterByContact($this->getUser())
            ->findHostDowntime();

        $context = (new Context())->setGroups(['dwt_main']);

        return $this->view([
            'result' => $hostsDowntime,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }

    /**
     * Entry point to find one host downtime.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/downtimes/{downtimeId}",
     *     requirements={"downtimeId"="\d+"},
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.downtime.findOneHostDowntime")
     * @param int $downtimeId Downtime id to find
     * @return View
     * @throws \Exception
     */
    public function findOneHostDowntime(int $downtimeId): View
    {
        $hostDowntime = $this->downtimeService
            ->filterByContact($this->getUser())
            ->findOneDowntime($downtimeId);

        $context = (new Context())->setGroups(['dwt_main']);

        if ($hostDowntime !== null) {
            $context = (new Context())
                ->setGroups(['dwt_main'])
                ->enableMaxDepth();

            return $this->view($hostDowntime)->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }

    /**
     * Entry point to find the last downtimes.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/downtimes",
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.downtime.findDowntime")
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findDowntime(RequestParametersInterface $requestParameters): View
    {
        $hostsDowntime = $this->downtimeService
            ->filterByContact($this->getUser())
            ->findDowntime();

        $context = (new Context())->setGroups(['dwt_main', 'dwt_service']);

        return $this->view([
            'result' => $hostsDowntime,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }

    /**
     * Entry point to find the last downtimes linked to a host.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/hosts/{hostId}/downtimes",
     *     requirements={"hostId"="\d+"},
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.downtime.findDowntimesByHost")
     * @param RequestParametersInterface $requestParameters
     * @param int $hostId Host id for which we want to find downtimes
     * @return View
     * @throws \Exception
     */
    public function findDowntimesByHost(RequestParametersInterface $requestParameters, int $hostId): View
    {
        $this->monitoringService->filterByContact($this->getUser());

        if ($this->monitoringService->isHostExists($hostId)) {
            $downtimesByHost = $this->downtimeService
                ->filterByContact($this->getUser())
                ->findDowntimesByHost($hostId);

            $context = (new Context())->setGroups(['dwt_main']);

            return $this->view([
                'result' => $downtimesByHost,
                'meta' => [
                    'pagination' => $requestParameters->toArray()
                ]
            ])->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }
}
