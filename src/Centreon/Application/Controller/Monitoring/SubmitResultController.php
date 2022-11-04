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

namespace Centreon\Application\Controller\Monitoring;

use FOS\RestBundle\View\View;
use Centreon\Domain\Contact\Contact;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Monitoring\SubmitResult\SubmitResult;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\SubmitResult\Interfaces\SubmitResultServiceInterface;
use Exception;

class SubmitResultController extends AbstractController
{
    /**
     * submitResult
     *
     * @var SubmitResultServiceInterface
     */
    private $submitResultService;

    private const SUBMIT_RESULT_RESOURCES_PAYLOAD_VALIDATION_FILE =
        __DIR__ . '/../../../../../config/json_validator/latest/Centreon/SubmitResult/SubmitResultResources.json';

    private const SUBMIT_SINGLE_RESULT_PAYLOAD_VALIDATION_FILE =
        __DIR__ . '/../../../../../config/json_validator/latest/Centreon/SubmitResult/SubmitResult.json';

    /**
     * @param SubmitResultServiceInterface $submitResultService
     */
    public function __construct(SubmitResultServiceInterface $submitResultService)
    {
        $this->submitResultService = $submitResultService;
    }

    /**
     * Check if all resources provided can be submitted a result
     * by the current user.
     *
     * @param Contact $contact
     * @param array<string,mixed> $resources
     * @return bool
     */
    private function hasSubmitResultRightsForResources(Contact $contact, array $resources): bool
    {
        if ($contact->isAdmin()) {
            return true;
        }

        /**
         * Retrieving the current submit result rights of the user.
         */
        $hasHostRights = $contact->hasRole(Contact::ROLE_HOST_SUBMIT_RESULT);
        $hasServiceRights = $contact->hasRole(Contact::ROLE_SERVICE_SUBMIT_RESULT);

        foreach ($resources as $resource) {
            if (
                ($resource['type'] === ResourceEntity::TYPE_HOST && $hasHostRights)
                || ($resource['type'] === ResourceEntity::TYPE_SERVICE && $hasServiceRights)
                || ($resource['type'] === ResourceEntity::TYPE_META && $hasServiceRights)
            ) {
                continue;
            }
            return false;
        }

        return true;
    }

    /**
     * Entry point to submit result to multiple hosts.
     *
     * @param Request $request
     * @return View
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function submitResultResources(
        Request $request
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();
        /**
        * @var Contact $contact
        */
        $contact = $this->getUser();
        $this->submitResultService->filterByContact($contact);

       /*
        * Validate the content of the POST request against the JSON schema validator
        */
        $payload = $this->validateAndRetrieveDataSent($request, self::SUBMIT_RESULT_RESOURCES_PAYLOAD_VALIDATION_FILE);

        /**
         * If user has no rights to submit result for host and/or service
         * return view with unauthorized HTTP header response
         */
        if (!$this->hasSubmitResultRightsForResources($contact, $payload['resources'])) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        foreach ($payload['resources'] as $resource) {
            $result = (new SubmitResult($resource['id'], $resource['status']))
                ->setOutput($resource['output'])
                ->setPerformanceData($resource['performance_data']);
            try {
                if ($resource['type'] === ResourceEntity::TYPE_SERVICE) {
                    $result->setParentResourceId($resource['parent']['id']);
                    $this->submitResultService
                        ->submitServiceResult($result);
                } elseif ($resource['type'] === ResourceEntity::TYPE_HOST) {
                    $this->submitResultService
                        ->submitHostResult($result);
                } elseif ($resource['type'] === ResourceEntity::TYPE_META) {
                    $this->submitResultService
                        ->submitMetaServiceResult($result);
                }
            } catch (EntityNotFoundException $e) {
                throw $e;
            }
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Entry point to submit result to a host.
     *
     * @param Request $request
     * @param int $hostId ID of the host
     * @return View
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function submitResultHost(
        Request $request,
        int $hostId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_SUBMIT_RESULT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

       /*
        * Validate the content of the POST request against the JSON schema validator
        */
        $payload = $this->validateAndRetrieveDataSent($request, self::SUBMIT_SINGLE_RESULT_PAYLOAD_VALIDATION_FILE);

        if (!empty($payload)) {
            /**
             * At this point we made sure that the mapping will work since we validate
             * the JSON sent with the JSON validator.
             */
            $result = (new SubmitResult($hostId, $payload['status']))
                ->setOutput($payload['output'])
                ->setPerformanceData($payload['performance_data']);

            $this->submitResultService
                ->filterByContact($contact)
                ->submitHostResult($result);
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Entry point to submit result to a service.
     *
     * @param Request $request
     * @param int $hostId ID of service parent (host)
     * @param int $serviceId ID of the service
     * @return View
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function submitResultService(
        Request $request,
        int $hostId,
        int $serviceId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_SUBMIT_RESULT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

       /*
        * Validate the content of the POST request against the JSON schema validator
        */
        $payload = $this->validateAndRetrieveDataSent($request, self::SUBMIT_SINGLE_RESULT_PAYLOAD_VALIDATION_FILE);

        if (!empty($payload)) {
            $result = (new SubmitResult($serviceId, $payload['status']))
                ->setOutput($payload['output'])
                ->setPerformanceData($payload['performance_data'])
                ->setParentResourceId($hostId);

            $this->submitResultService
                ->filterByContact($contact)
                ->submitServiceResult($result);
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
