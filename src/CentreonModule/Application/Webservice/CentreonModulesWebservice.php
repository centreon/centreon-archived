<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

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
     *   description="Get list of modules and widgets",
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
     *      @OA\JsonContent(
     *          @OA\Property(property="enabled", type="boolean"),
     *          @OA\Property(property="status", type="boolean")
     *      )
     *   )
     * )
     *
     * Get info for BAM module
     *
     * @return array<string,bool>
     */
    public function postGetBamModuleInfo(): array
    {
        $moduleInfoObj = $this->getDi()[\CentreonLegacy\ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION];
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
    public function authorize($action, $user, $isInternal = false): bool
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
