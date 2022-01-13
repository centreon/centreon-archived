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

namespace Centreon\Domain\HostConfiguration\Exception;

/**
 * This class is designed to provide all exceptions for the HostTemplate entity factory.
 *
 * @package Centreon\Domain\HostConfiguration\Exception
 */
class HostTemplateFactoryException extends \Exception
{
    public static function notificationOptionsNotAllowed(string $options): self
    {
        return new self(sprintf(_('Notification options not allowed (%s)'), $options));
    }

    public static function stalkingOptionsNotAllowed(string $options): self
    {
        return new self(sprintf(_('Stalking options not allowed (%s)'), $options));
    }
}
