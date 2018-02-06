<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

if (!isset($oreon)) {
    exit;
}

$searchOptions = array(
    'host' => 0,
    'service' => 0,
    'hostTemplate' => 0,
    'serviceTemplate' => 0,
    'poller' => 0,
    'hostgroup' => 0,
    'servicegroup' => 0,
    'hasNoProcedure' => 0,
    'templatesWithNoProcedure' => 0
);

$labels = array(
    'host' => _("Host"),
    'service' => _("Service"),
    'hostTemplate' => _("Host Template"),
    'serviceTemplate' => _("Service Template"),
    'poller' => _("Poller"),
    'hostgroup' => _("Hostgroup"),
    'servicegroup' => _("Servicegroup"),
    'hasNoProcedure' => _("Show wiki pageless only"),
    'templatesWithNoProcedure' => _("Show wiki pageless only - inherited templates included"),
    'search' => _("Search")
);

if ($currentPage  == "hosts") {
    $searchOptions['host'] = 1;
    $searchOptions['poller'] = 1;
    $searchOptions['hostgroup'] = 1;
    $searchOptions['hasNoProcedure'] = 1;
    $searchOptions['templatesWithNoProcedure'] = 1;
} elseif ($currentPage == "services") {
    $searchOptions['host'] = 1;
    $searchOptions['service'] = 1;
    $searchOptions['poller'] = 1;
    $searchOptions['hostgroup'] = 1;
    $searchOptions['servicegroup'] = 1;
    $searchOptions['hasNoProcedure'] = 1;
    $searchOptions['templatesWithNoProcedure'] = 1;
} elseif ($currentPage == "hostTemplates") {
    $searchOptions['hostTemplate'] = 1;
    $searchOptions['hasNoProcedure'] = 1;
    $searchOptions['templatesWithNoProcedure'] = 1;
} elseif ($currentPage == "serviceTemplates") {
    $searchOptions['serviceTemplate'] = 1;
    $searchOptions['hasNoProcedure'] = 1;
    $searchOptions['templatesWithNoProcedure'] = 1;
}

$tpl->assign('searchHost', isset($_REQUEST['searchHost']) ? $_REQUEST['searchHost'] : "");
$tpl->assign('searchService', isset($_REQUEST['searchService']) ? $_REQUEST['searchService'] : "");
$tpl->assign('searchHostTemplate', isset($_REQUEST['searchHostTemplate']) ? $_REQUEST['searchHostTemplate'] : "");
$tpl->assign(
    'searchServiceTemplate',
    isset($_REQUEST['searchServiceTemplate']) ? $_REQUEST['searchServiceTemplate'] : ""
);

$checked = "";
if (isset($_REQUEST['searchHasNoProcedure'])) {
    $checked = 'checked';
}
$tpl->assign('searchHasNoProcedure', $checked);

$checked2 = "";
if (isset($_REQUEST['searchTemplatesWithNoProcedure'])) {
    $checked2 = 'checked';
}
$tpl->assign('searchTemplatesWithNoProcedure', $checked2);


/**
 * Get Poller List
 */
if ($searchOptions['poller']) {
    $query = "SELECT id, name FROM nagios_server ORDER BY name";
    $res = $pearDB->query($query);
    $searchPoller = "<option value='0'></option>";
    while ($row = $res->fetchRow()) {
        if (isset($_REQUEST['searchPoller']) && $row['id'] == $_REQUEST['searchPoller']) {
            $searchPoller .= "<option value='".$row['id']."' selected>" . $row['name'] . "</option>";
        } else {
            $searchPoller .= "<option value='".$row['id']."'>" .$row['name']. "</option>";
        }
    }
    $tpl->assign('searchPoller', $searchPoller);
}

/**
 * Get Hostgroup List
 */
if ($searchOptions['hostgroup']) {
    $query = "SELECT hg_id, hg_name FROM hostgroup ORDER BY hg_name";
    $res = $pearDB->query($query);
    $searchHostgroup = "<option value='0'></option>";
    while ($row = $res->fetchRow()) {
        if (isset($_REQUEST['searchHostgroup']) && $row['hg_id'] == $_REQUEST['searchHostgroup']) {
            $searchHostgroup .= "<option value ='".$row['hg_id']."' selected>" . $row['hg_name'] . "</option>";
        } else {
            $searchHostgroup .= "<option value ='".$row['hg_id']."'>" . $row['hg_name'] . "</option>";
        }
    }
    $tpl->assign('searchHostgroup', $searchHostgroup);
}

/**
 * Get Servicegroup List
 */
if ($searchOptions['servicegroup']) {
    $query = "SELECT sg_id, sg_name FROM servicegroup ORDER BY sg_name";
    $res = $pearDB->query($query);
    $searchServicegroup = "<option value='0'></option>";
    while ($row = $res->fetchRow()) {
        if (isset($_REQUEST['searchServicegroup']) && $row['sg_id'] == $_REQUEST['searchServicegroup']) {
            $searchServicegroup .= "<option value ='".$row['sg_id']."' selected>" . $row['sg_name'] . "</option>";
        } else {
            $searchServicegroup .= "<option value ='".$row['sg_id']."'>" . $row['sg_name'] . "</option>";
        }
    }
    $tpl->assign('searchServicegroup', $searchServicegroup);
}

$tpl->assign('labels', $labels);
$tpl->assign('searchOptions', $searchOptions);
