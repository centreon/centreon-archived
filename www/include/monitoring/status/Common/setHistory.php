<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

require_once realpath(__DIR__ . "/../../../../../config/centreon.config.php");

$path = _CENTREON_PATH_ . "/www";
require_once("$path/class/centreon.class.php");
require_once("$path/class/centreonSession.class.php");
require_once("$path/class/centreonDB.class.php");

$DB = new CentreonDB();

CentreonSession::start();
if (!CentreonSession::checkSession(session_id(), $DB)) {
    print "Bad Session ID";
    exit();
}

$centreon = $_SESSION['centreon'];

if (isset($_POST["url"])) {
    $url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
    if (!empty($url)) {
        if (isset($_POST["search"])) {
            $search = filter_input(
                INPUT_POST,
                'search',
                FILTER_SANITIZE_STRING
            );
            $centreon->historySearchService[$url] = $search;
        }

        if (isset($_POST["search_host"])) {
            $searchHost = filter_input(
                INPUT_POST,
                'seach_host',
                FILTER_SANITIZE_STRING
            );
            $centreon->historySearch[$url] = $searchHost;
        }

        if (isset($_POST["search_output"])) {
            $searchOutput = filter_input(
                INPUT_POST,
                'search_output',
                FILTER_SANITIZE_STRING
            );
            $centreon->historySearchOutput[$url] = $searchOutput;
        }

        $defaultLimit = $centreon->optGen['maxViewConfiguration'] >= 1
            ? (int)$centreon->optGen['maxViewConfiguration']
            : 30;
        if (isset($_POST["limit"])) {
            $limit = filter_input(
                INPUT_POST,
                'limit',
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'default' => $defaultLimit]]
            );
            $centreon->historyLimit[$url] = $limit;
        }

        if (isset($_POST["page"])) {
            $page = filter_input(
                INPUT_POST,
                'page',
                FILTER_VALIDATE_INT
            );
            if (false !== $page) {
                $centreon->historyPage[$url] = $page;
            }
        }
    }
}
