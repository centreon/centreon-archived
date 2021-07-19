<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

use Centreon\Application\Request\DowntimeRequest;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Downtime\Interfaces\DowntimeServiceInterface;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use JMS\Serializer\Exception\ValidationFailedException;
use JMS\Serializer\SerializerInterface;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Entity\EntityValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Centreon\Domain\Monitoring\Resources as ResourceEntity;
use Centreon\Domain\Monitoring\ResourceService;

/**
 * This class is design to manage all API REST about downtime requests
 *
 * @package Centreon\Infrastructure\Downtime\Controller
 */
class DowntimeController extends AbstractController
{
    // Groups for serialization
    public const SERIALIZER_GROUPS_HOST = ['downtime_host'];
    public const SERIALIZER_GROUPS_SERVICE = ['downtime_service'];

    private const VALIDATION_SCHEME_FOR_A_DOWNTIME = 'config/json_validator/latest/Centreon/Downtime/Downtime.json';
    private const VALIDATION_SCHEME_FOR_SEVERAL_DOWNTIMES =
        'config/json_validator/latest/Centreon/Downtime/Downtimes.json';

    /**
     * @var DowntimeServiceInterface
     */
    private $downtimeService;

    /**
     * @var MonitoringServiceInterface
     */
    private $monitoringService;

    /**
     * DowntimeController constructor.
     *
     * @param DowntimeServiceInterface $downtimeService
     * @param MonitoringServiceInterface $monitoringService
     */
    public function __construct(
        DowntimeServiceInterface $downtimeService,
        MonitoringServiceInterface $monitoringService
    ) {
        $this->downtimeService = $downtimeService;
        $this->monitoringService = $monitoringService;
    }

    /**
     * Entry point to add multiple host downtimes
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function addHostDowntimes(Request $request, SerializerInterface $serializer): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_ADD_HOST_DOWNTIME)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        /*
        * Validate the content of the request against the JSON schema validator
        */
        $this->validateOrFail($request, self::VALIDATION_SCHEME_FOR_SEVERAL_DOWNTIMES);
        /**
         * @var Downtime[] $downtimes
         */
        $downtimes = $serializer->deserialize(
            (string) $request->getContent(),
            'array<' . Downtime::class . '>',
            'json'
        );

        $this->monitoringService->filterByContact($contact);
        $this->downtimeService->filterByContact($contact);
        foreach ($downtimes as $downtime) {
            try {
                $host = $this->monitoringService->findOneHost($downtime->getResourceId());

                if ($host === null) {
                    throw new EntityNotFoundException(
                        sprintf(_('Host %d not found'), $downtime->getResourceId())
                    );
                }

                $this->downtimeService->addHostDowntime($downtime, $host);
            } catch (EntityNotFoundException $e) {
                continue;
            }
        }

