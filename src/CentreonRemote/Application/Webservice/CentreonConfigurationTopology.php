<?php

namespace CentreonRemote\Application\Webservice;

/**
 * @OA\Tag(name="centreon_configuration_topology", description="")
 */
class CentreonConfigurationTopology extends CentreonWebServiceAbstract
{

    /**
     * Name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_configuration_topology';
    }

    /**
     * @OA\Post(
     *   path="/internal.php?object=centreon_configuration_topology&action=getTopologyData",
     *   description="Get data for topology_id",
     *   tags={"centreon_configuration_topology"},
     *   security={{"Session": {}}},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_configuration_topology"},
     *          default="centreon_configuration_topology"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"getTopologyData"},
     *          default="getTopologyData"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          required={
     *              "topology_id"
     *          },
     *          @OA\Property(
     *              property="topology_id",
     *              type="string",
     *              description="the ID of the topology page"
     *          )
     *       )
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="JSON with topology data",
     *       @OA\JsonContent(
     *          @OA\Property(property="url", type="string"),
     *          @OA\Property(property="is_react", type="boolean")
     *       )
     *   )
     * )
     *
     * Get data for topology_id
     * @return string
     * @throws \RestBadRequestException
     */
    public function postGetTopologyData()
    {
        if (!isset($_POST['topology_id']) || !$_POST['topology_id']) {
            throw new \RestBadRequestException('You need to send \'topology_id\' in the request.');
        }

        $topologyID = (int) $_POST['topology_id'];
        $statement = $this->pearDB->prepare(
            'SELECT `topology_url`, `is_react` FROM `topology` WHERE `topology_id` = :id'
        );
        $statement->execute([':id' => $topologyID]);
        $result = $statement->fetch();

        if (!$result) {
            throw new \RestBadRequestException('No topology found.');
        }

        return [
            'url'      => $result['topology_url'],
            'is_react' => (bool) $result['is_react'],
        ];
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
