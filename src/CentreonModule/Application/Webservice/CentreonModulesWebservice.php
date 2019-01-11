<?php
namespace CentreonModule\Application\Webservice;

use CentreonRemote\Application\Webservice\CentreonWebServiceAbstract;

/**
 * @OA\Tag(name="centreon_modules_webservice", description="Resource for external public access")
 */
class CentreonModulesWebservice extends CentreonWebServiceAbstract
{

    /**
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
        return 'centreon_modules_webservice';
    }
}
