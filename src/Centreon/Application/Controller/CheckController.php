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

use DateTime;
use JsonSchema\Validator;
use FOS\RestBundle\View\View;
use Centreon\Domain\Check\Check;
use Centreon\Domain\Contact\Contact;
use JsonSchema\Constraints\Constraint;
use JMS\Serializer\SerializerInterface;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Check\CheckException;
use JMS\Serializer\DeserializationContext;
use Centreon\Domain\Entity\EntityValidator;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Request\CheckRequest;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Exception\EntityNotFoundException;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Check\Interfaces\CheckServiceInterface;

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
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_CHECK)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $context = DeserializationContext::create()->setGroups(self::SERIALIZER_GROUPS_HOST_ADD);

        /**
         * @var Check[] $checks
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
     * Check if the resource can be checked by the current user
     *
     * @param Contact $contact
     * @param ResourceEntity $resource
     * @return bool
     */
    private function hasCheckRightsForResource(Contact $contact, ResourceEntity $resource): bool
    {
        if ($contact->isAdmin()) {
            return true;
        }

        $hasRights = false;

        switch ($resource->getType()) {
            case ResourceEntity::TYPE_HOST:
                $hasRights = $contact->hasRole(Contact::ROLE_HOST_CHECK);
                break;
            case ResourceEntity::TYPE_SERVICE:
            case ResourceEntity::TYPE_META:
                $hasRights = $contact->hasRole(Contact::ROLE_SERVICE_CHECK);
                break;
        }

        return $hasRights;
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
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_CHECK)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $context = DeserializationContext::create()->setGroups(self::SERIALIZER_GROUPS_SERVICE);

        /**
         * @var Check[] $checks
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
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_CHECK)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $context = DeserializationContext::create()->setGroups(self::SERIALIZER_GROUPS_HOST_ADD);

        /**
         * @var Check $check
         */
        $check = $serializer->deserialize(
            (string) $request->getContent(),
            Check::class,
            'json',
            $context
        );
        $check
            ->setResourceId($hostId)
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
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_CHECK)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $context = DeserializationContext::create()->setGroups(self::SERIALIZER_GROUPS_SERVICE);

        /**
         * @var Check $check
         */
        $check = $serializer->deserialize(
            (string) $request->getContent(),
            Check::class,
            'json',
            $context
        );
        $check
            ->setParentResourceId($hostId)
            ->setResourceId($serviceId)
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

    /**
     * Entry point to check a meta service.
     *
     * @param Request $request
     * @param EntityValidator $entityValidator
     * @param SerializerInterface $serializer
     * @param int $metaId
     * @return View
     * @throws \Exception
     */
    public function checkMetaService(
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
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_CHECK)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $context = DeserializationContext::create()->setGroups(self::SERIALIZER_GROUPS_SERVICE);

        /**
         * @var Check $check
         */
        $check = $serializer->deserialize(
            (string) $request->getContent(),
            Check::class,
            'json',
            $context
        );
        $check
            ->setResourceId($metaId)
            ->setCheckTime(new \DateTime());

        $errors = $entityValidator->validate(
            $check,
            null,
            Check::VALIDATION_GROUPS_META_SERVICE_CHECK
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $this->checkService
            ->filterByContact($contact)
            ->checkMetaService($check);

        return $this->view();
    }

    /**
     * Entry point to check resources.
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return View
     * @throws \Exception
     * @throws CheckException
     */
    public function checkResources(
        Request $request,
        SerializerInterface $serializer
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $user
         */
        $user = $this->getUser();

        $checks = json_decode((string) $request->getContent(), true);
        if (!is_array($checks)) {
            throw new \InvalidArgumentException(_('Error when decoding sent data'));
        }

        /*
         * Validate the content of the POST request against the JSON schema validator
         */
        $validator = new Validator();
        $content = json_decode((string) $request->getContent());
        $file = 'file://' . $this->getParameter('centreon_path') .
            'config/json_validator/latest/Centreon/Check/AddChecks.json';
        $validator->validate(
            $content,
            (object) ['$ref' => $file],
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );

        if (!$validator->isValid()) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new CheckException($message);
        }

        /**
         * @var CheckRequest $checkRequest
         */
        $checkRequest = $serializer->deserialize(
            (string)$request->getContent(),
            CheckRequest::class,
            'json'
        );

        $checkRequest->setCheck((new Check())->setCheckTime(new DateTime()));

        foreach ($checkRequest->getResources() as $resource) {
            // start check process
            try {
                if ($this->hasCheckRightsForResource($user, $resource)) {
                    $this->checkService
                        ->filterByContact($user)
                        ->checkResource($checkRequest->getCheck(), $resource);
                }
            } catch (EntityNotFoundException $e) {
                continue;
            }
        }

        return $this->view();
    }
}
