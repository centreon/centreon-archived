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
use FOS\RestBundle\View\View;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * MonitoringService
     *
     * @var MonitoringServiceInterface
     */
    private $monitoringService;

    private const COMMENT_RESOURCES_PAYLOAD_VALIDATION_FILE =
        __DIR__ . '/../../../../../config/json_validator/latest/Centreon/Comment/CommentResources.json';


    private const SINGLE_COMMENT_PAYLOAD_VALIDATION_FILE =
        __DIR__ . '/../../../../../config/json_validator/latest/Centreon/Comment/Comment.json';

    public function __construct(
        CommentServiceInterface $commentService,
        MonitoringServiceInterface $monitoringService
    ) {
        $this->commentService = $commentService;
        $this->monitoringService = $monitoringService;
    }

    /**
     * This function will verify that the contact is authorized to add a comment
     * on the selected resources
     *
     * @param Contact $contact
     * @param array<string,mixed> $resources
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
                || ($resource['type'] === ResourceEntity::TYPE_META && $hasServiceRights)
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
        * Validate the content of the request against the JSON schema validator
        */
        $payload = $this->validateAndRetrieveDataSent($request, self::COMMENT_RESOURCES_PAYLOAD_VALIDATION_FILE);

        /**
         * If user has no rights to add a comment for host and/or service
         * return view with unauthorized HTTP header response
         */
        if (!$this->hasCommentRightsForResources($contact, $payload['resources'])) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

        /**
         * Dissect the results to extract the hostids and serviceids from it
         */
        $resourceIds = [];
        $comments = [];

        $now = new \DateTime();

        foreach ($payload['resources'] as $resource) {
            $date = ($resource['date'] !== null) ? new \DateTime($resource['date']) : $now;
            $comments[$resource['id']] = (new Comment($resource['id'], $resource['comment']))
                ->setDate($date);

            if ($resource['type'] === ResourceEntity::TYPE_HOST) {
                $resourceIds['host'][] = $resource['id'];
            } elseif ($resource['type'] === ResourceEntity::TYPE_SERVICE) {
                $comments[$resource['id']]->setParentResourceId($resource['parent']['id']);
                $resourceIds['service'][] = [
                    'host_id' => $resource['parent']['id'],
                    'service_id' => $resource['id']
                ];
            } elseif ($resource['type'] === ResourceEntity::TYPE_META) {
                $resourceIds['metaservice'][] = [
                    'service_id' => $resource['id']
                ];
            }
        }

        $this->commentService->addResourcesComment($comments, $resourceIds);

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
        $this->commentService->filterByContact($contact);

        /**
         * Checking that user is allowed to add a comment for a host resource
         */
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_HOST_ADD_COMMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

       /*
        * Validate the content of the request against the JSON schema validator
        */
        $payload = $this->validateAndRetrieveDataSent($request, self::SINGLE_COMMENT_PAYLOAD_VALIDATION_FILE);

        $date = ($payload['date'] !== null) ? new \DateTime($payload['date']) : new \DateTime();
        $comment = (new Comment($hostId, $payload['comment']))
            ->setDate($date);
        $host = new Host();
        $host->setId($hostId);
        $this->commentService->addHostComment($comment, $host);

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
        $this->commentService->filterByContact($contact);
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ADD_COMMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

       /*
        * Validate the content of the request against the JSON schema validator
        */
        $payload = $this->validateAndRetrieveDataSent($request, self::SINGLE_COMMENT_PAYLOAD_VALIDATION_FILE);

        /**
         * At this point we validate the JSON sent with the JSON validator.
         */
        $date = ($payload['date'] !== null) ? new \DateTime($payload['date']) : new \DateTime();
        $comment = (new Comment($serviceId, $payload['comment']))
            ->setDate($date)
            ->setParentResourceId($hostId);

        $service = new Service();
        $host = new Host();

        $host->setId($hostId);
        $service->setId($serviceId)->setHost($host);

        $this->commentService->addServiceComment($comment, $service);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Entry point to add a comment on a service
     *
     * @param Request $request
     * @param int $metaId ID of the Meta Service
     * @return View
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function addMetaServiceComment(
        Request $request,
        int $metaId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        $this->commentService->filterByContact($contact);
        if (!$contact->isAdmin() && !$contact->hasRole(Contact::ROLE_SERVICE_ADD_COMMENT)) {
            return $this->view(null, Response::HTTP_UNAUTHORIZED);
        }

       /*
        * Validate the content of the request against the JSON schema validator
        */
        $payload = $this->validateAndRetrieveDataSent($request, self::SINGLE_COMMENT_PAYLOAD_VALIDATION_FILE);

        /**
         * At this point we validate the JSON sent with the JSON validator.
         */
        $date = ($payload['date'] !== null) ? new \DateTime($payload['date']) : new \DateTime();

        $service = $this->monitoringService->findOneServiceByDescription('meta_' . $metaId);

        if ($service === null) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Meta service %d not found'),
                    $metaId
                )
            );
        }

        $comment = (new Comment($service->getId(), $payload['comment']))
            ->setParentResourceId($service->getHost()->getId())
            ->setDate($date);

        $this->commentService->addServiceComment($comment, $service);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
