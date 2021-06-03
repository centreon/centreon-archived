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

use JsonSchema\Validator;
use FOS\RestBundle\View\View;
use Centreon\Domain\Contact\Contact;
use JsonSchema\Constraints\Constraint;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\Monitoring\ResourceStatus;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Monitoring\SubmitResult\SubmitResult;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\SubmitResult\SubmitResultException;
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

    public function __construct(SubmitResultServiceInterface $submitResultService)
    {
        $this->submitResultService = $submitResultService;
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
        $file = 'file://' . __DIR__ . '/../../../../../' . $jsonValidatorFile;
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

        return $results;
    }

    /**
     * Check if all resources provided can be submitted a result
     * by the current user.
     *
     * @param Contact $contact
     * @param array $resources
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
        $results = $this->validateAndRetrievePostData(
            $request,
            'config/json_validator/latest/Centreon/SubmitResult/SubmitResultResources.json'
        );

        /**
         * If user has no rights to submit result for host and/or service
         * return view with unauthorized HTTP header response
         */
        if (!$this->hasSubmitResultRightsForResources($contact, $results['resources'])) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        foreach ($results['resources'] as $submitResource) {
            $result = (new SubmitResult($submitResource['id'], $submitResource['status']))
                ->setOutput($submitResource['output'])
                ->setPerformanceData($submitResource['performance_data']);
            try {
                if ($submitResource['type'] === ResourceEntity::TYPE_SERVICE) {
                    $result->setParentResourceId($submitResource['parent']['id']);
                    $this->submitResultService
                        ->submitServiceResult($result);
                } elseif ($submitResource['type'] === ResourceEntity::TYPE_HOST) {
                    $this->submitResultService
                        ->submitHostResult($result);
                } elseif ($submitResource['type'] === ResourceEntity::TYPE_META) {
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

        $results = $this->validateAndRetrievePostData(
            $request,
            'config/json_validator/latest/Centreon/SubmitResult/SubmitResult.json'
        );

        if (!empty($results)) {
            /**
             * At this point we made sure that the mapping will work since we validate
             * the JSON sent with the JSON validator.
             */
            $result = (new SubmitResult($hostId, $results['status']))
                ->setOutput($results['output'])
                ->setPerformanceData($results['performance_data']);

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

        $results = $this->validateAndRetrievePostData(
            $request,
            'config/json_validator/latest/Centreon/SubmitResult/SubmitResult.json'
        );

        if (!empty($results)) {
            $result = (new SubmitResult($serviceId, $results['status']))
                ->setOutput($results['output'])
                ->setPerformanceData($results['performance_data'])
                ->setParentResourceId($hostId);

            $this->submitResultService
                ->filterByContact($contact)
                ->submitServiceResult($result);
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
