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

namespace CentreonBam\Controllers;

use Centreon\Internal\Di;
use Centreon\Controllers\FormController;

class BusinessViewController extends FormController
{
    protected $objectDisplayName = 'BusinessView';
    public static $objectName = 'businessview';
    protected $objectClass = '\CentreonBam\Models\BusinessView';
    protected $datatableObject = '\CentreonBam\Internal\BusinessViewDatatable';
    protected $repository = '\CentreonBam\Repository\BusinessViewRepository';     
    public static $relationMap = array();

    /**
     *
     * @method get
     * @route /businessview/realtime
     */
    public function displayAction()
    {
        $repository = $this->repository;
        $buList = $repository::getBuList();

        // Add css
        $this->tpl->addCss('select2.css')
			->addCss('select2-bootstrap.css')
            ->addCss('gridstack.css','centreon-bam')
			->addCss('bam.css','centreon-bam');

        // Add js
        $this->tpl->addJs('jquery.min.js')
            ->addJs('jquery-ui.min.js')
			->addJs('d3.min.js')
            ->addJs('underscore-min.js','bottom','centreon-bam')
            ->addJs('jquery.easing.min.js','bottom','centreon-bam')
        	->addJs('gridstack.js','bottom','centreon-bam')
        	->addJs('bam.js','bottom','centreon-bam');

        // Send values to Smarty
        $this->tpl->assign('buList', $buList);

        // Display template
        $this->tpl->display("businessview.tpl");
    }


	/**
     *
     * @method get
     * @route /businessview/configuration
     */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('hogan-3.0.0.min.js')
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addCss('centreon.tag.css', 'centreon-administration');
        $urls = array(
            'tag' => array(
                'add' => $router->getPathFor('/centreon-administration/tag/add'),
                'del' => $router->getPathFor('/centreon-administration/tag/delete')
            )
        );
        $this->tpl->append('jsUrl', $urls, true);
        parent::listAction();
    }
}
