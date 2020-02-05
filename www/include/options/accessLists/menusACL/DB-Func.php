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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($centreon)) {
    exit();
}

/**
 * Indicates if the topology name has already been used
 *
 * @global \CentreonDB $pearDB
 * @global HTML_QuickFormCustom $form
 * @param string $topologyName
 * @return boolean Return false if the topology name has already been used
 */
function hasTopologyNameNeverUsed($topologyName = null)
{
    global $pearDB, $form;
    
    $topologyId = null;
    if (isset($form)) {
        $topologyId = $form->getSubmitValue('lca_id');
    }
    $prepareSelect = $pearDB->prepare(
        "SELECT acl_topo_name, acl_topo_id FROM `acl_topology` "
        . "WHERE acl_topo_name = :topology_name"
    );
    $prepareSelect->bindValue(
        ':topology_name',
        $topologyName,
        \PDO::PARAM_STR
    );
    if ($prepareSelect->execute()) {
        $result = $prepareSelect->fetch(\PDO::FETCH_ASSOC);
        $total = $prepareSelect->rowCount();
        if ($total >= 1 && $result["acl_topo_id"] == $topologyId) {
            /**
             * In case of modification, we need to return true
             */
            return true;
        } elseif ($total >= 1 && $result["acl_topo_id"] != $topologyId) {
            return false;
        } else {
            return true;
        }
    }
}

/**
 * Enable an ACL
 *
 * @global CentreonDB $pearDB
 * @global Centreon $centreon
 * @param int $aclTopologyId ACL topology id to enable
 * @param array $acls Array of ACL topology id to disable
 */
function enableLCAInDB($aclTopologyId = null, $acls = array())
{
    global $pearDB, $centreon;
    
    if (!is_int($aclTopologyId) && empty($acls)) {
        return;
    }
    if (is_int($aclTopologyId)) {
        $acls = array($aclTopologyId => "1");
    }

    foreach (array_keys($acls) as $currentAclTopologyId) {
        $prepareUpdate = $pearDB->prepare(
            "UPDATE `acl_topology` SET acl_topo_activate = '1' "
            . "WHERE `acl_topo_id` = :topology_id"
        );
        $prepareUpdate->bindValue(
            ':topology_id',
            $currentAclTopologyId,
            \PDO::PARAM_INT
        );
        
        if (!$prepareUpdate->execute()) {
            continue;
        }

        $prepareSelect = $pearDB->prepare(
            "SELECT acl_topo_name FROM `acl_topology` "
            . "WHERE acl_topo_id = :topology_id LIMIT 1"
        );
        $prepareSelect->bindValue(
            ':topology_id',
            $currentAclTopologyId,
            \PDO::PARAM_INT
        );
        
        if ($prepareSelect->execute()) {
            $result = $prepareSelect->fetch(PDO::FETCH_ASSOC);
            $centreon->CentreonLogAction->insertLog(
                "menu access",
                $currentAclTopologyId,
                $result['acl_topo_name'],
                "enable"
            );
        }
    }
}

/**
 * Disable an ACL
 *
 * @global CentreonDB $pearDB
 * @global Centreon $centreon
 * @param int $aclTopologyId ACL topology id to disable
 * @param array $acls Array of ACL topology id to disable
 */
function disableLCAInDB($aclTopologyId = null, $acls = array())
{
    global $pearDB, $centreon;

    if (!is_int($aclTopologyId) && empty($acls)) {
        return;
    }
    if (is_int($aclTopologyId)) {
        $acls = array($aclTopologyId => "1");
    }
    
    foreach (array_keys($acls) as $currentTopologyId) {
        $prepareUpdate = $pearDB->prepare(
            "UPDATE `acl_topology` SET acl_topo_activate = '0' "
            . "WHERE `acl_topo_id` = :topology_id"
        );
        $prepareUpdate->bindValue(
            ':topology_id',
            $currentTopologyId,
            \PDO::PARAM_INT
        );
        
        if (!$prepareUpdate->execute()) {
            continue;
        }
        
        $prepareSelect = $pearDB->prepare(
            "SELECT acl_topo_name FROM `acl_topology` "
            . "WHERE acl_topo_id = :topology_id LIMIT 1"
        );
        $prepareSelect->bindValue(
            ':topology_id',
            $currentTopologyId,
            \PDO::PARAM_INT
        );
        
        if ($prepareSelect->execute()) {
            $result = $prepareSelect->fetch(PDO::FETCH_ASSOC);
            $centreon->CentreonLogAction->insertLog(
                'menu access',
                $currentTopologyId,
                $result['acl_topo_name'],
                'disable'
            );
        }
    }
}

