<?php
/*
 * Centreon
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more informations : contact@centreon.com
 *
 */

namespace CentreonAutoDiscovery\Domain\Service;

use CentreonAutoDiscovery\Infrastructure\Repository\HostDiscoveryRepositoryInterface;
use CentreonAutoDiscovery\Domain\Entity\Security;

class HostDiscoveryService
{
    /**
     * @var $hostDiscoveryRepository HostDiscoveryRepositoryInterface
     */
    protected $hostDiscoveryRepository;

    /**
     * @var $security Security
     */
    private $security;

    /**
     * @var $centreonHost \CentreonHost
     */
    private $centreonHost;

    /**
     * HostDiscoveryService constructor.
     * @param HostDiscoveryRepositoryInterface $repository
     * @param \CentreonHost $centreonHost
     */
    public function __construct(HostDiscoveryRepositoryInterface $repository, \CentreonHost $centreonHost)
    {
        $this->hostDiscoveryRepository = $repository;
        $this->centreonHost = $centreonHost;
    }

    /**
     * format the discovery command with credentials
     *
     * @param string $command
     * @param array $credentials
     * @return string
     */
    public function formatCommand(string $command, array $credentials): string
    {
        foreach ($credentials as $credential) {
            if (($credential->getType() === 'password') && !empty($credential->getValue())) {
                $value = $this->security->decrypt($credential->getValue());
            } else {
                $value = $credential->getValue();
            }

            $command = str_replace(
                '$_' . strtoupper($credential->getName()) . '$',
                $value,
                $command
            );
        }
        return $command;
    }

    /**
     * Get mappings based on the job id.
     *
     * @param int $jobId Id of the job
     * @return Mapping[]
     */
    public function getMappingsByJob(int $jobId): array
    {
        return $this->hostDiscoveryRepository->getMappingsByJob($jobId);
    }

    /**
     * Get the version of the module auto-discovery who gets installed.
     *
     * @return string|null
     */
    public function getModuleVersionInstalled(): ?string
    {
        return $this->hostDiscoveryRepository->getModuleVersionInstalled();
    }

    /**
     * Delete illegal char define by poller on the host name
     *
     * @param string $hostName
     * @param int|null $pollerId
     * @return string
     * @throws \Exception
     */
    public function checkIllegalChar(string $hostName, int $pollerId = null): string
    {
        return $this->centreonHost->checkIllegalChar($hostName, $pollerId);
    }

    /**
     * Retrieve the Centreon's default poller id
     *
     * @return int Id of the default poller if found otherwise NULL
     */
    public function getDefaultPoller(): ?int
    {
        return $this->hostDiscoveryRepository->getDefaultPoller();
    }

    /**
     * Update a job.
     *
     * @param array $jobDetails [['duration' => int, 'total' => int, 'id' => int], ...]
     * @return bool Returns TRUE if successful otherwise FALSE
     */
    public function updateJob(array $jobDetails)
    {
        $this->hostDiscoveryRepository->updateJob($jobDetails);
    }

    /**
     * Retrieve the list of all scheduled jobs.
     *
     * @return array [['id' => int, 'provider' => string, 'command' => string], ...]
     */
    public function getScheduledJobs(): array
    {
        return $this->hostDiscoveryRepository->getScheduledJobs();
    }

    /**
     * Add hosts list for a specific job.
     *
     * @param int $jobId Job id bind to the new hosts
     * @param array $hostsDetails Hosts details
     * [['host_name' => string, 'host_data' => string],  'mapping_id' => integer, ...]
     */
    public function addHosts(int $jobId, array $hostsDetails): void
    {
        $this->hostDiscoveryRepository->addHosts($jobId, $hostsDetails);
    }

    /**
     * Retrieve the list of connection parameters for a specific job.
     *
     * @param int $jobId Job id
     * @return ConnectionParameter[]
     */
    public function getConnectionParametersByJob(int $jobId): array
    {
        return $this->hostDiscoveryRepository->getConnectionParametersByJob($jobId);
    }

    /**
     * @param string $resourceName
     * @return string
     */
    public function getPluginPath(string $resourceName): string
    {
        return $this->hostDiscoveryRepository->getPluginPath($resourceName);
    }

    /**
     * Sets the security class and sets the second secure key immediately after.
     *
     * @param Security $security
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
        $this->security->setSecondKey(
            'Vpk6Ap5d36L0MjoWtGn8i1Kk4a9JEjRxboFHzDuHyjJwJ69FKfvCycwXNVYOfZf2cXtj+L9Gyl9DVodu35afBA=='
        );
    }

    /**
     * Parse attributes of the discovery mappings.
     *
     * @param Mapping[] $mappings List of mappings used to retrieve data into the discovery result
     * @param array $hostData Result of the discovery for one host
     * @return array [['host_name' => string, 'host_alias' => string, 'host_address' => string],...]
     */
    public function parseAttributes(array $mappings, array $hostData): array
    {
        $finalHost = [];

        foreach ($mappings as $mapping) {
            $filters = json_decode($mapping->getFilters(), true);
            foreach ($filters as $name => $value) {
                if (!isset($hostData[$name]) || $hostData[$name] != $value) {
                    continue 2;
                }
            }
            $finalHost['mapping_id'] = $mapping->getId();

            $attributes = json_decode($mapping->getAttributes(), true);
            $association = json_decode($mapping->getAssociation(), true);

            foreach ($association as $name => $values) {

                // try to parse value
                // if one of the attributes is empty, try the next value
                foreach ($values as $value) {
                    if (preg_match_all('/\$\{(.+?)\}/', $value, $matches)) {
                        foreach ($matches[1] as $match) {
                            if (empty($hostData[$match])) {
                                continue 2;
                            }

                            // if attribute is an array, get the first element of it
                            if (isset($attributes[$match]) && $attributes[$match]['type'] === 'array') {
                                $attributeValue = $hostData[$match][0];
                            } else {
                                $attributeValue = $hostData[$match];
                            }

                            $value = str_replace('${' . $match . '}', $attributeValue, $value);
                        }
                    }

                    switch ($name) {
                        case 'hostname':
                            $finalHost['host_name'] = $value;
                            break;
                        case 'description':
                            $finalHost['host_alias'] = $value;
                            break;
                        case 'ip':
                            $finalHost['host_address'] = $value;
                            break;
                    }
                    break;
                }
            }
        }

        return $finalHost;
    }

    /**
     * Get links between mappings and custom templates (or base template if custom is not found)
     *
     * @param Mapping[] $mappings List of mappings used to retrieve linked templates
     * @return array ['mapping_id_1' => ['id' => integer, 'name' => string], ...]
     */
    public function getCustomTemplatesFromMappings(array $mappings): array
    {
        $customTemplateMappings = [];

        foreach ($mappings as $mapping) {
            $customTemplateMappings[$mapping->getId()] = [];
            $templates = json_decode($mapping->getTemplates(), true);
            foreach ($templates as $hostTemplateName) {
                $customTemplate = $this->hostDiscoveryRepository->findCustomTemplateFromTemplateName(
                    $hostTemplateName
                );
                if ($customTemplate) {
                    $customTemplateMappings[$mapping->getId()][] = $customTemplate;
                }
            }
        }

        return $customTemplateMappings;
    }
}
