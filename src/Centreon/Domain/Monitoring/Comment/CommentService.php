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

namespace Centreon\Domain\Monitoring\Comment;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Monitoring\MonitoringService;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Exception\EntityNotFoundException;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Monitoring\Comment\CommentException;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\Comment\Interfaces\CommentServiceInterface;

/**
 * Monitoring class used to manage result submitting to services, hosts and resources
 *
 * @package Centreon\Domain\Monitoring\SubmitResult
 */
class CommentService extends AbstractCentreonService implements CommentServiceInterface
{
    public const VALIDATION_GROUPS_HOST_ADD_COMMENT = ['add_host_comment'];
    public const VALIDATION_GROUPS_SERVICE_ADD_COMMENT = ['add_service_comment'];

    /**
     * @var EngineServiceInterface Used to send external commands to engine.
     */
    private $engineService;

    /**
     * @var EntityValidator
     */
    private $validator;

    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @var MonitoringServiceInterface
     */
    private $monitoringService;

    /**
     * CommentService constructor.
     *
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param MonitoringRepositoryInterface $monitoringRepository
     * @param EngineServiceInterface $engineService
     * @param MonitoringServiceInterface $monitoringService
     * @param EntityValidator $validator
     */
    public function __construct(
        AccessGroupRepositoryInterface $accessGroupRepository,
        MonitoringRepositoryInterface $monitoringRepository,
        EngineServiceInterface $engineService,
        MonitoringServiceInterface $monitoringService,
        EntityValidator $validator
    ) {
        $this->accessGroupRepository = $accessGroupRepository;
        $this->monitoringRepository = $monitoringRepository;
        $this->engineService = $engineService;
        $this->monitoringService = $monitoringService;
        $this->validator = $validator;
    }

    /**
     * {@inheritDoc}
     * @param Contact $contact
     * @return CommentServiceInterface
     */
    public function filterByContact($contact): CommentServiceInterface
    {
        parent::filterByContact($contact);
        $this->engineService->filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->monitoringRepository
            ->setContact($contact)
            ->filterByAccessGroups($accessGroups);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addServiceComment(Comment $comment, Service $service): void
    {
        // We validate the comment entity sent
        $errors = $this->validator->validate(
            $comment,
            null,
            self::VALIDATION_GROUPS_SERVICE_ADD_COMMENT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $hostComment = $this->monitoringService
            ->filterByContact($this->contact)
            ->findOneHost($service->getHost()->getId());

        if (is_null($hostComment)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Host %d not found'),
                    $service->getHost()->getId()
                )
            );
        }

        $serviceComment = $this->monitoringService
            ->filterByContact($this->contact)
            ->findOneService($hostComment->getId(), $service->getId());

        if (is_null($serviceComment)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Service %d not found'),
                    $service->getId()
                )
            );
        }
        $serviceComment->setHost($hostComment);

        $this->engineService->addServiceComment($comment, $serviceComment);
    }

    /**
     * @inheritDoc
     */
    public function addMetaServiceComment(Comment $comment, Service $metaService): void
    {
        // We validate the comment entity sent
        $errors = $this->validator->validate(
            $comment,
            null,
            self::VALIDATION_GROUPS_SERVICE_ADD_COMMENT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $hostComment = $this->monitoringService
            ->filterByContact($this->contact)
            ->findOneHost($metaService->getHost()->getId());

        if (is_null($hostComment)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Meta host %d not found'),
                    $metaService->getHost()->getId()
                )
            );
        }
        $metaService->setHost($hostComment);

        $this->engineService->addServiceComment($comment, $metaService);
    }

    /**
     * @inheritDoc
     */
    public function addHostComment(Comment $comment, Host $host): void
    {
        // We validate the comment entity sent
        $errors = $this->validator->validate(
            $comment,
            null,
            self::VALIDATION_GROUPS_HOST_ADD_COMMENT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $hostComment = $this->monitoringService
            ->filterByContact($this->contact)
            ->findOneHost($host->getId());

        if (is_null($hostComment)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Host %d not found'),
                    $host->getId()
                )
            );
        }

        $this->engineService->addHostComment($comment, $hostComment);
    }

    /**
     * @inheritDoc
     */
    public function addResourcesComment(array $comments, array $resourceIds): void
    {
        /**
         * @var Host[] $hosts
         */
        $hosts = [];

        /**
         * @var Service[] $services
         */
        $services = [];

        /**
         * @var Service[] $metaServices
         */
        $metaServices = [];
        /**
         * Retrieving at this point all the host and services entities linked to
         * the resource ids provided
         */
        if ($this->contact->isAdmin()) {
            if (!empty($resourceIds['host'])) {
                try {
                    $hosts = $this->monitoringRepository->findHostsByIdsForAdminUser($resourceIds['host']);
                } catch (\Throwable $ex) {
                    throw new CommentException(_('Error when searching for hosts'), 0, $ex);
                }
            }

            if (!empty($resourceIds['service'])) {
                try {
                    $services = $this->monitoringRepository->findServicesByIdsForAdminUser($resourceIds['service']);
                } catch (\Throwable $ex) {
                    throw new CommentException(_('Error when searching for services'), 0, $ex);
                }
            }

            if (!empty($resourceIds['metaservice'])) {
                try {
                    foreach ($resourceIds['metaservice'] as $resourceId) {
                        $metaServices[$resourceId['service_id']] = $this->monitoringRepository
                            ->findOneServiceByDescription('meta_' . $resourceId['service_id']);
                    }
                } catch (\Throwable $ex) {
                    throw new CommentException(_('Error when searching for services'), 0, $ex);
                }
            }
        } else {
            $accessGroups = $this->accessGroupRepository->findByContact($this->contact);

            if (!empty($resourceIds['host'])) {
                try {
                    $hosts = $this->monitoringRepository
                        ->filterByAccessGroups($accessGroups)
                        ->findHostsByIdsForNonAdminUser($resourceIds['host']);
                } catch (\Throwable $ex) {
                    throw new CommentException(_('Error when searching for hosts'), 0, $ex);
                }
            }

            if (!empty($resourceIds['service'])) {
                try {
                    $services = $this->monitoringRepository
                        ->filterByAccessGroups($accessGroups)
                        ->findServicesByIdsForNonAdminUser($resourceIds['service']);
                } catch (\Throwable $ex) {
                    throw new CommentException(_('Error when searching for services'), 0, $ex);
                }
            }

            if (!empty($resourceIds['metaservice'])) {
                try {
                    foreach ($resourceIds['metaservice'] as $resourceId) {
                        $metaServices[$resourceId['service_id']] = $this->monitoringRepository
                            ->filterByAccessGroups($accessGroups)
                            ->findOneServiceByDescription('meta_' . $resourceId['service_id']);
                    }
                } catch (\Throwable $ex) {
                    throw new CommentException(_('Error when searching for meta services'), 0, $ex);
                }
            }
        }

        foreach ($hosts as $host) {
            $this->addHostComment($comments[$host->getId()], $host);
        }

        foreach ($services as $service) {
            $this->addServiceComment($comments[$service->getId()], $service);
        }

        foreach ($metaServices as $metaId => $metaService) {
            $this->addMetaServiceComment($comments[$metaId], $metaService);
        }
    }
}
