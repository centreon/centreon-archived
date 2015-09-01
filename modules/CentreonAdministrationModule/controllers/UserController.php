<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace CentreonAdministration\Controllers;

use Centreon\Internal\Di;
use CentreonAdministration\Models\User as UserModel;
use CentreonAdministration\Internal\User;
use Centreon\Controllers\FormController;
use CentreonAdministration\Models\Relation\User\Timezone;
use Centreon\Internal\Exception;

class UserController extends FormController
{
    protected $objectDisplayName = 'User';
    public static $objectName = 'user';
    public static $enableDisableFieldName = 'is_activated';
    protected $objectBaseUrl = '/centreon-administration/user';
    protected $datatableObject = '\CentreonAdministration\Internal\UserDatatable';
    protected $objectClass = '\CentreonAdministration\Models\User';
    protected $repository = '\CentreonAdministration\Repository\UserRepository';
    
    public static $relationMap = array('user_timezone' => "\CentreonAdministration\Models\Relation\User\Timezone");
    
    public static $isDisableable = true;

    /**
     * Update a user
     *
     *
     * @method post
     * @route /user/update
     */
    public function updateAction()
    {
        parent::updateAction();

        /* Let's see if we need to refresh the user object that is stored in session */
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            $userId = $user->getId();
            $givenParameters = $this->getParams('post');
            /* Modified account matches the current user */
            if (isset($givenParameters['object_id']) && $givenParameters['object_id'] == $userId) {
                $_SESSION['user'] = new User($userId); 
            }
        }
    }
    
    /**
     * lock action for user
     * 
     * @method post
     * @route /user/lock
     */
    public function lockAction()
    {
        parent::lockAction('is_locked');
    }
    
    /**
     * unlock action for user
     * 
     * @method post
     * @route /user/unlock
     */
    public function unlockAction()
    {
        parent::unlockAction('is_locked');
    }
    
    /**
     * Get list of pollers for a specific host
     *
     *
     * @method get
     * @route /user/[i:id]/language
     */
    public function languageForUserAction()
    {
        parent::getSimpleRelation('language_id', '\CentreonAdministration\Models\Language');
    }
    
    /**
     * Get list of timezones for a specific user
     * 
     * @method get
     * @route /user/[i:id]/timezonesForUser
     */
    public function timezonesForUserAction()
    {
        parent::getRelations(static::$relationMap['user_timezone']);
    }
    /**
     * @method post
     * @route /user/settimezone
     */
    public function settimezoneAction()
    {
        $insertSuccess = true;
        $errorMessage = '';
        
        $givenParameters = $this->getParams('post');
        $router = Di::getDefault()->get('router');
        $repository = $this->repository;
        $user = $_SESSION['user'];
        $userId = $user->getId();

        $infoToInsert = array(
            'user_id' => $userId,
            'timezone_id' => $givenParameters['select_name']
        );
        
        try {
            $repository::settimezone($infoToInsert);
        }  catch (\Exception $e) {
            $insertSuccess = false;
            $errorMessage = $e->getMessage();
        }

        $this->router->response()->json(
            array(
                'success' => $insertSuccess,
                'error' => $errorMessage
            )
        );
    }
    
    
    /**
     * Delete a object
     *
     * Response JSON
     *
     * @method post
     * @route /user/delete
     */
    public function deleteAction()
    {
        
        $deleteSuccess = true;
        $errorMessage = '';
        
        try {
            $params = $this->router->request()->paramsPost();
            $repository = $this->repository;
            $repository::delete($params['ids'],$_SESSION['user']);
        } catch (Exception $e) {
            $deleteSuccess = false;
            $errorMessage = $e->getMessage();
        }
        
        $this->router->response()->json(
            array(
                'success' => $deleteSuccess,
                'errorMessage' => $errorMessage
            )
        );
    }
    
    
    
    
    /**
     * @method post
     * @route /user/deletetimezone
     */
    public function deletetimezoneAction()
    {
        $deleteSuccessful = true;
        $errorMessage = '';
        
        $givenParameters = $this->getParams('post');
        $router = Di::getDefault()->get('router');
        $repository = $this->repository;
        $user = $_SESSION['user'];
        $userId = $user->getId();

        $infoToDelete = array(
            'user_id' => $userId,
            'timezone_id' => $givenParameters['id']
        );

       
        try {
            $repository::deletetimezone($infoToDelete);
        }  catch (\Exception $e) {
            $deleteSuccessful = false;
            $errorMessage = $e->getMessage();
        }

        $this->router->response()->json(
            array(
                'success' => $deleteSuccessful,
                'errorMessage' => $errorMessage
            )
        );
    }
    
}
