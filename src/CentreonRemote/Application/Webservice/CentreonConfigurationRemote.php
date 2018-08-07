<?php

namespace CentreonRemote\Application\Webservice;

use Centreon\Infrastructure\Service\CentreonWebserviceServiceInterface;

class CentreonConfigurationRemote extends \CentreonWebService implements CentreonWebserviceServiceInterface
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

    //TODO doc
    public function postDoSomethingAboutConnectingRemote()
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
        $remoteName = count($remoteIps) > 1 ? '' : substr($_POST['remote_name'], 0, 40);

        foreach ($remoteIps as $remoteIp) {
            // Get data for nagios and broker tables
            // Replace ip, name and id relations
            // Give $this->pearDB to a db service or extend the class with insert method
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
