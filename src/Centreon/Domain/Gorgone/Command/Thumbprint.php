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

namespace Centreon\Domain\Gorgone\Command;

use Centreon\Domain\Gorgone\Interfaces\CommandInterface;

/**
 * This class is designed to send thumbprint command of internal type to the Gorgone server.
 *
 * @package Centreon\Domain\Gorgone\Command
 */
final class Thumbprint extends AbstractCommand implements CommandInterface
{
    // Internal name of this command
    private const NAME = 'internal::thumbprint';

    /**
     * @inheritDoc
     */
    public function getUriRequest(): string
    {
        return 'nodes/' . $this->getMonitoringInstanceId() . '/internal/thumbprint';
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    public function getMethod(): string
    {
        return self::METHOD_GET;
    }
}
