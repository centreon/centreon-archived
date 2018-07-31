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

/*
 * Path to the configuration dir
 */
$path = "./include/views/graphs/";

/*
 * Include Pear Lib
 */

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

function getGetPostValue($str)
{
    $value = null;
    if (isset($_GET[$str]) && $_GET[$str]) {
        $value = $_GET[$str];
    }
    if (isset($_POST[$str]) && $_POST[$str]) {
        $value = $_POST[$str];
    }
    return urldecode($value);
}

$svc_id = getGetPostValue('chartId');
list($hostId, $svcId) = explode('_', $svc_id);

/* Get host and service name */
$svcName = '';
$query = 'SELECT h.name, s.description FROM hosts h, services s WHERE h.host_id = ' . CentreonDB::escape($hostId) .
    ' AND s.service_id = ' . CentreonDB::escape($svcId) . ' AND h.host_id = s.host_id';
$res = $pearDBO->query($query);
if (false === PEAR::isError($res)) {
    $row = $res->fetchRow();
    $svcName = $row['name'] . ' - ' . $row['description'];
}

$periods = array(
    array(
      'short' => '1d',
      'long' => _("last day")
    ),
    array(
      'short' => '7d',
      'long' => _("last week")
    ),
    array(
      'short' => '31d',
      'long' => _("last month")
    ),
    array(
      'short' => '1y',
      'long' => _("last year")
    )
);

$tpl->assign('periods', $periods);
$tpl->assign('svc_id', $svc_id);
$tpl->assign('srv_name', $svcName);

$tpl->display("graph-periods.html");
