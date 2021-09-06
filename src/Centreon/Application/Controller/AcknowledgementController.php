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

use JsonSchema\Validator;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Context\Context;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Log\LoggerTrait;
use JsonSchema\Constraints\Constraint;
use JMS\Serializer\SerializerInterface;
use Centreon\Domain\Entity\EntityValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Exception\EntityNotFoundException;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Acknowledgement\AcknowledgementService;
use Centreon\Domain\Acknowledgement\AcknowledgementException;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\Acknowledgement\Interfaces\AcknowledgementServiceInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator as Validators;
use Centreon\Infrastructure\Monitoring\Acknowledgement\API\Model\MassAcknowledgeResourceRequest;

/**
 * Used to manage all requests of hosts acknowledgements
 *
 * @package Centreon\Application\Controller
 */
class AcknowledgementController extends AbstractController
{
    use LoggerTrait;

    private const VALIDATION_SCHEME_FOR_AN_ACKNOWLEDGEMENT =
        'config/json_validator/latest/Centreon/Acknowledgement/Acknowledgement.json';
    private const VALIDATION_SCHEME_FOR_SEVERAL_ACKNOWLEDGEMENTS =
        'config/json_validator/latest/Centreon/Acknowledgement/Acknowledgements.json';

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

        /*
        * Validate the content of the request against the JSON schema validator
        */
        $this->validateOrFail($request, self::VALIDATION_SCHEME_FOR_SEVERAL_ACKNOWLEDGEMENTS);

