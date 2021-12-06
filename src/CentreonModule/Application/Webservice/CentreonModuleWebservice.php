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

use Centreon\Infrastructure\Webservice;
use Centreon\Application\DataRepresenter\Bulk;
use Centreon\Application\DataRepresenter\Response;
use CentreonModule\Application\DataRepresenter\ModuleEntity;
use CentreonModule\Application\DataRepresenter\ModuleDetailEntity;
use CentreonModule\Application\DataRepresenter\UpdateAction;
use CentreonModule\ServiceProvider;

/**
 * @OA\Tag(name="centreon_module", description="Resource for authorized access")
 */
class CentreonModuleWebservice extends Webservice\WebServiceAbstract implements
    Webservice\WebserviceAutorizeRestApiInterface
{

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
        if (!$user->admin && $user->access->page('50709') === 0) {
            return false;
        }
        return true;
    }

    /**
     * @OA\Get(
     *   path="/internal.php?object=centreon_module&action=list",
     *   description="Get list of modules and widgets",
     *   tags={"centreon_module"},
     *   security={{"Session": {}}},
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
     *                  @OA\Property(
     *                      property="entities",
     *                      type="array",
     *                      @OA\Items(ref="#/components/schemas/ModuleEntity")
     *                  ),
     *                  @OA\Property(
     *                      property="pagination",
     *                      ref="#/components/schemas/Pagination"
     *                  )
     *              ),
     *              @OA\Property(property="widget", type="object",
     *                  @OA\Property(
     *                      property="entities",
     *                      type="array",
     *                      @OA\Items(ref="#/components/schemas/ModuleEntity")
     *                  ),
     *                  @OA\Property(
     *                      property="pagination",
     *                      ref="#/components/schemas/Pagination"
     *                  )
     *              ),
     *              @OA\Property(property="status", type="boolean")
     *          )
     *      )
     *   )
     * )
     *
     * Get list of modules and widgets
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

        $list = $this->getDi()[ServiceProvider::CENTREON_MODULE]
            ->getList($search, $installed, $updated, $typeList);

        $result = new Bulk($list, null, null, null, ModuleEntity::class);

        $response = new Response($result);

        return $response;
    }

    /**
     * @OA\Get(
     *   path="/internal.php?object=centreon_module&action=details",
     *   description="Get details of modules and widgets",
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
     *          enum={"details"},
     *          default="details"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="id",
     *       @OA\Schema(
     *          type="string"
     *       ),
     *       description="ID of a module or a widget",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="type",
     *       @OA\Schema(
     *          type="string",
     *          enum={
     *              "module",
     *              "widget"
     *          }
     *       ),
     *       description="type of object",
     *       required=true
     *   ),
     *   @OA\Response(
     *      response="200",
     *      description="OK",
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="result", ref="#/components/schemas/ModuleDetailEntity"),
     *              @OA\Property(property="status", type="boolean")
     *          )
     *      )
     *   )
     * )
     *
     * Get details of module/widget
     *
     * @throws \RestBadRequestException
     * @return []
     */
    public function getDetails()
    {
        // extract post payload
        $request = $this->query();

        $id = isset($request['id']) && $request['id'] ? $request['id'] : null;
        $type = isset($request['type']) ? $request['type'] : null;

        $detail = $this->getDi()[ServiceProvider::CENTREON_MODULE]
            ->getDetail($id, $type);

        $result = null;
        $status = false;

        if ($detail !== null) {
            $result = new ModuleDetailEntity($detail);
            $status = true;
        }

        $response = new Response($result, $status);

        return $response;
    }

    /**
     * @OA\Post(
     *   path="/internal.php?object=centreon_module&action=install",
     *   summary="Install module or widget",
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
     *          enum={"install"},
     *          default="install"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="id",
     *       @OA\Schema(
     *          type="string"
     *       ),
     *       description="ID of a module or a widget",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="type",
     *       @OA\Schema(
     *          type="string",
     *          enum={
     *              "module",
     *              "widget"
     *          }
     *       ),
     *       description="type of object",
     *       required=true
     *   ),
     *   @OA\Response(
     *       response="200",
     *       description="OK",
     *       @OA\JsonContent(
     *          @OA\Property(property="result", ref="#/components/schemas/UpdateAction"),
     *          @OA\Property(property="status", type="boolean")
     *       )
     *   )
     * )
     *
     * Install module or widget
     *
     * @throws \RestBadRequestException
     * @return []
     */
    public function postInstall()
    {
        // extract post payload
        $request = $this->query();

        $id = isset($request['id']) && $request['id'] ? $request['id'] : '';
        $type = isset($request['type']) ? $request['type'] : '';

        $status = false;
        $result = null;
        $entity = null;

        try {
            $entity = $this->getDi()[ServiceProvider::CENTREON_MODULE]
                ->install($id, $type);
        } catch (\Exception $e) {
            $result = new UpdateAction(null, $e->getMessage());
        }

        if ($entity !== null) {
            $result = new UpdateAction($entity);
            $status = true;
        }

        $response = new Response($result, $status);

        return $response;
    }

    /**
     * @OA\Post(
     *   path="/internal.php?object=centreon_module&action=update",
     *   summary="Update module or widget",
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
     *          enum={"update"},
     *          default="update"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="id",
     *       @OA\Schema(
     *          type="string"
     *       ),
     *       description="ID of a module or a widget",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="type",
     *       @OA\Schema(
     *          type="string",
     *          enum={
     *              "module",
     *              "widget"
     *          }
     *       ),
     *       description="type of object",
     *       required=true
     *   ),
     *   @OA\Response(
     *       response="200",
     *       description="OK",
     *       @OA\JsonContent(
     *          @OA\Property(property="result", ref="#/components/schemas/UpdateAction"),
     *          @OA\Property(property="status", type="boolean")
     *       )
     *   )
     * )
     *
     * Update module or widget
     *
     * @throws \RestBadRequestException
     * @return []
     */
    public function postUpdate()
    {
        // extract post payload
        $request = $this->query();

        $id = isset($request['id']) && $request['id'] ? $request['id'] : '';
        $type = isset($request['type']) ? $request['type'] : '';

        $status = false;
        $result = null;
        $entity = null;

        try {
            $entity = $this->getDi()[ServiceProvider::CENTREON_MODULE]
                ->update($id, $type);
        } catch (\Exception $e) {
            $result = new UpdateAction(null, $e->getMessage());
        }

        if ($entity !== null) {
            $result = new UpdateAction($entity);
            $status = true;
        }

        $response = new Response($result, $status);

        return $response;
    }

    /**
     * @OA\Delete(
     *   path="/internal.php?object=centreon_module&action=remove",
     *   summary="Remove module or widget",
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
     *          enum={"remove"},
     *          default="remove"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="id",
     *       @OA\Schema(
     *          type="string"
     *       ),
     *       description="ID of a module or a widget",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="type",
     *       @OA\Schema(
     *          type="string",
     *          enum={
     *              "module",
     *              "widget"
     *          }
     *       ),
     *       description="type of object",
     *       required=true
     *   ),
     *   @OA\Response(
     *       response="200",
     *       description="OK",
     *       @OA\JsonContent(
     *          @OA\Property(property="result", type="string"),
     *          @OA\Property(property="status", type="boolean")
     *       )
     *   )
     * )
     *
     * Remove module or widget
     *
     * @throws \RestBadRequestException
     * @return []
     */
    public function deleteRemove()
    {
        // extract post payload
        $request = $this->query();

        $id = isset($request['id']) && $request['id'] ? $request['id'] : '';
        $type = isset($request['type']) ? $request['type'] : '';

        $status = false;
        $result = null;

        try {
            $this->getDi()[ServiceProvider::CENTREON_MODULE]
                ->remove($id, $type);

            $status = true;
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        $response = new Response($result, $status);

        return $response;
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
