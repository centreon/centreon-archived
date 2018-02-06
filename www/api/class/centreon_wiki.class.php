<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreon-knowledge/wikiApi.class.php";
require_once dirname(__FILE__) . "/webService.class.php";

class CentreonWiki extends CentreonWebService
{
    /**
     * @return array
     */
    public function postCheckConnection()
    {
        $sql_host = explode(':', $this->arguments['host']);
        $host = $sql_host[0];
        $port = isset($sql_host[1]) ? $sql_host[1] : '3306';
        $user = $this->arguments['user'];
        $password = $this->arguments['pwd'];
        $db = $this->arguments['name'];

        try {
            new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db, $user, $password);
            $outcome = true;
            $message = _('Connection Successful');
        } catch (PDOException $e) {
            $outcome = false;
            $message = $e->getMessage();
        }

        return array(
            'outcome' => $outcome,
            'message' => $message
        );
    }

    /**
     * @return array
     */
    public function postDeletePage()
    {
        $wikiApi = new WikiApi();
        $result =  $wikiApi->deletePage($this->arguments['title']);

        return array(
            'result' => $result
        );
    }
}
