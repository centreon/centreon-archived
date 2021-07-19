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

namespace Centreon\Application\Request;

use Centreon\Domain\Check\Check;
use Centreon\Domain\Monitoring\Resources as ResourceEntity;

class CheckRequest
{
    /**
     * resources
     *
     * @var ResourceEntity[]
     */
    private $resources = [];

    /**
     * check
     *
     * @var Check
     */
    private $check;

    /**
     * Get resources
     *
     * @return ResourceEntity[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * Set resources
     *
     * @param ResourceEntity[]  $resources  resources
     *
     * @return self
     */
    public function setResources(array $resources): CheckRequest
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * Get check
     *
     * @return Check
     */
    public function getCheck(): Check
    {
        return $this->check;
    }

    /**
     * Set check
     *
     * @param Check $check check
     *
     * @return self
     */
    public function setCheck(Check $check): CheckRequest
    {
        $this->check = $check;

        return $this;
    }
}
