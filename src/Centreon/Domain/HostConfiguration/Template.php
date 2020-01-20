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
 * This class is designed to represent a template and the order in which it should be applied.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class Template
{
    /**
     * @var int Order in which changes are to be implemented
     */
    private $order = 1;

    /**
     * @var Host Host template
     */
    private $hostTemplate;

      /**
     * @return int
     */
    public function getOrder (): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     * @return Template
     */
    public function setOrder (int $order): Template
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return Host
     */
    public function getHostTemplate (): Host
    {
        return $this->hostTemplate;
    }

    /**
     * @param Host $hostTemplate
     * @return Template
     */
    public function setHostTemplate (Host $hostTemplate): Template
    {
        $this->hostTemplate = $hostTemplate;
        return $this;
    }
}