/**
 * Delete a list of ACL
 *
 * @global CentreonDB $pearDB
 * @global Centreon $centreon
 * @param array $acls
 */
function deleteLCAInDB($acls = array())
{
    global $pearDB, $centreon;
    
    foreach (array_keys($acls) as $currentTopologyId) {
        $prepareSelect = $pearDB->prepare(
            "SELECT acl_topo_name FROM `acl_topology` "
            . "WHERE acl_topo_id = :topology_id LIMIT 1"
        );
        $prepareSelect->bindValue(
            ':topology_id',
            $currentTopologyId,
            \PDO::PARAM_INT
        );
        
        if (!$prepareSelect->execute()) {
            continue;
        }
        
        $result = $prepareSelect->fetch(PDO::FETCH_ASSOC);
        $topologyName = $result['acl_topo_name'];
        
        $prepareDelete = $pearDB->prepare(
            'DELETE FROM `acl_topology` WHERE acl_topo_id = :topology_id'
        );
        $prepareDelete->bindValue(
            ':topology_id',
            $currentTopologyId,
            \PDO::PARAM_INT
        );
        if ($prepareDelete->execute()) {
            $centreon->CentreonLogAction->insertLog(
                'menu access',
                $currentTopologyId,
                $topologyName,
                'd'
            );
        }
    }
}

/**
 * Duplicate a list of ACL
 *
 * @global CentreonDB $pearDB
 * @global Centreon $centreon
 * @param array $lcas
 * @param array $nbrDup
 */
function multipleLCAInDB($acls = array(), $duplicateNbr = array())
{
    global $pearDB, $centreon;
    
    foreach (array_keys($acls) as $currentTopologyId) {
        $prepareSelect = $pearDB->prepare(
            "SELECT * FROM `acl_topology` WHERE acl_topo_id = :topology_id LIMIT 1"
        );
        $prepareSelect->bindValue(
            ':topology_id',
            $currentTopologyId,
            \PDO::PARAM_INT
        );
        
        if (!$prepareSelect->execute()) {
            continue;
        }
        
        $topology = $prepareSelect->fetch(PDO::FETCH_ASSOC);
               
        $topology["acl_topo_id"] = '';
        for ($newIndex = 1; $newIndex <= $duplicateNbr[$currentTopologyId]; $newIndex++) {
            $val = null;
            $aclName = null;
            $fields = array();
            foreach ($topology as $column => $value) {
                if ($column === 'acl_topo_name') {
                    $count = 1;
                    $aclName = $value . "_" . $count;
                    while (!hasTopologyNameNeverUsed($aclName)) {
                        $count++;
                        $aclName = $value . "_" . $count;
                    }
                    $value = $aclName;
                    $fields['acl_topo_name'] = $aclName;
                }
                if (is_null($val)) {
                    $val .= (is_null($value) || empty($value))
                        ? 'NULL'
                        : "'" . $pearDB->escape($value) . "'";
                } else {
                    $val .= (is_null($value) || empty($value))
                        ? ', NULL'
                        : ", '" . $pearDB->escape($value) . "'";
                }
                
                if ($column !== 'acl_topo_id' && $column !== 'acl_topo_name') {
                    $fields[$column] = $value;
                }
            }
            
            if (!is_null($val)) {
                $pearDB->query(
                    "INSERT INTO acl_topology VALUES ($val)"
                );
                $newTopologyId = $pearDB->lastInsertId();

                $prepareInsertRelation = $pearDB->prepare(
                    "INSERT INTO acl_topology_relations "
                    . "(acl_topo_id, topology_topology_id, access_right) "
                    . "(SELECT :new_topology_id, topology_topology_id, access_right "
                    . "FROM acl_topology_relations "
                    . "WHERE acl_topo_id = :current_topology_id)"
                );
                $prepareInsertRelation->bindValue(
                    ':new_topology_id',
                    $newTopologyId,
                    \PDO::PARAM_INT
                );
                $prepareInsertRelation->bindValue(
                    ':current_topology_id',
                    $currentTopologyId,
                    \PDO::PARAM_INT
                );

                if (!$prepareInsertRelation->execute()) {
                    continue;
                }

                $prepareInsertGroup = $pearDB->prepare(
                    "INSERT INTO acl_group_topology_relations "
                    . "(acl_topology_id, acl_group_id) "
                    . "(SELECT :new_topology_id, acl_group_id "
                    . "FROM acl_group_topology_relations "
                    . "WHERE acl_topology_id = :current_topology_id)"
                );
                $prepareInsertGroup->bindValue(
                    ':new_topology_id',
                    $newTopologyId,
                    \PDO::PARAM_INT
                );
                $prepareInsertGroup->bindValue(
                    ':current_topology_id',
                    $currentTopologyId,
                    \PDO::PARAM_INT
                );

                if ($prepareInsertGroup->execute()) {
                    $centreon->CentreonLogAction->insertLog(
                        'menu access',
                        $newTopologyId,
                        $aclName,
                        'a',
                        $fields
                    );
                }
            }
        }
    }
}

