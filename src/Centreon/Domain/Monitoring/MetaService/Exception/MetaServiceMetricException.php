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

namespace Centreon\Domain\Monitoring\MetaService\Exception;

/**
 * This class is designed to contain all exceptions for the context of the metaservice metrics
 *
 * @package Centreon\Domain\MetaService\Exception
 */
class MetaServiceMetricException extends \Exception
{
    /**
     * @param \Throwable $ex
     * @return MetaServiceMetricException
     */
    public static function findMetaServiceMetricsException(\Throwable $ex, int $metaId): self
    {
        return new self(
            sprintf(_('Error when searching for the meta service (%d) metrics'), $metaId),
            0,
            $ex
        );
    }

    /**
     * Used when no meta service found
     * @return self
     */
    public static function findMetaServiceException(int $metaId): self
    {
        return new self(sprintf(_('Meta service with ID %d not found'), $metaId));
    }

    /**
     * @param integer $metaId
     * @return self
     */
    public static function unknownMetaMetricSelectionMode(int $metaId): self
    {
        return new self(sprintf(_('Unkown meta metrics selection mode provided for %d'), $metaId));
    }
}
