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

use Centreon\Application\Request\AckRequest;
use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Acknowledgement\AcknowledgementService;
use Centreon\Domain\Acknowledgement\Interfaces\AcknowledgementServiceInterface;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use JMS\Serializer\Exception\ValidationFailedException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

/**
 * Used to manage all requests of hosts acknowledgements
 *
 * @package Centreon\Application\Controller
 */
class AcknowledgementController extends AbstractController
{
    /**
     * @var AcknowledgementServiceInterface
     */
    private $acknowledgementService;

    private const ACKNOWLEDGE_RESOURCES_PAYLOAD_VALIDATION_FILE =
        __DIR__ . '/../../../../config/json_validator/latest/Centreon/Acknowledgement/AcknowledgeResources.json';

    private const DISACKNOWLEDGE_RESOURCES_PAYLOAD_VALIDATION_FILE =
        __DIR__ . '/../../../../config/json_validator/latest/Centreon/Acknowledgement/DisacknowledgeResources.json';
    /**
     * AcknowledgementController constructor.
     *
     * @param AcknowledgementServiceInterface $acknowledgementService
     */
    public function __construct(AcknowledgementServiceInterface $acknowledgementService)
    {
        $this->acknowledgementService = $acknowledgementService;
    }

    /**
     * Entry point to find the hosts acknowledgements.
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findHostsAcknowledgements(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $hostsAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findHostsAcknowledgements();

        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_HOST);

        return $this->view(
            [
                'result' => $hostsAcknowledgements,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to find acknowledgements linked to a host.
     *
     * @param RequestParametersInterface $requestParameters
     * @param int $hostId
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgementsByHost(
        RequestParametersInterface $requestParameters,
        int $hostId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $hostsAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findAcknowledgementsByHost($hostId);

        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_HOST);

        return $this->view(
            [
                'result' => $hostsAcknowledgements,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to find the services acknowledgements.
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findServicesAcknowledgements(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $servicesAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findServicesAcknowledgements();
        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_SERVICE);

        return $this->view(
            [
                'result' => $servicesAcknowledgements,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to find acknowledgements linked to a service.
     *
     * @param RequestParametersInterface $requestParameters
     * @param int $hostId
     * @param int $serviceId
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgementsByService(
        RequestParametersInterface $requestParameters,
        int $hostId,
        int $serviceId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $servicesAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findAcknowledgementsByService($hostId, $serviceId);
        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_SERVICE);

        return $this->view(
            [
                'result' => $servicesAcknowledgements,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to find acknowledgements linked to a meta service.
     *
     * @param RequestParametersInterface $requestParameters
     * @param int $metaId
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgementsByMetaService(
        RequestParametersInterface $requestParameters,
        int $metaId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $metaServicesAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findAcknowledgementsByMetaService($metaId);
        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_SERVICE);

        return $this->view(
            [
                'result' => $metaServicesAcknowledgements,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to add multiple host acknowledgements.
     *
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function addHostAcknowledgements(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_ACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        /**
         * @var Acknowledgement[] $acknowledgements
         */
        $acknowledgements = $serializer->deserialize(
            (string)$request->getContent(),
            'array<' . Acknowledgement::class . '>',
            'json'
        );

        $this->acknowledgementService->filterByContact($contact);

        foreach ($acknowledgements as $acknowledgement) {
            $errors = $entityValidator->validate(
                $acknowledgement,
                null,
                AcknowledgementService::VALIDATION_GROUPS_ADD_HOST_ACKS
            );

            if ($errors->count() > 0) {
                throw new ValidationFailedException($errors);
            }

            try {
                $this->acknowledgementService->addHostAcknowledgement($acknowledgement);
            } catch (EntityNotFoundException $e) {
                continue;
            }
        }

