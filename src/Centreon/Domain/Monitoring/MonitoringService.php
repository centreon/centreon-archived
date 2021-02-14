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

namespace Centreon\Domain\Monitoring;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationServiceInterface;
use Centreon\Domain\Monitoring\Exception\MonitoringServiceException;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationServiceInterface;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;
use Centreon\Domain\HostConfiguration\Exception\HostCommandException;
use Centreon\Domain\ServiceConfiguration\Exception\ServiceCommandException;

/**
 * Monitoring class used to manage the real time services and hosts
 *
 * @package Centreon\Domain\Monitoring
 */
class MonitoringService extends AbstractCentreonService implements MonitoringServiceInterface
{
    use CommandLineTrait;

    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @var ServiceConfigurationServiceInterface
     */
    private $serviceConfiguration;
    /**
     * @var HostConfigurationServiceInterface
     */
    private $hostConfiguration;
    /**
     * @var MonitoringServerServiceInterface
     */
    private $monitoringServerService;

    /**
     * @param MonitoringRepositoryInterface $monitoringRepository
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param ServiceConfigurationServiceInterface $serviceConfigurationService
     * @param HostConfigurationServiceInterface $hostConfigurationService
     * @param MonitoringServerServiceInterface $monitoringServerService
     */
    public function __construct(
        MonitoringRepositoryInterface $monitoringRepository,
        AccessGroupRepositoryInterface $accessGroupRepository,
        ServiceConfigurationServiceInterface $serviceConfigurationService,
        HostConfigurationServiceInterface $hostConfigurationService,
        MonitoringServerServiceInterface $monitoringServerService
    ) {
        $this->monitoringRepository = $monitoringRepository;
        $this->accessGroupRepository = $accessGroupRepository;
        $this->serviceConfiguration = $serviceConfigurationService;
        $this->hostConfiguration = $hostConfigurationService;
        $this->monitoringServerService = $monitoringServerService;
    }

