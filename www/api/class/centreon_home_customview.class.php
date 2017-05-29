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
require_once _CENTREON_PATH_ . 'www/class/centreonCustomView.class.php';

class CentreonHomeCustomview extends CentreonWebService
{
    /**
     * CentreonHomeCustomview constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getListSharedViews()
    {
        global $centreon;

        $views = array();

        $query = 'SELECT custom_view_id, name FROM ('
            . 'SELECT cv.custom_view_id, cv.name FROM custom_views cv '
            . 'INNER JOIN custom_view_user_relation cvur ON cv.custom_view_id = cvur.custom_view_id '
            . 'WHERE (cvur.user_id = ' . $centreon->user->user_id . ' '
            . 'OR cvur.usergroup_id IN ( '
                . 'SELECT contactgroup_cg_id '
                . 'FROM contactgroup_contact_relation '
                . 'WHERE contact_contact_id = ' . $centreon->user->user_id . ' '
                . ') '
            . ') '
            . 'UNION '
            . 'SELECT cv2.custom_view_id, cv2.name FROM custom_views cv2 '
            . 'WHERE cv2.public = 1 ) as d '
            . 'WHERE d.custom_view_id NOT IN ('
            . 'SELECT cvur2.custom_view_id FROM custom_view_user_relation cvur2 '
            . 'WHERE cvur2.user_id = ' . $centreon->user->user_id . ' '
            . 'AND cvur2.is_consumed = 1) ';

        $dbResult = $this->pearDB->query($query);

        while ($row = $dbResult->fetchRow()) {
            $views[] = array(
                'id' => $row['custom_view_id'],
                'text' => $row['name']
            );
        }

        return array(
            'items' => $views,
            'total' => count($views)
        );
    }

    /**
     * @return array
     */
    public function getLinkedUsers()
    {
        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $customViewId = 0;
        } else {
            $customViewId = $this->arguments['q'];
        }

        global $centreon;
        $viewObj = new CentreonCustomView($centreon, $this->pearDB);

        return $viewObj->getUsersFromViewId($customViewId);
    }

    /**
     * @return array
     */
    public function getLinkedUsergroups()
    {
        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $customViewId = 0;
        } else {
            $customViewId = $this->arguments['q'];
        }

        global $centreon;
        $viewObj = new CentreonCustomView($centreon, $this->pearDB);

        return $viewObj->getUsergroupsFromViewId($customViewId);
    }

    /**
     * Get the list of views
     *
     * @return array
     */
    public function getListViews()
    {
        global $centreon;
        $viewObj = new CentreonCustomView($centreon, $this->pearDB);

        $tabs = array();
        $tabsDb = $viewObj->getCustomViews();
        foreach ($tabsDb as $key => $tab) {
            $tabs[] = array(
                'default' => false,
                'name' => $tab['name'],
                'custom_view_id' => $tab['custom_view_id'],
                'public' => $tab['public'],
                'nbCols' => $tab['layout']
            );
        }

        return array(
            'current' => $viewObj->getCurrentView(),
            'tabs' => $tabs
        );
    }
}