/**
 * Update an ACL
 *
 * @global HTML_QuickFormCustom $form
 * @global Centreon $centreon
 * @param int $aclId Acl topology id to update
 */
function updateLCAInDB($aclId = null)
{
    global $form, $centreon;
    if (!$aclId) {
        return;
    }
    updateLCA($aclId);
    updateLCARelation($aclId);
    updateGroups($aclId);
    $submitedValues = $form->getSubmitValues();
    $fields = CentreonLogAction::prepareChanges($submitedValues);
    $centreon->CentreonLogAction->insertLog(
        'menu access',
        $aclId,
        $submitedValues['acl_topo_name'],
        'c',
        $fields
    );
}

/**
 * Insert an ACL
 *
 * @global HTML_QuickFormCustom $form
 * @global Centreon $centreon
 * @return int Id of the new ACL
 */
function insertLCAInDB()
{
    global $form, $centreon;

    $aclId = insertLCA();
    updateLCARelation($aclId);
    updateGroups($aclId);
    $submitedValues = $form->getSubmitValues();
    $fields = CentreonLogAction::prepareChanges($submitedValues);
    $centreon->CentreonLogAction->insertLog(
        'menu access',
        $aclId,
        $submitedValues['acl_topo_name'],
        'a',
        $fields
    );

    return $aclId;
}

/**
 * Insert an ACL
 *
 * @global HTML_QuickFormCustom $form
 * @global CentreonDB $pearDB
 * @return int Id of the new ACL topology
 */
function insertLCA()
{
    global $form, $pearDB;

    $submitedValues = $form->getSubmitValues();
    $isAclActivate = false;
    if (isset($submitedValues['acl_topo_activate'])
        && isset($submitedValues['acl_topo_activate']['acl_topo_activate'])
        && $submitedValues['acl_topo_activate']['acl_topo_activate'] == '1'
    ) {
        $isAclActivate = true;
    }
    $prepare = $pearDB->prepare(
        "INSERT INTO `acl_topology` "
        . "(acl_topo_name, acl_topo_alias, acl_topo_activate, acl_comments) "
        . "VALUES (:acl_name, :acl_alias, :is_activate, :acl_comment)"
    );
    $prepare->bindValue(
        ':acl_name',
        $submitedValues['acl_topo_name'],
        \PDO::PARAM_STR
    );
    $prepare->bindValue(
        ':is_activate',
        ($isAclActivate ? '1': '0'),
        \PDO::PARAM_STR
    );
    $prepare->bindValue(
        ':acl_alias',
        $submitedValues['acl_topo_alias'],
        \PDO::PARAM_STR
    );
    $prepare->bindValue(
        ':acl_comment',
        $submitedValues['acl_comments'],
        \PDO::PARAM_STR
    );
    
    return $prepare->execute()
        ? $pearDB->lastInsertId()
        : null;
}

