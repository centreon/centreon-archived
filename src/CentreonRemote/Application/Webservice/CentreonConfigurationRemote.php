<?php

namespace CentreonRemote\Application\Webservice;

use CentreonRemote\Domain\Service\ServerConnectionConfigurationService;
use Centreon\Domain\Entity\Task;

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
     * @return string
     */
    public function postGetWaitList(): string
    {
        $statement = $this->pearDB->query('SELECT ip, version FROM `remote_servers` WHERE `is_connected` = 0');

        return json_encode($statement->fetchAll());
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
     * @return string
     */
    public function postGetRemotesList(): string
    {
        $statement = $this->pearDB->query('SELECT ip FROM `remote_servers` WHERE `is_connected` = 1');

        return json_encode($statement->fetchAll());
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
     *       name="remote_server_ip",
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
     *       name="remote_name",
     *       type="string",
     *       description="the remote centreon instance name",
     *       required=true,
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
        $openBrokerFlow = isset($_POST['open_broker_flow']);

        // - poller/remote ips can be a multiple select
        //  -- form can have option to add IP of server without being pinged previously
        if (!isset($_POST['remote_server_ip']) || !$_POST['remote_server_ip']) {
            throw new \RestBadRequestException('You need t send \'remote_server_ip\' in the request.');
        }

        if (!isset($_POST['centreon_central_ip']) || !$_POST['centreon_central_ip']) {
            throw new \RestBadRequestException('You need t send \'centreon_central_ip\' in the request.');
        }

        if (!isset($_POST['remote_name']) || !$_POST['remote_name']) {
            throw new \RestBadRequestException('You need t send \'remote_name\' in the request.');
        }

        $remoteIps = (array) $_POST['remote_server_ip'];
        $centreonCentralIp = $_POST['centreon_central_ip'];
        $remoteName = substr($_POST['remote_name'], 0, 40);

        // Get service form container
        //  - use $this->pearDB or db-manager?

        foreach ($remoteIps as $index => $remoteIp) {
            $remoteName = count($remoteIps) > 1 ? "{$remoteName}_1" : $remoteName;
            // Set ip in service
            // Set name in service
            // Insert rows with data
            // Finish remote connection by:
            // - $openBrokerFlow?
            // - create or update in remote_servers
            //  -- name = $remoteName
            //  -- is_connected = 1
            //  -- connected_at = timestamp
            // - Centreon Broker config
            // - Centreon RRD config
        }

        return json_encode([]);
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
        $openBrokerFlow = isset($_POST['open_broker_flow']);
        $isRemoteConnection = isset($_POST['server_type']) && $_POST['server_type'] = 'remote';
        $configurationServiceName = $isRemoteConnection ?
            'centreon_remote.remote_connection_service' :
            'centreon_remote.poller_connection_service';

        // - poller/remote ips can be a multiple select
        //  -- form can have option to add IP of server without being pinged previously
        if (!isset($_POST['server_ip']) || !$_POST['server_ip']) {
            throw new \RestBadRequestException('You need to send \'server_ip\' in the request.');
        }

        if (!isset($_POST['server_name']) || !$_POST['server_name']) {
            throw new \RestBadRequestException('You need t send \'server_name\' in the request.');
        }

        if (!isset($_POST['centreon_central_ip']) || !$_POST['centreon_central_ip']) {
            throw new \RestBadRequestException('You need t send \'centreon_central_ip\' in the request.');
        }

        $serverIps = (array) $_POST['server_ip'];
        $serverName = substr($_POST['server_name'], 0, 40);
        $centreonCentralIp = $_POST['centreon_central_ip'];

        /** @var $serverConfigurationService ServerConnectionConfigurationService */
        $serverConfigurationService = $this->getDi()[$configurationServiceName];
        $serverConfigurationService->setCentralIp($centreonCentralIp);

        foreach ($serverIps as $index => $serverIp) {
            $serverName = count($serverIps) > 1 ? "{$serverName}_1" : $serverName;

            $serverConfigurationService->setServerIp($serverIp);
            $serverConfigurationService->setName($serverName);

            try {
                $serverConfigurationService->insert();
            } catch(\Exception $e) {
                return json_encode(['error' => true, 'message' => $e->getMessage()]);
            }

            if ($isRemoteConnection) {
                $this->addServerToListOfRemotes($serverIp);
            }

            // Finish remote connection by:
            // - $openBrokerFlow?
            // - update informations table set isRemote=yes in the slave server
        }

        if ($isRemoteConnection) {
            $this->setCentreonInstanceAsCentral();
        }

        //todo: update return based on success/fail
        $this->createExportTask($serverIps);


         return json_encode(['success' => true]);
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
     * @param $serverIp
     */
    private function addServerToListOfRemotes($serverIp)
    {
        $dbAdapter = $this->getDi()['centreon.db-manager']->getAdapter('configuration_db');
        $date = date('Y-m-d H:i:s');

        $sql = 'SELECT * FROM `remote_servers` WHERE `ip` = ?';
        $dbAdapter->query($sql, [$serverIp]);
        $hasIpInTable = (bool) $dbAdapter->count();

        if ($hasIpInTable) {
            $sql = 'UPDATE `remote_servers` SET `is_connected` = ?, `connected_at` = ? WHERE `ip` = ?';
            $data = ['1', $date, $serverIp];
            $dbAdapter->query($sql, $data);
        } else {
            $data = [
                'ip'           => $serverIp,
                'app_key'      => '',
                'version'      => '',
                'is_connected' => '1',
                'created_at'   => $date,
                'connected_at' => $date,
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
     * @var $toIps array
     * @return bool
     */
    private function createExportTask($toIps)
    {
        $result = $this->getDi()['centreon.taskservice']->addTask(Task::TYPE_EXPORT, array('ips'=>$toIps));

        return $result;
    }
}
