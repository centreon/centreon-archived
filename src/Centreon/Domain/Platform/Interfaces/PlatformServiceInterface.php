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

namespace Centreon\Domain\Platform\Interfaces;

use Centreon\Domain\Platform\PlatformException;

interface PlatformServiceInterface
{
    /**
     * Retrieves the web version of the Centreon platform.
     *
     * @return string Version of the Centreon platform
     * @throws PlatformException
     */
    public function getWebVersion(): string;

    /**
     * Retrieves the version of each modules installed on the Centreon platform.
     *
     * @return array<string, string> Version of the modules on the Centreon platform
     * @throws PlatformException
     */
    public function getModulesVersion(): array;

    /**
     * Retrieves the version of each widget installed on the Centreon platform.
     *
     * @return array<string, string> Version of the widgets on the Centreon platform
     * @throws PlatformException
     */
    public function getWidgetsVersion(): array;
}
