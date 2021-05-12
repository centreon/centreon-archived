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

namespace CentreonRemote\Application\Webservice;

use Centreon\Domain\Entity\Task;
use Centreon\Domain\PlatformTopology\Model\PlatformPending;
use CentreonRemote\Domain\Value\ServerWizardIdentity;
use CentreonRemote\Application\Validator\WizardConfigurationRequestValidator;

/**
 * @OA\Tag(name="centreon_configuration_remote", description="")
 */
class CentreonConfigurationRemote extends CentreonWebServiceAbstract
{

    /**
     * Name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_configuration_remote';
    }

    /**
     * @OA\Post(
     *   path="/internal.php?object=centreon_configuration_remote&action=getWaitList",
     *   description="Get remotes servers waitlist",
     *   tags={"centreon_configuration_remote"},
     *   security={{"Session": {}}},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_configuration_remote"},
     *          default="centreon_configuration_remote"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"getWaitList"},
     *          default="getWaitList"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="JSON with the IPs inside the waitlist",
     *       @OA\JsonContent(
     *          @OA\Property(property="ip", type="string"),
     *          @OA\Property(property="version", type="string")
     *      )
     *   )
     * )
     *
     * Get remotes servers waitlist
     *
     * @return array
     */
    public function postGetWaitList(): array
    {
        $statement = $this->pearDB->query("
            SELECT id, address as ip, name as server_name FROM `platform_topology`
            WHERE `type` = 'remote' AND pending = '1'
        ");

        return $statement->fetchAll();
    }

    /**
     * Get Pollers servers waitlist
     *
     * @return array
     */
    public function postGetPollerWaitList(): array
    {
        $statement = $this->pearDB->query("
            SELECT id, address as ip, name as server_name FROM `platform_topology`
            WHERE `type` = 'poller' AND pending = '1'
        ");

        return $statement->fetchAll();
    }

    /**
     * @OA\Get(
     *   path="/internal.php?object=centreon_configuration_remote&action=list",
     *   description="Get list with connected remotes",
     *   tags={"centreon_configuration_remote"},
     *   security={{"Session": {}}},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_configuration_remote"},
     *          default="centreon_configuration_remote"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"getRemotesList"},
     *          default="getRemotesList"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="JSON with the IPs of connected remotes",
     *       @OA\JsonContent(
     *          @OA\Property(property="id", type="string"),
     *          @OA\Property(property="ip", type="string"),
     *          @OA\Property(property="name", type="string")
     *       )
     *   )
     * )
     *
     * Get list with connected remotes
     *
     * @return array
     * @example [['id' => 'poller id', 'ip' => 'poller ip address', 'name' => 'poller name']]
     */
    public function getList(): array
    {
        $list = [];
        foreach ($this->postGetRemotesList() as $row) {
            $row['id'] = (int)$row['id'];
            $list[] = $row;
        }

        return $list;
    }

    /**
     * @OA\Post(
     *   path="/internal.php?object=centreon_configuration_remote&action=getRemotesList",
     *   description="Get list with connected remotes",
     *   tags={"centreon_configuration_remote"},
     *   security={{"Session": {}}},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_configuration_remote"},
     *          default="centreon_configuration_remote"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"getRemotesList"},
     *          default="getRemotesList"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="JSON with the IPs of connected remotes",
     *       @OA\JsonContent(
     *          @OA\Property(property="id", type="string"),
     *          @OA\Property(property="ip", type="string"),
     *          @OA\Property(property="name", type="string")
     *       )
     *   )
     * )
     *
     * Get list with connected remotes
     *
     * @return array
     * @example [['id' => 'poller id', 'ip' => 'poller ip address', 'name' => 'poller name']]
     */
    public function postGetRemotesList(): array
    {
        $query = 'SELECT ns.id, ns.ns_ip_address as ip, ns.name FROM nagios_server as ns ' .
            'JOIN remote_servers as rs ON rs.ip = ns.ns_ip_address ' .
            'WHERE rs.is_connected = 1';
        $statement = $this->pearDB->query($query);

        return $statement->fetchAll();
    }

    /**
     * @OA\Post(
     *   path="/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer",
     *   description="Link centreon remote server",
     *   tags={"centreon_configuration_remote"},
     *   security={{"Session": {}}},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_configuration_remote"},
     *          default="centreon_configuration_remote"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"linkCentreonRemoteServer"},
     *          default="linkCentreonRemoteServer"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          required={
     *              "manage_broker_configuration",
     *              "server_ip",
     *              "centreon_central_ip",
     *              "server_name"
     *          },
     *          @OA\Property(
     *              property="manage_broker_configuration",
     *              type="string",
     *              description="if broker configuration of poller should be managed"
     *          ),
     *          @OA\Property(
     *              property="server_ip",
     *              type="string",
     *              description="the remote server ip address"
     *          ),
     *          @OA\Property(
     *              property="centreon_central_ip",
     *              type="string",
     *              description="the centreon central ip address"
     *          ),
     *          @OA\Property(
     *              property="server_name",
     *              type="string",
     *              description="the remote centreon instance name"
     *          ),
     *          @OA\Property(
     *              property="open_broker_flow",
     *              type="string",
     *              description="if the connection should be made with open broker flow"
     *          ),
     *          @OA\Property(
     *              property="db_user",
     *              type="string",
     *              description="database username"
     *          ),
     *          @OA\Property(
     *              property="db_password",
     *              type="string",
     *              description="database password"
     *          ),
     *          @OA\Property(
     *              property="server_type",
     *              type="string",
     *              description="type of server - remote or poller"
     *          ),
     *          @OA\Property(
     *              property="centreon_folder",
     *              type="string",
     *              description="path to the centreon web folder on the remote machine"
     *          ),
     *          @OA\Property(
     *              property="linked_pollers",
     *              type="string",
     *              description="pollers to link with the new remote"
     *          ),
     *          @OA\Property(
     *              property="linked_remote_master",
     *              type="string",
     *              description="remote to manage the new poller"
     *          ),
     *          @OA\Property(
     *              property="linked_remote_slaves",
     *              type="string",
     *              description="additional remotes which receive data from the new poller"
     *          )
     *       )
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="JSON",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="boolean"),
     *          @OA\Property(property="task_id", type="integer")
     *       )
     *   )
     * )
     *
     * Link centreon remote server
     *
     * @return array
     * @throws \RestBadRequestException
     * @throws \Exception
     * @example ['success' => true, 'task_id' => 'task id']
     *
     * @example ['error' => true, 'message' => 'error message']
     */
    public function postLinkCentreonRemoteServer(): array
    {
        // retrieve post values to be used in other classes
        $_POST = json_decode(file_get_contents('php://input'), true);

        $openBrokerFlow = isset($this->arguments['open_broker_flow']) && $this->arguments['open_broker_flow'] === true;
        $centreonPath = $this->arguments['centreon_folder'] ?? '/centreon/';
        $noCheckCertificate = isset($this->arguments['no_check_certificate'])
            && $this->arguments['no_check_certificate'] === true;
        $noProxy = isset($this->arguments['no_proxy']) && $this->arguments['no_proxy'] === true;
        $serverWizardIdentity = new ServerWizardIdentity();
        $isRemoteConnection = $serverWizardIdentity->requestConfigurationIsRemote();
        $configurationServiceName = $isRemoteConnection
            ? 'centreon_remote.remote_connection_service'
            : 'centreon_remote.poller_connection_service';

        // validate form fields
        WizardConfigurationRequestValidator::validate();

        $pollerConfigurationService = $this->getDi()['centreon_remote.poller_config_service'];
        $serverConfigurationService = $this->getDi()[$configurationServiceName];
        $pollerConfigurationBridge = $this->getDi()['centreon_remote.poller_config_bridge'];

        // extract HTTP method and port from form or database if registered
        $serverIP = parse_url($this->arguments['server_ip'], PHP_URL_HOST) ?: $this->arguments['server_ip'];
        $serverName = substr($this->arguments['server_name'], 0, 40);

        // Check IPv6, IPv4 and FQDN format
        if (
            !filter_var($serverIP, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
            && !filter_var($serverIP, FILTER_VALIDATE_IP)
        ) {
            return ['error' => true, 'message' => "Invalid IP address"];
        }
        $dbAdapter = $this->getDi()['centreon.db-manager']->getAdapter('configuration_db');

        /**
         * Avoid Ip duplication
         */
        $statement = $this->pearDB->prepare(
            'SELECT COUNT(*) as `total` FROM `nagios_server` WHERE `ns_ip_address` = :serverIp'
        );
        $statement->bindValue(':serverIp', $serverIP, \PDO::PARAM_STR);
        $statement->execute();
        $isInNagios = $statement->fetch(\PDO::FETCH_ASSOC);
        if ((int)$isInNagios['total'] > 0) {
            throw new \Exception(_('This IP Address already exist'));
        }

        $sql = 'SELECT * FROM `remote_servers` WHERE `ip` = ?';
        $dbAdapter->query($sql, [$serverIP]);
        $hasIpInTable = (bool)$dbAdapter->count();

        if (!$hasIpInTable) {
            $httpMethod = parse_url($this->arguments['server_ip'], PHP_URL_SCHEME) ?: 'http';
            $httpPort = parse_url($this->arguments['server_ip'], PHP_URL_PORT) ?: '';
        } else {
            $result = $dbAdapter->results();
            $remoteData = reset($result);
            $httpMethod = $remoteData->http_method;
            $httpPort = $remoteData->http_port;
        }

        $serverConfigurationService->setCentralIp($this->arguments['centreon_central_ip']);
        $serverConfigurationService->setServerIp($serverIP);
        $serverConfigurationService->setName($serverName);
        $serverConfigurationService->setOnePeerRetention($openBrokerFlow);

        // set linked pollers
        $pollerConfigurationBridge->collectDataFromRequest();
        // set additional Remote Servers
        $pollerConfigurationBridge->collectDataFromAdditionalRemoteServers();

        // if it's a remote server, set database connection information and check if bam is installed
        if ($isRemoteConnection) {
            $serverConfigurationService->setDbUser($this->arguments['db_user']);
            $serverConfigurationService->setDbPassword($this->arguments['db_password']);
            if (
                $serverWizardIdentity->checkBamOnRemoteServer(
                    $httpMethod . '://' . $serverIP . ':' . $httpPort . '/' . trim($centreonPath, '/'),
                    $noCheckCertificate,
                    $noProxy
                )
            ) {
                $serverConfigurationService->shouldInsertBamBrokers();
            }
        }

        // Add configuration of the new server in the database (poller, engine, broker...)
        try {
            // If server not linked to a poller, then it is linked to central server
            if (!$pollerConfigurationBridge->hasPollersForUpdating()) {
                $serverConfigurationService->isLinkedToCentralServer();
            }

            $serverId = $serverConfigurationService->insert();
        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }

        $taskId = null;

        // if it is remote server wizard, create an export task and link pollers to it if needed
        if ($isRemoteConnection) {
            $remoteServer = $pollerConfigurationBridge->getPollerFromId($serverId);

            // set basic parameters to export task
            $params = [
                'server' => $remoteServer->getId(),
                'pollers' => [],
                'remote_ip' => $serverIP,
                'centreon_path' => $centreonPath,
                'http_method' => $httpMethod,
                'http_port' => $httpPort,
                'no_check_certificate' => $noCheckCertificate,
                'no_proxy' => $noProxy,
            ];

            // If you want to link pollers to a remote
            if ($pollerConfigurationBridge->hasPollersForUpdating()) {
                $pollers = $pollerConfigurationBridge->getLinkedPollersSelectedForUpdate();
                $pollerConfigurationService->linkPollersToParentPoller($pollers, $remoteServer);

                foreach ($pollers as $poller) {
                    $params['pollers'][] = $poller->getId();
                }
            }

            // Create export task
            $taskId = $this->createExportTask($params);

            // add server to the list of remote servers in database (table remote_servers)
            $this->addServerToListOfRemotes(
                $serverIP,
                $centreonPath,
                $httpMethod,
                $httpPort,
                $noCheckCertificate,
                $noProxy
            );
            $this->setCentreonInstanceAsCentral();
            $this->updateServerInPlatformTopology([
                'type' => PlatformPending::TYPE_REMOTE,
                'server_name' => $serverName,
                'nagios_id' => $serverId,
                'address' => $serverIP,
                'children_pollers' => $pollers ?? null
            ]);
            // if it is poller wizard and poller is linked to another poller/remote server (instead of central)
        } elseif ($pollerConfigurationBridge->hasPollersForUpdating()) {
            $pollers = [$pollerConfigurationBridge->getPollerFromId($serverId)];
            $parentPoller = $pollerConfigurationBridge->getLinkedPollersSelectedForUpdate()[0];
            $pollerConfigurationService->linkPollersToParentPoller($pollers, $parentPoller);
            // add broker output to forward data to additional remote server and link in db
            $additionalRemotes = $pollerConfigurationBridge->getAdditionalRemoteServers();
            $pollerConfigurationService->linkPollerToAdditionalRemoteServers($pollers[0], $additionalRemotes);
            $this->updateServerInPlatformTopology([
                'type' => PlatformPending::TYPE_POLLER,
                'server_name' => $serverName,
                'nagios_id' => $serverId,
                'address' => $serverIP,
                'parent' => $parentPoller->getId()
            ]);
        } else {
            $this->updateServerInPlatformTopology([
                'type' => PlatformPending::TYPE_POLLER,
                'server_name' => $serverName,
                'nagios_id' => $serverId,
                'address' => $serverIP,
            ]);
        }

        return ['success' => true, 'task_id' => $taskId];
    }

    /**
     * Authorize to access to the action
     *
     * @param string $action The action name
     * @param \CentreonUser $user The current user
     * @param bool $isInternal If the api is call in internal
     *
     * @return bool If the user has access to the action
     */
    public function authorize($action, $user, $isInternal = false)
    {
        if (parent::authorize($action, $user, $isInternal)) {
            return true;
        }

        return $user && $user->hasAccessRestApiConfiguration();
    }

    /**
     * Add server ip in table of remote servers
     *
     * @param string $serverIP the IP of the server
     * @param string $centreonPath the path to access to Centreon
     * @param string $httpMethod the method to access to server (HTTP/HTTPS)
     * @param string $httpPort the port to access to the server
     * @param bool $noCheckCertificate to do not check SSL CA
     * @param bool $noProxy to do not use configured proxy
     */
    private function addServerToListOfRemotes(
        string $serverIP,
        string $centreonPath,
        string $httpMethod,
        string $httpPort,
        bool $noCheckCertificate,
        bool $noProxy
    ): void {
        $dbAdapter = $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getAdapter('configuration_db');
        $date = date('Y-m-d H:i:s');

        $sql = 'SELECT * FROM `remote_servers` WHERE `ip` = ?';
        $dbAdapter->query($sql, [$serverIP]);
        $hasIpInTable = (bool)$dbAdapter->count();

        if ($hasIpInTable) {
            $sql = 'UPDATE `remote_servers` SET
                `is_connected` = ?, `connected_at` = ?, `centreon_path` = ?,
                `no_check_certificate` = ?, `no_proxy` = ?
                WHERE `ip` = ?';
            $data = ['1', $date, $centreonPath, ($noCheckCertificate ?: 0), ($noProxy ?: 0), $serverIP];
            $dbAdapter->query($sql, $data);
        } else {
            $data = [
                'ip' => $serverIP,
                'app_key' => '',
                'version' => '',
                'is_connected' => '1',
                'created_at' => $date,
                'connected_at' => $date,
                'centreon_path' => $centreonPath,
                'http_method' => $httpMethod,
                'http_port' => $httpPort ?: null,
                'no_check_certificate' => $noCheckCertificate ?: 0,
                'no_proxy' => $noProxy ?: 0
            ];
            $dbAdapter->insert('remote_servers', $data);
        }
    }

    /**
     * Set current centreon instance as central
     */
    private function setCentreonInstanceAsCentral()
    {
        $dbAdapter = $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getAdapter('configuration_db');

        $sql = "SELECT * FROM `informations` WHERE `key` = 'isCentral'";
        $dbAdapter->query($sql);
        $hasInfoRecord = (bool)$dbAdapter->count();

        if ($hasInfoRecord) {
            $sql = "UPDATE `informations` SET `value` = 'yes' WHERE `key` = 'isCentral'";
            $dbAdapter->query($sql);
        } else {
            $data = [
                'key' => 'isCentral',
                'value' => 'yes',
            ];
            $dbAdapter->insert('informations', $data);
        }
    }

    /**
     * Create New Task for export
     *
     * @return bool|int
     * @var $params array
     */
    private function createExportTask(array $params)
    {
        return $this->getDi()['centreon.taskservice']->addTask(Task::TYPE_EXPORT, array('params' => $params));
    }

    /**
     * @param array $topologyInformation
     * @throws \Exception
     */
    private function updateServerInPlatformTopology(array $topologyInformation): void
    {
        /**
         * Get platform_topology id
         */
        $statement = $this->pearDB->prepare(
            "SELECT id, server_id as nagios_id FROM `platform_topology` WHERE address = :address"
        );
        $statement->bindValue(':address', $topologyInformation['address'], \PDO::PARAM_STR);
        $statement->execute();
        $server = $statement->fetch(\PDO::FETCH_ASSOC);

        if (isset($server['nagios_id'])) {
            throw new \Exception(_('This server is already registered'));
        }
        if (!empty($topologyInformation['parent'])) {
            $statement = $this->pearDB->prepare('SELECT id FROM platform_topology WHERE server_id = :serverId');
            $statement->bindValue(':serverId', (int)$topologyInformation['parent'], \PDO::PARAM_INT);
            $statement->execute();
            $parent = $statement->fetch(\PDO::FETCH_ASSOC);
        } else {
            // Get Central ID
            $statement = $this->pearDB->query("SELECT id FROM `platform_topology` WHERE type = 'central'");
            $parent = $statement->fetch(\PDO::FETCH_ASSOC);
            if (empty($parent['id'])) {
                throw new \Exception(_('No Central in topology,please edit it from Configuration > Pollers menu'));
            }
        }
        /**
         * If the server is already registered in platform_topology Update else insert
         */
        $insertedPlatform = [];
        if (!empty($server['id'])) {
            $statement = $this->pearDB->prepare(
                "UPDATE `platform_topology` SET
                `name` = :name,
                parent_id = :parentId,
                server_id = :nagiosId,
                pending = '0'
                WHERE id = :topologyId"
            );
            $statement->bindValue(':name', $topologyInformation['server_name'], \PDO::PARAM_STR);
            $statement->bindValue(':parentId', (int)$parent['id'], \PDO::PARAM_INT);
            $statement->bindValue(':nagiosId', $topologyInformation['nagios_id'], \PDO::PARAM_INT);
            $statement->bindValue(':topologyId', (int)$server['id'], \PDO::PARAM_INT);
            $statement->execute();
        } else {
            $statement = $this->pearDB->prepare(
                "INSERT INTO `platform_topology` (`address`,`name`,`type`,`parent_id`,`server_id`, `pending`)
                VALUES (:address, :name, :type, :parentId, :serverId, '0')"
            );
            $statement->bindValue(':address', $topologyInformation['address'], \PDO::PARAM_STR);
            $statement->bindValue(':name', $topologyInformation['server_name'], \PDO::PARAM_STR);
            $statement->bindValue(':type', $topologyInformation['type'], \PDO::PARAM_STR);
            $statement->bindValue(':parentId', (int)$parent['id'], \PDO::PARAM_INT);
            $statement->bindValue(':serverId', $topologyInformation['nagios_id'], \PDO::PARAM_INT);
            $statement->execute();
            /**
             * Get the new registered platform IP
             */
            $statement = $this->pearDB->prepare('SELECT MAX(id) as last_id FROM `platform_topology`');
            $statement->execute();
            $insertedPlatform = $statement->fetch(\PDO::FETCH_ASSOC);
        }
        /**
         * If it's a remote with attached poller. Update their parent id
         */
        if (!empty($topologyInformation['children_pollers'])) {
            $statement = $this->pearDB->prepare(
                "UPDATE `platform_topology`
                SET parent_id = :parentId, `pending` = '0'
                WHERE server_id = :pollerId"
            );
            foreach ($topologyInformation['children_pollers'] as $poller) {
                $statement->bindValue(
                    ':parentId',
                    isset($insertedPlatform['last_id']) ? (int)$insertedPlatform['last_id'] : (int)$server['id'],
                    \PDO::PARAM_INT
                );
                $statement->bindValue(':pollerId', (int)$poller->getId(), \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
}
