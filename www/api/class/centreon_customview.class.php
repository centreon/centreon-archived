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

require_once dirname(__FILE__) . "/webService.class.php";

class CentreonCustomview extends CentreonWebService
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the list of views
     */
    public function getListPublic()
    {
        global $centreon;

        $arrayViewUse = array();

        $query = "SELECT cv.* FROM custom_views cv "
            . " INNER JOIN custom_view_user_relation cvur on cv.custom_view_id = cvur.custom_view_id "
            . " WHERE (cvur.user_id = " . $centreon->user->user_id
            . "        OR cvur.usergroup_id IN ( "
            . "           SELECT contactgroup_cg_id "
            . "           FROM contactgroup_contact_relation "
            . "           WHERE contact_contact_id = " . $centreon->user->user_id
            . "           ) "
            . " ) AND cvur.is_consumed = 1 "
            . " AND (cvur.is_public = 1 OR  cvur.is_share = 1 OR  cv.public = 1)";

        $DBRES = $this->pearDB->query($query);

        while ($row = $DBRES->fetchRow()) {
            $arrayViewUse[$row['custom_view_id']] = $row['name'];
        }
        $query = "SELECT cv.*, '1' as from_public FROM custom_views cv where public = 1 "
            . " UNION "
            . " SELECT cv.*, '0' as from_public FROM custom_views cv "
            . " INNER JOIN custom_view_user_relation cvur on cv.custom_view_id = cvur.custom_view_id "
            . " WHERE (cvur.user_id = " . $centreon->user->user_id
            . "        OR cvur.usergroup_id IN ( "
            . "           SELECT contactgroup_cg_id "
            . "           FROM contactgroup_contact_relation "
            . "           WHERE contact_contact_id = " . $centreon->user->user_id
            . "           ) "
            . " ) AND cvur.is_consumed = 0  AND cvur.is_share = 1 ";

        $DBRES = $this->pearDB->query($query);
        $arrayView = array();
        $arrayViewShared = array();

        while ($row = $DBRES->fetchRow()) {
            if ($row['from_public'] == '1') {
                $arrayView[$row['custom_view_id']] = $row['name'];
            } else {
                $arrayViewShared[$row['custom_view_id']] = $row['name'];
            }
        }

        $arrayViewShared = array_diff($arrayViewShared, $arrayViewUse);
        $arrayView = array_diff($arrayView, $arrayViewUse);

        $arrayView = array_diff($arrayView, $arrayViewShared);

        asort($arrayView);

        $list = array();
        foreach ($arrayView as $id => $view) {
            $list[] = array(
                'id' => $id,
                'text' => $view
            );
        }

        return array(
            'items' => $list,
            'total' => count($arrayView)
        );
    }

    public function getListShare()
    {
        global $centreon;

        $arrayViewUse = array();

        $query = "SELECT cv.* FROM custom_views cv "
            . " INNER JOIN custom_view_user_relation cvur on cv.custom_view_id = cvur.custom_view_id "
            . " WHERE (cvur.user_id = " . $centreon->user->user_id
            . "        OR cvur.usergroup_id IN ( "
            . "           SELECT contactgroup_cg_id "
            . "           FROM contactgroup_contact_relation "
            . "           WHERE contact_contact_id = " . $centreon->user->user_id
            . "           ) "
            . " ) AND cvur.is_consumed = 1 "
            . " AND (cvur.is_public = 1 OR  cvur.is_share = 1 OR  cv.public = 1)";

        $DBRES = $this->pearDB->query($query);

        while ($row = $DBRES->fetchRow()) {
            $arrayViewUse[$row['custom_view_id']] = $row['name'];
        }

        $query = "SELECT cv.*, '0' as from_public FROM custom_views cv "
            . " INNER JOIN custom_view_user_relation cvur on cv.custom_view_id = cvur.custom_view_id "
            . " WHERE (cvur.user_id = " . $centreon->user->user_id
            . "        OR cvur.usergroup_id IN ( "
            . "           SELECT contactgroup_cg_id "
            . "           FROM contactgroup_contact_relation "
            . "           WHERE contact_contact_id = " . $centreon->user->user_id
            . "           ) "
            . " ) AND cvur.is_consumed = 0  AND cvur.is_share = 1  ";


        $DBRES = $this->pearDB->query($query);
        $arrayViewShared = array();

        while ($row = $DBRES->fetchRow()) {
            $arrayViewShared[$row['custom_view_id']] = $row['name'];
        }

        $arrayViewShared = array_diff($arrayViewShared, $arrayViewUse);

        asort($arrayViewShared);

        $list = array();
        foreach ($arrayViewShared as $id => $view) {
            $list[] = array(
                'id' => $id,
                'text' => $view
            );
        }

        return array(
            'items' => $list,
            'total' => count($arrayViewShared)
        );
    }
}
