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

        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }

        if (isset($element['label_urlLoadPeriods'])) {
            $element['loadUrl'] = Di::getDefault()
                ->get('router')
                ->getPathFor($element['label_urlLoadPeriods'], $element['label_extra']);
        } else {
            $element['loadUrl'] = '';
        }

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
