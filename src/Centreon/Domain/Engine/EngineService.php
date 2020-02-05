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

namespace Centreon\Domain\Engine;

use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Engine\Interfaces\EngineRepositoryInterface;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;
use JMS\Serializer\Exception\ValidationFailedException;

/**
 * This class is designed to send external command for Engine
 *
 * @package Centreon\Domain\Engine
 * @todo Replace the ValidationFailedException with a domain exception to avoid depending on the framework
 */
class EngineService extends AbstractCentreonService implements EngineServiceInterface
{
    /**
     * @var EngineRepositoryInterface
     */
    private $engineRepository;
    /**
     * @var EntityValidator
     */
    private $validator;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * CentCoreService constructor.
     *
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param EngineRepositoryInterface $engineRepository
     * @param EntityValidator $validator
     */
    public function __construct(
        AccessGroupRepositoryInterface $accessGroupRepository,
        EngineRepositoryInterface $engineRepository,
        EntityValidator $validator
    ) {
        $this->engineRepository = $engineRepository;
        $this->validator = $validator;
        $this->accessGroupRepository = $accessGroupRepository;
    }

    /**
     * @inheritDoc
     */
    public function addHostAcknowledgement(Acknowledgement $acknowledgement, Host $host): void
    {
        if (empty($this->contact->getAlias())) {
            throw new EngineException('The contact alias is empty');
        }
        if (empty($host->getName())) {
            throw new EngineException('Host name can not be empty');
        }

        // We validate the acknowledgement instance
        $errors = $this->validator->getValidator()->validate(
            $acknowledgement,
            null,
            EntityValidator::ACKNOWLEDGEMENT_VALIDATION_GROUPS_ADD_HOST_ACK
        );
        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        $preCommand = sprintf(
            'ACKNOWLEDGE_HOST_PROBLEM;%s;%d;%d;%d;%s;%s',
            $host->getName(),
            (int) $acknowledgement->isSticky(),
            (int) $acknowledgement->isNotifyContacts(),
            (int) $acknowledgement->isPersistentComment(),
            $this->contact->getAlias(),
            $acknowledgement->getComment()
        );
        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
        $commandFull = $this->createCommandHeader($host->getPollerId()) . $commandToSend;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * @inheritDoc
     */
    public function addServiceAcknowledgement(Acknowledgement $acknowledgement, Service $service): void
    {
        if (empty($this->contact->getAlias())) {
            throw new EngineException('The contact alias is empty');
        }
        if (empty($service->getHost())) {
            throw new EngineException('The host of service is not defined');
        }
        if ($this->validator->hasValidatorFor(Acknowledgement::class)) {
            $errors = $this->validator->getValidator()->validate(
                $acknowledgement,
                null,
                EntityValidator::ACKNOWLEDGEMENT_VALIDATION_GROUPS_ADD_SERVICE_ACK
            );
            if ($errors->count() > 0) {
                throw new ValidationFailedException($errors);
            }
        }

        $preCommand = sprintf(
            'ACKNOWLEDGE_SVC_PROBLEM;%s;%s;%d;%d;%d;%s;%s',
            $service->getHost()->getName(),
            $service->getDescription(),
            (int) $acknowledgement->isSticky(),
            (int) $acknowledgement->isNotifyContacts(),
            (int) $acknowledgement->isPersistentComment(),
            $this->contact->getAlias(),
            $acknowledgement->getComment()
        );
        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
        $commandFull = $this->createCommandHeader($service->getHost()->getPollerId()) . $commandToSend;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * @inheritDoc
     */
    public function disacknowledgeHost(Host $host): void
    {
        if (empty($host->getName())) {
            throw new EngineException('Host name can not be empty');
        }
        $preCommand = sprintf(
            'REMOVE_HOST_ACKNOWLEDGEMENT;%s',
            $host->getName()
        );
        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
        $commandFull = $this->createCommandHeader($host->getPollerId()) . $commandToSend;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * @inheritDoc
     */
    public function disacknowledgeService(Service $service): void
    {
        if (empty($service->getHost())) {
            throw new EngineException('Host of service not defined');
        }
        if (empty($service->getHost()->getName())) {
            throw new EngineException('The host of service is not defined');
        }

        $preCommand = sprintf(
            'REMOVE_SVC_ACKNOWLEDGEMENT;%s;%s',
            $service->getHost()->getName(),
            $service->getDescription()
        );
        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);

        $commandFull = $this->createCommandHeader($service->getHost()->getPollerId()) . $commandToSend;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * @inheritDoc
     */
    public function addHostDowntime(Downtime $downtime, Host $host): void
    {
        if (empty($this->contact->getAlias())) {
            throw new EngineException('The contact alias is empty');
        }
        if ($host === null) {
            throw new EngineException('Host of downtime not found');
        }
        if (empty($host->getName())) {
            throw new EngineException('Host name can not be empty');
        }

        if ($this->validator->hasValidatorFor(Downtime::class)) {
            // We validate the downtime instance
            $errors = $this->validator->getValidator()->validate(
                $downtime,
                null,
                EntityValidator::DOWNTIME_VALIDATION_GROUPS_ADD_DOWNTIME
            );
            if ($errors->count() > 0) {
                throw new ValidationFailedException($errors);
            }
        }

        $preCommand = sprintf(
            'SCHEDULE_HOST_DOWNTIME;%s;%d;%d;%d;0;%d;%s;%s',
            $host->getName(),
            $downtime->getStartTime()->getTimestamp(),
            $downtime->getEndTime()->getTimestamp(),
            (int) $downtime->isFixed(),
            $downtime->getDuration(),
            $this->contact->getAlias(),
            $downtime->getComment()
        );
        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
        $commandFull = $this->createCommandHeader($host->getPollerId()) . $commandToSend;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * Add a downtime on a list of services.
     *
     * @param Downtime $downtime Downtime to add
     * @param Service[] $services List of service for which we want to add a downtime
     * @throws \Exception
     */
    public function addServicesDowntime(Downtime $downtime, array $services): void
    {
        if (empty($this->contact->getAlias())) {
            throw new EngineException('The contact alias is empty');
        }
        if ($this->validator->hasValidatorFor(Downtime::class)) {
            // We validate the downtime instance
            $errors = $this->validator->getValidator()->validate(
                $downtime,
                null,
                EntityValidator::DOWNTIME_VALIDATION_GROUPS_ADD_DOWNTIME
            );
            if ($errors->count() > 0) {
                throw new ValidationFailedException($errors);
            }
        }

        if (empty($services)) {
            throw new EngineException('The list of services is empty');
        }
        $commandsToSend = [];
        foreach ($services as $service) {
            if ($service->getHost() == null) {
                throw new EngineException('The host of service (id: '. $service->getId() . ') is not defined');
            }
            if (empty($service->getHost()->getName())) {
                throw new EngineException('Host name of service (id: '. $service->getId() . ') can not be empty');
            }
            if (empty($service->getDescription())) {
                throw new EngineException('The description of service (id: '. $service->getId() . ') can not be empty');
            }
            $preCommand = sprintf(
                'SCHEDULE_SVC_DOWNTIME;%s;%s;%d;%d;%d;0;%d;%s;%s',
                $service->getHost()->getName(),
                $service->getDescription(),
                $downtime->getStartTime()->getTimestamp(),
                $downtime->getEndTime()->getTimestamp(),
                (int) $downtime->isFixed(),
                $downtime->getDuration(),
                $this->contact->getAlias(),
                $downtime->getComment()
            );
            $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
            $commandsToSend[] = $this->createCommandHeader($service->getHost()->getPollerId()) . $commandToSend;
        }

        if (!empty($commandsToSend)) {
            $this->engineRepository->sendExternalCommands($commandsToSend);
        }
    }

    /**
     * @inheritDoc
     */
    public function addServiceDowntime(Downtime $downtime, Service $service): void
    {
        // We validate the downtime instance
        $errors = $this->validator->getValidator()->validate(
            $downtime,
            null,
            EntityValidator::DOWNTIME_VALIDATION_GROUPS_ADD_DOWNTIME
        );
        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        if (empty($this->contact->getAlias())) {
            throw new EngineException('The contact alias is empty');
        }
        if (empty($service->getHost())) {
            throw new EngineException('The host of service is not defined');
        }
        if (empty($service->getHost()->getName())) {
            throw new EngineException('Host name can not be empty');
        }
        if (empty($service->getDescription())) {
            throw new EngineException('The service description can not be empty');
        }

        $preCommand = sprintf(
            'SCHEDULE_SVC_DOWNTIME;%s;%s;%d;%d;%d;0;%d;%s;%s',
            $service->getHost()->getName(),
            $service->getDescription(),
            $downtime->getStartTime()->getTimestamp(),
            $downtime->getEndTime()->getTimestamp(),
            (int) $downtime->isFixed(),
            $downtime->getDuration(),
            $this->contact->getAlias(),
            $downtime->getComment()
        );
        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
        $commandFull = $this->createCommandHeader($service->getHost()->getPollerId()) . $commandToSend;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * @inheritDoc
     */
    public function scheduleForcedHostCheck(Host $host): void
    {
        if (empty($host->getName())) {
            throw new EngineException('Host name can not be empty');
        }

        $preCommand = sprintf(
            'SCHEDULE_FORCED_HOST_CHECK;%s;%d',
            $host->getName(),
            (new \DateTime())->getTimestamp()
        );

        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
        $commandFull = $this->createCommandHeader($host->getPollerId()) . $commandToSend;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * @inheritDoc
     */
    public function cancelDowntime(Downtime $downtime, Host $host): void
    {
        if ($downtime->getServiceId() === null && $downtime->getHostId() === null) {
            throw new EngineException('Host and service id can not be null at the same time');
        }
        if ($downtime->getInternalId() === null) {
            throw new EngineException('Downtime internal id can not be null');
        }

        $suffix = ($downtime->getServiceId() === null) ? 'HOST' : 'SVC';
        $preCommand = sprintf('DEL_%s_DOWNTIME;%d', $suffix, $downtime->getInternalId());
        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
        $commandFull = $this->createCommandHeader($host->getPollerId()) . $commandToSend;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * Create the command header for external commands
     *
     * @param int $pollerId Id of the poller
     * @return string Returns the new generated command header
     * @throws \Exception
     */
    private function createCommandHeader(int $pollerId): string
    {
        return sprintf(
            "%s:%d:[%d] ",
            'EXTERNALCMD',
            $pollerId,
            (new \DateTime())->getTimestamp()
        );
    }
}
