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

namespace Centreon\Domain\Platform;

use Centreon\Domain\Platform\Interfaces\PlatformRepositoryInterface;
use Centreon\Domain\Platform\Interfaces\PlatformServiceInterface;

/**
 * This class is designed to retrieve the version of modules, widgets, remote pollers from the Centreon Platform.
 *
 * @package Centreon\Domain\Platform
 */
class PlatformService implements PlatformServiceInterface
{

    /**
     * @var PlatformRepositoryInterface
     */
    private $platformRepository;

    /**
     * @param PlatformRepositoryInterface $informationRepository
     */
    public function __construct(PlatformRepositoryInterface $informationRepository)
    {
        $this->platformRepository = $informationRepository;
    }

    /**
     * @inheritDoc
     */
    public function getWebVersion(): string
    {
        try {
            $webVersion = $this->platformRepository->getWebVersion();
            return ($webVersion !== null) ? $webVersion : '0.0.0';
        } catch (\Exception $ex) {
            throw new PlatformException('Error while searching for the web version of the Centreon platform');
        }
    }

    /**
     * @inheritDoc
     */
    public function getModulesVersion(): array
    {
        try {
            return $this->platformRepository->getModulesVersion();
        } catch (\Exception $ex) {
            throw new PlatformException('Error while searching for the modules version of the Centreon platform');
        }
    }

    /**
     * @inheritDoc
     */
    public function getWidgetsVersion(): array
    {
        try {
            return $this->platformRepository->getWidgetsVersion();
        } catch (\Exception $ex) {
            throw new PlatformException('Error while searching for the widgets version of the Centreon platform');
        }
    }
}
