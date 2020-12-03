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

use Exception;
use JsonSchema\Validator;
use FOS\RestBundle\View\View;
use Centreon\Domain\Contact\Contact;
use JsonSchema\Constraints\Constraint;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\Monitoring\ResourceStatus;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Monitoring\Comment\Comment;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Comment\Interfaces\CommentServiceInterface;

class CommentController extends AbstractController
{
    /**
     * comment
     *
     * @var CommentServiceInterface
     */
    private $commentService;

    public function __construct(
        CommentServiceInterface $commentService,
        MonitoringServiceInterface $monitoringService
    ) {
        $this->commentService = $commentService;
        $this->monitoringService = $monitoringService;
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
     * This function will verify that the contact is authorized to add a comment
     * on the selected resources
     *
     * @param Contact $contact
     * @param array $resources
     * @return boolean
     */
    private function hasCommentRightsForResources(Contact $contact, array $resources): bool
    {
        if ($contact->isAdmin()) {
            return true;
        }
        /**
         * Retrieving the current rights of the user for adding comments
         */
        $hasHostRights = $contact->hasRole(Contact::ROLE_HOST_ADD_COMMENT);
        $hasServiceRights = $contact->hasRole(Contact::ROLE_SERVICE_ADD_COMMENT);

        /**
         * If the user has no rights at all, do not go further
         */
        if (!$hasHostRights && !$hasServiceRights) {
            return false;
        }

        foreach ($resources as $resource) {
            if (
                ($resource['type'] === ResourceEntity::TYPE_HOST && $hasHostRights)
                || ($resource['type'] === ResourceEntity::TYPE_SERVICE && $hasServiceRights)
            ) {
                continue;
            }
            return false;
        }

        return true;
    }

    /**
     * Entry point to add comments to multiple resources
     *
     * @param Request $request
     * @return View
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function addResourcesComment(
        Request $request
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();
        /**
        * @var Contact $contact
        */
        $contact = $this->getUser();
        $this->commentService->filterByContact($contact);

       /*
        * Validate the content of the POST request against the JSON schema validator
        */
        $results = $this->validateAndRetrievePostData(
            $request,
            'config/json_validator/latest/Centreon/Comment/CommentResources.json'
        );

        /**
         * If user has no rights to add a comment for host and/or service
         * return view with unauthorized HTTP header response
         */
        if (!$this->hasCommentRightsForResources($contact, $results['resources'])) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        /**
         * Dissect the results to extract the hostids and serviceids from it
         */
        $hostIds = [];
        $serviceIds = [];
        $comments = [];

        $now = new \DateTime('now');

        foreach ($results['resources'] as $index => $commentResource) {
            $date = ($commentResource['date'] !== null) ? new \DateTime($commentResource['date']) : $now;
            $comments[$index] = (new Comment($commentResource['id'], $commentResource['comment']))
                ->setDate($date)
                ->setParentResourceId($commentResource['parent']['id']);

            if ($commentResource['type'] === ResourceEntity::TYPE_HOST) {
                $hostIds[$index] = $commentResource['id'];
            } elseif ($commentResource['type'] === ResourceEntity::TYPE_SERVICE) {
                $serviceIds[$index] = [
                    'host_id' => $commentResource['parent']['id'],
                    'service_id' => $commentResource['id']
                ];
            }
        }

        /**
         * Retrieving all services and hosts
         */
        $hosts = $this->monitoringService->findMultipleHosts($hostIds);
        $services = $this->monitoringService->findMultipleServices($serviceIds);

        if (!empty($hosts)) {
            foreach ($hosts as $key => $host) {
                $this->commentService->addHostComment($comments[$key], $host);
            }
        }

        if (!empty($services)) {
            foreach ($services as $key => $service) {
                $this->commentService->addServiceComment($comments[$key], $service);
            }
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Entry point to add a comment on a host resource
     *
     * @param Request $request
     * @param int $hostId ID of the host
     * @return View
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function addHostComment(
        Request $request,
        int $hostId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();
        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        /**
         * Checking that user is allowed to add a comment for a host resource
         */
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_ADD_COMMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $results = $this->validateAndRetrievePostData(
            $request,
            'config/json_validator/latest/Centreon/Comment/Comment.json'
        );

        if (!empty($results)) {
            /**
             * At this point we made sure that the mapping will work since we validate
             * the JSON sent with the JSON validator.
             */
            $host = $this->monitoringService->findOneHost($hostId);
            if (is_null($host)) {
                throw new EntityNotFoundException(
                    sprintf(
                        _('Host %d not found'),
                        $hostId
                    )
                );
            }
            $date = ($results['date'] !== null) ? new \DateTime($results['date']) : new \DateTime('now');
            $result = (new Comment($hostId, $results['comment']))
                ->setDate($date);

            try {
                $this->commentService
                    ->filterByContact($contact)
                    ->addHostComment($result);
            } catch (EntityNotFoundException $e) {
                throw $e;
            }
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Entry point to add a comment on a service
     *
     * @param Request $request
     * @param int $hostId ID of service parent (host)
     * @param int $serviceId ID of the service
     * @return View
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function addServiceComment(
        Request $request,
        int $hostId,
        int $serviceId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ADD_COMMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        $results = $this->validateAndRetrievePostData(
            $request,
            'config/json_validator/latest/Centreon/Comment/Comment.json'
        );

        if (!empty($results)) {
            /**
             * At this point we made sure that the mapping will work since we validate
             * the JSON sent with the JSON validator.
             */
            $host = $this->monitoringService->findOneHost($hostId);
            if (is_null($host)) {
                throw new EntityNotFoundException(
                    sprintf(
                        _('Host %d not found'),
                        $hostId
                    )
                );
            }

            $service = $this->monitoringService->findOneService($hostId, $serviceId);
            if (is_null($service)) {
                throw new EntityNotFoundException(
                    sprintf(
                        _('Service %d not found'),
                        $serviceId
                    )
                );
            }
            $service->setHost($host);

            $date = ($results['date'] !== null) ? new \DateTime($results['date']) : new \DateTime('now');

            $result = (new Comment($serviceId, $results['comment']))
                ->setDate($date)
                ->setParentResourceId($hostId);

            $this->commentService
                ->filterByContact($contact)
                ->addServiceComment($result, $service);
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
