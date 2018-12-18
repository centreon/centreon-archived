<?php

namespace CentreonRemote\Application\Webservice;

use Centreon\Domain\Entity\Task;
use Centreon\Domain\Repository\InformationsRepository;
use Exception;

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
     * @SWG\Post(
     *   path="/centreon/api/internal.php",
     *   operationId="getTaskStatus",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       type="string",
     *       description="the name of the API object class",
     *       required=true,
     *       enum="centreon_task_service",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       type="string",
     *       description="the name of the action in the API class",
     *       required=true,
     *       enum="postGetTaskStatus",
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="task_id",
     *       type="string",
     *       description="Id of the task you want to get status of",
     *       required=true,
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="JSON"
     *   )
     * )
     *
     * Get Status of task
     *
     * @return array
     * @example ['success' => true, 'status' => 'status of the task']
     *
     * @throws \RestBadRequestException
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
     * @SWG\Post(
     *   path="/centreon/api/external.php",
     *   operationId="getTaskStatusByParent",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       type="string",
     *       description="the name of the API object class",
     *       required=true,
     *       enum="centreon_task_service",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       type="string",
     *       description="the name of the action in the API class",
     *       required=true,
     *       enum="getTaskStatusByParent",
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="parent_id",
     *       type="string",
     *       description="Id of the task you want to get status of",
     *       required=true,
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="JSON"
     *   )
     * )
     *
     * Get Status of task by parent
     *
     * @return array
     * @example ['success' => true, 'status' => 'status of the task']
     *
     * @throws \RestBadRequestException
     */
    public function postGetRemoteTaskStatusByParent(): array
    {
        if (!isset($this->arguments['server_ip']) ||
            !isset($this->arguments['centreon_folder']) ||
            !isset($this->arguments['parent_id'])
        ) {
            throw new \RestBadRequestException('Missing arguments');
        }

        $result = $this->getDi()['centreon.taskservice']
            ->getRemoteStatusByParent(
                $this->arguments['parent_id'],
                $this->arguments['server_ip'],
                $this->arguments['centreon_folder']
            );

        return ['success' => true, 'status' => $result];
    }

    /**
     * Find task status by parent id (used on remote server)
     *
     * @return array
     * @example ['success' => true, 'status' => 'status of the task']
     *
     * @throws \RestBadRequestException
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
     * @SWG\Post(
     *   path="/centreon/api/external.php",
     *   operationId="addImportTaskWithParent",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       type="string",
     *       description="the name of the API object class",
     *       required=true,
     *       enum="centreon_task_service",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       type="string",
     *       description="the name of the action in the API class",
     *       required=true,
     *       enum="getTaskStatusByParent",
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="parent_id",
     *       type="string",
     *       description="the id of the task you want to attach subtask",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="params",
     *       type="string",
     *       description="serialize data for task must contain list of pollers and server address",
     *       required=true,
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="JSON"
     *   )
     * )
     *
     * Add new import task with parent ID
     *
     * @return array
     * @example ['success' => true, 'status' => 'status of the task']
     *
     * @throws \RestBadRequestException
     */
    public function postAddImportTaskWithParent(): array
    {
        if (!isset($this->arguments['parent_id'])) {
            throw new \RestBadRequestException('Missing parent_id parameter');
        }

        /*
         * make sure only authorized master can create task
         */
        $authorizedMaster = $this->getDi()['centreon.db-manager']->getRepository(InformationsRepository::class)
            ->getOneByKey('authorizedMaster');
        $authorizedMasterTab = explode(',', $authorizedMaster->getValue());

        // if client is behind proxy or firewall, source ip can be updated
        // then, we try to get HTTP_X_FORWARDED_FOR
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $originIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $originIp = $_SERVER['REMOTE_ADDR'];
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $authorizedMasterTab)) {
            throw new \RestUnauthorizedException($originIp . ' is not authorized on this remote server');
        } 

        $parentId = $this->arguments['parent_id'];
        $params = isset($this->arguments['params']) ? $this->arguments['params'] : '';

        // try to unserialize params string to array
        if (!$params = unserialize($params)) {
            $params = [];
        }

        // add new task
        $result = $this->getDi()['centreon.taskservice']->addTask(Task::TYPE_IMPORT, $params, $parentId);

        return ['success' => true, 'status' => $result];
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
}
