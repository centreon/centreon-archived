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
     * @OA\Post(
     *   path="/centreon/api/internal.php",
     *   @OA\Parameter(
     *       name="object",
     *       in="query",
     *       description="the name of the API object class",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *       example="centreon_configuration_remote"
     *   ),
     *   @OA\Parameter(
     *       name="action",
     *       in="query",
     *       description="the name of the action in the API class",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *       example="getWaitList"
     *   ),
     *   @OA\Response(
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
