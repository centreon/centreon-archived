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
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Exception\EntityNotFoundException;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
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
     * SubmitResultService constructor.
     *
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param MonitoringRepositoryInterface $monitoringRepository
     * @param EngineServiceInterface $engineService
     * @param EntityValidator $validator
     */
    public function __construct(
        AccessGroupRepositoryInterface $accessGroupRepository,
        MonitoringRepositoryInterface $monitoringRepository,
        EngineServiceInterface $engineService,
        EntityValidator $validator
    ) {
        $this->accessGroupRepository = $accessGroupRepository;
        $this->monitoringRepository = $monitoringRepository;
        $this->engineService = $engineService;
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
    public function addServiceComment(Comment $comment): void
    {
        // We validate the check instance
        $errors = $this->validator->validate(
            $comment,
            null,
            self::VALIDATION_GROUPS_SERVICE_ADD_COMMENT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $host = $this->monitoringRepository->findOneHost($comment->getParentResourceId());
        if (is_null($host)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Host %d not found'),
                    $comment->getParentResourceId()
                )
            );
        }

        $service = $this->monitoringRepository->findOneService(
            $comment->getParentResourceId(),
            $comment->getResourceId()
        );
        if (is_null($service)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Service %d not found'),
                    $comment->getResourceId()
                )
            );
        }
        $service->setHost($host);

        $this->engineService->addServiceComment($comment, $service);
    }

    /**
     * @inheritDoc
     */
    public function addHostComment(Comment $comment): void
    {
        // We validate the SubmitResult instance instance
        $errors = $this->validator->validate(
            $comment,
            null,
            self::VALIDATION_GROUPS_HOST_ADD_COMMENT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $host = $this->monitoringRepository->findOneHost($comment->getResourceId());
        if (is_null($host)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Host %d not found'),
                    $comment->getResourceId()
                )
            );
        }

        $this->engineService->addHostComment($comment, $host);
    }
}
