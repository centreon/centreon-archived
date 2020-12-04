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

namespace Centreon\Domain\HostConfiguration\UseCase\V21;

use Centreon\Domain\HostConfiguration\Exception\HostGroupException;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroupReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\UseCase\V21\FindHostGroupsResponse;

/**
 * This class is designed to represent a use case to find all host templates
 *
 * @package Centreon\Domain\HostConfiguration\UseCase
 */
class FindHostGroups
{
    /**
     * @var HostGroupReadRepositoryInterface
     */
    private $configurationReadRepository;

    /**
     * @param HostGroupReadRepositoryInterface $configurationReadRepository
     */
    public function __construct(HostGroupReadRepositoryInterface $configurationReadRepository)
    {
        $this->configurationReadRepository = $configurationReadRepository;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return FindHostGroupsResponse
     * @throws \Exception
     */
    public function execute(): FindHostGroupsResponse
    {
        try {
            $hostGroups = $this->configurationReadRepository->findHostGroups();
        } catch (\Exception $ex) {
            throw HostGroupException::searchHostGroupsException();
        }
        $response = new FindHostGroupsResponse();
        $response->setHostGroups($hostGroups);
        return $response;
    }
}
