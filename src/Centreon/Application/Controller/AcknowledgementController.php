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
     * Entry point to find the hosts acknowledgements.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/hosts/acknowledgements",
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.acknowledgement.findHostsAcknowledgements")
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findHostsAcknowledgements(RequestParametersInterface $requestParameters): View
    {
        $hostsAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findHostsAcknowledgements();

        $context = (new Context())->setGroups(['ack_main']);

        return $this->view([
            'result' => $hostsAcknowledgements,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }

    /**
     * Entry point to find acknowledgements linked to a host.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/hosts/{hostId}/acknowledgements",
     *     requirements={"hostId"="\d+"},
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.acknowledgement.findAcknowledgementsByHost")
     * @param RequestParametersInterface $requestParameters
     * @param int $hostId
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgementsByHost(RequestParametersInterface $requestParameters, int $hostId): View
    {
        $hostsAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findAcknowledgementsByHost($hostId);

        $context = (new Context())->setGroups(['ack_main']);

        return $this->view([
            'result' => $hostsAcknowledgements,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }

    /**
     * Entry point to find the services acknowledgements.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/services/acknowledgements",
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.acknowledgement.findServicesAcknowledgements")
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findServicesAcknowledgements(RequestParametersInterface $requestParameters): View
    {
        $servicesAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findServicesAcknowledgements();
        $context = (new Context())->setGroups(['ack_main', 'ack_service']);

        return $this->view([
            'result' => $servicesAcknowledgements,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }

    /**
     * Entry point to find acknowledgements linked to a service.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/hosts/{hostId}/services/{serviceId}/acknowledgements",
     *     requirements={"hostId"="\d+", "serviceId"="\d+"},
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.acknowledgement.findAcknowledgementsByService")
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgementsByService(
        RequestParametersInterface $requestParameters,
        int $hostId,
        int $serviceId
    ): View {
        $servicesAcknowledgements = $this->acknowledgementService
            ->filterByContact($this->getUser())
            ->findAcknowledgementsByService($hostId, $serviceId);
        $context = (new Context())->setGroups(['ack_main', 'ack_service']);

        return $this->view([
            'result' => $servicesAcknowledgements,
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
     *     "/monitoring/hosts/acknowledgements",
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.acknowledgement.addHostAcknowledgement")
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
     *     "/monitoring/hosts/{hostId}/acknowledgements",
     *     requirements={"hostId"="\d+"},
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.acknowledgement.disacknowledgeHost")
     * @param int $hostId Host id for which we want to cancel the acknowledgement
     * @return View
     * @throws \Exception
     */
    public function disacknowledgeHost(int $hostId): View
    {
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
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Delete(
     *     "/monitoring/hosts/{hostId}/services/{serviceId}/acknowledgements",
     *     requirements={"hostId"="\d+", "serviceId"="\d+"},
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.acknowledgement.disacknowledgeService")
     * @param int $hostId Host id linked to service
     * @param int $serviceId Service Id for which we want to cancel the acknowledgement
     * @return View
     * @throws \Exception
     */
    public function disacknowledgeService(int $hostId, int $serviceId): View
    {
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
     * Entry point to add a service acknowledgement.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Post(
     *     "/monitoring/services/acknowledgements",
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.acknowledgement.addServiceAcknowledgement")
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

    /**
     * Entry point to find one acknowledgement.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/acknowledgements/{acknowledgementId}",
     *     requirements={"acknowledgementId"="\d+"},
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.acknowledgement.findOneAcknowledgement")
     * @param int $acknowledgementId Acknowledgement id to find
     * @return View
     * @throws \Exception
     */
    public function findOneAcknowledgement(int $acknowledgementId): View
    {
        $contact = $this->getUser();
        if ($contact === null) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }
        $acknowledgement = $this->acknowledgementService
            ->filterByContact($contact)
            ->findOneAcknowledgement($acknowledgementId);

        if ($acknowledgement !== null) {
            $context = (new Context())
                ->setGroups(['ack_main', 'ack_service'])
                ->enableMaxDepth();

            return $this->view($acknowledgement)->setContext($context);
        } else {
            return View::create(null, Response::HTTP_NOT_FOUND, []);
        }
    }

    /**
     * Entry point to find all acknowledgements.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/monitoring/acknowledgements",
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="monitoring.acknowledgement.findAcknowledgements")
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findAcknowledgements(RequestParametersInterface $requestParameters): View
    {
        $contact = $this->getUser();
        if ($contact === null) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }
        $acknowledgements = $this->acknowledgementService
            ->filterByContact($contact)
            ->findAcknowledgements();

        $context = (new Context())->setGroups(['ack_main', 'ack_service']);

        return $this->view([
            'result' => $acknowledgements,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }
}
