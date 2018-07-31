<?php

namespace CentreonRemote\Application\Webservice;

use Centreon\Infrastructure\Service\CentreonWebserviceServiceInterface;

class CentreonRemoteServer extends \CentreonWebService implements CentreonWebserviceServiceInterface
{

    /**
     * Name of web service object
     * 
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_remote_server';
    }

    /**
     * Add remote Centreon instance in waiting list
     * 
     * @return string
     * @throws \RestBadRequestException
     * @throws \RestConflictException
     */
    public function postAddToWaitList(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        if (!$ip) {
            throw new \RestBadRequestException('Can not access your IP address.');
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \RestBadRequestException('IP is not valid.');
        }

        if (!isset($_POST['app_key']) || !$_POST['app_key']) {
            throw new \RestBadRequestException('Please send \'app_key\' in the request.');
        }

        if (!isset($_POST['version']) || !$_POST['version']) {
            throw new \RestBadRequestException('Please send \'version\' in the request.');
        }

        $statement = $this->pearDB->prepare('SELECT COUNT(id) as count FROM `remote_servers` WHERE `ip` = :ip');
        $statement->execute([':ip' => $ip]);
        $result = $statement->fetch();

        if ((bool) $result['count']) {
            throw new \RestConflictException('IP already in wait list.');
        }

        $createdAt = date('Y-m-d H:i:s');
        $insertQuery = 'INSERT INTO `remote_servers` (`ip`, `app_key`, `version`, `is_connected`, `created_at`) ';
        $insertQuery .= "VALUES (:ip, :app_key, :version, 0, '{$createdAt}')";

        $insert = $this->pearDB->prepare($insertQuery);
        $bindings = [
            ':ip'      => $ip,
            ':app_key' => $_POST['app_key'],
            ':version' => $_POST['version'],
        ];

        try {
            $insert->execute($bindings);
        } catch(\Exception $e) {
            throw new \RestBadRequestException('There was an error saving the data.');
        }

        return '';
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
        return true;
    }
}
