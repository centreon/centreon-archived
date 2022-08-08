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

namespace Centreon\Domain\Monitoring\SubmitResult\Interfaces;

use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\SubmitResult\SubmitResult;
use Centreon\Domain\Contact\Interfaces\ContactFilterInterface;

interface SubmitResultServiceInterface extends ContactFilterInterface
{
    /**
     * Function allowing user to submit a result to a service
     *
     * @param  SubmitResult $result
     * @return void
     */
    public function submitServiceResult(SubmitResult $result): void;

    /**
     * Function allowing user to submit a result to a meta service
     *
     * @param  SubmitResult $result
     * @return void
     */
    public function submitMetaServiceResult(SubmitResult $result): void;

    /**
     * Function allowing user to submit a result to a host
     *
     * @param  SubmitResult $result
     * @return void
     */
    public function submitHostResult(SubmitResult $result): void;
}
