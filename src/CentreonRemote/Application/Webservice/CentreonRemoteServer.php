<?php

namespace CentreonRemote\Application\Webservice;

/**
 * @OA\Tag(name="centreon_remote_server", description="")
 */
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
     * @OA\Post(
     *   path="/external.php?object=centreon_remote_server&action=addToWaitList",
     *   description="Add remote Centreon instance in waiting list",
     *   tags={"centreon_remote_server"},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_remote_server"},
     *          default="centreon_remote_server"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"addToWaitList"},
     *          default="addToWaitList"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          required={
     *              "app_key",
     *              "version"
     *          },
     *          @OA\Property(
     *              property="app_key",
     *              type="string",
     *              description="the unique app key of the Centreon instance"
     *          ),
     *          @OA\Property(
     *              property="version",
     *              type="string",
     *              description="the app version Centreon instance"
     *          )
     *       )
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="Empty string",
     *       @OA\JsonContent(
     *          @OA\Property(type="string")
     *       )
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
        $insertQuery = 'INSERT INTO `remote_servers` (`ip`, `app_key`, `version`, `is_connected`, '
            . '`created_at`, `http_method`, `http_port`, `no_check_certificate`) ';
        $insertQuery .= "VALUES (:ip, :app_key, :version, 0, '{$createdAt}', "
        . ":http_method, :http_port, :no_check_certificate)";

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
        } catch (\Exception $e) {
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
