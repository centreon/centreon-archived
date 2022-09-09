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

namespace Centreon\Domain\HostConfiguration;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\HostConfiguration\Exception\HostCategoryException;
use Centreon\Domain\HostConfiguration\Interfaces\HostCategory\HostCategoryReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostCategory\HostCategoryServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostCategory\HostCategoryWriteRepositoryInterface;
use Centreon\Domain\HostConfiguration\Model\HostCategory;

/**
 * This class is designed to manage the host categories.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostCategoryService implements HostCategoryServiceInterface
{
    /**
     * @var HostCategoryReadRepositoryInterface
     */
    private $readRepository;

    /**
     * @var ContactInterface
     */
    private $contact;
    /**
     * @var HostCategoryWriteRepositoryInterface
     */
    private $writeRepository;

    /**
     * @param HostCategoryReadRepositoryInterface $readRepository
     * @param HostCategoryWriteRepositoryInterface $writeRepository
     * @param ContactInterface $contact
     */
    public function __construct(
        HostCategoryReadRepositoryInterface $readRepository,
        HostCategoryWriteRepositoryInterface $writeRepository,
        ContactInterface $contact
    ) {
        $this->contact = $contact;
        $this->readRepository = $readRepository;
        $this->writeRepository = $writeRepository;
    }

    /**
     * @inheritDoc
     */
    public function addCategory(HostCategory $category): void
    {
        try {
            $this->writeRepository->addCategory($category);
        } catch (\Throwable $ex) {
            HostCategoryException::addCategoryException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findAllWithAcl(): array
    {
        try {
            return $this->readRepository->findAllByContact($this->contact);
        } catch (\Throwable $ex) {
            throw HostCategoryException::findHostCategoriesException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findAllWithoutAcl(): array
    {
        try {
            return $this->readRepository->findAll();
        } catch (\Throwable $ex) {
            throw HostCategoryException::findHostCategoriesException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findWithAcl(int $categoryId): ?HostCategory
    {
        try {
            return $this->readRepository->findByIdAndContact($categoryId, $this->contact);
        } catch (\Throwable $ex) {
            throw HostCategoryException::findHostCategoryException($ex, ['id' => $categoryId]);
        }
    }

    /**
     * @inheritDoc
     */
    public function findWithoutAcl(int $categoryId): ?HostCategory
    {
        try {
            return $this->readRepository->findById($categoryId);
        } catch (\Throwable $ex) {
            throw HostCategoryException::findHostCategoryException($ex, ['id' => $categoryId]);
        }
    }

    /**
     * @inheritDoc
     */
    public function findByNameWithAcl(string $categoryName): ?HostCategory
    {
        try {
            return $this->readRepository->findByNameAndContact($categoryName, $this->contact);
        } catch (\Throwable $ex) {
            throw HostCategoryException::findHostCategoryException($ex, ['name' => $categoryName]);
        }
    }

    /**
     * @inheritDoc
     */
    public function findByNameWithoutAcl(string $categoryName): ?HostCategory
    {
        try {
            return $this->readRepository->findByName($categoryName);
        } catch (\Throwable $ex) {
            throw HostCategoryException::findHostCategoryException($ex, ['name' => $categoryName]);
        }
    }

    /**
     * @inheritDoc
     */
    public function findByNamesWithoutAcl(array $categoriesName): array
    {
        try {
            return $this->readRepository->findByNames($categoriesName);
        } catch (\Throwable $ex) {
            throw HostCategoryException::findHostCategoriesException($ex);
        }
    }
}
