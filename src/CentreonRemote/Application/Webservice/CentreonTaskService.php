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
     * @return string
     *
     * @throws \RestBadRequestException
     */
    public function postGetTaskStatus()
    {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $task_id = (isset($_POST['task_id'])) ? $_POST['task_id'] : null;

        if ($task_id){
            $result = $this->getDi()['centreon.taskservice']->getStatus($task_id);
        }

        return json_encode(['success' => true, 'status'=> $result]);
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
     * @return string
     *
     * @throws \RestBadRequestException
     */
    public function postGetTaskStatusByParent()
    {
        $parent_id = (isset($_POST['parent_id'])) ? $_POST['parent_id'] : null;
        $result = '';

        if ($parent_id){
            $result = $this->getDi()['centreon.taskservice']->getStatusByParent($parent_id);
        }

        return json_encode(['success' => true, 'status'=> $result]);
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
     * @return string
     *
     * @throws \RestBadRequestException
     */
    public function postAddImportTaskWithParent()
    {
        /*
         * make sure only authorized master can create task
         */
        $authorizedMaster = $this->getDi()['centreon.db-manager']->getRepository(InformationsRepository::class)->getOneByKey('authorizedMaster');
        if ($_SERVER['REMOTE_ADDR'] !== $authorizedMaster->getValue()){
            return json_encode(['success' => true, 'status'=> 'unauthorized']);
        }

        $parent_id = (isset($_POST['parent_id'])) ? intval($_POST['parent_id']) : null;
        $params = (isset($_POST['params'])) ? $_POST['params'] : '';

        // try to unserialize params string to array
        if (!$params = unserialize($params)) {
            $params = [];
        }

        // input data validation
        try {
            if (!$parent_id) {
                throw new Exception('Missing parent_id parameter');
            }
        } catch (\Exception $ex) {
            return json_encode([
                    'success' => false,
                    'status' => $ex->getMessage(),
                ]);
        }

        // add new task
        $result = $this->getDi()['centreon.taskservice']
            ->addTask(Task::TYPE_IMPORT, $params, $parent_id)
        ;

        return json_encode(['success' => true, 'status'=> $result]);
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
    public function authorize($action, $user, $isInternal = false)
    {
        return true;
    }
}
