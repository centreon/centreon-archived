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
 *Controller
 */
declare(strict_types=1);

namespace Centreon\Application\Controller;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Downtime\Interfaces\DowntimeServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\Service\JsonValidator\Interfaces\JsonValidatorInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use JMS\Serializer\Exception\ValidationFailedException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class is design to manage all API REST about downtime requests
 *
 * @package Centreon\Infrastructure\Downtime\Controller
 */
class DowntimeController extends AbstractController
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
     * Entry point to add a host downtime
     *
     * @param Request $request
     * @param JsonValidatorInterface $jsonValidator
     * @param SerializerInterface $serializer
     * @param int $hostId Host id for which we want to add a downtime
     * @param string $version
     * @return View
     * @throws \Exception
     */
    public function addHostDowntime(
        Request $request,
        JsonValidatorInterface $jsonValidator,
        SerializerInterface $serializer,
        int $hostId,
        string $version
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if ($contact === null || (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_ADD_HOST_DOWNTIME))) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }
        $errors = $jsonValidator
            ->forVersion($version)
            ->validate((string) $request->getContent(), 'add_downtime');

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $postData = json_decode((string) $request->getContent(), true);
        $showService = $postData['show_services'] ?? false;
        $this->monitoringService->filterByContact($contact);
        $host = $this->monitoringService->findOneHost($hostId);

        if ($host === null) {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }

        $this->downtimeService->filterByContact($contact);

        /**
         * @var Downtime $downtime
         */
        $downtime = $serializer->deserialize(
            (string)$request->getContent(),
            Downtime::class,
            'json'
        );

        $this->downtimeService->addHostDowntime($downtime, $host);

        if ($showService === true) {
            $services = $this->monitoringService->findServicesByHost($hostId);
            foreach ($services as $service) {
                $service->setHost($host);
            }
            $this->downtimeService->addServicesDowntime($downtime, $services);
        }
        return $this->view();
    }

    /**
     * Entry point to add a service downtime
     *
     * @param Request $request
     * @param JsonValidatorInterface $jsonValidator
     * @param SerializerInterface $serializer
     * @param int $hostId Host id linked to the service
     * @param int $serviceId Service id for which we want to add a downtime
     * @param string $version
     * @return View
     * @throws \Exception
     */
    public function addServiceDowntime(
        Request $request,
        JsonValidatorInterface $jsonValidator,
        SerializerInterface $serializer,
        int $hostId,
        int $serviceId,
        string $version
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if ($contact === null || (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_ADD_SERVICE_DOWNTIME))) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $errors = $jsonValidator
            ->forVersion($version)
            ->validate((string) $request->getContent(), 'add_downtime');

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        } else {
            /**
             * @var Downtime $downtime
             */
            $downtime = $serializer->deserialize(
                (string) $request->getContent(),
                Downtime::class,
                'json'
            );
            $this->monitoringService->filterByContact($contact);

            $service = $this->monitoringService->findOneService($hostId, $serviceId);
            if ($service === null) {
                return View::create(null, Response::HTTP_NOT_FOUND, []);
            }

            $host = $this->monitoringService->findOneHost($hostId);
            $service->setHost($host);

            $this->downtimeService
                ->filterByContact($contact)
                ->addServicesDowntime($downtime, [$service]);
            return $this->view();
        }
    }

    /**
     * Entry point to find the last hosts downtimes.
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findHostDowntimes(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $contact = $this->getUser();
        if ($contact === null) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }
        $hostsDowntime = $this->downtimeService
            ->filterByContact($contact)
            ->findHostDowntimes();

        $context = (new Context())->setGroups([
            Downtime::SERIALIZER_GROUP_MAIN,
        ]);

        return $this->view([
            'result' => $hostsDowntime,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }

    /**
     * Entry point to find the last services downtimes.
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findServiceDowntimes(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $contact = $this->getUser();
        if ($contact === null) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }
        $servicesDowntimes = $this->downtimeService
            ->filterByContact($contact)
            ->findServicesDowntimes();

        $context = (new Context())->setGroups([
            Downtime::SERIALIZER_GROUP_MAIN,
            Downtime::SERIALIZER_GROUP_SERVICE,
        ]);

        return $this->view([
            'result' => $servicesDowntimes,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }

    /**
     * Entry point to find the last downtimes linked to a service.
     *
     * @param RequestParametersInterface $requestParameters
     * @param int $hostId Host id linked to this service
     * @param int $serviceId Service id for which we want to find downtimes
     * @return View
     * @throws \Exception
     */
    public function findDowntimesByService(
        RequestParametersInterface $requestParameters,
        int $hostId,
        int $serviceId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $contact = $this->getUser();
        if ($contact === null) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }
        $this->monitoringService->filterByContact($contact);

        if ($this->monitoringService->isHostExists($hostId)) {
            $downtimesByHost = $this->downtimeService
                ->filterByContact($contact)
                ->findDowntimesByService($hostId, $serviceId);

            $context = (new Context())->setGroups([
                Downtime::SERIALIZER_GROUP_MAIN,
                Downtime::SERIALIZER_GROUP_SERVICE,
            ]);

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

    /**
     * Entry point to find one host downtime.
     *
     * @param int $downtimeId Downtime id to find
     * @return View
     * @throws \Exception
     */
    public function findOneDowntime(int $downtimeId): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $contact = $this->getUser();
        if ($contact === null) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }
        $downtime = $this->downtimeService
            ->filterByContact($contact)
            ->findOneDowntime($downtimeId);

        if ($downtime !== null) {
            $context = (new Context())
                ->setGroups([
                    Downtime::SERIALIZER_GROUP_MAIN,
                    Downtime::SERIALIZER_GROUP_SERVICE,
                ])
                ->enableMaxDepth();

            return $this->view($downtime)->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }

    /**
     * Entry point to find the last downtimes.
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findDowntimes(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $contact = $this->getUser();
        if ($contact === null) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }
        $hostsDowntime = $this->downtimeService
            ->filterByContact($contact)
            ->findDowntimes();

        $context = (new Context())->setGroups([
                Downtime::SERIALIZER_GROUP_MAIN,
                Downtime::SERIALIZER_GROUP_SERVICE,
            ]);

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
     * @param RequestParametersInterface $requestParameters
     * @param int $hostId Host id for which we want to find downtimes
     * @return View
     * @throws \Exception
     */
    public function findDowntimesByHost(RequestParametersInterface $requestParameters, int $hostId): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $contact = $this->getUser();
        if ($contact === null) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }
        $this->monitoringService->filterByContact($contact);
        $withServices = $requestParameters->getExtraParameter('with_services') === 'true';

        if ($this->monitoringService->isHostExists($hostId)) {
            $downtimesByHost = $this->downtimeService
                ->filterByContact($contact)
                ->findDowntimesByHost($hostId, $withServices);

            $contextGroups = $withServices
                ? [
                    Downtime::SERIALIZER_GROUP_MAIN,
                    Downtime::SERIALIZER_GROUP_SERVICE,
                ]
                : [
                    Downtime::SERIALIZER_GROUP_MAIN,
                ];
            $context = (new Context())->setGroups($contextGroups)->enableMaxDepth();

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

    /**
     * Entry point to cancel one downtime.
     *
     * @param int $downtimeId Downtime id to cancel
     * @return View
     * @throws \Exception
     */
    public function cancelOneDowntime(int $downtimeId): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if ($contact === null) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $downtime = $this->downtimeService
            ->filterByContact($contact)
            ->findOneDowntime($downtimeId);

        if ($downtime === null) {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
        $host = $this->monitoringService
            ->filterByContact($contact)
            ->findOneHost($downtime->getHostId());

        if ($host === null) {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }

        if (!$contact->isAdmin()) {
            $isServiceDowntime = $downtime->getServiceId() !== null;
            $svcCancel = $contact->hasRole(Contact::ROLE_CANCEL_SERVICE_DOWNTIME);
            $hostCancel = $contact->hasRole(Contact::ROLE_CANCEL_HOST_DOWNTIME);
            if (($isServiceDowntime && !$svcCancel) || (!$isServiceDowntime && !$hostCancel)) {
                return $this->view(null, Response::HTTP_UNAUTHORIZED);
            }
        }

        $this->downtimeService->cancelDowntime($downtimeId, $host);
        return $this->view();
    }
}
