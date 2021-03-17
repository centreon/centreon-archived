<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace CentreonRemote\Application\Webservice;

use Centreon\Domain\Repository\TopologyRepository;

/**
 * @OA\Tag(name="centreon_acl_webservice", description="")
 */
class CentreonAclWebservice extends CentreonWebServiceAbstract
{
    /**
     * Name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_acl_webservice';
    }

    /**
     * @OA\Get(
     *   path="/internal.php?object=centreon_acl_webservice&action=getCurrentAcl",
     *   description="Get list of ACLs",
     *   tags={"centreon_acl_webservice"},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_acl_webservice"},
     *          default="centreon_acl_webservice"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"getCurrentAcl"},
     *          default="getCurrentAcl"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\Response(
     *      response="200",
     *      description="OK",
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(type="array", items={"type": "string"})
     *      )
     *   )
     * )
     *
     * @return []
     */
    public function getGetCurrentAcl()
    {
        $user = $_SESSION['centreon']->user;
        if (empty($user)) {
            return [];
        }
        return $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
            ->getRepository(TopologyRepository::class)
            ->getReactTopologiesPerUserWithAcl($user);
    }
}
