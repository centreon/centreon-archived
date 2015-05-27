<?php
/*
 * Copyright 2005-2015 CENTREON
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

namespace CentreonConfiguration\Forms\Components;

use Centreon\Internal\Di;
use Centreon\Internal\Form\Component\Component;

/**
 * Component for select the period
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package CentreonConfiguration
 * @subpackage Form
 * @version 3.0.0
 */
class Scheduleddowntimecalendar extends Component
{
    /**
     * Generate and return the html for the element
     *
     * @param array $element The element to parse
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        $tpl = Di::getDefault()->get('template');
        $weeklyDays = array(
            0 => "sunday",
            1 => "monday",
            2 => "tuesday",
            3 => "wednesday",
            4 => "thursday",
            5 => "friday",
            6 => "saturday"
        );

        $tpl->assign('element', $element);
        $tpl->assign('weeklyDays', $weeklyDays);
        $tpl->addCss('centreon.scheduled-downtime.css', 'centreon-configuration');
        $tpl->addCss('bootstrap-datetimepicker.min.css');
        $tpl->addJs('centreon.scheduled-downtime.js', 'bottom', 'centreon-configuration');
        $tpl->addJs('hogan-3.0.0.min.js');
        $tpl->addJs('bootstrap-datetimepicker.min.js');
         
        return array(
            'html' => $tpl->fetch('file:[CentreonConfigurationModule]/forms/components/scheduled_downtime_calendar.tpl'),
        );
    }
}
