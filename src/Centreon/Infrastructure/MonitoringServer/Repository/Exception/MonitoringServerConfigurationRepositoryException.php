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

namespace Centreon\Infrastructure\MonitoringServer\Repository\Exception;

use Centreon\Domain\Exception\TimeoutException;
use Centreon\Domain\Repository\RepositoryException;

/**
 * This class is designed to contain all the exceptions concerning the repository for generating and reloading the
 * monitoring server configurations.
 *
 * @package Centreon\Infrastructure\MonitoringServer\Repository\Exception
 */
class MonitoringServerConfigurationRepositoryException extends RepositoryException
{
    /**
     * @param int|null $errorCode
     * @return self
     */
    public static function apiRequestFailed(?int $errorCode): self
    {
        return new self(sprintf(_('Request failed (%d)'), $errorCode));
    }

    /**
     * @return self
     */
    public static function errorWhenInitializingApiUri(): self
    {
        return new self(_('Error when initializing the api uri'));
    }

    /**
     * @return self
     */
    public static function responseEmpty(): self
    {
        return new self(_('Response empty'));
    }

    /**
     * @param \Throwable $ex
     * @return TimeoutException
     */
    public static function timeout(\Throwable $ex): TimeoutException
    {
        return new TimeoutException(_('Execution was too long and reached timeout'), 0, $ex);
    }

    /**
     * @param \Throwable $ex
     * @return self
     */
    public static function unexpectedError(\Throwable $ex): self
    {
        return new self($ex->getMessage(), $ex->getCode(), $ex);
    }
}
