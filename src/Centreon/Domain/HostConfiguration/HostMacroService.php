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

namespace Centreon\Domain\HostConfiguration;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\HostConfiguration\Exception\HostMacroServiceException;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroWriteRepositoryInterface;

/**
 * This class is designed to manage all host macros.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostMacroService implements HostMacroServiceInterface
{
    /**
     * HostMacroService constructor.
     *
     * @param HostMacroWriteRepositoryInterface $writeRepository
     * @param HostMacroReadRepositoryInterface $readRepository
     */
    public function __construct(
        private HostMacroWriteRepositoryInterface $writeRepository,
        private HostMacroReadRepositoryInterface $readRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function addMacroToHost(Host $host, HostMacro $hostMacro): void
    {
        try {
            $this->writeRepository->addMacroToHost($host, $hostMacro);
        } catch (\Throwable $ex) {
            throw HostMacroServiceException::addMacroException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findHostMacros(Host $host): array
    {
        try {
            Assertion::notNull($host->getId(), 'Host::id');
            return $this->readRepository->findAllByHost($host);
        } catch (\Throwable $ex) {
            throw HostMacroServiceException::errorOnReadingHostMacros($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function updateMacro(HostMacro $macro): void
    {
        try {
            Assertion::notNull($macro->getId(), 'HostMacro::id');
            Assertion::notNull($macro->getHostId(), 'HostMacro::host_id');
            $this->writeRepository->updateMacro($macro);
        } catch (\Throwable $ex) {
            throw HostMacroServiceException::errorOnUpdatingMacro($ex);
        }
    }
}
