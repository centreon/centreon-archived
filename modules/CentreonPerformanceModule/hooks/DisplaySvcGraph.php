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

namespace CentreonPerformance\Hooks;

use Centreon\Internal\Di;

/**
 * Hook for display a service hook
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonPerformance
 */
class DisplaySvcGraph
{
    /**
     * Execute hook
     *
     * @param array $params The parameters for hooks
     */
    public static function execute($params)
    {
        return array(
            'template' => 'displaySvcGraph.tpl',
            'variables' => $params
        );
    }

    /**
     * Add the javascript file for this hook
     *
     * @param \Centreon\Internal\Template $tpl The current template
     */
    public static function addJs($tpl)
    {
        $router = Di::getDefault()->get('router');
        $tpl->addJs('centreon.graph.js', 'bottom', 'centreon-performance')
            ->addJs('d3.min.js')
            ->addJs('c3.min.js');
        $tpl->append(
            'jsUrl',
            array(
                'graph' => $router->getPathFor('/centreon-performance/graph')
            ),
            true
        );
    }

    /**
     * Add the css file for this hook
     *
     * @param \Centreon\Internal\Template $tpl The current template
     */
    public static function addCss($tpl)
    {
        $tpl->addCss('c3.css');
    }
}
