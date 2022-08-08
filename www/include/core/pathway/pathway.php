<?php

/*
 * Copyright 2005-2020 Centreon
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

if (isset($url)) {
    /**
     * If url is defined we can use it to retrieve the associated page number
     * to show the right tree in case when we have just the topology parent
     * and we need to show breadcrumb for the first menu found (processed by
     * main.php).
     */
    $statementSelect = $pearDB->prepare(
        'SELECT topology_url FROM topology WHERE topology_page = :topology_page'
    );
    $statementSelect->bindValue(':topology_page', $p, \PDO::PARAM_INT);
    if ($statementSelect->execute()) {
        $result = $statementSelect->fetch(\PDO::FETCH_ASSOC);
        if ($result !== false && $result['topology_url'] != $url) {
            /**
             * If urls are not equal we can retrieve the topology page number
             * associated to this url because there is multiple topology page
             * number with the same URL.
             */
            $statement = $pearDB->prepare(
                'SELECT topology_page FROM topology '
                . 'WHERE topology_url = :url'
            );
            $statement->bindValue(':url', $url, \PDO::PARAM_STR);
            if ($statement->execute()
                && $result = $statement->fetch(\PDO::FETCH_ASSOC)
            ) {
                $p = $result['topology_page'];
            }
        }
    }
}

/**
 * Recursive query to retrieve tree details from child to parent
 */
$pdoStatement = $pearDB->prepare(
    'SELECT `topology_url`, `topology_url_opt`, `topology_parent`,
    `topology_name`, `topology_page`, `is_react`
    FROM topology where topology_page IN
        (SELECT :topology_page
        UNION
        SELECT * FROM (
            SELECT @pv:=(
                    SELECT topology_parent
                    FROM topology
                    WHERE topology_page = @pv
            ) AS topology_parent FROM topology
            JOIN
            (SELECT @pv:=:topology_page) tmp
        ) a
    WHERE topology_parent IS NOT NULL)
    ORDER BY topology_page ASC'
);
$pdoStatement->bindValue(':topology_page', (int) $p, \PDO::PARAM_INT);

$breadcrumbData = [];
$basePath = '/' . trim(explode('main.get.php', $_SERVER['REQUEST_URI'])[0], "/");
/*
 * <a href="<?= $details['is_react'] ? "{$basePath}{$details['url']}" : "main.php?p={$page}{$details["opt"]}" ?>" class="pathWay"><?= _($details["name"]); ?></a>
 */

if ($pdoStatement->execute()) {
    while ($result = $pdoStatement->fetch(\PDO::FETCH_ASSOC)) {
        $isNameAlreadyInserted = array_search(
            $result['topology_name'],
            array_column($breadcrumbData, 'name')
        );

        if ($isNameAlreadyInserted) {
            /**
             * We don't show two items with the same name. So we remove the first
             * item with the same name (in tree with duplicate topology name,
             * the first has no url)
             */
            $breadcrumbDataArrayNames = array_column($breadcrumbData, 'name');
            $topologyNameSearch = array_search($result['topology_name'], $breadcrumbDataArrayNames);
            $breadcrumbTopologyResults = array_slice($breadcrumbData, $topologyNameSearch);
            $topology = array_pop($breadcrumbTopologyResults);
            unset($breadcrumbData[$topology['page']]);
        }

        $breadcrumbData[$result['topology_page']] = [
            'is_react' => $result['is_react'],
            'name' => $result['topology_name'],
            'url' => $result['topology_url'],
            'opt' => $result['topology_url_opt'],
            'page' => $result['topology_page']
        ];
    }
}
?>
<div class="pathway">
<?php
if ($centreon->user->access->page($p)) {
    $flag = '';
    foreach ($breadcrumbData as $page => $details) {
        echo $flag;
        ?>
        <a href="<?= $details['is_react'] ? "{$basePath}{$details['url']}" : "main.php?p={$page}{$details["opt"]}" ?>"<?= $details['is_react'] ? ' isreact="isreact"' : '' ?> class="pathWay"><?= _($details["name"]); ?></a>
        <?php
        $flag = '<span class="pathWayBracket" >  &nbsp;&#62;&nbsp; </span>';
    }

    if (isset($_GET["host_id"])) {
        echo '<span class="pathWayBracket" > &nbsp;&#62;&nbsp; </span>';
        echo getMyHostName(htmlentities($_GET["host_id"], ENT_QUOTES, "UTF-8"));
    }
}
?>
</div>
