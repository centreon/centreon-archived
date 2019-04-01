<?php

namespace CentreonRemote\Application\Webservice;

use CentreonRemote\Application\Validator\WizardConfigurationRequestValidator;
use CentreonRemote\Domain\Service\ConfigurationWizard\LinkedPollerConfigurationService;
use Centreon\Domain\Entity\Task;
use CentreonRemote\Domain\Service\ConfigurationWizard\PollerConfigurationRequestBridge;
use CentreonRemote\Domain\Service\ConfigurationWizard\ServerConnectionConfigurationService;
use CentreonRemote\Domain\Value\ServerWizardIdentity;

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
        $statement = $this->pearDB->query('SELECT ip, version FROM `remote_servers` WHERE `is_connected` = 0');

        return $statement->fetchAll();
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
     * @example ['id' => 'poller id', 'ip' => 'poller ip address', 'name' => 'poller name']
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
     *              property="linked_remote",
     *              type="string",
     *              description="remote to manage the new poller"
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
     * @example ['error' => true, 'message' => 'error message']
     * @example ['success' => true, 'task_id' => 'task id']
     *
     * @throws \RestBadRequestException
     */
    public function postLinkCentreonRemoteServer(): array
    {
        // retrieve post values to be used in other classes
        $_POST = json_decode(file_get_contents('php://input'), true);

        $openBrokerFlow = isset($this->arguments['open_broker_flow']) && $this->arguments['open_broker_flow'] === true;
        $centreonPath = $this->arguments['centreon_folder'] ?? '/centreon/';
        $noCheckCertificate = isset($this->arguments['no_check_certificate'])
            && $this->arguments['no_check_certificate'] === true;
        $serverWizardIdentity = new ServerWizardIdentity;
        $isRemoteConnection = $serverWizardIdentity->requestConfigurationIsRemote();
        $configurationServiceName = $isRemoteConnection ?
            'centreon_remote.remote_connection_service' :
            'centreon_remote.poller_connection_service';

        // validate form fields
        WizardConfigurationRequestValidator::validate();

        /** @var $pollerConfigurationService LinkedPollerConfigurationService */
        $pollerConfigurationService = $this->getDi()['centreon_remote.poller_config_service'];
        /** @var $serverConfigurationService ServerConnectionConfigurationService */
        $serverConfigurationService = $this->getDi()[$configurationServiceName];
        /** @var $pollerConfigurationBridge PollerConfigurationRequestBridge */
        $pollerConfigurationBridge = $this->getDi()['centreon_remote.poller_config_bridge'];

        $httpMethod = parse_url($this->arguments['server_ip'], PHP_URL_SCHEME) ?: 'http';
        $httpPort = parse_url($this->arguments['server_ip'], PHP_URL_PORT) ?: '';
        $serverIP = parse_url($this->arguments['server_ip'], PHP_URL_HOST) ?: $this->arguments['server_ip'];
        $serverName = substr($this->arguments['server_name'], 0, 40);

        $serverConfigurationService->setCentralIp($this->arguments['centreon_central_ip']);
        $serverConfigurationService->setServerIp($serverIP);
        $serverConfigurationService->setName($serverName);
        $serverConfigurationService->setOnePeerRetention($openBrokerFlow);

        // set linked pollers
        $pollerConfigurationBridge->collectDataFromRequest();

        // if it's a remote server, set database connection information and check if bam is installed
        if ($isRemoteConnection) {
            $serverConfigurationService->setDbUser($this->arguments['db_user']);
            $serverConfigurationService->setDbPassword($this->arguments['db_password']);
            if ($serverWizardIdentity->checkBamOnRemoteServer($serverIP, $centreonPath)) {
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
                'server'               => $remoteServer->getId(),
                'remote_ip'            => $remoteServer->getIp(),
                'centreon_path'        => $centreonPath,
                'http_method'          => $httpMethod,
                'http_port'            => $httpPort,
                'no_check_certificate' => $noCheckCertificate,
                'pollers'              => []
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
            $this->addServerToListOfRemotes($serverIP, $centreonPath, $httpMethod, $httpPort, $noCheckCertificate);
            $this->setCentreonInstanceAsCentral();

        // if it is poller wizard and poller is linked to another poller/remote server (instead of central)
        } elseif ($pollerConfigurationBridge->hasPollersForUpdating()) {
            $pollers = [$pollerConfigurationBridge->getPollerFromId($serverId)];
            $parentPoller = $pollerConfigurationBridge->getLinkedPollersSelectedForUpdate()[0];
            $pollerConfigurationService->linkPollersToParentPoller($pollers, $parentPoller);
        }

        return ['success' => true, 'task_id' => $taskId];
    }

    /**
     * Authorize to access to the action
     *
     * @param string $action The action name
     * @param \CentreonUser $user The current user
     * @param boolean $isInternal If the api is call in internal
     *
     * @return boolean If the user has access to the action
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
     * @param $serverIP
     */
    private function addServerToListOfRemotes($serverIP, $centreonPath, $httpMethod = 'http', $httpPort = null, $noCheckCertificate = 0)
    {
        $dbAdapter = $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getAdapter('configuration_db');
        $date = date('Y-m-d H:i:s');

        $sql = 'SELECT * FROM `remote_servers` WHERE `ip` = ?';
        $dbAdapter->query($sql, [$serverIP]);
        $hasIpInTable = (bool) $dbAdapter->count();

        if ($hasIpInTable) {
            $sql = 'UPDATE `remote_servers` SET `is_connected` = ?, `connected_at` = ?, `centreon_path` = ?, ' .
                'http_method = ?, http_port = ?, no_check_certificate = ? ' .
                'WHERE `ip` = ?';
            $data = ['1', $date, $centreonPath, $httpMethod, $httpPort, $noCheckCertificate, $serverIP];
            $dbAdapter->query($sql, $data);
        } else {
            $data = [
                'ip'                   => $serverIP,
                'app_key'              => '',
                'version'              => '',
                'is_connected'         => '1',
                'created_at'           => $date,
                'connected_at'         => $date,
                'centreon_path'        => $centreonPath,
                'http_method'          => $httpMethod,
                'http_port'            => $httpPort ?: null,
                'no_check_certificate' => $noCheckCertificate ?: 0
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
        $hasInfoRecord = (bool) $dbAdapter->count();

        if ($hasInfoRecord) {
            $sql = "UPDATE `informations` SET `value` = 'yes' WHERE `key` = 'isCentral'";
            $dbAdapter->query($sql);
        } else {
            $data = [
                'key'   => 'isCentral',
                'value' => 'yes',
            ];
            $dbAdapter->insert('informations', $data);
        }
    }

    /**
     * Create New Task for export
     *
     * @var $params array
     * @return bool|int
     */
    private function createExportTask($params)
    {
        $result = $this->getDi()['centreon.taskservice']->addTask(Task::TYPE_EXPORT, array('params'=>$params));
        return $result;
    }
}
