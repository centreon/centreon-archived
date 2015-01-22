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

use Centreon\Internal\Di;
use CentreonConfiguration\Models\Hostcategory;
use Centreon\Models\Image;
use Centreon\Controllers\FormController;

class HostCategoryController extends FormController
{
    protected $objectDisplayName = 'Hostcategory';
    public static $objectName = 'hostcategory';
    public static $enableDisableFieldName = 'hc_activate';
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
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $finalIconList = array();
        $iconId = Hostcategory::get($requestParam['id'], "icon_id");
        
        if (is_array($iconId) && (count($iconId) > 0)) {
        
            $icon = Image::getIcon($iconId['icon_id']);

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
