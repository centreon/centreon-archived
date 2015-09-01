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

namespace CentreonAdministration\Controllers;

use Centreon\Internal\Di;
use Centreon\Internal\Controller;

class EventLogsController extends Controller
{

    /**
     * Get the storage of eventlogs
     *
     * @method get
     * @route /eventlogs/storage
     */
    public function getEventlogsStorageAction()
    {
        $label = array(
            'database' => 'Database',
            'elasticsearch' => 'Elastic Search'
        );
        $di = Di::getDefault();
        $storage = $di->get('config')->get('default', 'eventlogs');
        $values = array(
            'id' => 'database',
            'text' => 'Database'
        );
        if (false === is_null($storage)) {
            $values = array(
                'id' => $storage,
                'text' => $label[$storage]
            );
        }
        $this->router->response()->json($values);
    }

    /**
     * Get the elasticsearch security
     *
     * @method get
     * @route /eventlogs/es_security
     */
    public function getEventlogsEsSecurityAction()
    {
        $label = array(
            'none' => 'None',
            'http' => 'Http Authentication'
        );
        $di = Di::getDefault();
        $security = $di->get('config')->get('default', 'es_security');
        $values = array(
            'id' => 'none',
            'text' => 'None'
        );
        if (false === is_null($security)) {
            $values = array(
                'id' => $security,
                'text' => $label[$security]
            );
        } 
        $this->router->response()->json($values);
    }
}
