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

use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Acknowledgement\AcknowledgementService;
use Centreon\Domain\Acknowledgement\Interfaces\AcknowledgementServiceInterface;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\Exception\ValidationFailedException;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Used to manage all requests of hosts acknowledgements
 *
 * @package Centreon\Application\Controller
 */
class AcknowledgementController extends AbstractFOSRestController
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
     * Entry point to find the last hosts acknowledgements.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/acknowledgements/hosts",
     *     condition="request.attributes.get('version.is_beta') == true")
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findLastHostAcknowledgement(RequestParametersInterface $requestParameters): View
    {
        $hostsAcknowledgments = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findLastHostsAcknowledgements();

        $context = (new Context())->setGroups(['ack_main']);

        return $this->view([
            'result' => $hostsAcknowledgments,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }

    /**
     * Entry point to find the last services acknowledgements.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/acknowledgements/services",
     *     condition="request.attributes.get('version.is_beta') == true")
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findLastServiceAcknowledgement(RequestParametersInterface $requestParameters): View
    {
        $servicesAcknowledgments = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findLastServicesAcknowledgements();
        $context = (new Context())->setGroups(['ack_main', 'ack_service']);

        return $this->view([
            'result' => $servicesAcknowledgments,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }

    /**
     * Entry point to add a host acknowledgement.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Post(
     *     "/acknowledgements/hosts",
     *     condition="request.attributes.get('version.is_beta') == true")
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function addHostAcknowledgement(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer
    ): View {
        /**
         * @var $contact Contact
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
        } else {
            /**
             * @var $acknowledgement Acknowledgement
             */
            $acknowledgement = $serializer->deserialize(
                $request->getContent(),
                Acknowledgement::class,
                'json'
            );
            $this->acknowledgementService
                ->filterByContact($contact)
                ->addHostAcknowledgement($acknowledgement);
            return $this->view();
        }
    }

    /**
     * Entry point to disacknowledge an acknowledgement.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Delete(
     *     "/acknowledgements/hosts/{hostId}",
     *     condition="request.attributes.get('version.is_beta') == true")
     * @param int $hostId Host id for which we want to cancel the acknowledgement
     * @return View
     * @throws \Exception
     */
    public function disacknowledgeHostAcknowledgement(int $hostId): View
    {
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_DISACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }
        $this->acknowledgementService
            ->filterByContact($contact)
            ->disacknowledgeHostAcknowledgement($hostId);
        return $this->view();
    }

    /**
     * Entry point to remove a service acknowledgement.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Delete(
     *     "/acknowledgements/hosts/{hostId}/services/{serviceId}",
     *     condition="request.attributes.get('version.is_beta') == true")
     * @param int $hostId Host id linked to service
     * @param int $serviceId Service Id for which we want to cancel the acknowledgement
     * @return View
     * @throws \Exception
     */
    public function disacknowledgeServiceAcknowledgement(int $hostId, int $serviceId): View
    {
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_DISACKNOWLEDGEMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $this->acknowledgementService
            ->filterByContact($contact)
            ->disacknowledgeServiceAcknowledgement($hostId, $serviceId);
        return $this->view();
    }

    /**
     * Entry point to add a service acknowledgement.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Post(
     *     "/acknowledgements/services",
     *     condition="request.attributes.get('version.is_beta') == true")
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function addServiceAcknowledgement(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer
    ): View {
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
        } else {
            /**
             * @var $acknowledgement Acknowledgement
             */
            $acknowledgement = $serializer->deserialize(
                $request->getContent(),
                Acknowledgement::class,
                'json'
            );
            $this->acknowledgementService
                ->filterByContact($contact)
                ->addServiceAcknowledgement($acknowledgement);
            return $this->view();
        }
    }
}
