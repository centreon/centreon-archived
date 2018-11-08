<?php

namespace CentreonRemote\Application\Webservice;

use CentreonRemote\Application\Validator\WizardConfigurationRequestValidator;
use CentreonRemote\Domain\Service\ConfigurationWizard\LinkedPollerConfigurationService;
use Centreon\Domain\Entity\Task;
use CentreonRemote\Domain\Service\ConfigurationWizard\PollerConfigurationRequestBridge;
use CentreonRemote\Domain\Service\ConfigurationWizard\ServerConnectionConfigurationService;
use CentreonRemote\Domain\Value\ServerWizardIdentity;

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
     * @SWG\Post(
     *   path="/centreon/api/internal.php",
     *   operationId="getWaitList",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       type="string",
     *       description="the name of the API object class",
     *       required=true,
     *       enum="centreon_configuration_remote",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       type="string",
     *       description="the name of the action in the API class",
     *       required=true,
     *       enum="getWaitList",
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="JSON with the IPs inside the waitlist"
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
     * @SWG\Post(
     *   path="/centreon/api/internal.php",
     *   operationId="getRemotesList",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       type="string",
     *       description="the name of the API object class",
     *       required=true,
     *       enum="centreon_configuration_remote",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       type="string",
     *       description="the name of the action in the API class",
     *       required=true,
     *       enum="getRemotesList",
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="JSON with the IPs of connected remotes"
     *   )
     * )
     *
     * Get list with connected remotes
     *
     * @return array
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
     * @SWG\Post(
     *   path="/centreon/api/internal.php",
     *   operationId="linkCentreonRemoteServer",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       type="string",
     *       description="the name of the API object class",
     *       required=true,
     *       enum="centreon_configuration_remote",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       type="string",
     *       description="the name of the action in the API class",
     *       required=true,
     *       enum="linkCentreonRemoteServer",
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="open_broker_flow",
     *       type="string",
     *       description="if the connection should be made with open broker flow",
     *       required=false,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="manage_broker_configuration",
     *       type="string",
     *       description="if broker configuration of poller should be managed",
     *       required=false,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="server_ip",
     *       type="string",
     *       description="the remote server ip address",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="centreon_central_ip",
     *       type="string",
     *       description="the centreon central ip address",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="server_name",
     *       type="string",
     *       description="the remote centreon instance name",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="db_user",
     *       type="string",
     *       description="database username",
     *       required=false,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="db_password",
     *       type="string",
     *       description="database password",
     *       required=false,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="server_type",
     *       type="string",
     *       description="type of server - remote or poller",
     *       required=false,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="centreon_folder",
     *       type="string",
     *       description="path to the centreon web folder on the remote machine",
     *       required=false,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="linked_pollers",
     *       type="string",
     *       description="pollers to link with the new remote",
     *       required=false,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="linked_remote",
     *       type="string",
     *       description="remote to manage the new poller",
     *       required=false,
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="JSON"
     *   )
     * )
     *
     * Link centreon remote server
     *
     * @return string
     *
     * @throws \RestBadRequestException
     */
    public function postLinkCentreonRemoteServer()
    {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $openBrokerFlow = isset($_POST['open_broker_flow']) && $_POST['open_broker_flow'] === true;
        $serverWizardIdentity = new ServerWizardIdentity;
        $isRemoteConnection = $serverWizardIdentity->requestConfigurationIsRemote();
        $serverHasBamInstalled = false;
        $configurationServiceName = $isRemoteConnection ?
            'centreon_remote.remote_connection_service' :
            'centreon_remote.poller_connection_service';

        WizardConfigurationRequestValidator::validate();

        /** @var $pollerConfigurationService LinkedPollerConfigurationService */
        /** @var $pollerConfigurationBridge PollerConfigurationRequestBridge */
        /** @var $serverConfigurationService ServerConnectionConfigurationService */
        $pollerConfigurationService = $this->getDi()['centreon_remote.poller_config_service'];
        $serverConfigurationService = $this->getDi()[$configurationServiceName];
        $pollerConfigurationBridge = $this->getDi()['centreon_remote.poller_config_bridge'];

        $serverIP = $_POST['server_ip'];
        $serverName = substr($_POST['server_name'], 0, 40);

        $serverConfigurationService->setCentralIp($_POST['centreon_central_ip']);
        $serverConfigurationService->setServerIp($serverIP);
        $serverConfigurationService->setName($serverName);
        $serverConfigurationService->setOpenBrokerFlow($openBrokerFlow);

        if ($isRemoteConnection) {
            $serverConfigurationService->setDbUser($_POST['db_user']);
            $serverConfigurationService->setDbPassword($_POST['db_password']);
            $serverHasBamInstalled = $serverWizardIdentity->fetchIfServerInstalledBam($serverIP, $_POST['centreon_folder']);
        }

        // Add configuration of the new server in the database
        try {
            if ($serverHasBamInstalled) {
                $serverConfigurationService->shouldInsertBamBrokers();
            }

            $serverID = $serverConfigurationService->insert();
        } catch(\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }

        $pollerConfigurationBridge->setServerID($serverID);
        $pollerConfigurationBridge->collectDataFromRequest();
        $taskId = null;

        // If you want to link pollers to a remote
        if ($pollerConfigurationBridge->hasPollersForUpdating()) {
            $remoteServer = $pollerConfigurationBridge->getRemoteServerForConfiguration();
            $pollerServers = $pollerConfigurationBridge->getLinkedPollersSelectedForUpdate();
            $pollerConfigurationService->setOpenBrokerFlow($openBrokerFlow);
            $pollerConfigurationService->setPollersConfigurationWithServer($pollerServers, $remoteServer);

            /**
             * Create Export Task
             */
            $params = [];
            foreach ($pollerServers as $poller){
                $params['pollers'][] = $poller->getId();
            }
            $params['server'] = $remoteServer->getId();
            $params['remote_ip'] = $remoteServer->getIp();
            $params['centreon_path'] = $_POST['centreon_folder'] ?? '/centreon/';
            $taskId = $this->createExportTask($params);
        }

        if ($isRemoteConnection) {
            $centreonPath = $_POST['centreon_folder'] ?? '/centreon/';
            $this->addServerToListOfRemotes($serverIP, $centreonPath);
            $this->setCentreonInstanceAsCentral();
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
    private function addServerToListOfRemotes($serverIP, $centreonPath)
    {
        $dbAdapter = $this->getDi()['centreon.db-manager']->getAdapter('configuration_db');
        $date = date('Y-m-d H:i:s');

        $sql = 'SELECT * FROM `remote_servers` WHERE `ip` = ?';
        $dbAdapter->query($sql, [$serverIP]);
        $hasIpInTable = (bool) $dbAdapter->count();

        if ($hasIpInTable) {
            $sql = 'UPDATE `remote_servers` SET `is_connected` = ?, `connected_at` = ?, `centreon_path` = ? ' .
                'WHERE `ip` = ?';
            $data = ['1', $date, $centreonPath, $serverIP];
            $dbAdapter->query($sql, $data);
        } else {
            $data = [
                'ip'            => $serverIP,
                'app_key'       => '',
                'version'       => '',
                'is_connected'  => '1',
                'created_at'    => $date,
                'connected_at'  => $date,
                'centreon_path' => $centreonPath,
            ];
            $dbAdapter->insert('remote_servers', $data);
        }
    }

    /**
     * Set current centreon instance as central
     */
    private function setCentreonInstanceAsCentral()
    {
        $dbAdapter = $this->getDi()['centreon.db-manager']->getAdapter('configuration_db');

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
     * @var $params array
     * @return bool
     */
    private function createExportTask($params)
    {
        $result = $this->getDi()['centreon.taskservice']->addTask(Task::TYPE_EXPORT, array('params'=>$params));
        return $result;
    }
}
