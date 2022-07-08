<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Platform\Application\UseCase\UpdateVersions;

class UpdateVersionsException extends \Exception
{
    /**
     * @return self
     */
    public static function updateAlreadyInProgress(): self
    {
        return new self(_('Update already in progress'));
    }

    /**
     * @param \Throwable $e
     * @return self
     */
    public static function errorWhenRetrievingCurrentVersion(\Throwable $e): self
    {
        return new self(_('An error occurred when retrieving current version'), 0, $e);
    }

    /**
     * @return self
     */
    public static function cannotRetrieveCurrentVersion(): self
    {
        return new self(_('Cannot retrieve current version'));
    }

    /**
     * @param \Throwable $e
     * @return self
     */
    public static function errorWhenRetrievingAvailableUpdates(\Throwable $e): self
    {
        return new self(_('An error occurred when getting available updates'), 0, $e);
    }

    /**
     * @param string $version
     * @param string $technicalMessage
     * @param \Throwable $e
     * @return self
     */
    public static function errorWhenApplyingUpdate(
        string $version,
        string $technicalMessage,
        \Throwable $e
    ): self {
        return new self(
            sprintf(_('An error occurred when applying update %s (%s)'), $version, $technicalMessage),
            0,
            $e
        );
    }

    /**
     * @param \Throwable $e
     * @return self
     */
    public static function errorWhenApplyingPostUpdate(\Throwable $e): self
    {
        return new self(_('An error occurred when applying post update actions'), 0, $e);
    }
}
