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

namespace Centreon\Domain\HostConfiguration\UseCase\V2110\HostTemplate;

use Centreon\Domain\HostConfiguration\Exception\HostTemplateException;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationReadRepositoryInterface;
use Centreon\Domain\Repository\RepositoryException;

/**
 * This class is designed to represent a use case to find all host templates
 *
 * @package Centreon\Domain\HostConfiguration\UseCase\V21\HostTemplate
 */
class FindHostTemplates
{
    /**
     * @var HostConfigurationReadRepositoryInterface
     */
    private $configurationReadRepository;

    /**
     * @param HostConfigurationReadRepositoryInterface $configurationReadRepository
     */
    public function __construct(HostConfigurationReadRepositoryInterface $configurationReadRepository)
    {
        $this->configurationReadRepository = $configurationReadRepository;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return FindHostTemplatesResponse
     * @throws RepositoryException
     * @throws HostTemplateException
     * @throws \Throwable
     */
    public function execute(): FindHostTemplatesResponse
    {
        try {
            $hostTemplates = $this->configurationReadRepository->findHostTemplates();
        } catch (RepositoryException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw HostTemplateException::searchHostTemplatesException($ex);
        }
        $response = new FindHostTemplatesResponse();
        $response->setHostTemplates($hostTemplates);
        return $response;
    }

    /**
     * @return HostConfigurationReadRepositoryInterface
     */
    public function getConfigurationReadRepository(): HostConfigurationReadRepositoryInterface
    {
        return $this->configurationReadRepository;
    }
}
