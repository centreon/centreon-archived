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

use Centreon\Domain\Check\Check;
use Centreon\Domain\Check\Interfaces\CheckServiceInterface;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Exception\EntityNotFoundException;
use FOS\RestBundle\View\View;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Exception\ValidationFailedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Used to manage all requests to schedule checks on hosts and services
 *
 * @package Centreon\Application\Controller
 */
class CheckController extends AbstractController
{
    // Groups for serialization
    public const SERIALIZER_GROUPS_HOST = ['check_host'];
    public const SERIALIZER_GROUPS_SERVICE = ['check_service'];
    public const SERIALIZER_GROUPS_HOST_ADD = ['check_host', 'check_host_add'];

    /**
     * @var CheckServiceInterface
     */
    private $checkService;

    /**
     * CheckController constructor.
     *
     * @param CheckServiceInterface $checkService
     */
    public function __construct(CheckServiceInterface $checkService)
    {
        $this->checkService = $checkService;
    }

    /**
     * Entry point to check multiple hosts.
     *
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function checkHosts(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var $contact Contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_CHECK)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $context = DeserializationContext::create()->setGroups(self::SERIALIZER_GROUPS_HOST_ADD);

        /**
         * @var $checks Check[]
         */
        $checks = $serializer->deserialize(
            (string) $request->getContent(),
            'array<' . Check::class . '>',
            'json',
            $context
        );

        $this->checkService->filterByContact($contact);

        $checkTime = new \DateTime();
        foreach ($checks as $check) {
            $check->setCheckTime($checkTime);

            $errors = $entityValidator->validate(
                $check,
                null,
                Check::VALIDATION_GROUPS_HOST_CHECK
            );

            if ($errors->count() > 0) {
                throw new ValidationFailedException($errors);
            }

            try {
                $this->checkService->checkHost($check);
            } catch (EntityNotFoundException $e) {
                continue;
            }
        }

        return $this->view();
    }

    /**
     * Entry point to check multiple services.
     *
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     */
    public function checkServices(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var $contact Contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_CHECK)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $context = DeserializationContext::create()->setGroups(self::SERIALIZER_GROUPS_SERVICE);

        /**
         * @var $checks Check[]
         */
        $checks = $serializer->deserialize(
            (string) $request->getContent(),
            'array<' . Check::class . '>',
            'json',
            $context
        );

        $this->checkService->filterByContact($contact);

        $checkTime = new \DateTime();
        foreach ($checks as $check) {
            $check->setCheckTime($checkTime);

            $errors = $entityValidator->validate(
                $check,
                null,
                Check::VALIDATION_GROUPS_SERVICE_CHECK
            );

            if ($errors->count() > 0) {
                throw new ValidationFailedException($errors);
            }

            try {
                $this->checkService->checkService($check);
            } catch (EntityNotFoundException $e) {
                continue;
            }
        }

        return $this->view();
    }

    /**
     * Entry point to check a host.
     *
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @param int $hostId
     * @return View
     * @throws \Exception
     */
    public function checkHost(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer,
        int $hostId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var $contact Contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_CHECK)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $context = DeserializationContext::create()->setGroups(self::SERIALIZER_GROUPS_HOST);

        /**
         * @var $check Check
         */
        $check = $serializer->deserialize(
            (string) $request->getContent(),
            Check::class,
            'json',
            $context
        );
        $check
            ->setId($hostId)
            ->setCheckTime(new \DateTime());

        $errors = $entityValidator->validate(
            $check,
            null,
            Check::VALIDATION_GROUPS_HOST_CHECK
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $this->checkService
            ->filterByContact($contact)
            ->checkHost($check);

        return $this->view();
    }

    /**
     * Entry point to check a service.
     *
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @param int $hostId
     * @param int $serviceId
     * @return View
     * @throws \Exception
     */
    public function checkService(
        Request $request,
        EntityValidator $entityValidator,
        SerializerInterface $serializer,
        int $hostId,
        int $serviceId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var $contact Contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_CHECK)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $context = DeserializationContext::create()->setGroups(self::SERIALIZER_GROUPS_SERVICE);

        /**
         * @var $check Check
         */
        $check = $serializer->deserialize(
            (string) $request->getContent(),
            Check::class,
            'json',
            $context
        );
        $check
            ->setParentId($hostId)
            ->setId($serviceId)
            ->setCheckTime(new \DateTime());

        $errors = $entityValidator->validate(
            $check,
            null,
            Check::VALIDATION_GROUPS_SERVICE_CHECK
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $this->checkService
            ->filterByContact($contact)
            ->checkService($check);

        return $this->view();
    }
}
