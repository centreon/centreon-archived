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
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration');
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
