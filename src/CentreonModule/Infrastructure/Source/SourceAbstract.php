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

namespace CentreonModule\Infrastructure\Source;

use Psr\Container\ContainerInterface;
use CentreonLegacy\Core\Configuration\Configuration;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Infrastructure\Source\SourceInterface;

abstract class SourceAbstract implements SourceInterface
{
    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $db;

    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var \CentreonLegacy\Core\Widget\Upgrader|\CentreonLegacy\Core\Module\Upgrader
     */
    protected $upgrader;

    /**
     * @var \CentreonLegacy\Core\Module\License
     */
    protected $license;

    /**
     * @var \CentreonLegacy\Core\Widget\Remover|\CentreonLegacy\Core\Module\Remover
     */
    protected $remover;

    /**
     * @var \CentreonLegacy\Core\Widget\Installer|\CentreonLegacy\Core\Module\Installer
     */
    protected $installer;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->db = $services->get(\Centreon\ServiceProvider::CENTREON_DB_MANAGER);
        $this->finder = $services->get('finder');
        $this->path = $services->get('configuration')
            ->get(Configuration::CENTREON_PATH)
        ;
    }

    /**
     * Install module or widget
     *
     * @param string $id
     * @return Module|null
     */
    public function install(string $id): ?Module
    {
        ($this->installer)($id)->install();

        $this->initInfo();

        return $this->getDetail($id);
    }

    /**
     * Update module or widget
     *
     * @param string $id
     * @return Module|null
     */
    public function update(string $id): ?Module
    {
        ($this->upgrader)($id)->upgrade();

        $this->initInfo();

        return $this->getDetail($id);
    }

    /**
     * Remove module or widget
     *
     * @param string $id
     */
    public function remove(string $id): void
    {
        ($this->remover)($id)->remove();
    }

    public function isEligible(
        Module $entity,
        string $search = null,
        bool $installed = null,
        bool $updated = null
    ): bool {
        if ($search !== null && stripos($entity->getKeywords() . $entity->getName(), $search) === false) {
            return false;
        } elseif ($installed !== null && $entity->isInstalled() !== $installed) {
            return false;
        } elseif ($updated !== null && ($entity->isInstalled() !== true || $entity->isUpdated() !== $updated)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $installedVersion
     * @param string $codeVersion
     * @return boolean
     */
    public function isUpdated($installedVersion, $codeVersion): bool
    {
        $result = false;
        $installedVersion = trim(strtolower($installedVersion));
        $codeVersion = trim(strtolower($codeVersion));

        if ($installedVersion == $codeVersion) {
            $result = true;
        }

        return $result;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    protected function getPath(): string
    {
        return $this->path . static::PATH;
    }
}
