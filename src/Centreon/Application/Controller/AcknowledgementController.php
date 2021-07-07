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
use Centreon\Domain\Monitoring\ResourceService;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use JMS\Serializer\Exception\ValidationFailedException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
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
     * This function will ensure that the POST data is valid
     * regarding validation constraints defined and will return
     * the decoded JSON content
     *
     * @param Request $request
     * @param string $jsonValidatorFile
     * @return array $results
     * @throws InvalidArgumentException
     */
    private function validateAndRetrievePostData(Request $request, string $jsonValidatorFile): array
    {
        $results = json_decode((string) $request->getContent(), true);
        if (!is_array($results)) {
            throw new \InvalidArgumentException(_('Error when decoding sent data'));
        }

        /*
        * Validate the content of the POST request against the JSON schema validator
        */
        $validator = new Validator();
        $bodyContent = json_decode((string) $request->getContent());
        $file = 'file://' . __DIR__ . '/../../../../config/json_validator/latest/Centreon/' . $jsonValidatorFile;
        $validator->validate(
            $bodyContent,
            (object) ['$ref' => $file],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if (!$validator->isValid()) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s" . PHP_EOL, $error['property'], $error['message']);
            }
            throw new \InvalidArgumentException($message);
        }

        return $results;
    }

    /**
     * Entry point to find the hosts acknowledgements.
     *
     * @param RequestParametersInterface $requestParameters
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function findHostsAcknowledgements(RequestParametersInterface $requestParameters, Request $request): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $isBeta = (bool) $request->attributes->get('version.is_beta');
        $hostsAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findHostsAcknowledgements();

        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_HOST);

        return $this->view(
            [
                'result' => $hostsAcknowledgements,
                'meta' => !$isBeta
                    ? ['pagination' => $requestParameters->toArray()]
                    : $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to find acknowledgements linked to a host.
     *
     * @param RequestParametersInterface $requestParameters
     * @param int $hostId
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgementsByHost(
        RequestParametersInterface $requestParameters,
        int $hostId,
        Request $request
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $isBeta = (bool) $request->attributes->get('version.is_beta');
        $hostsAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findAcknowledgementsByHost($hostId);

        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_HOST);

        return $this->view(
            [
                'result' => $hostsAcknowledgements,
                'meta' => !$isBeta
                    ? ['pagination' => $requestParameters->toArray()]
                    : $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to find the services acknowledgements.
     *
     * @param RequestParametersInterface $requestParameters
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function findServicesAcknowledgements(RequestParametersInterface $requestParameters, Request $request): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $isBeta = (bool) $request->attributes->get('version.is_beta');
        $servicesAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findServicesAcknowledgements();
        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_SERVICE);

        return $this->view(
            [
                'result' => $servicesAcknowledgements,
                'meta' => !$isBeta
                    ? ['pagination' => $requestParameters->toArray()]
                    : $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to find acknowledgements linked to a service.
     *
     * @param RequestParametersInterface $requestParameters
     * @param int $hostId
     * @param int $serviceId
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgementsByService(
        RequestParametersInterface $requestParameters,
        int $hostId,
        int $serviceId,
        Request $request
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $isBeta = (bool) $request->attributes->get('version.is_beta');
        $servicesAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findAcknowledgementsByService($hostId, $serviceId);
        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_SERVICE);

        return $this->view(
            [
                'result' => $servicesAcknowledgements,
                'meta' => !$isBeta
                    ? ['pagination' => $requestParameters->toArray()]
                    : $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * Entry point to find acknowledgements linked to a meta service.
     *
     * @param RequestParametersInterface $requestParameters
     * @param int $metaId
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgementsByMetaService(
        RequestParametersInterface $requestParameters,
        int $metaId,
        Request $request
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
        $errors = $entityValidator->validateEntity(
            Acknowledgement::class,
            json_decode($request->getContent(), true),
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
            $request->getContent(),
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

        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $errors = $entityValidator->validateEntity(
            Acknowledgement::class,
            json_decode($request->getContent(), true),
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
            $request->getContent(),
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

        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $errors = $entityValidator->validateEntity(
            Acknowledgement::class,
            json_decode($request->getContent(), true),
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
            $request->getContent(),
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
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgements(RequestParametersInterface $requestParameters, Request $request): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $isBeta = (bool) $request->attributes->get('version.is_beta');
        $acknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findAcknowledgements();

        $context = (new Context())->setGroups(Acknowledgement::SERIALIZER_GROUPS_SERVICE);

        return $this->view(
            [
                'result' => $acknowledgements,
                'meta' => !$isBeta
                    ? ['pagination' => $requestParameters->toArray()]
                    : $requestParameters->toArray()
            ]
        )->setContext($context);
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
         * @var Contact $contact
         */
        $contact = $this->getUser();
        $this->acknowledgementService->filterByContact($contact);

        // Validate the content of the DELETE request against the JSON schema validator
        $results = $this->validateAndRetrievePostData(
            $request,
            'Acknowledgement/DisacknowledgeResources.json'
        );

        $disacknowledgement = new Acknowledgement();
        if (isset($results['disacknowledgement']['with_services'])) {
            $disacknowledgement->setWithServices($results['disacknowledgement']['with_services']);
        }

        foreach ($results['resources'] as $resultingResource) {
            $resource = (new ResourceEntity())
                ->setType($resultingResource['type'])
                ->setId($resultingResource['id']);
            if (isset($resultingResource['parent']) && $resultingResource['parent'] !== null) {
                $resource->setParent(
                    (new ResourceEntity())
                        ->setId($resultingResource['parent']['id'])
                        ->setType(ResourceEntity::TYPE_HOST)
                );
            }

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
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function massAcknowledgeResources(
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
         * @var AckRequest $ackRequest
         */
        $ackRequest = $serializer->deserialize(
            (string)$request->getContent(),
            AckRequest::class,
            'json'
        );

        $this->acknowledgementService->filterByContact($contact);

        //validate input
        $errorList = new ConstraintViolationList();

        //validate resources
        $resources = $ackRequest->getResources() ?? [];
        foreach ($resources as $resource) {
            switch ($resource->getType()) {
                case ResourceEntity::TYPE_HOST:
                    $errorList->addAll(ResourceService::validateResource(
                        $entityValidator,
                        $resource,
                        ResourceEntity::VALIDATION_GROUP_ACK_HOST
                    ));
                    break;
                case ResourceEntity::TYPE_SERVICE:
                    $errorList->addAll(ResourceService::validateResource(
                        $entityValidator,
                        $resource,
                        ResourceEntity::VALIDATION_GROUP_ACK_SERVICE
                    ));
                    break;
                case ResourceEntity::TYPE_META:
                    $errorList->addAll(ResourceService::validateResource(
                        $entityValidator,
                        $resource,
                        ResourceEntity::VALIDATION_GROUP_ACK_META
                    ));
                    break;
                default:
                    throw new \RestBadRequestException(_('Incorrect resource type for acknowledgement'));
            }
        }

        //validate acknowledgement
        $acknowledgement = $ackRequest->getAcknowledgement();
        $errorList->addAll(
            $entityValidator->validate(
                $acknowledgement,
                null,
                Acknowledgement::VALIDATION_GROUP_ACK_RESOURCE
            )
        );

        if ($errorList->count() > 0) {
            throw new ValidationFailedException($errorList);
        }

        // set default values [sticky, persistent_comment] to true
        $acknowledgement->setSticky(true);
        $acknowledgement->setPersistentComment(true);

        foreach ($resources as $resource) {
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
