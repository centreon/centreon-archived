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

use Centreon\Domain\Entity\Task;
use Centreon\Domain\Repository\InformationsRepository;
use Exception;

/**
 * @OA\Tag(name="centreon_task_service", description="")
 */
class CentreonTaskService extends CentreonWebServiceAbstract
{

    /**
     * Name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_task_service';
    }

    /**
     * @OA\Post(
     *   path="/internal.php?object=centreon_task_service&action=getTaskStatus",
     *   description="Get Status of task",
     *   tags={"centreon_task_service"},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_task_service"},
     *          default="centreon_task_service"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"getTaskStatus"},
     *          default="getTaskStatus"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          required={
     *              "task_id"
     *          },
     *          @OA\Property(
     *              property="task_id",
     *              type="string",
     *              description="Id of the task you want to get status of"
     *          )
     *       )
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="JSON",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="boolean"),
     *          @OA\Property(property="status", type="string")
     *       )
     *   )
     * )
     *
     * Get Status of task
     *
     * @return array<string,bool|string>
     * @throws \RestBadRequestException
     * @example ['success' => true, 'status' => 'status of the task']
     *
     */
    public function postGetTaskStatus(): array
    {
        if (!isset($this->arguments['task_id'])) {
            throw new \RestBadRequestException('Missing argument task_id');
        }

        $result = $this->getDi()['centreon.taskservice']->getStatus($this->arguments['task_id']);

        return ['success' => true, 'status' => $result];
    }

    /**
     * Find task status by parent id (used on remote server)
     *
     * @return array<string,bool|string>
     * @throws \RestBadRequestException
     * @example ['success' => true, 'status' => 'status of the task']
     *
     */
    public function postGetTaskStatusByParent(): array
    {
        if (!isset($this->arguments['parent_id'])) {
            throw new \RestBadRequestException('Missing argument parent_id');
        }

        $result = $this->getDi()['centreon.taskservice']->getStatusByParent($this->arguments['parent_id']);

        return ['success' => true, 'status' => $result];
    }

    /**
     * Authorize to access to the action
     *
     * @param string $action The action name
     * @param \CentreonUser $user The current user
     * @param bool $isInternal If the api is call in internal
     *
     * @return bool If the user has access to the action
     */
    public function authorize($action, $user, $isInternal = false): bool
    {
        return true;
    }
}
