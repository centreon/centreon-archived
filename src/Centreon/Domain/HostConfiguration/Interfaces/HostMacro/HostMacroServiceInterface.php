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

namespace Centreon\Domain\HostConfiguration\Interfaces\HostMacro;

use Centreon\Domain\HostConfiguration\Exception\HostMacroServiceException;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\HostConfiguration\HostMacroService;

/**
 * @package Centreon\Domain\HostConfiguration\Interfaces\HostMacro
 */
interface HostMacroServiceInterface
{
    /**
     * Add a macro to a host.
     *
     * @param Host $host Host linked to host macro to be added
     * @param HostMacro $hostMacro Host macro to be added
     * @throws HostMacroServiceException
     */
    public function addMacroToHost(Host $host, HostMacro $hostMacro): void;

    /**
     * find all macros of host.
     *
     * @param Host $host
     * @return HostMacro[]
     * @throws HostMacroServiceException
     */
    public function findHostMacros(Host $host): array;

    /**
     * Update a host macro.
     *
     * @param HostMacro $macro
     * @throws HostMacroServiceException
     */
    public function updateMacro(HostMacro $macro): void;
}
