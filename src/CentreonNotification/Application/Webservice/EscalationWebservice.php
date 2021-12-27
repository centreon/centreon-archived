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

namespace CentreonNotification\Application\Webservice;

use Centreon\Infrastructure\Webservice;
use CentreonNotification\Application\Serializer;
use CentreonNotification\Domain\Repository;
use Centreon\ServiceProvider;

/**
 * @OA\Tag(name="centreon_escalation", description="Resource for authorized access")
 */
class EscalationWebservice extends Webservice\WebServiceAbstract implements
    Webservice\WebserviceAutorizeRestApiInterface
{
    use Webservice\DependenciesTrait;

    /**
     * Name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_escalation';
    }

    /**
     * {@inheritdoc}
     *
     * @return array<ServiceProvider::CENTREON_PAGINATION>
     */
    public static function dependencies(): array
    {
        return [
            ServiceProvider::CENTREON_PAGINATION
        ];
    }

    /**
     * @OA\Get(
     *   path="/external.php?object=centreon_escalation&action=list",
     *   description="Get list of escalations",
     *   tags={"centreon_escalation"},
     *   security={{"Session": {}}},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_escalation"},
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"list"}
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
     *       description="filter the list by name of the entity"
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="searchByIds",
     *       @OA\Schema(
     *          type="string"
     *       ),
     *       description="filter by IDs for more than one separate them with a comma sign"
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="offset",
     *       @OA\Schema(
     *          type="integer"
     *       ),
     *       description="the argument specifies the offset of the first row to return"
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="limit",
     *       @OA\Schema(
     *          type="integer"
     *       ),
     *       description="maximum entities in the list"
     *   ),
     *   @OA\Response(
     *       response="200",
     *       description="OK",
     *       @OA\JsonContent(
     *          @OA\Property(
     *              property="entities",
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/EscalationEntity")
     *          ),
     *          @OA\Property(
     *              property="pagination",
     *              ref="#/components/schemas/Pagination"
     *          )
     *      )
     *   )
     * )
     *
     * Get list of escalations
     *
     * @throws \RestBadRequestException
     * @return \Centreon\Application\DataRepresenter\Response
     */
    public function getList()
    {
        // extract post payload
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
        $pagination->setRepository(Repository\EscalationRepository::class);
        $pagination->setContext(Serializer\Escalation\ListContext::context());
        $pagination->setFilters($filters);
        $pagination->setLimit($limit);
        $pagination->setOffset($offset);

        return $pagination->getResponse();
    }
}
