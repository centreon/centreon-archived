<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\MonitoringServer;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\MonitoringServer\Exception\RealTimeMonitoringServerException;
use Centreon\Domain\MonitoringServer\Interfaces\RealTimeMonitoringServerServiceInterface;
use Centreon\Domain\MonitoringServer\Interfaces\RealTimeMonitoringServerRepositoryInterface;

/**
 * This class is designed to manage the real time monitoring servers.
 *
 * @package Centreon\Domain\MonitoringServer
 */
class RealTimeMonitoringServerService implements RealTimeMonitoringServerServiceInterface
{
    /**
     * @var RealTimeMonitoringServerRepositoryInterface
     */
    private $repository;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * @param RealTimeMonitoringServerRepositoryInterface $repository
     * @param ContactInterface $contact
     */
    public function __construct(
        RealTimeMonitoringServerRepositoryInterface $repository,
        ContactInterface $contact
    ) {
        $this->repository = $repository;
        $this->contact = $contact;
    }

    /**
     * @inheritDoc
     */
    public function findAllWithAcl(): array
    {
        try {
            return $this->repository->findAllByContact($this->contact);
        } catch (\Throwable $ex) {
            throw RealTimeMonitoringServerException::findRealTimeMonitoringServersException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findAllWithoutAcl(): array
    {
        try {
            return $this->repository->findAll();
        } catch (\Throwable $ex) {
            throw RealTimeMonitoringServerException::findRealTimeMonitoringServersException($ex);
        }
    }
}
