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
 *Controller
 */
declare(strict_types=1);

namespace Centreon\Domain\Service\JsonValidator\Interfaces;

use Centreon\Domain\Service\JsonValidator\ValidatorCache;

interface ValidatorCacheInterface
{
    /**
     * Stores data in the cache file.
     *
     * @param string $data Data to store
     * @param array $metadata Metadata list
     */
    public function setCache(string $data, array $metadata = []): void;

    /**
     * Get the pathname of cache file.
     *
     * @return string
     */
    public function getCacheFile(): string;

    /**
     * Get the cached data.
     *
     * @return string|null
     */
    public function getCache(): ?string;

    /**
     * Indicates whether cache is valid or not.
     * <br\>**Warning**, with Docker the "filemtime" function returns the start date of
     * the Container and not the modification date of the file.
     * So when debug = TRUE, the Cache will not be able to detect the
     * modifications of the YAML definition files stored in metadata.
     *
     * @return bool Returns TRUE if the cache is valid
     */
    public function isCacheValid(): bool;
}
