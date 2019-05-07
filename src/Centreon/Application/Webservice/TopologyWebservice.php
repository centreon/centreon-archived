<?php

namespace Centreon\Application\Webservice;

use Centreon\ServiceProvider;
use CentreonRemote\Application\Webservice\CentreonWebServiceAbstract;

class TopologyWebservice extends CentreonWebServiceAbstract
{

    /**
     * List of required services
     *
     * @return array
     */
    public static function dependencies(): array
    {
        return [
            ServiceProvider::CENTREON_DB_MANAGER,
        ];
    }

    /**
     * Name of web service object
     * 
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_topology';
    }

    /**
     * @OA\Get(
     *   path="/internal.php?object=centreon_topology&action=getTopologyByPage",
     *   description="Get topology object by page id",
     *   tags={"centreon_topology"},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_topology"},
     *          default="centreon_topology"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"getTopologyByPage"},
     *          default="getTopologyByPage"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="topology_page",
     *       @OA\Schema(
     *          type="string"
     *       ),
     *       description="Page ID for topology",
     *       required=false
     *   ),
     * )
     * @throws \RestBadRequestException
     * @return array
     */
    public function getGetTopologyByPage(): array
    {
        if (!isset($_GET['topology_page']) || !$_GET['topology_page']) {
            throw new \RestBadRequestException('You need to send \'topology_page\' in the request.');
        }

        $topologyID = (int) $_GET['topology_page'];
        $statement = $this->pearDB->prepare('SELECT * FROM `topology` WHERE `topology_page` = :id');
        $statement->execute([':id' => $topologyID]);
        $result = $statement->fetch();

        if (!$result) {
            throw new \RestBadRequestException('No topology found.');
        }

        return $result;
    }

    /**
     * @OA\Get(
     *   path="/internal.php?object=centreon_topology&action=menuList",
     *   description="Get list of menu items by acl",
     *   tags={"centreon_topology"},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_topology"},
     *          default="centreon_topology"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"menuList"},
     *          default="menuList"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   )
     * )
     * @throws \RestBadRequestException
     * @return array
     */
    public function getMenuList(): array
    {
        $user = $this->getDi()[ServiceProvider::CENTREON_USER];

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
