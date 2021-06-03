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

namespace Centreon\Domain\MetaServiceConfiguration;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\MetaServiceConfiguration\Exception\MetaServiceConfigurationException;
use Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationServiceInterface;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationReadRepositoryInterface;

/**
 * This class is designed to manage the host categories.
 *
 * @package Centreon\Domain\MetaServiceConfiguration
 */
class MetaServiceConfigurationService implements MetaServiceConfigurationServiceInterface
{
    /**
     * @var MetaServiceConfigurationReadRepositoryInterface
     */
    private $readRepository;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * @param MetaServiceConfigurationReadRepositoryInterface $readRepository
     * @param ContactInterface $contact
     */
    public function __construct(
        MetaServiceConfigurationReadRepositoryInterface $readRepository,
        ContactInterface $contact
    ) {
        $this->contact = $contact;
        $this->readRepository = $readRepository;
    }

    /**
     * @inheritDoc
     */
    public function findWithAcl(int $metaId): ?MetaServiceConfiguration
    {
        try {
            return $this->readRepository->findByIdAndContact($metaId, $this->contact);
        } catch (\Throwable $ex) {
            throw MetaServiceConfigurationException::findOneMetaServiceConfiguration($ex, $metaId);
        }
    }

    /**
     * @inheritDoc
     */
    public function findWithoutAcl(int $metaId): ?MetaServiceConfiguration
    {
        try {
            return $this->readRepository->findById($metaId);
        } catch (\Throwable $ex) {
            throw MetaServiceConfigurationException::findOneMetaServiceConfiguration($ex, $metaId);
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
            throw MetaServiceConfigurationException::findMetaServicesConfigurations($ex);
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
            throw MetaServiceConfigurationException::findMetaServicesConfigurations($ex);
        }
    }
}
