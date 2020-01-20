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

namespace CentreonAutoDiscovery\Infrastructure\Repository;

use CentreonAutoDiscovery\Domain\Entity\Provider;
use CentreonAutoDiscovery\Domain\Entity\ConnectionParameter as ConnectionParameterAlias;
use CentreonAutoDiscovery\Domain\Entity\Mapping;
use CentreonAutoDiscovery\Domain\Entity\Pagination;

interface HostDiscoveryRepositoryInterface
{
    /**
     * Add hosts list for a specific job.
     *
     * @param int $jobId Job id bind to the new hosts
     * @param array $hostsDetails Hosts details
     * [['host_name' => string, 'host_data' => string], 'mapping_id' => integer ...]
     */
    public function addHosts(int $jobId, array $hostsDetails): void;

    /**
     * Add a job.
     *
     * @param string $providerName provider name
     * @param int $connectionParametersId Id of connection parameters
     * @return int Return the new job id.
     */
    public function addJob(string $providerName, int $connectionParametersId): int;

    /**
     * Add or update credentials
     *
     * @param string $providerName Provider name
     * @param string $connectionName Name of the connection parameters
     * @param array $connectionParameters Connection parameters
     * @return int Return the existing credential id of the newly created otherwise NULL
     * @throws \Exception
     */
    public function addOrUpdateConnectionParameters(
        string $providerName,
        string $connectionName,
        array $connectionParameters
    ): ?int;

    /**
     * Retrieve the installed version number of the auto-discovery module
     *
     * @return string|null
     */
    public function getModuleVersionInstalled(): ?string;

    /**
     * Retrieve the list of all scheduled jobs.
     *
     * @return array [['id' => int, 'provider' => string, 'command' => string], ...]
     */
    public function getScheduledJobs(): array;

    /**
     * Retrieve the list of connection parameters for a specific group id.
     *
     * @param int $connectionParametersId Id of the connection parameters
     * @return ConnectionParameterAlias[]
     */
    public function getConnectionParameters(int $connectionParametersId): array;

    /**
     * Retrieve the list of connection parameters for a specific job.
     *
     * @param int $jobId Job id
     * @return ConnectionParameterAlias[]
     */
    public function getConnectionParametersByJob(int $jobId): array;

    /**
     * Retrieve list of connection id and name by provider type.
     *
     * @param string $providerType provider type
     * @return array
     */
    public function getConnectionParametersByProviderType(string $providerType): array;

    /**
     * Retrieve the template of connection parameters for the provider given.
     *
     * @param string $providerName Name of the provider
     * @return array Return the template of connection parameters
     */
    public function getConnectionTemplateByProvider(string $providerName): array;

    /**
     * Find default host template name from the provider id
     *
     * @param integer $providerId The id of the provider
     * @return string|null
     */
    public function findDefaultTemplateFromProviderId(int $providerId): ?string;

    /**
     * Retrieve a host by its id
     *
     * @param int $hostId Host id
     * @return array
     */
    public function getHost(int $hostId): ?array;

    /**
     * Retrieve the list of the job based on the job id given.
     *
     * @param int $jobId Job id
     * @param Pagination|null $pagination Paging system
     * @return array [['id' => int, 'name' => string, 'data' => string], ...]
     */
    public function getHostsByJob(int $jobId, Pagination $pagination = null): array;

    /**
     * Retrieve the list of jobs.
     *
     * @param Pagination $pagination Pagination for this request
     * @return array [[
     *  'id' => ...,
     *  'alias' => ...,
     *  'author' => ...,
     *  'generate_date' => ...,
     *  'status' => ...,
     *  'duration' => ...,
     *  'discovered_items => ...,
     *  'connection_name' => ...], ...
     * ]
     */
    public function getJobs(Pagination $pagination): array;

    /**
     * Get mappings based on the job id.
     *
     * @param int $jobId Id of the job
     * @return Mapping[]
     */
    public function getMappingsByJob(int $jobId): array;

    /**
     * Update a job.
     *
     * @param array $jobDetails [['duration' => int, 'total' => int, 'id' => int], ...]
     * @return bool Returns TRUE if successful otherwise FALSE
     */
    public function updateJob(array $jobDetails): bool;

    /**
     * Retrieve the details for a job.
     *
     * @param int $jobId Job id
     * @return array
     * @throws \RestInternalServerErrorException
     */
    public function getJobDetails(int $jobId): array;

    /**
     * reschedule list of jobs (set status to '0').
     *
     * @param array $jobIds list of job ids to update
     * @return void
     */
    public function rescheduleJobs(array $jobIds): void;

    /**
     * Get the providers list.
     *
     * @return array [['id' => string, 'label' => string], [...]]
     */
    public function getProviders(): array;

    /**
     * Retrieve the Centreon's default poller id
     *
     * @return int Id of the default poller if found otherwise NULL
     * @todo change repository later
     */
    public function getDefaultPoller(): ?int;

    /**
     * Retrieve the plugin path based on the resource
     *
     * @param string $resourceName Resource name
     * @return string Return the plugin path
     * @todo change repository later
     */
    public function getPluginPath(string $resourceName): string;

    /**
     * Indicates if the user has right to use the api
     *
     * @param \CentreonUser $user Centreon user
     * @return bool Return TRUE if the user has right to use the api otherwise FALSE
     */
    public function hasRightToUseApi(\CentreonUser $user): bool;

    /**
     * Retrieve provider id from provider name
     *
     * @param string $name Provider name
     * @return integer|null return provider id if found
     */
    public function findProviderIdByName(string $name): ?int;

    /**
     * Retrieve provider mapping id from mapping name
     *
     * @param string $name Mapping name
     * @return integer|null return mapping id if found
     */
    public function findProviderMappingIdByName(string $name): ?int;

    /**
     * Retrieve provider type from provider name
     *
     * @param string $name Provider name
     * @return string|null
     */
    public function findProviderTypeByProviderName(string $name): ?string;

    /**
     * Retrieve provider information from provider name
     *
     * @param integer $name Provider name
     * @return integer|null return provider information if found
     */
    public function findProviderByName(string $name): ?Provider;

    /**
     * Retrieve provider name from job id
     *
     * @param int $jobId Filtered job id
     * @return string|null Provider name if found
     */
    public function findProviderNameByJobId(int $jobId): ?string;

    /**
     * Retrieve custom host template from host template name
     * if "-custom" is not found, return the locked host template
     *
     * @param string $name Host template name
     * @return array|null ['id' => integer, 'name' => string]
     */
    public function findCustomTemplateFromTemplateName(string $name): ?array;

    /**
     * Retrieve macros from provider id
     *
     * @param int $id Provider id
     * @return array return list of macros
     */
    public function findMacrosByJobId(int $jobId): string;

    /**
     * Retrieve provider type from provider name
     *
     * @param string $name Provider name
     * @return string|null Provider id if found
     */
    public function findProviderTypeIdByName(string $name): ?string;
}
