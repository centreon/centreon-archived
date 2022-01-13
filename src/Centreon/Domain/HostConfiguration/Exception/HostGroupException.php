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

namespace Centreon\Domain\HostConfiguration\Exception;

/**
 * This class is designed to contain all exceptions for the context of the host group.
 *
 * @package Centreon\Domain\HostConfiguration\Exception
 */
class HostGroupException extends \Exception
{
    /**
     * @param \Throwable $ex
     * @return HostGroupException
     */
    public static function addGroupException(\Throwable $ex)
    {
        return new self(_('Error when adding a host groups'), 0, $ex);
    }

    /**
     * @param \Throwable $ex
     * @return self
     */
    public static function findHostGroupsException(\Throwable $ex): self
    {
        return new self(_('Error when searching for host groups'), 0, $ex);
    }

    /**
     * @param \Throwable $ex
     * @param array<string, mixed> $data
     * @return self
     */
    public static function findHostGroupException(\Throwable $ex, array $data = []): self
    {
        return new self(
            sprintf(_('Error when searching for the host group (%s)'), $data['id'] ?? $data['name'] ?? null),
            0,
            $ex
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param \Throwable|null $ex
     * @return self
     */
    public static function notFoundException(array $data, \Throwable $ex = null): self
    {
        return new self(
            sprintf(_('Host group (%s) not found'), $data['id'] ?? $data['name'] ?? null),
            0,
            $ex
        );
    }
}
