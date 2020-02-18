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

namespace Centreon\Domain\Monitoring\Metric\Interfaces;

use Centreon\Domain\Contact\Interfaces\ContactFilterInterface;
use Centreon\Domain\Monitoring\Service;

interface MetricServiceInterface extends ContactFilterInterface
{
    /**
     * Find metrics data linked to a service.
     *
     * @param Service $service
     * @param \DateTime $start start date
     * @param \DateTime $end end date
     * @return array
     * @throws \Exception
     */
    public function findMetricsByService(Service $service, \DateTime $start, \DateTime $end): array;

    /**
     * Find status data linked to a service.
     *
     * @param Service $service
     * @param \DateTime $start start date
     * @param \DateTime $end end date
     * @return array
     * @throws \Exception
     */
    public function findStatusByService(Service $service, \DateTime $start, \DateTime $end): array;
}
