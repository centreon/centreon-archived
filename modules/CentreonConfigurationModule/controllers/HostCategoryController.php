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
 * For more information : contact@centreon.com
 *
 */

namespace CentreonConfiguration\Controllers;

class HostCategoryController extends \CentreonConfiguration\Controllers\BasicController
{
    protected $objectDisplayName = 'Hostcategory';
    protected $objectName = 'hostcategory';
    protected $objectBaseUrl = '/centreon-configuration/hostcategory';
    protected $objectClass = '\CentreonConfiguration\Models\Hostcategory';
    protected $repository = '\CentreonConfiguration\Repository\HostcategoryRepository';

    public static $relationMap = array(
        'hc_hosts' => '\CentreonConfiguration\Models\Relation\Host\Hostcategory',
        'hc_hosttemplates' => '\CentreonConfiguration\Models\Relation\Hosttemplate\Hostcategory'
    );
    
    protected $datatableObject = '\CentreonConfiguration\Internal\HostCategoryDatatable';
    public static $isDisableable = true;

    /**
     * List hostcategories
     *
     * @method get
     * @route /hostcategory
     */
    public function listAction()
    {
        parent::listAction();
    }
    
    /**
     * 
     * @method get
     * @route /hostcategory/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }

    /**
     * 
     * @method get
     * @route /hostcategory/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Update a hostcategory
     *
     *
     * @method post
     * @route /hostcategory/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a hostcategory
     *
     *
     * @method get
     * @route /hostcategory/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/centreon-configuration/hostcategory/add');
        parent::addAction();
    }

    /**
     * Add a hostcategory
     *
     *
     * @method post
     * @route /hostcategory/add
     */
    public function createAction()
    {
        parent::createAction();
    }
    
    /**
     * Update a hostcategory
     *
     *
     * @method get
     * @route /hostcategory/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /hostcategory/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /hostcategory/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate a hosts
     *
     * @method POST
     * @route /hostcategory/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /hostcategory/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for hostcategory
     *
     * @method post
     * @route /hostcategory/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
    
    /**
     * Enable action for hostcategory
     * 
     * @method post
     * @route /hostcategory/enable
     */
    public function enableAction()
    {
        parent::enableAction('hc_activate');
    }
    
    /**
     * Disable action for hostcategory
     * 
     * @method post
     * @route /hostcategory/disable
     */
    public function disableAction()
    {
        parent::disableAction('hc_activate');
    }

    /**
     * Host for specific host category
     *
     * @method get
     * @route /hostcategory/[i:id]/host
     */
    public function hostForHostcategoryAction()
    {
        parent::getRelations(static::$relationMap['hc_hosts']);
    }

    /**
     * Host for specific host category
     *
     * @method get
     * @route /hostcategory/[i:id]/hosttemplate
     */
    public function hosttemplateForHostcategoryAction()
    {
        parent::getRelations(static::$relationMap['hc_hosttemplates']);
    }
    
    /**
     * Get list of hostcategories for a specific host
     *
     *
     * @method get
     * @route /hostcategory/[i:id]/icon
     */
    public function iconForHostAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $finalIconList = array();
        $iconId = \CentreonConfiguration\Models\Hostcategory::get($requestParam['id'], "icon_id");
        
        if (is_array($iconId) && (count($iconId) > 0)) {
        
            $icon = \Centreon\Models\Image::getIcon($iconId['icon_id']);

            if (count($icon) > 0) {
                $filenameExploded = explode('.', $icon['filename']);
                $nbOfOccurence = count($filenameExploded);
                $fileFormat = $filenameExploded[$nbOfOccurence-1];
                $filenameLength = strlen($icon['filename']);
                $routeAttr = array(
                    'image' => substr($icon['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                    'format' => '.'.$fileFormat
                );
                $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
                $finalIconList = array(
                    "id" => $icon['binary_id'],
                    "text" => $icon['filename'],
                    "theming" => '<img src="'.$imgSrc.'" style="width:20px;height:20px;"> '.$icon['filename']
                );
            }
        
        }
        
        $router->response()->json($finalIconList);
        
    }
}
