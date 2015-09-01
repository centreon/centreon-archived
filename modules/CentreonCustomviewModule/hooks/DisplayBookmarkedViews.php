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
namespace CentreonCustomview\Hooks;

use Centreon\Internal\Di;
use CentreonCustomview\Repository\CustomviewRepository;

class DisplayBookmarkedViews
{
    /**
     * Execute hook
     *
     * @param array $params
     */
    public static function execute($params)
    {
        $router = Di::getDefault()->get('router');
        if (!preg_match("/^\/centreon-customview/", $router->getCurrentUri())) {
            return;
        }
        $user = $_SESSION['user'];
        $bookmarkedViews = CustomviewRepository::getCustomViewsOfUser($user->getId());
        $publicViews = CustomviewRepository::getPublicViews();
        foreach ($publicViews as $viewId => $view) {
            if (isset($bookmarkedViews[$viewId])) {
                unset($publicViews[$viewId]);
            }
        }
        return array(
            'template' => 'displayLeftMenu.tpl',
            'variables' => array(
                'bookmarkedViews' => $bookmarkedViews,
                'publicViews' => $publicViews
            )
        );
    }
}
