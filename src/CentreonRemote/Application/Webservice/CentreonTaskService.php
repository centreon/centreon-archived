<?php

namespace CentreonRemote\Application\Webservice;

use CentreonRemote\Application\Validator\WizardConfigurationRequestValidator;
use CentreonRemote\Domain\Service\ConfigurationWizard\LinkedPollerConfigurationService;
use Centreon\Domain\Entity\Task;
use CentreonRemote\Domain\Service\ConfigurationWizard\PollerConfigurationRequestBridge;
use CentreonRemote\Domain\Service\ConfigurationWizard\ServerConnectionConfigurationService;
use CentreonRemote\Domain\Value\ServerWizardIdentity;

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
     *   operationId="postGetTaskStatus",
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
        $task_id = isset($_POST['task_id']);

        if ($task_id){
            $result = $this->getDi()['centreon.taskservice']->getStatus($task_id);
        }

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
        if (parent::authorize($action, $user, $isInternal)) {
            return true;
        }

        return $user && $user->hasAccessRestApiConfiguration();
    }
}
