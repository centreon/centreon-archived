<?php
/*
 * Copyright 2005-2019 Centreon
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
 *
 */

// set cache for pollers
$pollers = [];
$result = $pearDB->query('SELECT id, name FROM nagios_server');
while ($row = $result->fetch()) {
    $pollerName = strtolower($row['name']);
    $pollers[$pollerName] = $row['id'];
}

// get poller preferences of engine-status widget
$result = $pearDB->query(
    'SELECT wpr.widget_view_id, wpr.parameter_id, wpr.preference_value, wpr.user_id
    FROM widget_preferences wpr
    INNER JOIN widget_parameters wpa ON wpa.parameter_id = wpr.parameter_id
    AND wpa.parameter_code_name = \'poller\'
    INNER JOIN widget_models wm ON wm.widget_model_id = wpa.widget_model_id
    AND wm.title = \'Engine-Status\''
);

$statement = $pearDB->prepare(
    'UPDATE widget_preferences
    SET preference_value= :value
    WHERE widget_view_id = :view_id
    AND parameter_id = :parameter_id
    AND user_id = :user_id'
);

// update poller preferences from name to id
while ($row = $result->fetch()) {
    $pollerId = isset($pollers[$row['preference_value']])
        ? $pollers[$row['preference_value']]
        : '';

    $statement->bindValue(':value', $pollerId, \PDO::PARAM_STR);
    $statement->bindValue(':view_id', $row['widget_view_id'], \PDO::PARAM_INT);
    $statement->bindValue(':parameter_id', $row['parameter_id'], \PDO::PARAM_INT);
    $statement->bindValue(':user_id', $row['user_id'], \PDO::PARAM_INT);
    $statement->execute();
}