        return $this->view();
    }

    /**
     * Entry point to add multiple service downtimes
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function addServiceDowntimes(Request $request, SerializerInterface $serializer): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_ADD_HOST_DOWNTIME)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $this->validateOrFail($request, self::VALIDATION_SCHEME_FOR_SEVERAL_DOWNTIMES);

        $this->monitoringService->filterByContact($contact);
        $this->downtimeService->filterByContact($contact);

        /**
         * @var Downtime[] $downtimes
         */
        $downtimes = $serializer->deserialize(
            (string) $request->getContent(),
            'array<' . Downtime::class . '>',
            'json'
        );

        foreach ($downtimes as $downtime) {
            try {
                $service = $this->monitoringService->findOneService(
                    $downtime->getParentResourceId(),
                    $downtime->getResourceId()
                );
                if ($service === null) {
                    throw new EntityNotFoundException(
                        sprintf(
                            _('Service %d on host %d not found'),
                            $downtime->getResourceId(),
                            $downtime->getParentResourceId()
                        )
                    );
                }

                $host = $this->monitoringService->findOneHost($downtime->getParentResourceId());
                $service->setHost($host);

                $this->downtimeService->addServiceDowntime($downtime, $service);
            } catch (EntityNotFoundException $e) {
                continue;
            }
        }

        return $this->view();
    }

    /**
     * Entry point to add a host downtime
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param int $hostId Host id for which we want to add a downtime
     * @return View
     * @throws \Exception
     */
    public function addHostDowntime(Request $request, SerializerInterface $serializer, int $hostId): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_ADD_HOST_DOWNTIME)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        /*
        * Validate the content of the request against the JSON schema validator
        */
        $this->validateOrFail($request, self::VALIDATION_SCHEME_FOR_A_DOWNTIME);

        /**
         * @var Downtime $downtime
         */
        $downtime = $serializer->deserialize(
            (string) $request->getContent(),
            Downtime::class,
            'json'
        );

        $this->monitoringService->filterByContact($contact);
        $host = $this->monitoringService->findOneHost($hostId);
        if ($host === null) {
            throw new EntityNotFoundException(
                sprintf(_('Host %d not found'), $hostId)
            );
        }

        $this->downtimeService->filterByContact($contact);
        $this->downtimeService->addHostDowntime($downtime, $host);
        return $this->view();
    }

    /**
     * Entry point to add a service downtime
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param int $hostId Host id linked to the service
     * @param int $serviceId Service id for which we want to add a downtime
     * @return View
     * @throws \Exception
     */
    public function addServiceDowntime(
        Request $request,
        SerializerInterface $serializer,
        int $hostId,
        int $serviceId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_ADD_SERVICE_DOWNTIME)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        /*
        * Validate the content of the request against the JSON schema validator
        */
        $this->validateOrFail($request, self::VALIDATION_SCHEME_FOR_A_DOWNTIME);

        /**
         * @var Downtime $downtime
         */
        $downtime = $serializer->deserialize((string) $request->getContent(), Downtime::class, 'json');

        $this->monitoringService->filterByContact($contact);

        $service = $this->monitoringService->findOneService($hostId, $serviceId);
        if ($service === null) {
            throw new EntityNotFoundException(
                sprintf(_('Service %d on host %d not found'), $serviceId, $hostId)
            );
        }

        $host = $this->monitoringService->findOneHost($hostId);
        $service->setHost($host);

        $this->downtimeService
            ->filterByContact($contact)
            ->addServiceDowntime($downtime, $service);
        return $this->view();
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

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $hostsDowntime = $this->downtimeService
            ->filterByContact($contact)
            ->findHostDowntimes();

        $context = (new Context())->setGroups(Downtime::SERIALIZER_GROUPS_MAIN);

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

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $servicesDowntimes = $this->downtimeService
            ->filterByContact($contact)
            ->findServicesDowntimes();

        $context = (new Context())->setGroups(Downtime::SERIALIZER_GROUPS_SERVICE);

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

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $this->monitoringService->filterByContact($contact);

        if ($this->monitoringService->isHostExists($hostId)) {
            $downtimesByHost = $this->downtimeService
                ->filterByContact($contact)
                ->findDowntimesByService($hostId, $serviceId);

            $context = (new Context())->setGroups(Downtime::SERIALIZER_GROUPS_SERVICE);

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

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $downtime = $this->downtimeService
            ->filterByContact($contact)
            ->findOneDowntime($downtimeId);

        if ($downtime !== null) {
            $context = (new Context())
                ->setGroups(Downtime::SERIALIZER_GROUPS_SERVICE)
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

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $hostsDowntime = $this->downtimeService
            ->filterByContact($contact)
            ->findDowntimes();

        $context = (new Context())->setGroups(Downtime::SERIALIZER_GROUPS_SERVICE);

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

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $this->monitoringService->filterByContact($contact);
        $withServices = $requestParameters->getExtraParameter('with_services') === 'true';

        if ($this->monitoringService->isHostExists($hostId)) {
            $downtimesByHost = $this->downtimeService
                ->filterByContact($contact)
                ->findDowntimesByHost($hostId, $withServices);

            $contextGroups = $withServices
                ? Downtime::SERIALIZER_GROUPS_SERVICE
                : Downtime::SERIALIZER_GROUPS_MAIN;
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

    /**
     * Entry point to bulk set downtime for resources (hosts and services)
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function massDowntimeResources(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        /**
         * @var DowntimeRequest $dtRequest
         */
        $dtRequest = $serializer->deserialize(
            (string)$request->getContent(),
            DowntimeRequest::class,
            'json'
        );

        $this->downtimeService->filterByContact($contact);

        //validate input
        $errorList = new ConstraintViolationList();

        //validate resources
        $resources = $dtRequest->getResources() ?? [];
        foreach ($resources as $resource) {
            if ($resource->getType() === ResourceEntity::TYPE_SERVICE) {
                $errorList->addAll(ResourceService::validateResource(
                    $entityValidator,
                    $resource,
                    ResourceEntity::VALIDATION_GROUP_DOWNTIME_SERVICE
                ));
            } elseif ($resource->getType() === ResourceEntity::TYPE_HOST) {
                $errorList->addAll(ResourceService::validateResource(
                    $entityValidator,
                    $resource,
                    ResourceEntity::VALIDATION_GROUP_DOWNTIME_HOST
                ));
            } else {
                throw new \RestBadRequestException(_('Incorrect resource type for downtime'));
            }
        }

        // validate downtime
        $downtime = $dtRequest->getDowntime();
        $errorList->addAll(
            $entityValidator->validate(
                $downtime,
                null,
                Downtime::VALIDATION_GROUP_DT_RESOURCE
            )
        );

        if ($errorList->count() > 0) {
            throw new ValidationFailedException($errorList);
        }

        foreach ($resources as $resource) {
            //start applying downtime process
            try {
                if ($this->hasDtRightsForResource($contact, $resource)) {
                    if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_ADD_SERVICE_DOWNTIME)) {
                        $downtime->setWithServices(false);
                    }

                    $this->downtimeService->addResourceDowntime(
                        $resource,
                        $downtime
                    );
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $this->view();
    }

    /**
     * @param Contact $contact
     * @param ResourceEntity $resouce
     * @return bool
     */
    private function hasDtRightsForResource(Contact $contact, ResourceEntity $resouce): bool
    {
        $hasRights = false;

        if ($resouce->getType() === ResourceEntity::TYPE_HOST) {
            $hasRights = $contact->isAdmin() || $contact->hasRole(Contact::ROLE_ADD_HOST_DOWNTIME);
        } elseif ($resouce->getType() === ResourceEntity::TYPE_SERVICE) {
            $hasRights = $contact->isAdmin() || $contact->hasRole(Contact::ROLE_ADD_SERVICE_DOWNTIME);
        }

        return $hasRights;
    }

    /**
     * This function will ensure that the POST data is valid regarding validation constraints defined.
     *
     * @param Request $request
     * @param string $jsonValidatorFile
     * @throws \InvalidArgumentException
     */
    private function validateOrFail(Request $request, string $jsonValidatorFile): void
    {
        $receivedData = json_decode((string) $request->getContent(), true);
        if (!is_array($receivedData)) {
            throw new \InvalidArgumentException(_('Error when decoding sent data'));
        }
        $centreonPath = $this->getParameter('centreon_path');
        /*
        * Validate the content of the POST request against the JSON schema validator
        */
        $validator = new Validator();
        $bodyContent = json_decode((string) $request->getContent());
        $file = 'file://' . $centreonPath . $jsonValidatorFile;
        $validator->validate(
            $bodyContent,
            (object) ['$ref' => $file],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if (!$validator->isValid()) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new \InvalidArgumentException($message);
        }
    }
}
