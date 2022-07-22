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

use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;

class DowntimeRequest
{
    private ?\Centreon\Domain\Downtime\Downtime $downtime = null;

    /**
     * @var ResourceEntity[]
     */
    private array $resources = [];

    public function getDowntime(): Downtime
    {
        return $this->downtime;
    }

    public function setDowntime(Downtime $downtime): DowntimeRequest
    {
        $this->downtime = $downtime;
        return $this;
    }

    /**
     * @return ResourceEntity[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * @param ResourceEntity[] $resources
     */
    public function setResources(array $resources): DowntimeRequest
    {
        $this->resources = $resources;
        return $this;
    }
}
