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

namespace Centreon\Domain\MonitoringServer\Exception;

use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Exception\TimeoutException;

/**
 * This class is designed to contain all exceptions concerning the generation and reloading of the monitoring server
 * configuration.
 *
 * @package Centreon\Domain\MonitoringServer\Exception
 */
class ConfigurationMonitoringServerException extends \Exception
{
    /**
     * @param int $monitoringServerId
     * @return EntityNotFoundException
     */
    public static function notFound(int $monitoringServerId): EntityNotFoundException
    {
        return new EntityNotFoundException(sprintf(_('Monitoring server not found (#%d)'), $monitoringServerId));
    }

    /**
     * @param int $monitoringServerId
     * @param string $errorMessage
     * @return self
     */
    public static function errorOnGeneration(int $monitoringServerId, string $errorMessage): self
    {
        return new self(
            sprintf(_('Generation error on monitoring server #%d: %s'), $monitoringServerId, $errorMessage)
        );
    }

    /**
     * @param int $monitoringServerId
     * @param string $errorMessage
     * @return self
     */
    public static function errorOnReload(int $monitoringServerId, string $errorMessage): self
    {
        return new self(sprintf(_('Reloading error on monitoring server #%d: %s'), $monitoringServerId, $errorMessage));
    }

    /**
     * @param \Throwable $ex
     * @return self
     */
    public static function errorRetrievingMonitoringServers(\Throwable $ex): self
    {
        return new self(_('Error on retrieving monitoring servers'));
    }

    /**
     * @param string $message
     * @return TimeoutException
     */
    public static function timeout(int $monitoringServerId, string $message): TimeoutException
    {
        return new TimeoutException(sprintf(_('Error on monitoring server #%d: %s'), $monitoringServerId, $message));
    }
}
