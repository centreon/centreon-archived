<?php
namespace CentreonModule\Application\Webservice;

use CentreonRemote\Application\Webservice\CentreonWebServiceAbstract;
use Centreon\Application\DataRepresenter\Bulk;
use Centreon\Application\DataRepresenter\Response;
use CentreonModule\Application\DataRepresenter\ModuleEntity;

/**
 * @OA\Tag(name="centreon_module", description="")
 */
class CentreonModuleWebservice extends CentreonWebServiceAbstract
{

    /**
     * @OA\Get(
     *   path="/internal.php?object=centreon_module&action=list",
     *   summary="Get list of modules and widgets",
     *   tags={"centreon_module"},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_module"},
     *          default="centreon_module"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"list"},
     *          default="list"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="search",
     *       @OA\Schema(
     *          type="string"
     *       ),
     *       description="filter the result by name and keywords",
     *       required=false
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="installed",
     *       @OA\Schema(
     *          type="boolean"
     *       ),
     *       description="filter the result by installed or non-installed modules",
     *       required=false
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="updated",
     *       @OA\Schema(
     *          type="boolean"
     *       ),
     *       description="filter the result by updated or non-installed modules",
     *       required=false
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="types",
     *       @OA\Schema(
     *          type="array",
     *          items={"type": "string", "enum": {"module", "widget"}}
     *       ),
     *       description="filter the result by type",
     *       required=false
     *   ),
     *   @OA\Response(
     *      response="200",
     *      description="OK",
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="module",
     *                  @OA\Property(property="entities", type="array", @OA\Items(ref="#/components/schemas/ModuleEntity")),
     *                  @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *              ),
     *              @OA\Property(property="widget", type="object",
     *                  @OA\Property(property="entities", type="array", @OA\Items(ref="#/components/schemas/ModuleEntity")),
     *                  @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *              ),
     *              @OA\Property(property="status", type="boolean")
     *          )
     *      )
     *   )
     * )
     *
     * Get list of modules and
     *
     * @throws \RestBadRequestException
     * @return []
     */
    public function getList()
    {
        // extract post payload
        $request = $this->query();

        $search = isset($request['search']) && $request['search'] ? $request['search'] : null;
        $installed = isset($request['installed']) ? $request['installed'] : null;
        $updated = isset($request['updated']) ? $request['updated'] : null;
        $typeList = isset($request['types']) ? (array) $request['types'] : null;

        if ($installed && strtolower($installed) === 'true') {
            $installed = true;
        } elseif ($installed && strtolower($installed) === 'false') {
            $installed = false;
        } elseif ($installed) {
            $installed = null;
        }

        if ($updated && strtolower($updated) === 'true') {
            $updated = true;
        } elseif ($updated && strtolower($updated) === 'false') {
            $updated = false;
        } elseif ($updated) {
            $updated = null;
        }

        $list = $this->getDi()['centreon.module']
            ->getList($search, $installed, $updated, $typeList);

        $result = new Bulk($list, null, null, null, ModuleEntity::class);

        $response = new Response($result);

        return $response;
    }

    /**
     * @OA\Post(
     *   path="/external.php?object=centreon_module&action=getBamModuleInfo",
     *   summary="Get list of modules and widgets",
     *   tags={"centreon_module"},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       description="the name of the API object class",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_modules_webservice"}
     *       )
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       description="the name of the action in the API class",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          enum={"getBamModuleInfo"}
     *       )
     *   ),
     *   @OA\Response(
     *      response="200",
     *      description="JSON with BAM module info",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="enabled", type="boolean"),
     *              @OA\Property(property="status", type="boolean"),
     *          )
     *      )
     *   )
     * )
     * @OA\Post(
     *   path="/external.php?object=centreon_modules_webservice&action=getBamModuleInfo",
     *   summary="Get list of modules and widgets",
     *   tags={"centreon_modules_webservice"},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       description="the name of the API object class",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_modules_webservice"}
     *       )
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       description="the name of the action in the API class",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          enum={"getBamModuleInfo"}
     *       )
     *   ),
     *   @OA\Response(
     *      response="200",
     *      description="JSON with BAM module info",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="enabled", type="boolean"),
     *              @OA\Property(property="status", type="boolean"),
     *          )
     *      )
     *   )
     * )
     *
     * Get info for BAM module
     * 
     * @return array
     */
    public function postGetBamModuleInfo(): array
    {
        $factory = new \CentreonLegacy\Core\Utils\Factory($this->getDi());
        $utils = $factory->newUtils();
        $moduleFactory = new \CentreonLegacy\Core\Module\Factory($this->getDi(), $utils);
        $moduleInfoObj = $moduleFactory->newInformation();
        $modules = $moduleInfoObj->getList();

        if (
            array_key_exists('centreon-bam-server', $modules) &&
            $modules['centreon-bam-server']['is_installed']
        ) {
            return ['enabled' => true];
        }

        return ['enabled' => false];
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
        return 'centreon_module';
    }
}
