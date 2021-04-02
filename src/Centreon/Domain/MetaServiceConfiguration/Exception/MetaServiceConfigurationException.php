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

namespace Centreon\Domain\MetaServiceConfiguration\Exception;

/**
 * This class is designed to contain all exceptions for the context of the meta service configuration.
 *
 * @package Centreon\Domain\MetaServiceConfiguration\Exception
 */
class MetaServiceConfigurationException extends \Exception
{
    /**
     * @param \Throwable $ex
     * @return self
     */
    public static function findMetaServicesConfigurations(\Throwable $ex): self
    {
        return new self(
            sprintf(_('Error when searching for the meta services configurations')),
            0,
            $ex
        );
    }

    /**
     * @param \Throwable $ex
     * @param int $metaId
     * @return self
     */
    public static function findOneMetaServiceConfiguration(\Throwable $ex, int $metaId): self
    {
        return new self(
            sprintf(_('Error when searching for the meta service configuration (%s)'), $metaId),
            0,
            $ex
        );
    }

    /**
     * @param int $metaId
     * @return self
     */
    public static function findOneMetaServiceConfigurationNotFound(int $metaId): self
    {
        return new self(
            sprintf(_('Meta service configuration (%s) not found'), $metaId)
        );
    }
}
