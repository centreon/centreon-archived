<?php
namespace CentreonModule\Application\Webservice;

use CentreonModule\Application\Webservice\CentreonModuleWebservice;

/**
 * @deprecated CentreonModulesWebservice is alias of CentreonModuleWebservice to provide back compatibility for a puller wizard
 *
 * @OA\Tag(name="centreon_modules_webservice", description="This object is deprecated and it's an alias to centreon_module")
 */
class CentreonModulesWebservice extends CentreonModuleWebservice
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
}
