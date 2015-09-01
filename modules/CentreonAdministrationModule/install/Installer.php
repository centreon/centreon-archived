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

namespace CentreonAdministration\Install;

use Centreon\Internal\Installer\Module\AbstractModuleInstaller;
use Centreon\Internal\Exception;
use Centreon\Internal\Di;



/**
 * 
 */
class Installer extends AbstractModuleInstaller
{
    /**
     *
     * @var string 
     */
    protected $objectName = 'user';
    
    /**
     *
     * @var string 
     */
    protected $objectClass = '\CentreonAdministration\Models\User';
    
    /**
     *
     * @var string 
     */
    protected $repository = '\CentreonAdministration\Repository\UserRepository';
    
    /**
     *
     * @var array 
     */
    public static $relationMap = array();
    
    /**
     * 
     * @param type $moduleDirectory
     * @param type $moduleInfo
     * @param type $launcher
     */
    public function __construct($moduleDirectory, $moduleInfo, $launcher)
    {
        parent::__construct($moduleDirectory, $moduleInfo, $launcher);
    }
    
    /**
     * 
     */
    public function customPreInstall()
    {
        
    }
    
    /**
     * 
     */
    public function customInstall()
    {
        $repository = $this->repository;
        try {
            $repository::setRelationMap(static::$relationMap);
            $repository::setObjectName($this->objectName);
            $repository::setObjectClass($this->objectClass);
            //$repository::setSaveEvents(false);
            $user = $repository::checkUser('admin', 'centreon');
        } catch (Exception $e) {
            $adminUser = array(
                'firstname' => 'admin',
                'lastname' => 'admin',
                'login' => 'admin',
                'password' => 'centreon',
                'is_admin' => 1,
                'is_locked' => 0,
                'is_activated' => 1,
                'is_password_old' => 1
            );
            $repository::create($adminUser, "", "", false, false);
        }
    }
    
    /**
     * 
     */
    public function customRemove()
    {
        
    }
}
