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

namespace Centreon\Domain\Engine;

use Centreon\Domain\Check\Check;
use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Engine\Exception\EngineConfigurationException;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Downtime\DowntimeService;
use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\Service\AbstractCentreonService;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Monitoring\SubmitResult\SubmitResult;
use Centreon\Domain\Acknowledgement\AcknowledgementService;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Engine\Interfaces\EngineRepositoryInterface;
use Centreon\Domain\Monitoring\SubmitResult\SubmitResultService;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationRepositoryInterface;
use Centreon\Domain\Monitoring\Comment\Comment;
use Centreon\Domain\Monitoring\Comment\CommentService;

/**
 * This class is designed to send external command for Engine
 *
 * @package Centreon\Domain\Engine
 * @todo Replace the ValidationFailedException with a domain exception to avoid depending on the framework
 */
class EngineService extends AbstractCentreonService implements
    EngineServiceInterface,
    EngineConfigurationServiceInterface
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
     * @var EngineConfigurationRepositoryInterface
     */
    private $engineConfigurationRepository;

    private const ACKNOWLEDGEMENT_WITH_STICKY_OPTION = 2;
    private const ACKNOWLEDGEMENT_WITH_NO_STICKY_OPTION = 0;

    /**
     * CentCoreService constructor.
     *
     * @param EngineRepositoryInterface $engineRepository
     * @param EngineConfigurationRepositoryInterface $engineConfigurationRepository
     * @param EntityValidator $validator
     */
    public function __construct(
        EngineRepositoryInterface $engineRepository,
        EngineConfigurationRepositoryInterface $engineConfigurationRepository,
        EntityValidator $validator
    ) {
        $this->engineRepository = $engineRepository;
        $this->validator = $validator;
        $this->engineConfigurationRepository = $engineConfigurationRepository;
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

        /**
         * Specificity of the engine.
         * We do consider that an acknowledgement is sticky when value 2 is sent.
         * 0 or 1 is considered as a normal acknowledgement.
         */
        $preCommand = sprintf(
            'ACKNOWLEDGE_HOST_PROBLEM;%s;%d;%d;%d;%s;%s',
            $host->getName(),
            $acknowledgement->isSticky()
                ? self::ACKNOWLEDGEMENT_WITH_STICKY_OPTION
                : self::ACKNOWLEDGEMENT_WITH_NO_STICKY_OPTION,
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

        /**
         * Specificity of the engine.
         * We do consider that an acknowledgement is sticky when value 2 is sent.
         * 0 or 1 is considered as a normal acknowledgement.
         */
        $preCommand = sprintf(
            'ACKNOWLEDGE_SVC_PROBLEM;%s;%s;%d;%d;%d;%s;%s',
            $service->getHost()->getName(),
            $service->getDescription(),
            $acknowledgement->isSticky()
                ? self::ACKNOWLEDGEMENT_WITH_STICKY_OPTION
                : self::ACKNOWLEDGEMENT_WITH_NO_STICKY_OPTION,
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
        if (empty($service->getHost()) || empty($service->getHost()->getName())) {
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
            throw new EngineException(_('The contact alias is empty'));
        }
        if (empty($host->getName())) {
            throw new EngineException(_('Host name can not be empty'));
        }

        if ($this->validator->hasValidatorFor(Downtime::class)) {
            // We validate the downtime instance
            $errors = $this->validator->getValidator()->validate(
                $downtime,
                null,
                DowntimeService::VALIDATION_GROUPS_ADD_HOST_DOWNTIME
            );
            if ($errors->count() > 0) {
                throw new ValidationFailedException($errors);
            }
        }

        $commandNames = ['SCHEDULE_HOST_DOWNTIME'];
        if ($downtime->isWithServices()) {
            $commandNames[] = 'SCHEDULE_HOST_SVC_DOWNTIME';
        }

        $commands = [];
        foreach ($commandNames as $commandName) {
            $preCommand = sprintf(
                '%s;%s;%d;%d;%d;0;%d;%s;%s',
                $commandName,
                $host->getName(),
                $downtime->getStartTime()->getTimestamp(),
                $downtime->getEndTime()->getTimestamp(),
                (int) $downtime->isFixed(),
                $downtime->getDuration(),
                $this->contact->getAlias(),
                $downtime->getComment()
            );
            $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
            $commands[] = $this->createCommandHeader($host->getPollerId()) . $commandToSend;
        }
        $this->engineRepository->sendExternalCommands($commands);
    }

    /**
     * @inheritDoc
     */
    public function addServiceDowntime(Downtime $downtime, Service $service): void
    {
        if (empty($this->contact->getAlias())) {
            throw new EngineException(_('The contact alias is empty'));
        }
        if ($this->validator->hasValidatorFor(Downtime::class)) {
            // We validate the downtime instance
            $errors = $this->validator->getValidator()->validate(
                $downtime,
                null,
                DowntimeService::VALIDATION_GROUPS_ADD_SERVICE_DOWNTIME
            );
            if ($errors->count() > 0) {
                throw new ValidationFailedException($errors);
            }
        }

        if ($service->getHost() == null) {
            throw new EngineException(
                sprintf(_('The host of service (id: %d) is not defined'), $service->getId())
            );
        }
        if (empty($service->getHost()->getName())) {
            throw new EngineException(
                sprintf(_('Host name of service (id: %d) can not be empty'), $service->getId())
            );
        }
        if (empty($service->getDescription())) {
            throw new EngineException(
                sprintf(_('The description of service (id: %d) can not be empty'), $service->getId())
            );
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
        $command = $this->createCommandHeader($service->getHost()->getPollerId()) . $commandToSend;

        $this->engineRepository->sendExternalCommand($command);
    }

    /**
     * @inheritDoc
     */
    public function findEngineConfigurationByMonitoringServer(MonitoringServer $monitoringServer): ?EngineConfiguration
    {
        try {
            Assertion::notNull($monitoringServer->getId(), 'MonitoringServer::id');
            return $this->engineConfigurationRepository->findEngineConfigurationByMonitoringServerId(
                $monitoringServer->getId()
            );
        } catch (\Throwable $ex) {
            throw EngineConfigurationException::findEngineConfigurationException(
                $ex,
                ['id' => $monitoringServer->getId()]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function findEngineConfigurationByHost(\Centreon\Domain\HostConfiguration\Host $host): ?EngineConfiguration
    {
        if ($host->getId() === null) {
            throw new EngineException(_('The host id cannot be null'));
        }
        try {
            return $this->engineConfigurationRepository->findEngineConfigurationByHost($host);
        } catch (\Throwable $ex) {
            throw new EngineException(
                sprintf(_('Error when searching for the Engine configuration (%s)'), $host->getId()),
                0,
                $ex
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function findEngineConfigurationByName(string $engineName): ?EngineConfiguration
    {
        try {
            return $this->engineConfigurationRepository->findEngineConfigurationByName($engineName);
        } catch (\Throwable $ex) {
            throw new EngineException(
                sprintf(_('Error when searching for the Engine configuration (%s)'), $engineName),
                0,
                $ex
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function scheduleForcedHostCheck(Host $host): void
    {
        if (empty($host->getName())) {
            throw new EngineException(_('Host name can not be empty'));
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
            throw new EngineException(_('Host and service id can not be null at the same time'));
        }
        if ($downtime->getInternalId() === null) {
            throw new EngineException(_('Downtime internal id can not be null'));
        }

        $suffix = (empty($downtime->getServiceId())) ? 'HOST' : 'SVC';
        $preCommand = sprintf('DEL_%s_DOWNTIME;%d', $suffix, $downtime->getInternalId());
        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
        $commandFull = $this->createCommandHeader($host->getPollerId()) . $commandToSend;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * @inheritDoc
     */
    public function scheduleHostCheck(Check $check, Host $host): void
    {
        // We validate the check instance
        $errors = $this->validator->validate(
            $check,
            null,
            Check::VALIDATION_GROUPS_HOST_CHECK
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        if (empty($host->getName())) {
            throw new EngineException(_('Host name can not be empty'));
        }

        $commandNames = [$check->isForced() ? 'SCHEDULE_FORCED_HOST_CHECK' : 'SCHEDULE_HOST_CHECK'];
        if ($check->isWithServices()) {
            $commandNames[] = $check->isForced() ? 'SCHEDULE_FORCED_HOST_SVC_CHECKS' : 'SCHEDULE_HOST_SVC_CHECKS';
        }

        $commands = [];
        foreach ($commandNames as $commandName) {
            $preCommand = sprintf(
                '%s;%s;%d',
                $commandName,
                $host->getName(),
                $check->getCheckTime()->getTimestamp()
            );
            $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
            $commands[] = $this->createCommandHeader($host->getPollerId()) . $commandToSend;
        }
        $this->engineRepository->sendExternalCommands($commands);
    }

    /**
     * @inheritDoc
     */
    public function scheduleServiceCheck(Check $check, Service $service): void
    {
        // We validate the check instance
        $errors = $this->validator->validate(
            $check,
            null,
            Check::VALIDATION_GROUPS_SERVICE_CHECK
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        if (empty($service->getHost()->getName())) {
            throw new EngineException(_('Host name cannot be empty'));
        }

        if (empty($service->getDescription())) {
            throw new EngineException(_('Service description cannot be empty'));
        }

        $commandName = $check->isForced() ? 'SCHEDULE_FORCED_SVC_CHECK' : 'SCHEDULE_SVC_CHECK';

        $command = sprintf(
            '%s;%s;%s;%d',
            $commandName,
            $service->getHost()->getName(),
            $service->getDescription(),
            $check->getCheckTime()->getTimestamp()
        );

        $commandFull = $this->createCommandHeader($service->getHost()->getPollerId()) . $command;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * @inheritDoc
     */
    public function submitHostResult(SubmitResult $result, Host $host): void
    {
        // We validate the SubmitResult instance (replace by the Validation of CHECK RESULT)
        $errors = $this->validator->validate(
            $result,
            null,
            SubmitResultService::VALIDATION_GROUPS_HOST_SUBMIT_RESULT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        if (empty($host->getName())) {
            throw new EngineException(_('Host name can not be empty'));
        }

        $commandName = 'PROCESS_HOST_CHECK_RESULT';

        $command = sprintf(
            '%s;%s;%d;%s|%s',
            $commandName,
            $host->getName(),
            $result->getStatus(),
            $result->getOutput(),
            $result->getPerformanceData()
        );

        $commandFull = $this->createCommandHeader($host->getPollerId()) . $command;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * @inheritDoc
     */
    public function submitServiceResult(SubmitResult $result, Service $service): void
    {
        // We validate the check instance (replace by the Validation of CHECK RESULT)
        $errors = $this->validator->validate(
            $result,
            null,
            SubmitResultService::VALIDATION_GROUPS_SERVICE_SUBMIT_RESULT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        if (empty($service->getHost()->getName())) {
            throw new EngineException(_('Host name cannot be empty'));
        }

        if (empty($service->getDescription())) {
            throw new EngineException(_('Service description cannot be empty'));
        }

        $commandName = 'PROCESS_SERVICE_CHECK_RESULT';

        $command = sprintf(
            '%s;%s;%s;%d;%s|%s',
            $commandName,
            $service->getHost()->getName(),
            $service->getDescription(),
            $result->getStatus(),
            $result->getOutput(),
            $result->getPerformanceData()
        );

        $commandFull = $this->createCommandHeader($service->getHost()->getPollerId()) . $command;
        $this->engineRepository->sendExternalCommand($commandFull);
    }

    /**
     * @inheritDoc
     */
    public function addServiceComment(Comment $comment, Service $service): void
    {
        // We validate the comment instance
        $errors = $this->validator->validate(
            $comment,
            null,
            CommentService::VALIDATION_GROUPS_SERVICE_ADD_COMMENT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        if (empty($this->contact->getAlias())) {
            throw new EngineException(_('The contact alias is empty'));
        }

        if ($service->getHost() == null) {
            throw new EngineException(
                sprintf(_('The host of service (id: %d) is not defined'), $service->getId())
            );
        }
        if (empty($service->getHost()->getName())) {
            throw new EngineException(
                sprintf(_('Host name of service (id: %d) can not be empty'), $service->getId())
            );
        }
        if (empty($service->getDescription())) {
            throw new EngineException(
                sprintf(_('The description of service (id: %d) can not be empty'), $service->getId())
            );
        }
        $preCommand = sprintf(
            'ADD_SVC_COMMENT;%s;%s;1;%s;%s',
            $service->getHost()->getName(),
            $service->getDescription(),
            $this->contact->getAlias(),
            $comment->getComment()
        );
        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
        $command = $this->createCommandHeader($service->getHost()->getPollerId(), $comment->getDate()) . $commandToSend;

        $this->engineRepository->sendExternalCommand($command);
    }

    /**
     * @inheritDoc
     */
    public function addHostComment(Comment $comment, Host $host): void
    {
        // We validate the comment instance
        $errors = $this->validator->validate(
            $comment,
            null,
            CommentService::VALIDATION_GROUPS_HOST_ADD_COMMENT
        );

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors);
        }

        if (empty($this->contact->getAlias())) {
            throw new EngineException(_('The contact alias is empty'));
        }

        if (empty($host->getName())) {
            throw new EngineException(
                sprintf(_('Host name can not be empty for host (id: %d)'), $host->getId())
            );
        }

        $preCommand = sprintf(
            'ADD_HOST_COMMENT;%s;1;%s;%s',
            $host->getName(),
            $this->contact->getAlias(),
            $comment->getComment()
        );
        $commandToSend = str_replace(['"', "\n"], ['', '<br/>'], $preCommand);
        $command = $this->createCommandHeader($host->getPollerId(), $comment->getDate()) . $commandToSend;
        $this->engineRepository->sendExternalCommand($command);
    }

    /**
     * Create the command header for external commands
     *
     * @param int $pollerId Id of the poller
     * @param \DateTime|null $date date of the command
     * @return string Returns the new generated command header
     * @throws \Exception
     */
    private function createCommandHeader(int $pollerId, \DateTime $date = null): string
    {
        return sprintf(
            "%s:%d:[%d] ",
            'EXTERNALCMD',
            $pollerId,
            $date ? $date->getTimestamp() : (new \DateTime())->getTimestamp()
        );
    }
}
