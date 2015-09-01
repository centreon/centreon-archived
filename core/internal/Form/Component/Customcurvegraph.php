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

namespace Centreon\Internal\Form\Component;

use Centreon\Internal\Di;
use CentreonPerformance\Repository\GraphTemplate as GraphTemplateRepository;

/**
 * Astract for custom form for graph
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
abstract class Customcurvegraph extends Component
{
    /**
     * @var string The template name for render input
     */
    protected static $templateName = null;

    /**
     * Render the html input
     *
     * @param array $element The element to render
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        $di = Di::getDefault();
        /* Add javascript file for clone */
        $tpl = $di->get('template');

        $tpl->addJs('centreon-clone.js')
            ->addJs('component/customcurvegraph.js')
            ->addJs('spectrum.js');

        $tpl->addCss('spectrum.css');

        /* Load default values */
        $listMetrics = array();
        if (isset($element['label_extra']) && isset($element['label_extra']['id'])) {
            $graphTmplId = $element['label_extra']['id'];
            $listMetrics = GraphTemplateRepository::getMetrics($graphTmplId);
        }
        $tpl->assign('currentMetrics', $listMetrics);

        $tpl->assign('element', $element);

        return array(
            'html' => $tpl->fetch('file:[Core]/form/component/customcurvegraph.tpl'),
        );
    }
}
