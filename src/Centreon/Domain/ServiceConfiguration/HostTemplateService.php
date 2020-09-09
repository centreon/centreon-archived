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

namespace Centreon\Domain\ServiceConfiguration;

use Centreon\Domain\HostConfiguration\Host;

/**
 * This class is designed to represent a service template associated to a host template
 *
 * @package Centreon\Domain\ServiceConfiguration
 */
class HostTemplateService
{
    /**
     * @var Host
     */
    private $hostTemplate;

    /**
     * @var Service
     */
    private $serviceTemplate;

    /**
     * @return Host
     */
    public function getHostTemplate(): Host
    {
        return $this->hostTemplate;
    }

    /**
     * @param Host $hostTemplate
     * @return HostTemplateService
     */
    public function setHostTemplate(Host $hostTemplate): HostTemplateService
    {
        if ($hostTemplate->getType() !== Host::TYPE_HOST_TEMPLATE) {
            throw new \InvalidArgumentException('This host is not a template');
        }
        $this->hostTemplate = $hostTemplate;
        return $this;
    }

    /**
     * @return Service
     */
    public function getServiceTemplate(): Service
    {
        return $this->serviceTemplate;
    }

    /**
     * @param Service $serviceTemplate
     * @return HostTemplateService
     */
    public function setServiceTemplate(Service $serviceTemplate): HostTemplateService
    {
        if ($serviceTemplate->getServiceType() !== Service::TYPE_TEMPLATE) {
            throw new \InvalidArgumentException('This service is not a template');
        }
        $this->serviceTemplate = $serviceTemplate;
        return $this;
    }
}
