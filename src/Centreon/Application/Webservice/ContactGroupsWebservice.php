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

use Centreon\Application\DataRepresenter\ContactGroupEntity;
use Centreon\Application\DataRepresenter\Response;
use Centreon\ServiceProvider;
use Centreon\Domain\Repository\ContactGroupRepository;
use Centreon\Infrastructure\Webservice;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;

/**
 * @OA\Tag(name="centreon_contact_groups", description="Web Service for Contact Groups")
 */
class ContactGroupsWebservice extends Webservice\WebServiceAbstract implements
    Webservice\WebserviceAutorizeRestApiInterface
{

    /**
     * Name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_contact_groups';
    }

    /**
     * @OA\Get(
     *   path="/internal.php?object=centreon_contact_groups&action=list",
     *   description="Get contact groups list",
     *   tags={"centreon_contact_groups"},
     *   security={{"Session": {}}},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_contact_groups"},
     *          default="centreon_contact_groups"
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
     *       name="name",
     *       @OA\Schema(
     *          type="string"
     *       ),
     *       description="filter the list by name",
     *       required=false
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="id",
     *       @OA\Schema(
     *          type="string"
     *       ),
     *       description="filter the list by id",
     *       required=false
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="offset",
     *       @OA\Schema(
     *          type="integer"
     *       ),
     *       description="offset",
     *       required=false
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="limit",
     *       @OA\Schema(
     *          type="integer"
     *       ),
     *       description="limit",
     *       required=false
     *   ),
     *   @OA\Response(
     *       response="200",
     *       description="OK",
     *       @OA\JsonContent(
     *          @OA\Property(
     *              property="entities",
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ImageEntity")
     *          ),
     *          @OA\Property(
     *              property="pagination",
     *              ref="#/components/schemas/Pagination"
     *          )
     *      )
     *   )
     * )
     *
     * Get contact groups list
     *
     * @throws \RestBadRequestException
     * @return array
     */
    public function getList(): Response
    {
        /*
         * process request
         */
        $request = $this->query();

        $limit = isset($request['limit']) ? (int) $request['limit'] : null;
        $offset = isset($request['offset']) ? (int) $request['offset'] : null;
        $sortField = isset($request['sortf']) ? $request['sortf'] : null;
        $sortOrder = isset($request['sorto']) ? $request['sorto'] : 'ASC';

        $filters = [];

        if (isset($request['search']) && $request['search']) {
            $filters['search'] = $request['search'];
        }

        if (isset($request['searchByIds']) && $request['searchByIds']) {
            $filters['ids'] = explode(',', $request['searchByIds']);
        }

        $pagination = $this->services->get(ServiceProvider::CENTREON_PAGINATION);
        $pagination->setRepository(ContactGroupRepository::class);
        $pagination->setDataRepresenter(ContactGroupEntity::class);
        $pagination->setFilters($filters);
        $pagination->setLimit($limit);
        $pagination->setOffset($offset);
        $pagination->setOrder($sortField, $sortOrder);

        return $pagination->getResponse();
    }

    /**
     * Extract services that are in use only
     *
     * @param \Pimple\Container $di
     */
    public function setDi(Container $di)
    {
        $this->services = new ServiceLocator($di, [
            ServiceProvider::CENTREON_PAGINATION,
        ]);
    }
}
