<?php
/*
 * Copyright 2005-2014 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonAdministration\Controllers;

use CentreonAdministration\Models\User as UserModel;
use CentreonAdministration\Internal\User;
use Centreon\Controllers\FormController;

class UserController extends FormController
{
    protected $objectDisplayName = 'User';
    public static $objectName = 'user';
    public static $enableDisableFieldName = 'is_activated';
    protected $objectBaseUrl = '/centreon-administration/user';
    protected $datatableObject = '\CentreonAdministration\Internal\UserDatatable';
    protected $objectClass = '\CentreonAdministration\Models\User';
    protected $repository = '\CentreonAdministration\Repository\UserRepository';
    public static $relationMap = array();
    
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
}
