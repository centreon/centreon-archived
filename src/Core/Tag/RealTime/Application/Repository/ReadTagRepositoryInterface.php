<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Tag\RealTime\Application\Repository;

use Core\Tag\RealTime\Domain\Model\Tag;

interface ReadTagRepositoryInterface
{
    /**
     * Find all tags
     *
     * @param int $typeId
     * @return Tag[]
     * @throws \Throwable
     */
    public function findAllByTypeId(int $typeId): array;

    /**
     * Find tags of type typeId linked to the resource (identified by id and parentId)
     *
     * @param integer $id
     * @param integer $parentId
     * @param integer $typeId
     * @return Tag[]
     * @throws \Throwable
     */
    public function findAllByResourceAndTypeId(int $id, int $parentId, int $typeId): array;
}
