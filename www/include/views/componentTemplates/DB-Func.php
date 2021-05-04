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

if (!isset($centreon)) {
    exit();
}

function NameHsrTestExistence($name = null)
{
    global $pearDB, $form;

    $formValues = array();
    $bindParams = array();

    if (isset($form)) {
        $formValues = $form->getSubmitValues();
    }
    $query = "SELECT compo_id FROM `giv_components_template` WHERE `name` = '" . CentreonUtils::escapeSecure($name) . "'";

    if (!empty($formValues['host_id'])) {
        if (preg_match('/([0-9]+)-([0-9]+)/', $formValues['host_id'], $matches)) {
            $formValues['host_id'] = (int) $matches[1];
            $formValues['service_id'] = (int) $matches[2];
        } else {
            throw new \InvalidArgumentException('host_id must be a combination of integers');
        }
    }

    if (!empty($formValues)) {
        $bindParams = sanitizeFormComponentTemplatesParameters($formValues);
    }

    if (!empty($bindParams['host_id']) && !empty($bindParams['service_id'])) {
        $query .= " AND host_id = ? AND service_id = ?";
        $result = $pearDB->query($query, array($bindParams['host_id'], $bindParams['service_id']));
    } else {
        $query .= " AND host_id IS NULL AND service_id IS NULL";
        $result = $pearDB->query($query);
    }

    $compo = $result->fetchRow();

    if ($result->numRows() >= 1 && $compo['compo_id'] === $formValues['compo_id']) {
        return true;
    } elseif ($result->numRows() >= 1 && $compo['compo_id'] !== $formValues['compo_id']) {
        return false;
    } else {
        return true;
    }
}

function DsHsrTestExistence($name = null)
{
    global $pearDB, $form;
    $formValues = array();
    if (isset($form)) {
        $formValues = $form->getSubmitValues();
    }
    $query = "SELECT compo_id FROM giv_components_template WHERE name = '" . CentreonUtils::escapeSecure($name) . "'";

    if (!empty($formValues['host_id'])) {
        if (preg_match('/([0-9]+)-([0-9]+)/', $formValues['host_id'], $matches)) {
            $formValues['host_id'] = (int) $matches[1];
            $formValues['service_id'] = (int) $matches[2];
        } else {
            throw new \InvalidArgumentException('host_id must be a combination of integers');
        }
    }

    if (!empty($formValues)) {
        $bindParams = sanitizeFormComponentTemplatesParameters($formValues);
    }

    if (!empty($bindParams['host_id']) && !empty($bindParams['service_id'])) {
        $query .= " AND host_id = ? AND service_id = ?";
        $result = $pearDB->query($query, array($bindParams['host_id'], $bindParams['service_id']));
    } else {
        $query .= " AND host_id IS NULL AND service_id IS NULL";
        $result = $pearDB->query($query);
    }

    $compo = $result->fetchRow();

    if ($result->numRows() >= 1 && $compo['compo_id'] === $formValues['compo_id']) {
        return true;
    } elseif ($result->numRows() >= 1 && $compo['compo_id'] !== $formValues['compo_id']) {
        return false;
    } else {
        return true;
    }
}

function checkColorFormat($color)
{
    if ($color != "" && strncmp($color, '#', 1)) {
        return false;
    } else {
        return true;
    }
}

function deleteComponentTemplateInDB($compos = array())
{
    global $pearDB;
    foreach ($compos as $key => $value) {
        $DBRESULT = $pearDB->query("DELETE FROM giv_components_template WHERE compo_id = '".$key."'");
    }
    defaultOreonGraph();
}

function defaultOreonGraph()
{
    global $pearDB;
    $DBRESULT = $pearDB->query("SELECT DISTINCT compo_id FROM giv_components_template WHERE default_tpl1 = '1'");
    if (!$DBRESULT->numRows()) {
        $DBRESULT2 = $pearDB->query("UPDATE giv_components_template SET default_tpl1 = '1' LIMIT 1");
    }
}

function noDefaultOreonGraph()
{
    global $pearDB;
    $rq = "UPDATE giv_components_template SET default_tpl1 = '0'";
    $DBRESULT = $pearDB->query($rq);
}

function multipleComponentTemplateInDB($compos = array(), $nbrDup = array())
{
    global $pearDB;
    foreach ($compos as $key => $value) {
        $query = 'SELECT * FROM giv_components_template WHERE compo_id = ' . (int) $key . ' LIMIT 1';
        $result = $pearDB->query($query);
        $row = $result->fetchRow();
        $row["compo_id"] = '';
        $row["default_tpl1"] = '0';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "name" ? ($name = $value2 = $value2."_".$i) : null;
                $val ? $val .= ($value2!=null?(", '".$value2."'"):", NULL") : $val .= ($value2!=null?("'".$value2."'"):"NULL");
            }
            if (NameHsrTestExistence($name)) {
                $val ? $rq = "INSERT INTO giv_components_template VALUES (".$val.")" : $rq = null;
                $DBRESULT2 = $pearDB->query($rq);
            }
        }
    }
}

function updateComponentTemplateInDB($compoId = null)
{
    if (!$compoId) {
        return;
    }
    updateComponentTemplate($compoId);
}

function insertComponentTemplateInDB()
{
    $compoId = insertComponentTemplate();
    return ($compoId);
}