    /**
     * {@inheritDoc}
     * @param Contact $contact
     * @return self
     */
    public function filterByContact($contact): self
    {
        parent::filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->monitoringRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findServices(): array
    {
        return $this->monitoringRepository->findServices();
    }

    /**
     * @inheritDoc
     */
    public function findServicesByHost(int $hostId): array
    {
        return $this->monitoringRepository->findServicesByHostWithRequestParameters($hostId);
    }

    /**
     * @inheritDoc
     */
    public function findHosts(bool $withServices = false): array
    {
        $hosts = $this->monitoringRepository->findHosts();
        if ($withServices && !empty($hosts)) {
            $hosts = $this->completeHostsWithTheirServices($hosts);
        }
        return $hosts;
    }

    /**
     * @inheritDoc
     */
    public function findHostGroups(bool $withHosts = false, bool $withServices = false, int $hostId = null): array
    {
        // Find hosts groups only
        $hostGroups = $this->monitoringRepository->findHostGroups($hostId);

        if (!empty($hostGroups)) {
            $hostIds = [];
            if ($withHosts || $withServices) {
                // We will find hosts linked to hosts groups found
                $hostGroupIds = [];
                foreach ($hostGroups as $hostGroup) {
                    $hostGroupIds[] = $hostGroup->getId();
                }

                if (!empty($hostGroupIds)) {
                    $hostsByHostsGroups = $this->monitoringRepository->findHostsByHostsGroups($hostGroupIds);

                    foreach ($hostGroups as $hostGroup) {
                        if (array_key_exists($hostGroup->getId(), $hostsByHostsGroups)) {
                            $hostGroup->setHosts($hostsByHostsGroups[$hostGroup->getId()]);
                            // We keep the host ids if we must to retrieve their services
                            if ($withServices && !empty($hostGroup->getHosts())) {
                                foreach ($hostGroup->getHosts() as $host) {
                                    if (!in_array($host->getId(), $hostIds)) {
                                        $hostIds[] = $host->getId();
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($withServices) {
                // We will find services linked to hosts linked to host groups
                $servicesByHost = $this->monitoringRepository->findServicesByHosts($hostIds);
                foreach ($hostGroups as $hostGroup) {
                    foreach ($hostGroup->getHosts() as $host) {
                        if (array_key_exists($host->getId(), $servicesByHost)) {
                            $host->setServices($servicesByHost[$host->getId()]);
                        }
                    }
                }
            }
        }

        return $hostGroups;
    }

    /**
     * @inheritDoc
     */
    public function findOneHost(int $hostId): ?Host
    {
        $host = $this->monitoringRepository->findOneHost($hostId);

        if (!empty($host)) {
            $host = $this->completeHostsWithTheirServices([$host])[0];
        }
        return $host;
    }

    /**
     * @inheritDoc
     */
    public function findOneService(int $hostId, int $serviceId): ?Service
    {
        return $this->monitoringRepository->findOneService($hostId, $serviceId);
    }

    /**
     * @inheritDoc
     */
    public function findServiceGroups(bool $withHosts = false, bool $withServices = false): array
    {
        // Find hosts groups only
        $serviceGroups = $this->monitoringRepository->findServiceGroups();

        if (!empty($serviceGroups) && ($withHosts || $withServices)) {
            // We will find hosts linked to hosts groups found
            $serviceGroupIds = [];
            foreach ($serviceGroups as $serviceGroup) {
                $serviceGroupIds[] = $serviceGroup->getId();
            }

            $hostsByServicesGroups = $this->monitoringRepository->findHostsByServiceGroups($serviceGroupIds);

            foreach ($serviceGroups as $serviceGroup) {
                if (array_key_exists($serviceGroup->getId(), $hostsByServicesGroups)) {
                    $serviceGroup->setHosts($hostsByServicesGroups[$serviceGroup->getId()]);
                }
            }

            if ($withServices) {
                // We will find services linked to hosts linked to service groups
                $servicesByServiceGroup = $this->monitoringRepository->findServicesByServiceGroups($serviceGroupIds);

                // First, we will sort services by service groups and hosts
                $servicesByServiceGroupAndHost = [];
                /**
                 * @var Service[] $services
                 */
                foreach ($servicesByServiceGroup as $serviceGroupId => $services) {
                    foreach ($services as $service) {
                        $hostId = $service->getHost()->getId();
                        $servicesByServiceGroupAndHost[$serviceGroupId][$hostId][] = $service;
                    }
                }

                // Next, we will linked services to host
                /**
                 * @var ServiceGroup $serviceGroup
                 */
                foreach ($serviceGroups as $serviceGroup) {
                    foreach ($serviceGroup->getHosts() as $host) {
                        if (
                            array_key_exists($serviceGroup->getId(), $servicesByServiceGroupAndHost)
                            && array_key_exists($host->getId(), $servicesByServiceGroupAndHost[$serviceGroup->getId()])
                        ) {
                            $host->setServices(
                                $servicesByServiceGroupAndHost[$serviceGroup->getId()][$host->getId()]
                            );
                        }
                    }
                }
            }
        }

        return $serviceGroups;
    }

    /**
     * @inheritDoc
     */
    public function isHostExists(int $hostId): bool
    {
        return !is_null($this->findOneHost($hostId));
    }

    /**
     * @inheritDoc
     */
    public function isServiceExists(int $hostId, int $serviceId): bool
    {
        return !is_null($this->findOneService($hostId, $serviceId));
    }

    /**
     * @inheritDoc
     */
    public function findServiceGroupsByHostAndService(int $hostId, int $serviceId): array
    {
        return $this->monitoringRepository->findServiceGroupsByHostAndService($hostId, $serviceId);
    }

    /**
     * Completes hosts with their services.
     *
     * @param array $hosts Host list for which we want to complete with their services
     * @return array Returns the host list with their services
     * @throws \Exception
     */
    private function completeHostsWithTheirServices(array $hosts): array
    {
        $hostIds = [];
        foreach ($hosts as $host) {
            $hostIds[] = $host->getId();
        }
        $services = $this->monitoringRepository->findServicesByHosts($hostIds);

        foreach ($hosts as $host) {
            if (array_key_exists($host->getId(), $services)) {
                $host->setServices($services[$host->getId()]);
            }
        }
        return $hosts;
    }

    /**
     * @inheritDoc
     */
    public function findCommandLineOfService(int $hostId, int $serviceId): ?string
    {
        try {
            $service = $this->findOneService($hostId, $serviceId);
            if ($service === null) {
                throw new MonitoringServiceException('Service not found');
            }
            $this->hidePasswordInServiceCommandLine($service);
            return $service->getCommandLine();
        } catch (MonitoringServiceException $ex) {
            throw $ex;
        } catch (\Throwable $ex) {
            throw new MonitoringServiceException('Error when getting the command line');
        }
    }

    /**
     * @inheritDoc
     */
    public function hidePasswordInHostCommandLine(Host $monitoringHost, string $replacementValue = '***'): void
    {
        $monitoringCommand = $monitoringHost->getCheckCommand();
        if (empty($monitoringCommand)) {
            return;
        }
        if ($monitoringHost->getId() === null) {
            throw MonitoringServiceException::hostIdNotNull();
        }

        $configurationCommand = $this->hostConfiguration->findCommandLine($monitoringHost->getId());
        if (empty($configurationCommand)) {
            throw HostCommandException::notFound($monitoringHost->getId());
        }

        $hostMacros = $this->hostConfiguration->findHostMacrosFromCommandLine(
            $monitoringHost->getId(),
            $configurationCommand
        );

        $builtCommand = $this->buildCommandLineFromConfiguration(
            $configurationCommand,
            $monitoringCommand,
            $hostMacros,
            $replacementValue
        );

        if (!empty($builtCommand)) {
            $monitoringHost->setCheckCommand($builtCommand);
        }
    }

    /**
     * @inheritDoc
     */
    public function hidePasswordInServiceCommandLine(Service $monitoringService, string $replacementValue = '***'): void
    {
        $monitoringCommand = $monitoringService->getCommandLine();
        if (empty($monitoringCommand)) {
            return;
        }
        if ($monitoringService->getId() === null) {
            throw MonitoringServiceException::serviceIdNotNull();
        }
        if ($monitoringService->getHost() === null || $monitoringService->getHost()->getId() === null) {
            throw MonitoringServiceException::hostIdNotNull();
        }

        $configurationCommand = $this->serviceConfiguration->findCommandLine($monitoringService->getId());
        if (empty($configurationCommand)) {
            // If there is no command line defined in the configuration, it's useless to continue.
            $service = $this->serviceConfiguration->findService($monitoringService->getId());
            if ($service->getServiceType() === \Centreon\Domain\ServiceConfiguration\Service::TYPE_META_SERVICE) {
                // For META SERVICE we can define the configuration command line with the monitoring command line
                $monitoringService->setCommandLine($monitoringCommand);
                return;
            } else {
                // The service is not a META SERVICE
                throw ServiceCommandException::notFound($monitoringService->getId());
            }
        }

        $hostMacros = $this->hostConfiguration->findHostMacrosFromCommandLine(
            $monitoringService->getHost()->getId(),
            $configurationCommand
        );
        $serviceMacros = $this->serviceConfiguration->findServiceMacrosFromCommandLine(
            $monitoringService->getId(),
            $configurationCommand
        );

        /**
         * @var ServiceMacro[]|HostMacro[] $macros
         */
        $macros = array_merge($hostMacros, $serviceMacros);

        $builtCommand = $this->buildCommandLineFromConfiguration(
            $configurationCommand,
            $monitoringCommand,
            $macros,
            $replacementValue
        );

        if (!empty($builtCommand)) {
            $monitoringService->setCommandLine($builtCommand);
        }
    }

    /**
     * Build command line by comparing monitoring & configuration commands
     * and by replacing macros in configuration command
     *
     * @param string $configurationCommand
     * @param string $monitoringCommand
     * @param array $macros
     * @param string $replacementValue
     * @return string|null
     */
    private function buildCommandLineFromConfiguration(
        string $configurationCommand,
        string $monitoringCommand,
        array $macros,
        string $replacementValue
    ): ?string {
        $macroPasswordNames = [];
        foreach ($macros as $macro) {
            if ($macro->isPassword()) {
                $macroPasswordNames[] = $macro->getName();
            } else {
                $configurationCommand = str_replace($macro->getName(), $macro->getValue(), $configurationCommand);
            }
        }

        if (count($macroPasswordNames) === 0) {
            return null;
        }

        $foundMacroNames = [];
        if (preg_match_all('/(\$\S+?\$)/', $configurationCommand, $matches)) {
            if (isset($matches[0])) {
                $foundMacroNames = $matches[0];
            }
        }

        // build a regex to identify macro associated value
        // example :
        //  - configuration command : $USER1$/check_icmp -H $HOSTADDRESS$ $_HOSTPASSWORD$
        //  - generated regex : ^(.*)\/check_icmp \-H (.*) (.*)$
        //  - monitoring : /usr/lib64/nagios/plugins/check_icmp -H 127.0.0.1 hiddenPassword
        //  ==> matched values : [/usr/lib64/nagios/plugins/check_icmp, hiddenPassword]
        $commandSplittedByMacros = preg_split('/(\$\S+?\$)/', $configurationCommand);
        $macroRegex = '^';
        foreach ($commandSplittedByMacros as $index => $commandSection) {
            $macroMatcher = isset($foundMacroNames[$index]) ? '(.*)' : '';

            $macroRegex .= preg_quote($commandSection, '/') . $macroMatcher;
        }
        $macroRegex .= '$';

        // if two macros are glued, regex cannot detect properly password string
        if (str_contains($macroRegex, '(.*)(.*)')) {
            throw MonitoringServiceException::macroPasswordNotDetected();
        }

        if (preg_match('/' . $macroRegex . '/', $monitoringCommand, $foundMacroValues)) {
            array_shift($foundMacroValues); // remove global string matching

            foreach ($foundMacroNames as $index => $foundMacroName) {
                $foundMacroValue = $foundMacroValues[$index];
                $macroValue = in_array($foundMacroName, $macroPasswordNames) ? $replacementValue : $foundMacroValue;
                $configurationCommand = str_replace($foundMacroName, $macroValue, $configurationCommand);
            }
        } else {
            throw MonitoringServiceException::configurationhasChanged();
        }

        return $configurationCommand;
    }
}
