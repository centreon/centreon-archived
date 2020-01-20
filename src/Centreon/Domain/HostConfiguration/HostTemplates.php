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

namespace Centreon\Domain\HostConfiguration;

/**
 * This class is designed to represent the relationship between a host and those templates to which it belongs.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostTemplates
{
    /**
     * @var Host Host for which the templates are linked
     */
    private $host;

    /**
     * @var Template[] Templates to which the host belongs
     */
    private $templates;

    /**
     * @return Host
     */
    public function getHost (): Host
    {
        return $this->host;
    }

    /**
     * @param Host $host
     * @return HostTemplates
     */
    public function setHost (Host $host): HostTemplates
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return Template[]
     */
    public function getTemplates (): array
    {
        return $this->templates;
    }

    /**
     * @param Template[] $templates
     * @return HostTemplates
     */
    public function setTemplates (array $templates): HostTemplates
    {
        $this->templates = $templates;
        return $this;
    }
}