        return $this->view();
    }

    /**
     * Entry point to add multiple service acknowledgements.
     *
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function addServiceAcknowledgements(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        /**
         * @var Acknowledgement[] $acknowledgements
         */
        $acknowledgements = $serializer->deserialize(
            (string)$request->getContent(),
            'array<' . Acknowledgement::class . '>',
            'json'
        );

        $this->acknowledgementService->filterByContact($contact);

        foreach ($acknowledgements as $acknowledgement) {
            $errors = $entityValidator->validate(
                $acknowledgement,
                null,
                AcknowledgementService::VALIDATION_GROUPS_ADD_SERVICE_ACKS
            );

            if ($errors->count() > 0) {
                throw new ValidationFailedException($errors);
            }

            try {
                $this->acknowledgementService->addServiceAcknowledgement($acknowledgement);
            } catch (EntityNotFoundException $e) {
                continue;
            }
        }

        return $this->view();
    }

    /**
     * Entry point to add a host acknowledgement.
     *
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @param int $hostId
     * @return View
     * @throws \Exception
     */
    public function addHostAcknowledgement(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer,
        int $hostId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_ACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $payload = json_decode((string) $request->getContent(), true);

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Error when decoding your sent data');
        }

        $errors = $entityValidator->validateEntity(
            Acknowledgement::class,
            $payload,
            AcknowledgementService::VALIDATION_GROUPS_ADD_HOST_ACK,
            false // To avoid error message for missing fields
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        /**
         * @var Acknowledgement $acknowledgement
         */
        $acknowledgement = $serializer->deserialize(
            (string) $request->getContent(),
            Acknowledgement::class,
            'json'
        );
        $acknowledgement->setResourceId($hostId);

        $this->acknowledgementService
            ->filterByContact($contact)
            ->addHostAcknowledgement($acknowledgement);

        return $this->view();
    }

    /**
     * Entry point to add a service acknowledgement.
     *
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @param int $hostId
     * @param int $serviceId
     * @return View
     * @throws \Exception
     */
    public function addServiceAcknowledgement(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer,
        int $hostId,
        int $serviceId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $payload = json_decode((string) $request->getContent(), true);

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Error when decoding your sent data');
        }

        $errors = $entityValidator->validateEntity(
            Acknowledgement::class,
            $payload,
            AcknowledgementService::VALIDATION_GROUPS_ADD_SERVICE_ACK,
            false // To show errors on not expected fields
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        /**
         * @var Acknowledgement $acknowledgement
         */
        $acknowledgement = $serializer->deserialize(
            (string) $request->getContent(),
            Acknowledgement::class,
            'json'
        );
        $acknowledgement
            ->setParentResourceId($hostId)
            ->setResourceId($serviceId);

        $this->acknowledgementService
            ->filterByContact($contact)
            ->addServiceAcknowledgement($acknowledgement);

        return $this->view();
    }

    /**
     * Entry point to add a service acknowledgement.
     *
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @param int $metaId
     * @return View
     * @throws \Exception
     */
    public function addMetaServiceAcknowledgement(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer,
        int $metaId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $payload = json_decode((string) $request->getContent(), true);

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Error when decoding your sent data');
        }

        $errors = $entityValidator->validateEntity(
            Acknowledgement::class,
            $payload,
            AcknowledgementService::VALIDATION_GROUPS_ADD_SERVICE_ACK,
            false // To show errors on not expected fields
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        /**
         * @var Acknowledgement $acknowledgement
         */
        $acknowledgement = $serializer->deserialize(
            (string) $request->getContent(),
            Acknowledgement::class,
            'json'
        );
        $acknowledgement
            ->setResourceId($metaId);

        $this->acknowledgementService
            ->filterByContact($contact)
            ->addMetaServiceAcknowledgement($acknowledgement);

        return $this->view();
    }

    /**
     * Entry point to disacknowledge an acknowledgement.
     *
     * @param int $hostId Host id for which we want to cancel the acknowledgement
     * @return View
     * @throws \Exception
     */
    public function disacknowledgeHost(int $hostId): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_DISACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $this->acknowledgementService
            ->filterByContact($contact)
            ->disacknowledgeHost($hostId);

        return $this->view();
    }

    /**
     * Entry point to remove a service acknowledgement.
     *
     * @param int $hostId Host id linked to service
     * @param int $serviceId Service Id for which we want to cancel the acknowledgement
     * @return View
     * @throws \Exception
     */
    public function disacknowledgeService(int $hostId, int $serviceId): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_DISACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $this->acknowledgementService
            ->filterByContact($contact)
            ->disacknowledgeService($hostId, $serviceId);

        return $this->view();
    }

    /**
     * Entry point to remove a metaservice acknowledgement.
     *
     * @param int $metaId ID of the metaservice
     * @return View
     * @throws \Exception
     */
    public function disacknowledgeMetaService(int $metaId): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_DISACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $this->acknowledgementService
            ->filterByContact($contact)
            ->disacknowledgeMetaService($metaId);

        return $this->view();
    }

    /**
     * Entry point to find one acknowledgement.
     *
     * @param int $acknowledgementId Acknowledgement id to find
     * @return View
     * @throws \Exception
     */
    public function findOneAcknowledgement(int $acknowledgementId): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $acknowledgement = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findOneAcknowledgement($acknowledgementId);

        if ($acknowledgement !== null) {
            $context = (new Context())
                ->setGroups(Acknowledgement::SERIALIZER_GROUPS_SERVICE)
                ->enableMaxDepth();

            return $this->view($acknowledgement)->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }

    /**
     * Entry point to find all acknowledgements.
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgements(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $acknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findAcknowledgements();

        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_SERVICE);

        return $this->view(
            [
                'result' => $acknowledgements,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Creates a ResourceEntity with payload sent
     *
     * @param array<string, mixed> $payload
     * @return ResourceEntity
     */
    private function createResourceEntity(array $payload): ResourceEntity
    {
        $resource = (new ResourceEntity())
            ->setType($payload['type'])
            ->setId($payload['id']);

        if ($payload['parent'] !== null) {
            $resource->setParent(
                (new ResourceEntity())
                    ->setId($payload['parent']['id'])
                    ->setType(ResourceEntity::TYPE_HOST)
            );
        }

        return $resource;
    }

    /**
     * Entry point to bulk disacknowledge resources (hosts and services)
     * @param Request $request
     * @return View
     */
    public function massDisacknowledgeResources(Request $request): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * Validate POST data for disacknowledge resources
         */
        $payload = $this->validateAndRetrieveDataSent($request, self::DISACKNOWLEDGE_RESOURCES_PAYLOAD_VALIDATION_FILE);

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $this->acknowledgementService->filterByContact($contact);

        $disacknowledgement = new Acknowledgement();

        if (isset($payload['disacknowledgement']['with_services'])) {
            $disacknowledgement->setWithServices($payload['disacknowledgement']['with_services']);
        }

        foreach ($payload['resources'] as $resourcePayload) {
            $resource = $this->createResourceEntity($resourcePayload);

            // start disacknowledgement process
            try {
                if ($this->hasDisackRightsForResource($contact, $resource)) {
                    if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_DISACKNOWLEDGEMENT)) {
                        $disacknowledgement->setWithServices(false);
                    }

                    $this->acknowledgementService->disacknowledgeResource(
                        $resource,
                        $disacknowledgement
                    );
                }
            } catch (EntityNotFoundException $e) {
                // don't stop process if a resource is not found
                continue;
            }
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Entry point to bulk acknowledge resources (hosts and services)
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function massAcknowledgeResources(
        Request $request,
        SerializerInterface $serializer
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * Validate POST data for acknowledge resources
         */
        $this->validateDataSent($request, self::ACKNOWLEDGE_RESOURCES_PAYLOAD_VALIDATION_FILE);

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        /**
         * @var AckRequest $ackRequest
         */
        $ackRequest = $serializer->deserialize(
            (string) $request->getContent(),
            AckRequest::class,
            'json'
        );

        $this->acknowledgementService->filterByContact($contact);

        $acknowledgement = $ackRequest->getAcknowledgement();

        foreach ($ackRequest->getResources() as $resource) {
            // start acknowledgement process
            try {
                if ($this->hasAckRightsForResource($contact, $resource)) {
                    if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT)) {
                        $acknowledgement->setWithServices(false);
                    }

                    $this->acknowledgementService->acknowledgeResource(
                        $resource,
                        $acknowledgement
                    );
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Check if the resource can be acknowledged
     *
     * @param Contact $contact
     * @param ResourceEntity $resource
     * @return bool
     */
    private function hasAckRightsForResource(Contact $contact, ResourceEntity $resource): bool
    {
        if ($contact->isAdmin()) {
            return true;
        }

        $hasRights = false;

        switch ($resource->getType()) {
            case ResourceEntity::TYPE_HOST:
                $hasRights = $contact->hasRole(Contact::ROLE_HOST_ACKNOWLEDGEMENT);
                break;
            case ResourceEntity::TYPE_SERVICE:
            case ResourceEntity::TYPE_META:
                $hasRights = $contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT);
                break;
        }

        return $hasRights;
    }

    /**
     * Check if the resource can be disacknowledged
     *
     * @param Contact $contact
     * @param ResourceEntity $resource
     * @return bool
     */
    private function hasDisackRightsForResource(Contact $contact, ResourceEntity $resource): bool
    {
        if ($contact->isAdmin()) {
            return true;
        }

        $hasRights = false;

        switch ($resource->getType()) {
            case ResourceEntity::TYPE_HOST:
                $hasRights = $contact->hasRole(Contact::ROLE_HOST_DISACKNOWLEDGEMENT);
                break;
            case ResourceEntity::TYPE_SERVICE:
            case ResourceEntity::TYPE_META:
                $hasRights = $contact->hasRole(Contact::ROLE_SERVICE_DISACKNOWLEDGEMENT);
                break;
        }

        return $hasRights;
    }
}
