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
use CentreonAdministration\Repository\NotificationWayRepository;
use CentreonAdministration\Repository\ContactRepository;

/**
 * @author Kevin Duret <kduret@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class NotificationWay extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        $notificationWays = NotificationWayRepository::getNotificationWays();
        
        $contactId = $element['label_extra']['id'];
        $currentNotificationWays = ContactRepository::getContactInfo($contactId, false);

        $tpl = Di::getDefault()->get('template');

        $tpl->addCss('select2.css')
            ->addCss('select2-bootstrap.css');

        $tpl->addJs('jquery.select2/select2.min.js')
            ->addJs('centreon-clone.js')
            ->addJs('component/notificationWay.js');

        $tpl->assign('notificationWays', $notificationWays);
        $tpl->assign('currentNotificationWays', $currentNotificationWays);

        return array(
            'html' => $tpl->fetch('file:[Core]/form/component/notificationway.tpl'),
        );
    }
}