/**
 * Update an ACL
 *
 * @global HTML_QuickFormCustom $form
 * @global \CentreonDB $pearDB
 * @param int $aclId Acl id to update
  */
function updateLCA($aclId = null)
{
    global $form, $pearDB;
    if (!$aclId) {
        return;
    }
    $submitedValues = $form->getSubmitValues();
    
    $isAclActivate = false;
    if (isset($submitedValues['acl_topo_activate'])
        && isset($submitedValues['acl_topo_activate']['acl_topo_activate'])
        && $submitedValues['acl_topo_activate']['acl_topo_activate'] == '1'
    ) {
        $isAclActivate = true;
    }
    
    $prepareUpdate = $pearDB->prepare(
        "UPDATE `acl_topology` "
        . "SET acl_topo_name = :acl_name, "
        . "acl_topo_alias = :acl_alias, "
        . "acl_topo_activate = :is_activate, "
        . "acl_comments = :acl_comment "
        . "WHERE acl_topo_id = :acl_id"
    );
    
    $prepareUpdate->bindValue(
        ':acl_name',
        $submitedValues["acl_topo_name"],
        \PDO::PARAM_STR
    );
    
    $prepareUpdate->bindValue(
        ':acl_alias',
        $submitedValues["acl_topo_alias"],
        \PDO::PARAM_STR
    );
    
    $prepareUpdate->bindValue(
        ':is_activate',
        ($isAclActivate ? '1': '0'),
        \PDO::PARAM_STR
    );
    
    $prepareUpdate->bindValue(
        ':acl_comment',
        $submitedValues["acl_comments"],
        \PDO::PARAM_STR
    );
    
    $prepareUpdate->bindValue(':acl_id', $aclId, \PDO::PARAM_INT);
    
    $prepareUpdate->execute();
}

/**
 * Update all relation of ACL from the global form
 *
 * @global HTML_QuickFormCustom $form
 * @global \CentreonDB $pearDB
 * @param type $acl_id
 * @return type
 */
function updateLCARelation($aclId = null)
{
    global $form, $pearDB;
    
    if (!$aclId) {
        return;
    }
    
    $prepareDelete = $pearDB->prepare(
        "DELETE FROM acl_topology_relations WHERE acl_topo_id = :acl_id"
    );
    $prepareDelete->bindValue(':acl_id', $aclId, \PDO::PARAM_INT);
    
    if ($prepareDelete->execute()) {
        $submitedValues = $form->getSubmitValue("acl_r_topos");
        foreach ($submitedValues as $key => $value) {
            if (isset($submitedValues) && $key != 0) {
                $pearDB->query(
                    "INSERT INTO acl_topology_relations "
                    . "(acl_topo_id, topology_topology_id, access_right) "
                    . "VALUES ($aclId, $key, $value)"
                );
            }
        }
    }
}

/**
 * Update all groups of ACL from the global form
 *
 * @global HTML_QuickFormCustom $form
 * @global \CentreonDB $pearDB
 * @param type $acl_id
 * @return type
 */
function updateGroups($aclId = null)
{
    global $form, $pearDB;
    if (!$aclId) {
        return;
    }
    
    $prepareDelete = $pearDB->prepare(
        "DELETE FROM acl_group_topology_relations WHERE acl_topology_id = :acl_id"
    );
    
    $prepareDelete->bindValue(':acl_id', $aclId, \PDO::PARAM_INT);
    
    if ($prepareDelete->execute()) {
        $submitedValues = $form->getSubmitValue("acl_groups");
        if (isset($submitedValues)) {
            foreach ($submitedValues as $key => $value) {
                if (isset($value)) {
                    $pearDB->query(
                        "INSERT INTO acl_group_topology_relations "
                        . "(acl_topology_id, acl_group_id) "
                        . "VALUES ($aclId, $value)"
                    );
                }
            }
        }
    }
}
