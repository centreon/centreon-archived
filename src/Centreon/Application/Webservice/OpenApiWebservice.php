<?php
namespace Centreon\Application\Webservice;

use CentreonRemote\Application\Webservice\CentreonWebServiceAbstract;

/**
 * @OA\Tag(name="openapi", description="Generate an OpenAPI documentation")
 * @codeCoverageIgnore
 */
class OpenApiWebservice extends CentreonWebServiceAbstract
{

    /**
     * @OA\Get(
     *   path="/external.php?object=openapi&action=generate",
     *   description="Generate OpenAPI documentation",
     *   tags={"openapi"},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"openapi"},
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"generate"}
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Response(
     *       response="200",
     *       description="OK",
     *       @OA\JsonContent()
     *   )
     * )
     *
     * Generate OpenAPI documentation
     *
     * @throws \RestBadRequestException
     * @return []
     */
    public function getGenerate()
    {
        if (defined('OpenApi\UNDEFINED') === false) {
            return;
        }

        $openapi = \OpenApi\scan(_CENTREON_PATH_ . 'src/');

        echo $openapi->toYaml();
        exit;
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

    /**
     * Name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'openapi';
    }
}
