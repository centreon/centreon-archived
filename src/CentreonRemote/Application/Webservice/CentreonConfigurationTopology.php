<?php

namespace CentreonRemote\Application\Webservice;

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
     * @SWG\Post(
     *   path="/centreon/api/internal.php",
     *   operationId="getTopologyData",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       type="string",
     *       description="the name of the API object class",
     *       required=true,
     *       enum="centreon_configuration_topology",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       type="string",
     *       description="the name of the action in the API class",
     *       required=true,
     *       enum="getTopologyData",
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="topology_id",
     *       description="the ID of the topology page",
     *       required=true,
     *       type="string",
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="JSON with topology data"
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
        $statement = $this->pearDB->prepare('SELECT `topology_url`, `is_react` FROM `topology` WHERE `topology_id` = :id');
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
