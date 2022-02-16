<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace Centreon\Application\Webservice;

use Centreon\Application\DataRepresenter;
use Centreon\ServiceProvider;
use Centreon\Domain\Repository\AclGroupRepository;
use Centreon\Infrastructure\Webservice;
use Centreon\Application\Serializer;

/**
 * @OA\Tag(name="centreon_acl_group", description="Web Service for ACL Groups")
 */
class AclGroupWebservice extends Webservice\WebServiceAbstract implements
    Webservice\WebserviceAutorizeRestApiInterface
{
    use Webservice\DependenciesTrait;

    /**
     * @var mixed
     */
    private $services;

    /**
     * {@inheritdoc}
     */
    public static function getName(): string
    {
        return 'centreon_acl_group';
    }

    /**
     * {@inheritdoc}
     * @return array<int,string>
     */
    public static function dependencies(): array
    {
        return [
            ServiceProvider::CENTREON_PAGINATION,
        ];
    }

    /**
     * @OA\Get(
     *   path="/internal.php?object=centreon_acl_group&action=list",
     *   description="Get a list of ACL groups",
     *   tags={"centreon_acl_group"},
     *   security={{"Session": {}}},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_acl_group"},
     *          default="centreon_acl_group"
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
     *              @OA\Items(ref="#/components/schemas/AclGroupEntity")
     *          ),
     *          @OA\Property(
     *              property="pagination",
     *              ref="#/components/schemas/Pagination"
     *          )
     *      )
     *   )
     * )
     *
     * Get a list of ACL groups
     *
     * @return DataRepresenter\Response
     * @throws \RestBadRequestException
     */
    public function getList(): DataRepresenter\Response
    {
        /*
         * process request
         */
        $request = $this->query();

        $limit = isset($request['limit']) ? (int) $request['limit'] : null;
        $offset = isset($request['offset']) ? (int) $request['offset'] : null;

        $filters = [];

        if (isset($request['search']) && $request['search']) {
            $filters['search'] = $request['search'];
        }

        if (isset($request['searchByIds']) && $request['searchByIds']) {
            $filters['ids'] = explode(',', $request['searchByIds']);
        }

        $pagination = $this->services->get(ServiceProvider::CENTREON_PAGINATION);
        $pagination->setRepository(AclGroupRepository::class);
        $pagination->setContext(Serializer\AclGroup\ListContext::context());
        $pagination->setFilters($filters);
        $pagination->setLimit($limit);
        $pagination->setOffset($offset);

        return $pagination->getResponse();
    }
}
