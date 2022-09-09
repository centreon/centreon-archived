<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

namespace Centreon\Application\Webservice;

use Centreon\Infrastructure\Webservice;

/**
 * @OA\Tag(name="openapi", description="Generate an OpenAPI documentation")
 * @codeCoverageIgnore
 */
class OpenApiWebservice extends Webservice\WebServiceAbstract implements
    Webservice\WebserviceAutorizePublicInterface
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

        $openapi = \OpenApi\scan(__DIR__ . '/../../../');

        echo $openapi->toYaml();
        exit;
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
