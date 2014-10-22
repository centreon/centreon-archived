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
namespace CentreonAdministration\Controllers;

use Centreon\Internal\Form;
use Centreon\Internal\Form\Generator;
use CentreonAdministration\Models\Options;
use CentreonAdministration\Repository\OptionRepository;

/**
 * Description of OptionsController
 *
 * @author lionel
 */
class OptionsController extends \Centreon\Internal\Controller
{
    public static $moduleName = 'centreon-administration';
    
    /**
     * @method get
     * @route /administration/options/centreon
     */
    public function centreonAction()
    {
        //
        $objectFormUpdateUrl = '/administration/options/centreon/update';
        
        $myForm = new Generator($objectFormUpdateUrl);
        
        // get object Current Values
        $myForm->setDefaultValues(Options::getList());
        
        // Display page
        $this->tpl->assign('pageTitle', 'Centreon Options');
        $this->tpl->assign('form', $myForm->getFormHandler()->toSmarty());
        $this->tpl->assign('formComponents', $myForm->getFormComponents());
        $this->tpl->assign('hookParams', array('pollerId' => 1));
        $this->tpl->assign('formName', $myForm->getName());
        $this->tpl->assign('validateUrl', $objectFormUpdateUrl);
        $this->tpl->display('editoptions.tpl');
    }
    
    /**
     * @method post
     * @route /administration/options/centreon/update
     */
    public function updateAction()
    {
        $givenParameters = clone $this->getParams('post');
        
        $validationResult = Form::validate("form", $this->getUri(), self::$moduleName, $givenParameters);
        if ($validationResult['success']) {
            if (isset($givenParameters['token'])) {
                unset($givenParameters['token']);
            }
            
            OptionRepository::update($givenParameters);
            
            unset($_SESSION['form_token']);
            unset($_SESSION['form_token_time']);
            $this->router->response()->json(array('success' => true));
        } else {
            $this->router->response()->json(array('success' => false,'error' => $validationResult['error']));
        }
    }
}
