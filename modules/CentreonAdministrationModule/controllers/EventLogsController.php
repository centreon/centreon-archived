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
