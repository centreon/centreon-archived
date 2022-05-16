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

namespace CentreonRemote\Infrastructure\Service;

class ExporterCacheService
{
    /**
     * @var mixed
     */
    private $data;

    /**
     * Get info if exists and if not add it using callback function
     *
     * @param string $key
     * @param callable $data
     * @return mixed
     */
    public function getIf(string $key, callable $data)
    {
        if (!$this->has($key)) {
            $this->data[$key] = $data();
        }

        $result = $this->data[$key];

        return $result;
    }

    /**
     * Setter
     *
     * @param string $key
     * @param mixed $data
     */
    public function set(string $key, $data): void
    {
        $this->data[$key] = $data;
    }

    /**
     * Merge
     *
     * @param string $key
     * @param mixed $data
     */
    public function merge(string $key, $data): void
    {
        if (!$this->has($key)) {
            $this->set($key, $data);
        } else {
            $this->data[$key] = array_merge($data, $this->data[$key]);
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $result = $this->data === null ? false : array_key_exists($key, $this->data);

        return $result;
    }

    /**
     * Getter
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if (!$this->has($key)) {
            return null;
        }

        $result = $this->data[$key];

        return $result;
    }

    /**
     * Destroy data
     */
    public function destroy(): void
    {
        $this->data = null;
    }
}
