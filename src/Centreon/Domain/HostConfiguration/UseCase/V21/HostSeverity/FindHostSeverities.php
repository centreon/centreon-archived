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

namespace Centreon\Domain\HostConfiguration\UseCase\V21\HostSeverity;

use Centreon\Domain\HostConfiguration\Exception\HostSeverityException;
use Centreon\Domain\HostConfiguration\Interfaces\HostSeverityReadRepositoryInterface;

/**
 * This class is designed to represent a use case to find all host severities.
 *
 * @package Centreon\Domain\HostConfiguration\UseCase\V21
 */
class FindHostSeverities
{
    /**
     * @var HostSeverityReadRepositoryInterface
     */
    private $repository;

    /**
     * FindHostSeverities constructor.
     *
     * @param HostSeverityReadRepositoryInterface $repository
     */
    public function __construct(HostSeverityReadRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return FindHostSeveritiesResponse
     * @throws \Exception
     */
    public function execute(): FindHostSeveritiesResponse
    {
        $response = new FindHostSeveritiesResponse();
        try {
            $hostSeverities = $this->repository->findHostSeverities();
            $response->setHostSeverities($hostSeverities);
        } catch (\Throwable $ex) {
            HostSeverityException::searchHostSeveritiesException($ex);
        }
        return $response;
    }
}
