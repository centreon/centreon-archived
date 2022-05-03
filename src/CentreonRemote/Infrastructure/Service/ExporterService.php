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

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use ReflectionClass;
use Psr\Container\ContainerInterface;
use Centreon\Infrastructure\Service\Traits\ServiceContainerTrait;

class ExporterService implements ContainerInterface
{
    use ServiceContainerTrait;

    /**
     * @param string $object
     * @param callable $factory
     */
    public function add(string $object, callable $factory): void
    {
        $interface = ExporterServiceInterface::class;
        $hasInterface = (new ReflectionClass($object))
            ->implementsInterface($interface);

        if ($hasInterface === false) {
            throw new NotFoundException(sprintf('Object %s must implement %s', $object, $interface));
        }

        $name = strtolower($object::getName());
        $this->objects[$name] = [
            'name' => $name,
            'classname' => $object,
            'factory' => $factory,
        ];

        $this->sort();
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id): bool
    {
        $result = $this->getKey($id);

        return $result !== null;
    }

    /**
     * @param string $id
     * @return int[]
     */
    public function get($id): array
    {
        $key = $this->getKey($id);
        if ($key === null) {
            throw new NotFoundException('Not found exporter with name: ' . $id);
        }

        $result = $this->objects[$key];

        return $result;
    }

    /**
     * @param string $id
     * @return int|null
     */
    private function getKey($id): ?int
    {
        foreach ($this->objects as $key => $object) {
            if ($object['name'] === $id) {
                return $key;
            }
        }

        return null;
    }

    private function sort(): void
    {
        usort($this->objects, function ($a, $b) {
            return $a['classname']::order() - $b['classname']::order();
        });
    }
}
