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

namespace Centreon\Domain\Option;

use Centreon\Domain\Option\Interfaces\OptionRepositoryInterface;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;

/**
 * This class is designed to manage the configuration options of Centreon
 *
 * @package Centreon\Domain\Option
 */
class OptionService implements OptionServiceInterface
{
    /**
     * @var OptionRepositoryInterface
     */
    private $repository;

    /**
     * @param OptionRepositoryInterface $repository
     */
    public function __construct(OptionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function findSelectedOptions(array $optionsToFind): array
    {
        try {
            $optionsFound = $this->repository->findAllOptions();
        } catch (\Throwable $ex) {
            throw new \Exception(_('Error when retrieving selected options'), 0, $ex);
        }
        $requestedOptions = [];
        foreach ($optionsFound as $option) {
            if (in_array($option->getName(), $optionsToFind)) {
                $requestedOptions[] = $option;
            }
        }
        return $requestedOptions;
    }

    /**
     * @inheritDoc
     */
    public function findAllOptions(bool $useCache): array
    {
        return $this->repository->findAllOptions($useCache);
    }
}
