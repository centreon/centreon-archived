<?php

namespace CentreonRemote\Application\Webservice;

class CentreonRemoteServer extends CentreonWebServiceAbstract
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
     * @SWG\Post(
     *   path="/centreon/api/external.php",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       description="the name of the API object class",
     *       required=true,
     *       type="string",
     *       enum="centreon_remote_server"
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       description="the name of the action in the API class",
     *       required=true,
     *       type="string",
     *       enum="addToWaitList"
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="app_key",
     *       description="the unique app key of the Centreon instance",
     *       required=true,
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="version",
     *       description="the app version Centreon instance",
     *       required=true,
     *       type="string",
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Empty string"
     *   )
     * )
     *
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
            throw new \RestBadRequestException('Can not access your address.');
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
            throw new \RestConflictException('Address already in wait list.');
        }

        $createdAt = date('Y-m-d H:i:s');
        $insertQuery = 'INSERT INTO `remote_servers` (`ip`, `app_key`, `version`, `is_connected`, `created_at`, `http_method`, `http_port`, `no_check_certificate`) ';
        $insertQuery .= "VALUES (:ip, :app_key, :version, 0, '{$createdAt}', :http_method, :http_port, :no_check_certificate)";

        $insert = $this->pearDB->prepare($insertQuery);
        $bindings = [
            ':ip'                   => $ip,
            ':app_key'              => $_POST['app_key'],
            ':version'              => $_POST['version'],
            ':http_method'          => $_POST['http_method'] ?? 'http',
            ':http_port'            => $_POST['http_port'] ?? null,
            ':no_check_certificate' => $_POST['no_check_certificate'] ?? 0,
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
