<?php
/*
 * Copyright 2005-2015 MERETHIS
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
