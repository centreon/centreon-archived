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

namespace Centreon\Domain\Menu\Interfaces;

use Centreon\Domain\Menu\Model\Page;

interface MenuRepositoryInterface
{
    /**
     * Disable Centreon Web Menu
     *
     * @return void
     */
    public function disableCentralMenus(): void;

    /**
     * Enable Centreon Web Menu
     *
     * @return void
     */
    public function enableCentralMenus(): void;

    /**
     * Find a Page by its topology page number.
     *
     * @param int $pageNumber
     * @return Page
     */
    public function findPageByTopologyPageNumber(int $pageNumber): ?Page;
}
