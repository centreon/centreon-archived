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
use CentreonAutoDiscovery\Domain\Entity\ConnectionParameter;
use CentreonAutoDiscovery\Domain\Entity\Mapping;
use CentreonAutoDiscovery\Domain\Entity\Pagination;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class HostDiscoveryRepository extends ServiceEntityRepository implements HostDiscoveryRepositoryInterface
{
    private const DISCOVERY_TOPOLOGY_PAGE = 60130;

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function addJob(string $providerName, int $connectionParametersId): int
    {
        $provider = $this->findProviderByName($providerName);
        if ($provider === null) {
            return null;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO `mod_host_disco_job` (`alias`, `credential_id`, `provider_id`)
            VALUES (:alias, :credential_id, :provider_id)'
        );

        $stmt->bindValue(':alias', $providerName, \PDO::PARAM_STR);
        $stmt->bindValue(':credential_id', $connectionParametersId, \PDO::PARAM_INT);
        $stmt->bindValue(':provider_id', $provider->getId(), \PDO::PARAM_INT);
        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function addOrUpdateConnectionParameters(
        string $providerName,
        string $connectionName,
        array $connectionParameters
    ): ?int {
        $providerType = $this->findProviderTypeByProviderName($providerName);
        if ($providerType === null) {
            return null;
        }

        $bindValues = [];

        // We check if the credential name has already been recorded
        $stmt = $this->db->prepare(
            'SELECT cred.id
            FROM mod_host_disco_credential cred, mod_host_disco_provider_type type
            WHERE cred.type_id = type.id
            AND cred.name = :connection_name
            AND type.name = :provider_type'
        );

        $stmt->bindValue(':connection_name', $connectionName, \PDO::PARAM_STR);
        $stmt->bindValue(':provider_type', $providerType, \PDO::PARAM_STR);

        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $credentialId = (int)$row['id'];
        } else {
            $credentialId = $this->createEmptyConnectionParameters(
                $connectionName,
                $providerName
            );
            if (is_null($credentialId)) {
                throw new \Exception("Cannot insert connnection parameters");
            }
        }

        if (empty($connectionParameters)) {
            return $credentialId;
        }

        $bindValues = [];
        $sqlCredential = '';
        $bindingsIndex = 1;
        foreach ($connectionParameters as $credential => $value) {
            $sqlCredential .= ' WHEN :name' . $bindingsIndex . ' THEN :value' . $bindingsIndex;
            $bindValues[':name' . $bindingsIndex] = strtoupper($credential);
            $bindValues[':value' . $bindingsIndex] = $value;
            $bindingsIndex++;
        }

        $stmt = $this->db->prepare(
            "UPDATE mod_host_disco_credential_parameter
            SET `value` =
             CASE `name`
               {$sqlCredential}
               ELSE `value`
             END
            WHERE `credential_id` = :credential_id"
        );

        $stmt->bindValue(':credential_id', $credentialId, \PDO::PARAM_INT);
        foreach ($bindValues as $key => $value) {
            $stmt->bindValue($key, $value, \PDO::PARAM_STR);
        }
        $stmt->execute();
        return $credentialId;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getModuleVersionInstalled(): ?string
    {
        $stmt = $this->db->query(
            "SELECT `mod_release` FROM `modules_informations` WHERE `name` = 'centreon-autodiscovery-server'"
        );
        $result = $stmt->fetch();
        return ($result !== false) ? $result['mod_release'] : null;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getScheduledJobs(): array
    {
        $stmt = $this->db->query(
            'SELECT
              job.id,
              provider.name AS provider,
              command.command_line AS command
            FROM mod_host_disco_job job
            INNER JOIN mod_host_disco_provider provider
              ON provider.id = job.provider_id
            INNER JOIN command
              ON command.command_id = provider.command_id
            WHERE job.status = "0"'
        );

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result !== false
            ? $result
            : [];
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getMappingsByJob(int $jobId): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT
              mapping.id,
              mapping.name,
              mapping.object,
              mapping.filters,
              mapping.attributes,
              mapping.association,
              mapping.templates,
              mapping.macros
            FROM mod_host_disco_provider_mapping mapping
            INNER JOIN mod_host_disco_job job
              ON mapping.provider_id = job.provider_id
              AND job.id = :job_id"
        );

        $stmt->bindValue(':job_id', $jobId, \PDO::PARAM_INT);
        $stmt->execute();

        $entities = [];
        foreach ($stmt->fetchAll() as $row) {
            $entities[] = (new Mapping())
                ->setId((int)$row['id'])
                ->setName($row['name'])
                ->setObject($row['object'])
                ->setFilters($row['filters'])
                ->setAttributes($row['attributes'])
                ->setAssociation($row['association'])
                ->settemplates($row['templates'])
                ->setmacros($row['macros']);
        }

        return $entities;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getConnectionParametersByJob(int $jobId): array
    {
        $lockedParameters = $this->getLockedConnectionParametersByJobId($jobId);

        $stmt = $this->db->prepare(
            "SELECT
              parameter.id,
              parameter.name,
              parameter.value,
              parameter.description,
              parameter.type,
              CASE
                WHEN parameter.mandatory = 1
                  THEN TRUE
                  ELSE FALSE
                END AS mandatory,
            CASE
              WHEN parameter.locked = 1
                THEN TRUE
                ELSE FALSE
              END AS locked,
            CASE
              WHEN parameter.hidden = 1
                THEN TRUE
                ELSE FALSE
              END AS hidden
            FROM mod_host_disco_credential_parameter parameter
            INNER JOIN mod_host_disco_credential cred
              ON cred.id = parameter.credential_id
            INNER JOIN mod_host_disco_job job
              ON job.credential_id = cred.id
            WHERE job.id = :job_id"
        );
        $stmt->bindValue(':job_id', $jobId, \PDO::PARAM_INT);
        $stmt->execute();
        $credentials = [];

        foreach ($stmt->fetchAll() as $recordSet) {
            foreach ($lockedParameters as $lockedParameter) {
                // if parameter is locked, get value from template
                if ($lockedParameter->getName() === $recordSet['name']) {
                    $credentials[] = $lockedParameter;
                    continue;
                }
            }

            $credentials[] = (new ConnectionParameter())
                ->setId((int)$recordSet['id'])
                ->setName($recordSet['name'])
                ->setValue($recordSet['value'])
                ->setDescription($recordSet['description'])
                ->setType($recordSet['type'])
                ->setLocked($recordSet['locked'])
                ->setHidden($recordSet['hidden'])
                ->setMandatory($recordSet['mandatory']);
        }

        return $credentials;
    }

    /**
     * Get locked parameters from template filtered by job id
     *
     * @param int $jobId the filtered job id
     * @return array the list of locked parameters
     */
    private function getLockedConnectionParametersByJobId(int $jobId): array
    {
        $parameters = [];

        $providerName = $this->findProviderNameByJobId($jobId);
        $template = $this->getConnectionTemplateByProvider($providerName);
        foreach ($template as $parameter) {
            if ($parameter->isLocked() === true) {
                $parameters[] = $parameter;
            }
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getConnectionParameters(int $connectionParametersId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
              id,
              name,
              value,
              description,
              type,
            CASE
              WHEN mandatory = 1
                THEN TRUE
                ELSE FALSE
              END AS mandatory,
            CASE
              WHEN locked = 1
                THEN TRUE
                ELSE FALSE
              END AS locked,
            CASE
              WHEN hidden = 1
                THEN TRUE
                ELSE FALSE
              END AS hidden
            FROM mod_host_disco_credential_parameter
            WHERE credential_id = :credential_id"
        );
        $stmt->bindValue(':credential_id', $connectionParametersId, \PDO::PARAM_INT);
        $stmt->execute();
        $credentials = [];

        foreach ($stmt->fetchAll() as $recorset) {
            $credentials[] = (new ConnectionParameter())
                ->setId($recorset['id'])
                ->setName($recorset['name'])
                ->setValue($recorset['value'])
                ->setDescription($recorset['description'])
                ->setType($recorset['type'])
                ->setLocked($recorset['locked'])
                ->setHidden($recorset['hidden'])
                ->setMandatory($recorset['mandatory']);
        }

        return $credentials;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getConnectionParametersByProviderType(string $providerType): array
    {
        $stmt = $this->db->prepare(
            "SELECT
              cred.name AS name,
              cred.id AS id
            FROM mod_host_disco_credential cred
            INNER JOIN mod_host_disco_provider_type type
              ON cred.type_id = type.id
              AND type.name = :provider_type"
        );
        $stmt->bindValue(':provider_type', $providerType, \PDO::PARAM_STR);
        $stmt->execute();
        $credentials = [];

        foreach ($stmt->fetchAll() as $recorset) {
            $credentials[] = $recorset;
        }

        return $credentials;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function findDefaultTemplateFromProviderId(int $providerId): ?string
    {
        $defaultTemplate = null;

        $stmt = $this->db->prepare(
            'SELECT default_template '
            . 'FROM mod_host_disco_provider '
            . 'WHERE id = :provider_id'
        );
        $stmt->bindValue(':provider_id', $providerId, \PDO::PARAM_INT);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $defaultTemplate = $row['default_template'];
        }

        return $defaultTemplate;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getHostsByJob(int $jobId, Pagination $pagination = null): array
    {
        $request =
            "SELECT SQL_CALC_FOUND_ROWS
              job_host.id AS 'id',
              job_host.name AS 'name',
              job_host.data AS 'data',
              job_host.mapping_id AS 'mapping_id',
              (SELECT COUNT(job_host2.name)
                FROM mod_host_disco_host AS job_host2
                WHERE job_host2.job_id = :job_id
                AND job_host2.name=job_host.name
              ) as count,
              CASE
                WHEN host.host_id IS NOT NULL
                THEN TRUE
                ELSE FALSE
              END AS 'exist'
            FROM mod_host_disco_host job_host
            LEFT JOIN host
            ON host.host_name = replace(replace(job_host.name, '(', ''), ')', '')
            AND host.host_register = '1'";
        // replace(replace(job_host.name, '(', ''), ')', '') remove parenthesis to find in a better way existing hosts
        // @todo construct replace from illegal characters set in centreon engine configuration

        $bindValues = [];
        if (!is_null($pagination)) {
            $pagination->setConcordance([
                'hosts.id' => 'job_host.id',
                'hosts.name' => 'job_host.name',
                'id' => 'job_host.job_id'
            ]);

            $existingHosts = $pagination->extractParameter('existingHosts');
            $whereQuery = $pagination->createQuery();
            $orderQuery = $pagination->createOrder();
            $limitQuery = $pagination->createPagination();
            $bindValues = $pagination->getBindValues();

            // filter on hosts which do not exist in configuration
            if ($existingHosts === false) {
                $whereQuery .= " AND host.host_id IS NULL";
            }

            $request .= $whereQuery
                . $orderQuery
                . $limitQuery;
        } else {
            $request .= ' WHERE job_id = :job_id';
        }

        $bindValues[':job_id'] = [
            \PDO::PARAM_INT => $jobId
        ];

        $stmt = $this->db->prepare($request);
        foreach ($bindValues as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();
        if (!is_null($pagination)) {
            $pagination->setTotal($this->db->numberRows());
        }

        return $stmt->fetchAll();
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function updateJob(array $jobDetails): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE `mod_host_disco_job`
            SET `status` =  :status,
                `duration` = :duration,
                `discovered_items` = :total,
                `message` = :message
            WHERE `id` = :id'
        );
        $stmt->bindValue(':duration', (int)$jobDetails['duration'], \PDO::PARAM_INT);
        $stmt->bindValue(':total', (int)$jobDetails['total'], \PDO::PARAM_INT);
        $stmt->bindValue(':status', (string)$jobDetails['status'], \PDO::PARAM_STR);
        $stmt->bindValue(':message', $jobDetails['message'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':id', (int)$jobDetails['id'], \PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function getJobs(Pagination $pagination): array
    {
        // Relationship table to hide the real column name
        $pagination->setConcordance([
            'id' => 'job.id',
            'alias' => 'alias',
            'author' => 'author',
            'generate_date' => 'generate_date',
            'status' => 'status',
            'duration' => 'duration',
            'discovered_items' => 'discovered_items',
            'connection_name' => 'credential.name'
        ]);

        $query =
            'SELECT SQL_CALC_FOUND_ROWS
              job.id,
              `alias`,
              `generate_date`,
              `status`,
              `duration`,
              `discovered_items`,
              `message`,
              credential.name AS connection_name
            FROM mod_host_disco_job job
            INNER JOIN mod_host_disco_credential credential
              ON credential.id = job.credential_id';

        $query .= $pagination->createQuery()
            . $pagination->createOrder()
            . $pagination->createPagination();

        try {
            $statement = $this->db->prepare($query);

            foreach ($pagination->getBindValues() as $key => $data) {
                $type = key($data);
                $value = $data[$type];
                $statement->bindValue($key, $value, $type);
            }

            $statement->execute();
            $pagination->setTotal($this->db->numberRows());
            $result = $statement->fetchAll();
            foreach ($result as $index => $dataset) {
                foreach ($dataset as $key => $value) {
                    if (in_array($key, ['id', 'duration', 'discovered_items'])) {
                        $result[$index][$key] = (int)$value;
                    }
                }
            }
            return $result;
        } catch (\PDOException $ex) {
            throw new \Exception('Cannot get jobs from database');
        }
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getHost(int $hostId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
            FROM mod_host_disco_host
            WHERE id = :host_id"
        );
        $stmt->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return ($result !== false) ? $result : null;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function addHosts(int $jobId, array $hostsDetails): void
    {
        $nbHostByQuery = 0;
        $totalHost = count($hostsDetails);
        $queryValues = [];
        $query = $queryBegin = '
            INSERT INTO mod_host_disco_host
            (job_id, mapping_id, name, data) VALUES ';

        foreach ($hostsDetails as $host) {
            $query .= '(:job_id, :mapping_id' . $nbHostByQuery . ', '
                . ':host_name' . $nbHostByQuery . ', :data' . $nbHostByQuery . '),';
            $queryValues[':mapping_id' . $nbHostByQuery] = $host['mapping_id'];
            $queryValues[':host_name' . $nbHostByQuery] = $host['host_name'];
            $queryValues[':data' . $nbHostByQuery] = $host['host_data'];
            $nbHostByQuery++;
            $totalHost--;
            //insert host 100 by 100
            if (($nbHostByQuery === 100) || ($totalHost === 0)) {
                $query = rtrim($query, ',');
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':job_id', $jobId);
                foreach ($queryValues as $key => $value) {
                    $stmt->bindValue($key, $value, \PDO::PARAM_STR);
                }
                $stmt->execute();
                $query = $queryBegin;
                $queryValues = [];
                $nbHostByQuery = 0;
            }
        }
    }

    /**
     * {@inheritdoc}
     * @throws \RestInternalServerErrorException
     */
    public function getJobDetails(int $jobId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT
                  id,
                  alias,
                  generate_date,
                  status,
                  duration,
                  provider_id
                FROM mod_host_disco_job
                WHERE id = :id"
            );

            $stmt->bindValue(':id', $jobId, \PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll();
            $jobDetails = [];
            if (!empty($results)) {
                foreach ($results[0] as $key => $value) {
                    if (in_array($key, ['id', 'status', 'duration', 'provider_id'])) {
                        $value = (int)$value;
                    }
                    $jobDetails[$key] = $value;
                }
            }
            return $jobDetails;
        } catch (\PDOException $ex) {
            throw new \RestInternalServerErrorException('Cannot get job details from database');
        }
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function rescheduleJobs(array $jobIds): void
    {
        $bindParams = [];
        foreach ($jobIds as $index => $jobId) {
            $bindParams[':id_' . $index] = $jobId;
        }

        $stmt = $this->db->prepare(
            'UPDATE mod_host_disco_job '
            . 'SET status = "0", duration = 0, discovered_items = 0 '
            . 'WHERE id IN (' . implode(',', array_keys($bindParams)) . ')'
        );
        foreach ($bindParams as $param => $value) {
            $stmt->bindValue($param, $value, \PDO::PARAM_INT);
        }
        $stmt->execute();

        // clear discovered items
        $stmt = $this->db->prepare(
            'DELETE FROM mod_host_disco_host '
            . 'WHERE job_id IN (' . implode(',', array_keys($bindParams)) . ')'
        );
        foreach ($bindParams as $param => $value) {
            $stmt->bindValue($param, $value, \PDO::PARAM_INT);
        }
        $stmt->execute();
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getProviders(): array
    {
        $stmt = $this->db->prepare(
            'SELECT provider.name as id, provider.name as label, pp.version as version '
            . 'FROM mod_host_disco_provider provider '
            . 'INNER JOIN mod_ppm_pluginpack pp ON pp.pluginpack_id = provider.pluginpack_id'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getConnectionTemplateByProvider(string $providerName): array
    {
        $template = [];

        $stmt = $this->db->prepare(
            "SELECT parameters
            FROM mod_host_disco_provider
            WHERE name = :name"
        );
        $stmt->bindParam(':name', $providerName, \PDO::PARAM_STR);
        $stmt->execute();

        if ($parameters = $stmt->fetch()['parameters']) {
            $parameters = json_decode($parameters, true);
            foreach ($parameters as $parameter) {
                $template[] = (new ConnectionParameter())
                    ->setName($parameter['name'])
                    ->setValue($parameter['value'])
                    ->setDescription($parameter['description'])
                    ->setType($parameter['type'])
                    ->setLocked((bool)$parameter['locked'])
                    ->setHidden((bool)$parameter['hidden'])
                    ->setMandatory((bool)$parameter['mandatory']);
            }
        }

        return $template;
    }

    /**
     * Create empty connection parameters by using the template of connection
     * parameters of the provider.
     *
     * @param string $connectionName Connection name
     * @param string $providerName Provider name
     * @return null|int If successful return the new id of the connection
     * parameters list newly created otherwise NULL.
     */
    private function createEmptyConnectionParameters(string $connectionName, string $providerName): ?int
    {
        try {
            $this->db->beginTransaction();

            $providerType = $this->findProviderTypeByProviderName($providerName);
            $typeId = $this->findProviderTypeIdByName($providerType);

            $addStmt = $this->db->prepare(
                "INSERT INTO mod_host_disco_credential
                (name, type_id)
                VALUES (:credential_name, :type_id)"
            );
            $addStmt->bindParam(':credential_name', $connectionName, \PDO::PARAM_STR);
            $addStmt->bindParam(':type_id', $typeId, \PDO::PARAM_INT);
            $addStmt->execute();
            $newCredentialId = (int)$this->db->lastInsertId();

            $credentialTemplate = $this->getConnectionTemplateByProvider($providerName);

            if (is_null($credentialTemplate)) {
                throw new \Exception("Cannot get connection parameters of $providerName");
            }

            $bindValues = [':cred_id' => [\PDO::PARAM_INT => $newCredentialId]];
            $credentialEntitiesValues = '';
            foreach ($credentialTemplate as $index => $template) {
                $credentialEntitiesValues .= !empty($credentialEntitiesValues)
                    ? ','
                    : '';

                $credentialEntitiesValues .= "(
                    :name_$index,
                    :value_$index,
                    :description_$index,
                    :mandatory_$index,
                    :locked_$index,
                    :type_$index,
                    :cred_id)";
                $bindValues[':name_' . $index] = [\PDO::PARAM_STR => $template->getName()];
                $bindValues[':value_' . $index] = [\PDO::PARAM_STR => $template->getValue()];
                $bindValues[':description_' . $index] = [\PDO::PARAM_STR => $template->getDescription()];
                $bindValues[':mandatory_' . $index] = [\PDO::PARAM_BOOL => $template->isMandatory()];
                $bindValues[':locked_' . $index] = [\PDO::PARAM_BOOL => $template->isLocked()];
                $bindValues[':type_' . $index] = [\PDO::PARAM_STR => $template->getType()];
            }

            $addStmt = $this->db->prepare(
                "INSERT INTO mod_host_disco_credential_parameter
                (name, value, description, mandatory, locked, type, credential_id)
                 VALUES {$credentialEntitiesValues}"
            );
            foreach ($bindValues as $key => $data) {
                $type = key($data);
                $value = $data[$type];
                $addStmt->bindValue($key, $value, $type);
            }
            $addStmt->execute();

            $this->db->commit();

            return $newCredentialId;
        } catch (\Exception $ex) {
            $this->db->rollBack();
        }
        return null;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getDefaultPoller(): ?int
    {
        $stmt = $this->db->query(
            "SELECT `id` FROM `nagios_server` WHERE `is_default` = 1"
        );
        if (($result = $stmt->fetch()) !== false) {
            return (int)$result['id'];
        }
        $stmt = $this->db->query(
            "SELECT `id` FROM `nagios_server` WHERE `localhost` = '1'"
        );
        if (($result = $stmt->fetch()) !== false) {
            return (int)$result['id'];
        }
        return null;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function getPluginPath(string $resourceName): string
    {
        $stmt = $this->db->prepare(
            "SELECT resource_line
            FROM cfg_resource
            WHERE resource_name = :resourceName
            LIMIT 1"
        );
        $stmt->bindValue(':resourceName', $resourceName, \PDO::PARAM_STR);
        $stmt->execute();
        if (($row = $stmt->fetch())) {
            return $row["resource_line"];
        }
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function hasRightToUseApi(\CentreonUser $user): bool
    {
        $stmt = $this->db->prepare(
            "SELECT
                CASE
                    WHEN COUNT(*) = 1
                        THEN TRUE
                        ELSE FALSE
                END is_allowed
            FROM topology topo
            INNER JOIN acl_topology_relations atr
              ON atr.topology_topology_id = topo.topology_id
            INNER JOIN acl_topology ato
              ON ato.acl_topo_id = atr.acl_topo_id
            INNER JOIN acl_group_topology_relations agtr
              ON agtr.acl_topology_id = ato.acl_topo_id
            INNER JOIN acl_groups ag
              ON ag.acl_group_id = agtr.acl_group_id
            LEFT JOIN acl_group_contactgroups_relations agcgr
              ON agcgr.acl_group_id = ag.acl_group_id
            LEFT JOIN contactgroup cg
              ON cg.cg_id = agcgr.cg_cg_id
            LEFT JOIN contactgroup_contact_relation ccr
              ON ccr.contactgroup_cg_id = cg.cg_id
            LEFT JOIN acl_group_contacts_relations agcr
              ON agcr.acl_group_id = ag.acl_group_id
            WHERE topo.topology_page = :topology_page
              AND (
                ccr.contact_contact_id = :contact_id
                OR agcr.contact_contact_id = :contact_id
                )"
        );
        $stmt->bindValue(':contact_id', (int)$user->get_id(), \PDO::PARAM_INT);
        $stmt->bindValue(':topology_page', self::DISCOVERY_TOPOLOGY_PAGE, \PDO::PARAM_INT);
        $stmt->execute();

        return (bool)$stmt->fetch(\PDO::FETCH_ASSOC)['is_allowed'];
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function findProviderIdByName(string $name): ?int
    {
        $id = null;

        $stmt = $this->db->prepare(
            'SELECT id '
            . 'FROM mod_host_disco_provider '
            . 'WHERE name = :name'
        );
        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $id = (int)$row['id'];
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function findProviderMappingIdByName(string $name): ?int
    {
        $id = null;

        $stmt = $this->db->prepare(
            'SELECT id '
            . 'FROM mod_host_disco_provider_mapping '
            . 'WHERE name = :name'
        );
        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $id = (int) $row['id'];
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function findProviderTypeIdByName(string $name): ?string
    {
        $typeId = null;

        $stmt = $this->db->prepare(
            'SELECT id '
            . 'FROM mod_host_disco_provider_type '
            . 'WHERE name = :name'
        );
        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $typeId = $row['id'];
        }

        return $typeId;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function findProviderTypeByProviderName(string $name): ?string
    {
        $type = null;

        $stmt = $this->db->prepare(
            'SELECT type.name '
            . 'FROM mod_host_disco_provider_type type '
            . 'INNER JOIN mod_host_disco_provider provider '
            . 'ON provider.type_id = type.id '
            . 'AND provider.name = :name'
        );
        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $type = $row['name'];
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function findProviderByName(string $name): ?Provider
    {
        $provider = null;

        $stmt = $this->db->prepare(
            'SELECT provider.*, type.name as type '
            . 'FROM mod_host_disco_provider provider '
            . 'INNER JOIN mod_host_disco_provider_type type '
            . 'ON type.id = provider.type_id '
            . 'WHERE provider.name = :name'
        );
        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $provider = (new Provider())
                ->setId((int)$row['id'])
                ->setPluginPackId((int)$row['pluginpack_id'])
                ->setName($row['name'])
                ->setDescription($row['description'])
                ->setType($row['type'])
                ->setCommandId((int)$row['command_id'])
                ->setTestOption($row['test_option'])
                ->setParameters(json_decode($row['parameters'], true));
        }

        return $provider;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function findProviderNameByJobId(int $jobId): ?string
    {
        $name = null;

        $stmt = $this->db->prepare(
            'SELECT provider.name '
            . 'FROM mod_host_disco_provider provider '
            . 'INNER JOIN mod_host_disco_job job '
            . 'ON job.provider_id = provider.id '
            . 'AND job.id = :job_id'
        );
        $stmt->bindValue(':job_id', $jobId, \PDO::PARAM_INT);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $name = $row['name'];
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function findCustomTemplateFromTemplateName(string $name): ?array
    {
        $template = null;

        $stmt = $this->db->prepare(
            'SELECT host_id, host_name '
            . 'FROM host '
            . 'WHERE host_name IN (:name, :custom_name) '
            . 'AND host_register = "0" '
            . 'ORDER BY host_name DESC'
        );
        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $stmt->bindValue(':custom_name', $name . '-custom', \PDO::PARAM_STR);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $template = [
                'id' => (int) $row['host_id'],
                'name' => $row['host_name']
            ];
        }

        return $template;
    }

    /**
     * {@inheritdoc}
     * @throws \PDOException
     */
    public function findMacrosByJobId(int $jobId): string
    {
        $macros = [];

        $stmt = $this->db->prepare(
            'SELECT macros '
            . 'FROM mod_host_disco_provider_mapping mapping '
            . 'INNER JOIN mod_host_disco_job job ON mapping.provider_id = job.provider_id '
            . 'AND job.id = :job_id'
        );
        $stmt->bindValue(':job_id', $jobId, \PDO::PARAM_INT);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $macros = $row['macros'];
        }

        return $macros;
    }
}