function insertComponentTemplate()
{
    global $form, $pearDB;
    $formValues = array();
    $formValues = $form->getSubmitValues();

    if (
        (isset($formValues['ds_filled']) && $formValues['ds_filled'] === '1') &&
        (!isset($formValues['ds_color_area']) || empty($formValues['ds_color_area']))
    ) {
        $formValues['ds_color_area'] = $formValues['ds_color_line'];
    }

    list($formValues['host_id'], $formValues['service_id']) = parseHostIdPostParameter($formValues['host_id']);

    $bindParams = sanitizeFormComponentTemplatesParameters($formValues);

    // Build Query Dynamically
    $query = 'INSERT INTO `giv_components_template` (`compo_id`, ';
    $query .= implode(', ', array_keys($bindParams));
    $query .= ') VALUES (NULL, ';
    for($i = 0; $i < count($bindParams); $i++) {
        $query .= '?, ';
    }
    $query = rtrim($query, ', ');
    $query .= ')';


    $pearDB->query($query, $bindParams);
    defaultOreonGraph();
    $DBRESULT = $pearDB->query("SELECT MAX(compo_id) FROM giv_components_template");
    $compo_id = $DBRESULT->fetchRow();
    return ($compo_id["MAX(compo_id)"]);
}

function updateComponentTemplate($compoId = null)
{
    if (!$compoId) {
        return;
    }
    global $form, $pearDB;
    $formValues = array();
    $formValues = $form->getSubmitValues();

    if (
        (isset($formValues['ds_filled']) && $formValues['ds_filled'] === '1') &&
        (!isset($formValues['ds_color_area']) || empty($formValues['ds_color_area']))
    ) {
        $formValues['ds_color_area'] = $formValues['ds_color_line'];
    }

    list($formValues['host_id'], $formValues['service_id']) = parseHostIdPostParameter($formValues['host_id']);

    $bindParams = sanitizeFormComponentTemplatesParameters($formValues);

    // Building the query dynamically
    $query = 'UPDATE `giv_components_template` SET ';
    foreach(array_keys($bindParams) as $parameter) {
        $query .= $parameter . " = ?, ";
    }
    $query = rtrim($query, ', ');
    $query .= ' WHERE compo_id = ' . (int) $compoId;

    $pearDB->query($query, $bindParams);

    defaultOreonGraph();
}

/**
 * Sanitize all the component templates parameters from the component template form
 * and return a ready to bind array.
 *
 * @param array $ret
 * @return array $bindParams
 */
function sanitizeFormComponentTemplatesParameters($ret = array())
{
    $bindParams = array();
    foreach ($ret as $inputName => $inputValue) {
        switch ($inputName) {
            case 'name':
            case 'ds_name':
            case 'ds_color_line':
            case 'ds_color_area':
            case 'ds_color_area_warn':
            case 'ds_color_area_crit':
            case 'ds_legend':
            case 'comment':
            case 'ds_transparency':
                if (!empty($inputValue)) {
                    $inputValue = filter_var($inputValue, FILTER_SANITIZE_STRING);
                    if (empty($inputValue)) {
                        $bindParams[$inputName] = null;
                    } else {
                        $bindParams[$inputName] = $inputValue;
                    }
                }
                break;
            case 'ds_color_line_mode':
                $bindParams[$inputName] = in_array($inputValue[$inputName], array('0', '1'))
                    ? $inputValue[$inputName]
                    : '0';
                break;
            case 'ds_max':
            case 'ds_min':
            case 'ds_minmax_int':
                $bindParams[$inputName] = in_array($inputValue, array('0', '1'))
                    ? $inputValue
                    : null;
                break;
            case 'ds_average':
            case 'ds_last':
            case 'ds_total':
            case 'ds_stack':
            case 'ds_invert':
            case 'ds_filled':
            case 'ds_hidecurve':
                $bindParams[$inputName] = in_array($inputValue, array('0', '1'))
                    ? $inputValue
                    : '0';
                break;
            case 'ds_jumpline':
                $bindParams[$inputName] = in_array($inputValue, array('0', '1', '2', '3'))
                    ? $inputValue
                    : '0';
                break;
            case 'host_id':
            case 'service_id':
            case 'ds_tickness':
            case 'ds_order':
                $bindParams[$inputName] = (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                    ? null
                    : (int) $inputValue;
                break;
            case 'default_tpl1':
                $bindParams[$inputName] = (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                    ? null
                    : (int) $inputValue;
                defaultOreonGraph();
                break;
        }
    }
    return $bindParams;
}

/**
 * Parses the host_id parameter from the form and checks the hostId-serviceId format
 * and returns the hostId et serviceId when defined.
 *
 * @param string|null $hostIdParameter
 * @return array
 */
function parseHostIdPostParameter($hostIdParameter = null)
{
    if (!empty($hostIdParameter)) {
        if (preg_match('/([0-9]+)-([0-9]+)/', $hostIdParameter, $matches)) {
            $hostId = (int) $matches[1];
            $serviceId = (int) $matches[2];
        } else {
            throw new \InvalidArgumentException('host_id must be a combination of integers');
        }
    } else {
        $hostId = null;
        $serviceId = null;
    }

    return array($hostId, $serviceId);
}