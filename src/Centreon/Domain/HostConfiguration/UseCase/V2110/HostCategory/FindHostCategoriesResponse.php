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

namespace Centreon\Domain\HostConfiguration\UseCase\V2110\HostCategory;

use Centreon\Domain\HostConfiguration\Model\HostCategory;

/**
 * This class is a DTO for the FindHostCategories use case.
 *
 * @package Centreon\Domain\HostConfiguration\UseCase\V21
 */
class FindHostCategoriesResponse
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private $hostCategories = [];

    /**
     * @param HostCategory[] $hostCategories
     */
    public function setHostCategories(array $hostCategories): void
    {
        foreach ($hostCategories as $hostCategory) {
            $this->hostCategories[] = [
                'id' => $hostCategory->getId(),
                'name' => $hostCategory->getName(),
                'alias' => $hostCategory->getAlias(),
                'comments' => $hostCategory->getComments(),
                'is_activated' => $hostCategory->isActivated()
            ];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getHostCategories(): array
    {
        return $this->hostCategories;
    }
}
