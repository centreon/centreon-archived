<?php

namespace Centreon\Application\Webservice;

use Centreon\Application\DataRepresenter\Response;
use Centreon\Application\DataRepresenter\Topology\NavigationList;
use Centreon\Application\DataRepresenter\Topology\ReactAcl;
use Centreon\Application\DataRepresenter\Topology\ReactAclForActive;
use Centreon\Domain\Repository\TopologyRepository;
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
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="reactOnly",
     *       @OA\Schema(
     *          type="integer"
     *       ),
     *       description="fetch react only list(value 1) or full list",
     *       required=false
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="forActive",
     *       @OA\Schema(
     *          type="integer"
     *       ),
     *       description="represent values for active check",
     *       required=false
     *   )
     * )
     * @throws \RestBadRequestException
     */
    public function getMenuList()
    {
        $user = $this->getDi()[ServiceProvider::CENTREON_USER];

        if (empty($user)) {
            throw new \RestBadRequestException('User not found in session. Please relog.');
        }

        $isReact = (isset($_GET['reactOnly']) && $_GET['reactOnly'] == 1);

        $forActive = (isset($_GET['forActive']) && $_GET['forActive'] == 1);

        $dbResult = $this->getDi()[ServiceProvider::CENTREON_DB_MANAGER]
            ->getRepository(TopologyRepository::class)
            ->getTopologyList($user, $isReact);

        if ($isReact) {
            $status = true;
            $result = ($forActive) ? new ReactAclForActive($dbResult) : new ReactAcl($dbResult);
        } else {
            $status = true;
            $result = new NavigationList($dbResult);
        }

        return new Response($result, $status);
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
