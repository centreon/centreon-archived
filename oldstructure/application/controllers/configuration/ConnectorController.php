<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : connector@centreon.com
 *
 */

namespace Controllers\Configuration;

use \Centreon\Core\Form;

class ConnectorController extends \Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'Connector';
    protected $objectName = 'connector';
    protected $objectBaseUrl = '/configuration/connector';
    protected $objectClass = '\Models\Configuration\Connector';

    /**
     * List connectors
     *
     * @method get
     * @route /configuration/connector
     * @acl view
     */
    public function listAction()
    {
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/connector/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/connector/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Create a new connector
     *
     * @method post
     * @route /configuration/connector/create
     * @acl add
     */
    public function createAction()
    {
        parent::createAction();
    }

    /**
     * Update a connector
     *
     *
     * @method post
     * @route /configuration/connector/update
     * @acl update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        
        if (Form::validateSecurity($givenParameters['token'])) {
            $connector = array(
                'name' => $givenParameters['name'],
                'description' => $givenParameters['description'],
                'command_line' => $givenParameters['command_line'],
                'enabled' => $givenParameters['enabled'],
            );
            
            $connObj = new \Models\Configuration\Connector();
            try {
                $connObj->update($givenParameters['id'], $connector);
            } catch (Exception $e) {
                echo "fail";
            }
            echo 'success';
        } else {
            echo "fail";
        }
    }
    
    /**
     * Add a connector
     *
     *
     * @method get
     * @route /configuration/connector/add
     * @acl add
     */
    public function addAction()
    {
        parent::addAction();
    }
    
    /**
     * Update a connector
     *
     *
     * @method get
     * @route /configuration/connector/[i:id]
     * @acl update
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/connector/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/connector/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate a hosts
     *
     * @method POST
     * @route /configuration/connector/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/connector/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for hosttemplate
     *
     * @method post
     * @route /configuration/connector/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }

    /**
     * Commands for specific connector
     *
     * @method get
     * @route /configuration/connector/[i:id]/command
     */
    public function commandsForConnectorAction()
    {
        parent::getSimpleRelation('connector_id', '\Models\Configuration\Command', true);
    }
}