        /**
         * @var Acknowledgement[] $acknowledgements
         */
        $acknowledgements = $serializer->deserialize(
            (string) $request->getContent(),
            'array<' . Acknowledgement::class . '>', // @phpstan-ignore-line
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
                $this->warning(_('Acknowledgement failed for the host'), ['hostId' => $acknowledgement->getHostId()]);
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

        /*
        * Validate the content of the request against the JSON schema validator
        */
        $this->validateOrFail($request, self::VALIDATION_SCHEME_FOR_SEVERAL_ACKNOWLEDGEMENTS);

        /**
         * @var Acknowledgement[] $acknowledgements
         */
        $acknowledgements = $serializer->deserialize(
            (string) $request->getContent(),
            'array<' . Acknowledgement::class . '>', // @phpstan-ignore-line
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
                $this->warning(
                    _('Acknowledgement failed for the service'),
                    [
                        'hostId' => $acknowledgement->getHostId(),
                        'serviceId' => $acknowledgement->getServiceId()
                    ]
                );
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

        /*
        * Validate the content of the request against the JSON schema validator
        */
        $this->validateOrFail($request, self::VALIDATION_SCHEME_FOR_AN_ACKNOWLEDGEMENT);

        $content = json_decode($request->getContent(), true);

        $errors = $entityValidator->validateEntity(
            Acknowledgement::class,
            $content,
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
         * @var Contact|null
         */
        $contact = $this->getUser();
        if ($contact === null) {
            // @todo add log 'Could not find the contact'
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        /*
        * Validate the content of the request against the JSON schema validator
        */
        $this->validateOrFail($request, self::VALIDATION_SCHEME_FOR_AN_ACKNOWLEDGEMENT);

        $content = json_decode($request->getContent(), true);

        $errors = $entityValidator->validateEntity(
            Acknowledgement::class,
            $content,
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
         * @var Contact|null
         */
        $contact = $this->getUser();

        if ($contact === null) {
            // @todo add log
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        /*
        * Validate the content of the request against the JSON schema validator
        */
        $this->validateOrFail($request, self::VALIDATION_SCHEME_FOR_AN_ACKNOWLEDGEMENT);

        $content = json_decode($request->getContent(), true);

        $errors = $entityValidator->validateEntity(
            Acknowledgement::class,
            $content,
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
         * @var Contact|null
         */
        $contact = $this->getUser();

        if ($contact === null) {
            // @todo add log
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

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
         * @var Contact|null
         */
        $contact = $this->getUser();

        if ($contact === null) {
            // @todo add log
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

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
         * @var Contact|null
         */
        $contact = $this->getUser();

        if ($contact === null) {
            // @todo add log
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

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
     * Entry point to bulk disacknowledge resources (hosts and services)
     * @param Request $request
     * @return View
     */
    public function massDisacknowledgeResources(
        Request $request,
        Validators\Interfaces\MassiveDisacknowledgementValidatorInterface $massiveDisacknowledgementValidator
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        $this->acknowledgementService->filterByContact($contact);

        $disacknowledgementPayload = json_decode((string) $request->getContent(), true);

        if (!is_array($disacknowledgementPayload)) {
            throw new \InvalidArgumentException(_('Error when decoding sent data'));
        }

        $massiveDisacknowledgementValidator->validateOrFail($disacknowledgementPayload);

        $disacknowledgement = new Acknowledgement();
        if (isset($disacknowledgementPayload['disacknowledgement']['with_services'])) {
            $disacknowledgement->setWithServices(
                $disacknowledgementPayload['disacknowledgement']['with_services']
            );
        }

        foreach ($disacknowledgementPayload['resources'] as $resultingResource) {
            $monitoringResource = new MonitoringResource(
                (int) $resultingResource['id'],
                $resultingResource['name'],
                $resultingResource['type']
            );
            if (isset($resultingResource['parent']) && $resultingResource['parent'] !== null) {
                $monitoringResourceParent = new MonitoringResource(
                    (int) $resultingResource['parent']['id'],
                    $resultingResource['parent']['name'],
                    $resultingResource['parent']['type'],
                );
                $monitoringResource->setParent($monitoringResourceParent);
            }

            // start disacknowledgement process
            try {
                if ($this->hasDisackRightsForResource($contact, $monitoringResource)) {
                    if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_DISACKNOWLEDGEMENT)) {
                        $disacknowledgement->setWithServices(false);
                    }

                    $this->acknowledgementService->disacknowledgeResource(
                        $monitoringResource,
                        $disacknowledgement
                    );
                }
            } catch (EntityNotFoundException $e) {
                // don't stop process if a resource is not found
                $this->warning(
                    _('Disacknowledgement failed for the resource'),
                    [
                        'id' => $monitoringResource->getId(),
                        'name' => $monitoringResource->getName(),
                        'type' => $monitoringResource->getType()
                    ]
                );
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
        SerializerInterface $serializer,
        Validators\Interfaces\MassiveAcknowledgementValidatorInterface $massiveAcknowledgementValidator
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $this->acknowledgementService->filterByContact($contact);

        $payload = (string) $request->getContent();
        $acknowledgementPayload = json_decode($payload, true);

        if (!is_array($acknowledgementPayload)) {
            throw new \InvalidArgumentException(_('Error when decoding sent data'));
        }

        // validate the payload sent
        $massiveAcknowledgementValidator->validateOrFail($acknowledgementPayload);

        /**
         * @var MassAcknowledgeResourceRequest $ackRequest
         */
        $massAcknowledgeResourceRequest = $serializer->deserialize(
            $payload,
            MassAcknowledgeResourceRequest::class,
            'json'
        );

        // Get acknowledgement entity
        $acknowledgement = $massAcknowledgeResourceRequest->getAcknowledgement();

        // set default values [sticky, persistent_comment] to true
        $acknowledgement->setSticky(true);
        $acknowledgement->setPersistentComment(true);

        foreach ($ackRequest->getMonitoringResources() as $monitoringResource) {
            /**
             * Start the acknowledgement process.
             * Failed acknowledgements are passed to avoid others to fail.
             */
            try {
                if ($this->hasAckRightsForResource($contact, $monitoringResource)) {
                    if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT)) {
                        $acknowledgement->setWithServices(false);
                    }

                    $this->acknowledgementService->acknowledgeResource(
                        $monitoringResource,
                        $acknowledgement
                    );
                }
            } catch (\Exception $e) {
                $this->warning(
                    _('Disacknowledgement failed for the resource'),
                    [
                        'id' => $monitoringResource->getId(),
                        'name' => $monitoringResource->getName(),
                        'type' => $monitoringResource->getType()
                    ]
                );
                continue;
            }
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Check if the resource can be acknowledged
     *
     * @param Contact $contact
     * @param MonitoringResource $monitoringResource
     * @return bool
     */
    private function hasAckRightsForResource(Contact $contact, MonitoringResource $monitoringResource): bool
    {
        if ($contact->isAdmin()) {
            return true;
        }

        $hasRights = false;

        switch ($monitoringResource->getType()) {
            case MonitoringResource::TYPE_HOST:
                $hasRights = $contact->hasRole(Contact::ROLE_HOST_ACKNOWLEDGEMENT);
                break;
            case MonitoringResource::TYPE_SERVICE:
            case MonitoringResource::TYPE_META:
                $hasRights = $contact->hasRole(Contact::ROLE_SERVICE_ACKNOWLEDGEMENT);
                break;
        }

        return $hasRights;
    }

    /**
     * Check if the resource can be disacknowledged
     *
     * @param Contact $contact
     * @param MonitoringResource $monitoringResource
     * @return bool
     */
    private function hasDisackRightsForResource(Contact $contact, MonitoringResource $monitoringResource): bool
    {
        if ($contact->isAdmin()) {
            return true;
        }

        $hasRights = false;

        switch ($monitoringResource->getType()) {
            case MonitoringResource::TYPE_HOST:
                $hasRights = $contact->hasRole(Contact::ROLE_HOST_DISACKNOWLEDGEMENT);
                break;
            case MonitoringResource::TYPE_SERVICE:
            case MonitoringResource::TYPE_META:
                $hasRights = $contact->hasRole(Contact::ROLE_SERVICE_DISACKNOWLEDGEMENT);
                break;
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

        if (is_string($centreonPath) === false) {
            throw new InvalidConfigurationException(
                _('Failed to get centreon path in configuration')
            );
        }
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
