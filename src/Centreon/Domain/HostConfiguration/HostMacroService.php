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

use Centreon\Domain\HostConfiguration\Exception\HostMacroException;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroWriteRepositoryInterface;

/**
 * This class is designed to manage all host macros.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostMacroService implements HostMacroServiceInterface
{
    /**
     * @var HostMacroReadRepositoryInterface
     */
    private $readRepository;
    /**
     * @var HostMacroWriteRepositoryInterface
     */
    private $writeRepository;

    /**
     * HostMacroService constructor.
     *
     * @param HostMacroReadRepositoryInterface $readRepository
     * @param HostMacroWriteRepositoryInterface $writeRepository
     */
    public function __construct(
        HostMacroReadRepositoryInterface $readRepository,
        HostMacroWriteRepositoryInterface $writeRepository
    ) {
        $this->readRepository = $readRepository;
        $this->writeRepository = $writeRepository;
    }

    /**
     * @inheritDoc
     */
    public function addMacroToHost(Host $host, HostMacro $hostMacro): void
    {
        try {
            $this->writeRepository->addMacroToHost($host, $hostMacro);
        } catch (\Throwable $ex) {
            throw HostMacroException::addMacroException($ex);
        }
    }
}
