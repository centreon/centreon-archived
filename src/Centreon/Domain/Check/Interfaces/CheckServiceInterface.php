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

namespace Centreon\Domain\Check\Interfaces;

use Centreon\Domain\Check\Check;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Engine\EngineException;
use Centreon\Domain\Exception\EntityNotFoundException;
use JMS\Serializer\Exception\ValidationFailedException;
use Centreon\Domain\Contact\Interfaces\ContactFilterInterface;

interface CheckServiceInterface extends ContactFilterInterface
{
    /**
     * Adds a host check.
     *
     * @param Check $check Host check to schedule
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     * @throws ValidationFailedException
     */
    public function checkHost(Check $check): void;

    /**
     * Adds a service check.
     *
     * @param Check $check Service check to schedule
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     * @throws ValidationFailedException
     */
    public function checkService(Check $check): void;

    /**
     * Adds a Meta service check.
     *
     * @param Check $check Meta Service check to schedule
     * @throws EngineException
     * @throws EntityNotFoundException
     * @throws \Exception
     * @throws ValidationFailedException
     */
    public function checkMetaService(Check $check): void;

    /**
     * Adds a resource check.
     *
     * @param Check $check
     * @param ResourceEntity $resource
     * @throws EntityNotFoundException
     * @throws \Exception
     * @return void
     */
    public function checkResource(Check $check, ResourceEntity $resource): void;
}
