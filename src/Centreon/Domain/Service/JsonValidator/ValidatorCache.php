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

namespace Centreon\Domain\Service\JsonValidator;

use Centreon\Domain\Service\JsonValidator\Interfaces\ValidatorCacheInterface;
use Symfony\Component\Config\ConfigCache;

class ValidatorCache implements ValidatorCacheInterface
{
    /**
     * @var ConfigCache
     */
    private $cache;
    /**
     * @var string Name of the cache file used to store data
     */
    private $cacheFile;

    /**
     * ValidatorCache constructor.
     *
     * @param string $cacheFile Name of the cache file
     * @param bool $isDebug State of the debug mode
     */
    public function __construct(string $cacheFile, bool $isDebug)
    {
        $this->cacheFile = $cacheFile;
        $this->cache = new ConfigCache($cacheFile, $isDebug);
    }

    /**
     * @inheritDoc
     */
    public function getCacheFile(): string
    {
        return $this->cacheFile;
    }

    /**
     * @inheritDoc
     */
    public function setCache(string $data, array $metadata = []): void
    {
        $resourceFiles = [];
        foreach ($metadata as $yamlFile) {
            $resourceFiles[] = $yamlFile;
        }
        $this->cache->write($data, $metadata);
    }

    /**
     * @inheritDoc
     */
    public function getCache(): ?string
    {
        return (($cache = file_get_contents($this->cacheFile)) !== false)
            ? (string) $cache
            : null;
    }

    /**
     * @inheritDoc
     */
    public function isCacheValid(): bool
    {
        return $this->cache->isFresh();
    }
}
