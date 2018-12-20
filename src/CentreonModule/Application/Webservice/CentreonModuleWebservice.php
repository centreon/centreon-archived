<?php
namespace CentreonModule\Application\Webservice;

use CentreonRemote\Application\Webservice\CentreonWebServiceAbstract;
use Centreon\Application\DataRepresenter\Bulk;
use Centreon\Application\DataRepresenter\Response;
use CentreonModule\Application\DataRepresenter\ModuleEntity;

class CentreonModuleWebservice extends CentreonWebServiceAbstract
{

    /**
     * @SWG\Get(
     *   path="/centreon/api/internal.php",
     *   operationId="list",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       type="string",
     *       description="the name of the API object class",
     *       required=true,
     *       enum="centreon_task_service",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       type="string",
     *       description="the name of the action in the API class",
     *       required=true,
     *       enum="list",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="search",
     *       type="string",
     *       description="filter the result by name and keywords",
     *       required=false,
     *       enum="list",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="installed",
     *       type="boolean",
     *       description="filter the result by installed or non-installed modules",
     *       required=false,
     *       enum="list",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="updated",
     *       type="boolean",
     *       description="filter the result by updated or non-installed modules",
     *       required=false,
     *       enum="list",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="types",
     *       type="array",
     *       description="filter the result by type",
     *       required=false,
     *       enum="list",
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="JSON"
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
        $installed = isset($request['installed']) ? (bool) $request['installed'] : null;
        $updated = isset($request['updated']) ? (bool) $request['updated'] : null;
        $typeList = isset($request['types']) ? (array) $request['types'] : null;

        $list = $this->getDi()['centreon.module']
            ->getList($search, $installed, $updated, $typeList);
        
        //(new \ArrayObject($list))->getIterator()
        
        $result = new Bulk($list, null, null, null, ModuleEntity::class);

        $response = new Response($result);
        
        return $response;
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
     *       enum="centreon_modules_webservice"
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       description="the name of the action in the API class",
     *       required=true,
     *       type="string",
     *       enum="getBamModuleInfo"
     *   )
     *   @SWG\Response(
     *     response=200,
     *     description="JSON with BAM module info"
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
