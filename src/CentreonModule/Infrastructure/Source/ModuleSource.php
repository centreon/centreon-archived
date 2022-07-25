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
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Domain\Repository\ModulesInformationsRepository;
use CentreonModule\Infrastructure\Source\SourceAbstract;
use CentreonLegacy\ServiceProvider as ServiceProviderLegacy;

class ModuleSource extends SourceAbstract
{
    public const TYPE = 'module';
    public const PATH = 'www/modules/';
    public const PATH_WEB = 'modules/';
    public const CONFIG_FILE = 'conf.php';

    /**
     * @var array<string,mixed>
     */
    protected $info;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->installer = $services->get(ServiceProviderLegacy::CENTREON_LEGACY_MODULE_INSTALLER);
        $this->upgrader = $services->get(ServiceProviderLegacy::CENTREON_LEGACY_MODULE_UPGRADER);
        $this->remover = $services->get(ServiceProviderLegacy::CENTREON_LEGACY_MODULE_REMOVER);
        $this->license = $services->get(ServiceProviderLegacy::CENTREON_LEGACY_MODULE_LICENSE);

        parent::__construct($services);
    }

    public function initInfo(): void
    {
        $this->info = $this->db
            ->getRepository(ModulesInformationsRepository::class)
            ->getAllModuleVsVersion()
        ;
    }

    /**
     * {@inheritDoc}
     *
     * Install module
     *
     * @throws ModuleException
     */
    public function install(string $id): ?Module
    {
        $this->installOrUpdateDependencies($id);

        return parent::install($id);
    }

    /**
     * {@inheritDoc}
     *
     * Remove module
     *
     * @throws ModuleException
     */
    public function remove(string $id): void
    {
        $this->validateRemovalRequirementsOrFail($id);

        $recordId = $this->db
            ->getRepository(ModulesInformationsRepository::class)
            ->findIdByName($id)
        ;

        ($this->remover)($id, $recordId)->remove();
    }

    /**
     * {@inheritDoc}
     *
     * Update module
     *
     * @throws ModuleException
     */
    public function update(string $id): ?Module
    {
        $this->installOrUpdateDependencies($id);

        $recordId = $this->db
            ->getRepository(ModulesInformationsRepository::class)
            ->findIdByName($id)
        ;

        ($this->upgrader)($id, $recordId)->upgrade();

        $this->initInfo();

        return $this->getDetail($id);
    }

    /**
     * @param string|null $search
     * @param boolean|null $installed
     * @param boolean|null $updated
     * @return array<int,\CentreonModule\Infrastructure\Entity\Module>
     */
    public function getList(string $search = null, bool $installed = null, bool $updated = null): array
    {
        $files = ($this->finder::create())
            ->files()
            ->name(static::CONFIG_FILE)
            ->depth('== 1')
            ->sortByName()
            ->in($this->getPath());

        $result = [];

        foreach ($files as $file) {
            $entity = $this->createEntityFromConfig($file->getPathName());

            if (!$this->isEligible($entity, $search, $installed, $updated)) {
                continue;
            }

            $result[] = $entity;
        }

        return $result;
    }

    /**
     * @param string $id
     * @return Module|null
     */
    public function getDetail(string $id): ?Module
    {
        $result = null;
        $path = $this->getPath() . $id;

        if (file_exists($path) === false) {
            return $result;
        }

        $files = ($this->finder::create())
            ->files()
            ->name(static::CONFIG_FILE)
            ->depth('== 0')
            ->sortByName()
            ->in($path)
        ;

        foreach ($files as $file) {
            $result = $this->createEntityFromConfig($file->getPathName());
        }

        return $result;
    }

    /**
     * @param string $configFile
     * @return Module
     */
    public function createEntityFromConfig(string $configFile): Module
    {
        $module_conf = [];

        $module_conf = $this->getModuleConf($configFile);

        $info = current($module_conf);

        $entity = new Module();
        $entity->setId(basename(dirname($configFile)));
        $entity->setPath(dirname($configFile));
        $entity->setType(static::TYPE);
        $entity->setName($info['rname']);
        $entity->setAuthor($info['author']);
        $entity->setVersion($info['mod_release']);
        $entity->setDescription($info['infos']);
        $entity->setKeywords($entity->getId());
        $entity->setLicense($this->processLicense($entity->getId(), $info));

        if (array_key_exists('stability', $info) && $info['stability']) {
            $entity->setStability($info['stability']);
        }

        if (array_key_exists('last_update', $info) && $info['last_update']) {
            $entity->setLastUpdate($info['last_update']);
        }

        if (array_key_exists('release_note', $info) && $info['release_note']) {
            $entity->setReleaseNote($info['release_note']);
        }

        if (array_key_exists('images', $info) && $info['images']) {
            if (is_string($info['images'])) {
                $info['images'] = [$info['images']];
            }

            foreach ($info['images'] as $image) {
                $entity->addImage(static::PATH_WEB . $entity->getId() . '/'. $image);
            }
        }

        if (array_key_exists('dependencies', $info) && is_array($info['dependencies'])) {
            $entity->setDependencies($info['dependencies']);
        }

        // load information about installed modules/widgets
        if ($this->info === null) {
            $this->initInfo();
        }

        if (array_key_exists($entity->getId(), $this->info)) {
            $entity->setVersionCurrent($this->info[$entity->getId()]);
            $entity->setInstalled(true);

            $isUpdated = $this->isUpdated($this->info[$entity->getId()], $entity->getVersion());
            $entity->setUpdated($isUpdated);
        }

        return $entity;
    }

    /**
     * @codeCoverageIgnore
     * @param string $configFile
     * @return array<mixed>
     */
    protected function getModuleConf(string $configFile): array
    {
        $module_conf = [];

        require $configFile;

        return $module_conf;
    }

    /**
     * Process license check and return license information
     * @param string $moduleId the module id (slug)
     * @param array<string,mixed> $info the info of the module from conf.php
     * @return array<string,string|bool> the license information (required, expiration_date)
     */
    protected function processLicense(string $moduleId, array $info): array
    {
        $license = [
            'required' => false
        ];

        if (!empty($info['require_license']) && $info['require_license'] === true) {
            $license = [
                'required' => true,
                'expiration_date' => $this->license->getLicenseExpiration($moduleId)
            ];
        }

        return $license;
    }

    /**
     * Install or update module dependencies when needed
     *
     * @param string $moduleId
     *
     * @throws ModuleException
     */
    private function installOrUpdateDependencies(string $moduleId): void
    {
        $sortedDependencies = $this->getSortedDependencies($moduleId);
        foreach ($sortedDependencies as $dependency) {
            $dependencyDetails = $this->getDetail($dependency);
            if ($dependencyDetails === null) {
                throw ModuleException::cannotFindModuleDetails($dependency);
            }

            if (! $dependencyDetails->isInstalled()) {
                $this->install($dependency);
            } elseif (! $dependencyDetails->isUpdated()) {
                $this->update($dependency);
            }
        }
    }

    /**
     * Sort module dependencies
     *
     * @param string $moduleId (example: centreon-license-manager)
     * @param string[] $alreadyProcessed
     * @return string[]
     *
     * @throws ModuleException
     */
    private function getSortedDependencies(
        string $moduleId,
        array $alreadyProcessed = []
    ) {
        $dependencies = [];

        if (in_array($moduleId, $alreadyProcessed)) {
            return $dependencies;
        }

        $alreadyProcessed[] = $moduleId;

        $moduleDetails = $this->getDetail($moduleId);
        if ($moduleDetails === null) {
            throw ModuleException::moduleIsMissing($moduleId);
        }

        foreach ($moduleDetails->getDependencies() as $dependency) {
            $dependencies[] = $dependency;

            $dependencyDetails = $this->getDetail($dependency);

            $dependencies = array_unique([
                ...$this->getSortedDependencies($dependencyDetails->getId(), $alreadyProcessed),
                ...$dependencies,
            ]);
        }

        return $dependencies;
    }

    /**
     * Validate requirements before remove (dependencies)
     *
     * @param string $moduleId (example: centreon-license-manager)
     *
     * @throws ModuleException
     */
    private function validateRemovalRequirementsOrFail(string $moduleId): void
    {
        $dependenciesToRemove = [];

        $modules = $this->getList();
        foreach ($modules as $module) {
            if ($module->isInstalled() && in_array($moduleId, $module->getDependencies())) {
                $dependenciesToRemove[] = $module->getName();
            }
        }

        if (! empty($dependenciesToRemove)) {
            throw ModuleException::modulesNeedToBeRemovedFirst($dependenciesToRemove);
        }
    }
}
