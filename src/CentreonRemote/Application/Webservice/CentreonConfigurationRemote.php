<?php

namespace CentreonRemote\Application\Webservice;

use CentreonRemote\Domain\Service\RemoteConnectionConfigurationService;

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
            throw new \RestBadRequestException('You need to send \'remote_server_ip\' in the request.');
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

        /** @var $remoteConfiguration RemoteConnectionConfigurationService */
        $remoteConfiguration = $this->getDi()['centreon_remote.connection_config_service'];
        $dbAdapter = $this->getDi()['centreon.db-manager']->getAdapter('configuration_db');
        $date = date('Y-m-d H:i:s');

        foreach ($remoteIps as $index => $remoteIp) {
            $remoteName = count($remoteIps) > 1 ? "{$remoteName}_1" : $remoteName;

            $remoteConfiguration->setIp($remoteIp);
            $remoteConfiguration->setName($remoteName);

            try {
                $remoteConfiguration->insert();
            } catch(\Exception $e) {
                //TODO return json failure
            }

            $sql = 'SELECT * FROM `remote_servers` WHERE `ip` = ?';
            $dbAdapter->query($sql, [$remoteIp]);
            $hasIpInTable = (bool) $dbAdapter->count();

            if ($hasIpInTable) {
                $sql = 'UPDATE `remote_servers` SET `is_connected` = ?, `connected_at` = ? WHERE `ip` = ?';
                $data = ['1', $date, $remoteIp];
                $dbAdapter->query($sql, $data);
            } else {
                $data = [
                    'ip'           => $remoteIp,
                    'app_key'      => '',
                    'version'      => '',
                    'is_connected' => '1',
                    'created_at'   => $date,
                    'connected_at' => $date,
                ];
                $dbAdapter->insert('remote_servers', $data);
            }

            // Finish remote connection by:
            // - $openBrokerFlow?
            // - Centreon Broker config
            // - Centreon RRD config
        }

        return json_encode([]);
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
}
