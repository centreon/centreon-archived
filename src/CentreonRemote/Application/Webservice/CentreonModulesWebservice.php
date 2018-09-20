<?php

namespace CentreonRemote\Application\Webservice;

class CentreonModulesWebservice extends CentreonWebServiceAbstract
{

    /**
     * Name of web service object
     * 
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_modules_webservice';
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
}
