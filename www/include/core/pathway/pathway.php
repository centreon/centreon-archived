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

function getTopologyParent($p)
{
    global $pearDB;
    $rqPath = "SELECT `topology_url`, `topology_url_opt`, `topology_parent`, `topology_name`, `topology_page` FROM `topology` WHERE `topology_page` = '".$p."' ORDER BY `topology_page`";
    $DBRESULT = $pearDB->query($rqPath);
    
    $redirectPath = $DBRESULT->fetchRow();
    $DBRESULT->free();
    return $redirectPath;
}

function getTopologyDataPage($p)
{
    global $pearDB;
    $rqPath = "SELECT `topology_url`, `topology_url_opt`, `topology_parent`, `topology_name`, `topology_page` FROM `topology` WHERE `topology_page` = '".$p."' ORDER BY `topology_page`";
    $DBRESULT = $pearDB->query($rqPath);
    $redirectPath = $DBRESULT->fetchRow();
    $DBRESULT->free();
    return $redirectPath;
}

function getTopologyParentPage($p)
{
    global $pearDB;
    $rqPath = "SELECT `topology_parent` FROM `topology` WHERE `topology_page` = '".$p."'";
    $DBRESULT = $pearDB->query($rqPath);
    $redirectPath = $DBRESULT->fetchRow();
    $DBRESULT->free();
    return $redirectPath["topology_parent"];
}


$tab = getTopologyParent($p);
$tabPath = array();

$NameTopology = "";
if (!empty($tab["topology_name"])) {
    $NameTopology = _($tab["topology_name"]);
}

$tabPath[$tab["topology_page"]] = array();
$tabPath[$tab["topology_page"]]["name"] = $NameTopology;
$tabPath[$tab["topology_page"]]["opt"] = $tab["topology_url_opt"];
$tabPath[$tab["topology_page"]]["page"] = $tab["topology_page"];
$tabPath[$tab["topology_page"]]["url"] = $tab["topology_url"];

while ($tab["topology_parent"]) {
    $tab = getTopologyParent($tab["topology_parent"]);
    $tabPath[$tab["topology_page"]] = array();
    $tabPath[$tab["topology_page"]]["name"] = _($tab["topology_name"]);
    $tabPath[$tab["topology_page"]]["opt"] = $tab["topology_url_opt"];
    $tabPath[$tab["topology_page"]]["page"] = $tab["topology_page"];
    $tabPath[$tab["topology_page"]]["url"] = $tab["topology_url"];
}
ksort($tabPath);

$page = $p;
if (isset($tabPath[$p]) && !$tabPath[$p]["url"]) {
    while (1) {
        $DBRESULT = $pearDB->query("SELECT * FROM topology WHERE topology_page LIKE '".$page."%' AND topology_parent = '$page' ORDER BY topology_order, topology_page ASC");
        if (!$DBRESULT->numRows()) {
            break;
        }
        $new_url = $DBRESULT->fetchRow();
        $DBRESULT->free();
        $tabPath[$new_url["topology_page"]] = array();
        $tabPath[$new_url["topology_page"]]["name"] = _($new_url["topology_name"]);
        $tabPath[$new_url["topology_page"]]["opt"] = $new_url["topology_url_opt"];
        $tabPath[$new_url["topology_page"]]["page"] = $new_url["topology_page"];
        $page = $new_url["topology_page"];
        if (isset($new_url["topology_url"]) && $new_url["topology_url"]) {
            break;
        }
    }
}
    
/*
 * Not displaying two entries in a row having the same name
 */
$tmpLastTabKeyAndName = null;
foreach ($tabPath as $pageNumber => $tabInPath) {
    if ($tmpLastTabKeyAndName && $tabInPath['name'] === $tmpLastTabKeyAndName['name']) {
        unset($tabPath[$tmpLastTabKeyAndName['key']]);
    }
    $tmpLastTabKeyAndName = array('key' => $pageNumber, 'name' => $tabInPath['name']);
}

if ($centreon->user->access->page($p)) {
    $flag = '';
    foreach ($tabPath as $cle => $valeur) {
        echo $flag;
        ?>
        <a href="main.php?p=<?php echo $cle.$valeur["opt"]; ?>" class="pathWay"><?php print $valeur["name"]; ?></a>
        <?php
        $flag = '<span class="pathWayBracket" >  &nbsp;&#62;&nbsp; </span>';
    }

    if (isset($_GET["host_id"])) {
        echo '<span class="pathWayBracket" > &nbsp;&#62;&nbsp; </span>';
        echo getMyHostName(htmlentities($_GET["host_id"], ENT_QUOTES, "UTF-8"));
    }
}
?>
<hr>
