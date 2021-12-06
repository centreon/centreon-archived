<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Configuration\Icon;

use Centreon\Domain\Configuration\Icon\Interfaces\IconRepositoryInterface;
use Centreon\Domain\Configuration\Icon\Interfaces\IconServiceInterface;

/**
 * This class is designed to manage icon-related actions such as configuration.
 *
 * @package Centreon\Domain\Configuration\Icon
 */
class IconService implements IconServiceInterface
{
    /**
     * @var IconRepositoryInterface
     */
    private $iconRepository;

    /**
     * IconService constructor.
     *
     * @param IconRepositoryInterface $iconRepository
     */
    public function __construct(IconRepositoryInterface $iconRepository)
    {
        $this->iconRepository = $iconRepository;
    }

    /**
     * @inheritDoc
     */
    public function getIcons(): array
    {
        return $this->iconRepository->getIconsWithRequestParameters();
    }
}
