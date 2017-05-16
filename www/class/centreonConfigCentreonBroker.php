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

/**
 * Class for Centreon Broker configuration
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 */

require_once _CENTREON_PATH_ . "www/class/centreon-config/centreonMainCfg.class.php";

class CentreonConfigCentreonBroker
{
    /**
     *
     * @var int
     */
    public $nbSubGroup = 1;

    /**
     *
     * @var type
     */
    private $db;

    /**
     *
     * @var array
     */
    private $attrText = array("size" => "120");

    /**
     *
     * @var array
     */
    private $attrInt = array("size" => "10", "class" => "v_number");

    /**
     *
     * @var string
     */
    private $globalCommandFile = null;

    /**
     *
     * @var type
     */
    private $tagsCache = null;

    /**
     *
     * @var type
     */
    private $typesCache = null;

    /**
     *
     * @var type
     */
    private $typesNameCache = null;

    /**
     *
     * @var array
     */
    private $blockCache = array();

    /**
     *
     * @var array
     */
    private $fieldtypeCache = array();

    /**
     *
     * @var array
     */
    private $blockInfoCache = array();

    /**
     *
     * @var array
     */
    private $listValues = array();

    /**
     *
     * @var array
     */
    private $defaults = array();

    /**
     *
     * @var array
     */
    private $attrsAdvSelect = array("style" => "width: 270px; height: 70px;");

    /**
     *
     * @var string
     */
    private $advMultiTemplate = '<table><tr>
        <td><div class="ams">{label_2}</div>{unselected}</td>
        <td align="center">{add}<br><br><br>{remove}</td>
        <td><div class="ams">{label_3}</div>{selected}</td>
        </tr></table>{javascript}';

    /**
     * Construtor
     *
     * @param CentreonDB $db The connection to centreon database
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Serialize inner data
     * @return array
     */
    public function __sleep()
    {
        $this->db = null;
        return array(
            'attrText',
            'attrInt',
            'tagsCache',
            'typesCache',
            'blockCache',
            'blockInfoCache',
            'listValues',
            'defaults',
            'fieldtypeCache'
        );
    }

    /**
     * Set the database
     *
     * @param CentreonDB $db The connection to centreon database
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * Return the list of tags
     *
     * @return array
     */
    public function getTags()
    {
        if (!is_null($this->tagsCache)) {
            return $this->tagsCache;
        }
        $query = "SELECT cb_tag_id, tagname " .
            "FROM cb_tag " .
            "ORDER BY tagname";
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return array();
        }
        $this->tagsCache = array();
        while ($row = $res->fetchRow()) {
            $this->tagsCache[$row['cb_tag_id']] = $row['tagname'];
        }
        return $this->tagsCache;
    }

    /**
     * Get the tagname
     *
     * @param int $tagId The tag id
     * @return string|null null in error
     */
    public function getTagName($tagId)
    {
        if (!is_null($this->tagsCache) && isset($this->tagsCache[$tagId])) {
            return $this->tagsCache[$tagId];
        }
        $query = "SELECT tagname
            FROM cb_tag
            WHERE cb_tag_id = %d";
        $res = $this->db->query(sprintf($query, $tagId));
        if (PEAR::isError($res)) {
            return null;
        }
        $row = $res->fetchRow();
        if (is_null($row)) {
            return null;
        }
        return $row['tagname'];
    }

    /**
     * Get the typename
     *
     * @param int $typeId The type id
     * @return string|null null in error
     */
    public function getTypeShortname($typeId)
    {
        if (!is_null($this->typesCache) && isset($this->typesCache[$typeId])) {
            return $this->typesCache[$typeId];
        }
        $query = "SELECT type_shortname
            FROM cb_type
            WHERE cb_type_id = %d";
        $res = $this->db->query(sprintf($query, $typeId));
        if (PEAR::isError($res)) {
            return null;
        }
        $row = $res->fetchRow();
        if (is_null($row)) {
            return null;
        }
        $this->typesCache[$typeId] = $row['type_shortname'];
        return $this->typesCache[$typeId];
    }

    /**
     * Get the Centreon Broker type name
     *
     * @param int $typeId The type id
     * @return string|null null in error
     */
    public function getTypeName($typeId)
    {
        if (!is_null($this->typesNameCache) && isset($this->typesNameCache[$typeId])) {
            return $this->typesNameCache[$typeId];
        }
        $query = 'SELECT type_name
            FROM cb_type
            WHERE cb_type_id = %d';
        $res = $this->db->query(sprintf($query, $typeId));
        if (PEAR::isError($res)) {
            return null;
        }
        $row = $res->fetchRow();
        if (is_null($row)) {
            return null;
        }
        $this->typesNameCache[$typeId] = $row['type_name'];
        return $this->typesNameCache[$typeId];
    }

    /**
     * Return the list of config block
     *
     * The id is 'tag_id'_'type_id'
     * The name is "module_name - type_name"
     *
     * @param int $tagId The tag id
     * @return array
     */
    public function getListConfigBlock($tagId)
    {
        if (isset($this->blockCache[$tagId])) {
            return $this->blockCache[$tagId];
        }
        $query = "SELECT m.name, t.cb_type_id, t.type_name, ttr.cb_type_uniq
            FROM cb_module m, cb_type t, cb_tag_type_relation ttr
            WHERE m.cb_module_id = t.cb_module_id AND ttr.cb_type_id = t.cb_type_id AND ttr.cb_tag_id = %d";
        $res = $this->db->query(sprintf($query, $tagId));
        if (PEAR::isError($res)) {
            return array();
        }
        $this->blockCache[$tagId] = array();
        while ($row = $res->fetchRow()) {
            $name = $row['name'] . ' - ' . $row['type_name'];
            $id = $tagId . '_' . $row['cb_type_id'];
            $this->blockCache[$tagId][] = array('id' => $id, 'name' => $name, 'unique' => $row['cb_type_uniq']);
        }
        return $this->blockCache[$tagId];
    }

    public $arrayMultiple;
    /**
     * Create the HTML_QuickForm object with element for a block
     *
     * @param int $blockId The block id ('tag_id'_'type_id')
     * @param int $page The centreon page id
     * @param int $formId The form post
     * @return HTML_QuickForm
     */
    public function quickFormById($blockId, $page, $formId = 1, $config_id = 0)
    {
        list($tagId, $typeId) = explode('_', $blockId);
        $fields = $this->getBlockInfos($typeId);
        $tag = $this->getTagName($tagId);
        $this->nbSubGroup = 1;

        $qf = new HTML_QuickForm('form_' . $formId, 'post', '?p=' . $page);

        $qf->addElement(
            'text',
            $tag . '[' . $formId . '][name]',
            _('Name'),
            array_merge(
                $this->attrText,
                array(
                    'id' => $tag . '[' . $formId . '][name]',
                    'class' => 'v_required'
                )
            )
        );

        $type = $this->getTypeShortname($typeId);
        $qf->addElement('hidden', $tag . '[' . $formId . '][type]');
        $qf->setDefaults(array($tag . '[' . $formId . '][type]' => $type));

        $typeName = $this->getTypeName($typeId);
        $qf->addElement('header', 'typeName', $typeName);

        $qf->addElement('hidden', $tag . '[' . $formId . '][blockId]');
        $qf->setDefaults(array($tag . '[' . $formId . '][blockId]' => $blockId));

        foreach ($fields as $field) {
            $parentGroup = "";
            $isMultiple = false;

            $elementName = $this->getElementName($tag, $formId, $field, $isMultiple);
            if ($isMultiple && $field['group'] !== '') {
                $displayNameGroup = "";
                $parentGroup = $this->getParentGroups($field['group'], $isMultiple, $displayNameGroup);
                $parentGroup = $parentGroup."_".$formId;
            }

            $elementType = null;
            $elementAttr = array();
            $default = null;
            $displayName = _($field['displayname']);
            switch ($field['fieldtype']) {
                case 'int':
                    $elementType = 'text';
                    $elementAttr = $this->attrInt;
                    break;
                case 'select':
                    $elementType = 'select';
                    $elementAttr = $this->getListValues($field['id']);
                    $default = $this->getDefaults($field['id']);
                    break;
                case 'radio':
                    $tmpRadio = array();

                    if ($isMultiple && $parentGroup != "") {
                        $elementAttr = array_merge($elementAttr, array(
                                                   'parentGroup' => $parentGroup,
                                                   'displayNameGroup' => $displayNameGroup
                                                   ));
                    }

                    foreach ($this->getListValues($field['id']) as $key => $value) {
                        $tmpRadio[] = HTML_QuickForm::createElement(
                            'radio',
                            $field['fieldname'],
                            null,
                            _($value),
                            $key,
                            $elementAttr
                        );
                    }
                    $qf->addGroup($tmpRadio, $elementName, _($field['displayname']), '&nbsp;');
                    $default = $this->getDefaults($field['id']);
                    break;
                case 'password':
                    $elementType = 'password';
                    $elementAttr = $this->attrText;
                    break;
                case 'multiselect':
                    $displayName = array(_($field['displayname']), _("Available"), _("Selected"));
                    $elementType = 'advmultiselect';
                    $elementAttr = $this->getListValues($field['id']);
                    break;
                case 'text':
                default:
                    $elementType = 'text';
                    $elementAttr = $this->attrText;
                    break;
            }

            /*
             *  If get information for read-only in database
             */
            if (!is_null($field['value']) && $field['value'] !== false) {
                $elementType = null;
                $roValue = $this->getInfoDb($field['value']);
                $field['value'] = $roValue;
                if (is_array($roValue)) {
                    $qf->addElement('select', $elementName, $displayName, $roValue);
                } else {
                    $qf->addElement('text', $elementName, $displayName, $this->attrText);
                }
                $qf->freeze($elementName);
            }

            /*
             * Add required informations
             */
            if ($field['required'] && is_null($field['value']) && $elementType != 'select') {
                $elementAttr = array_merge($elementAttr, array(
                                                               'id' => $elementName,
                                                               'class' => 'v_required'
                                                               ));
            }

            $elementAttrSelect = array();
            if ($isMultiple && $parentGroup != "") {
                if ($elementType != 'select') {
                    $elementAttr = array_merge($elementAttr, array(
                                               'parentGroup' => $parentGroup,
                                               'displayNameGroup' => $displayNameGroup
                                               ));
                } else {
                    $elementAttrSelect = array('parentGroup' => $parentGroup , 'displayNameGroup' => $displayNameGroup);
                }
            }

            /*
             * Add elements
             */
            if (!is_null($elementType)) {
                if ($elementType == 'advmultiselect') {
                    $el = $qf->addElement(
                        $elementType,
                        $elementName,
                        $displayName,
                        $elementAttr,
                        $this->attrsAdvSelect,
                        SORT_ASC
                    );
                    $el->setButtonAttributes('add', array('value' =>  _("Add"), "class" => "btc bt_success"));
                    $el->setButtonAttributes('remove', array('value' =>  _("Remove"), "class" => "btc bt_danger"));
                    $el->setElementTemplate($this->advMultiTemplate);
                } else {
                    $el = $qf->addElement($elementType, $elementName, $displayName, $elementAttr, $elementAttrSelect);
                }
            }

            /*
             * Defaults values
             */
            if (!is_null($field['value']) && $field['value'] !== false) {
                if ($field['fieldtype'] != 'radio') {
                    $qf->setDefaults(array($elementName => $field['value']));
                } else {
                    $qf->setDefaults(array($elementName . '[' . $field['fieldname'] . ']' => $field['value']));
                }
            } elseif (!is_null($default)) {
                if ($field['fieldtype'] != 'radio') {
                    $qf->setDefaults(array($elementName => $default));
                } else {
                    $qf->setDefaults(array($elementName . '[' . $field['fieldname'] . ']' => $default));
                }
            }
        }
        return $qf;
    }

    /**
     * Generate Cdata tag
     */
    public function generateCdata()
    {
        $cdata = CentreonData::getInstance();
        if (isset($this->arrayMultiple)) {
            foreach ($this->arrayMultiple as $key => $multipleGroup) {
                $cdata->addJsData('clone-values-'.$key, htmlspecialchars(
                    json_encode($multipleGroup),
                    ENT_QUOTES
                ));
                $cdata->addJsData('clone-count-'.$key, count($multipleGroup));
            }
        }
    }

    /**
     * Get informations for a block
     *
     * @param int $typeId The type id
     * @return array
     */
    public function getBlockInfos($typeId)
    {
        if (isset($this->blockInfoCache[$typeId])) {
            return $this->blockInfoCache[$typeId];
        }

        /*
         * Get the list of fields for a block
         */
        $fields = array();
        $query = "SELECT f.cb_field_id, f.fieldname, f.displayname, f.fieldtype, f.description, f.external,
            tfr.is_required, tfr.order_display, f.cb_fieldgroup_id
            FROM cb_field f, cb_type_field_relation tfr
                WHERE f.cb_field_id = tfr.cb_field_id AND (tfr.cb_type_id = %d
                    OR tfr.cb_type_id IN (SELECT t.cb_type_id
                        FROM cb_type t, cb_module_relation mr
                        WHERE mr.inherit_config = 1 AND t.cb_module_id IN (SELECT mr2.module_depend_id
                            FROM cb_type t2, cb_module_relation mr2
                            WHERE t2.cb_module_id = mr2.cb_module_id AND
                                mr2.inherit_config = 1 AND t2.cb_type_id = %d)))
            ORDER BY tfr.order_display";
        $res = $this->db->query(sprintf($query, $typeId, $typeId));
        if (PEAR::isError($res)) {
            return false;
        }
        while ($row = $res->fetchRow()) {
            $field = array();
            $field['id'] = $row['cb_field_id'];
            $field['fieldname'] = $row['fieldname'];
            $field['displayname'] = $row['displayname'];
            $field['fieldtype'] = $row['fieldtype'];
            $field['description'] = $row['description'];
            $field['required'] = $row['is_required'];
            $field['order'] = $row['order_display'];
            $field['group'] = $row['cb_fieldgroup_id'];
            if (!is_null($row['external']) && $row['external'] != '') {
                $field['value'] = $row['external'];
            } else {
                $field['value'] = null;
            }
            $fields[] = $field;
        }
        usort($fields, array($this, 'sortField'));
        $this->blockInfoCache[$typeId] = $fields;
        return $this->blockInfoCache[$typeId];
    }

    /**
     * Return a cb type id for the shortname given
     * @param type $typeName
     * @return boolean
     */
    public function getTypeId($typeName)
    {
        $typeId = null;

        $queryGetType = "SELECT cb_type_id FROM cb_type WHERE type_shortname = '$typeName'";
        $res = $this->db->query($queryGetType);

        if (!PEAR::isError($res)) {
            while ($row = $res->fetchRow()) {
                $typeId = $row['cb_type_id'];
            }
        }

        return $typeId;
    }

    /**
     * Insert a configuration into the database
     *
     * @param array $values The post array
     * @return bool
     */
    public function insertConfig($values)
    {
        $objMain = new CentreonMainCfg();

        /*
         * Insert the Centreon Broker configuration
         */
        $query = "INSERT INTO cfg_centreonbroker "
                . "(config_name, config_filename, ns_nagios_server, config_activate, daemon, config_write_timestamp, "
                . "config_write_thread_id, stats_activate, cache_directory, "
                . "event_queue_max_size, command_file) "
                . "VALUES (
                '" . $this->db->escape($values['name']) . "',
                '" . $this->db->escape($values['filename']) . "',
                " . $this->db->escape($values['ns_nagios_server']) . ",
                '" . $this->db->escape($values['activate']['activate']) . "',
                '" . $this->db->escape($values['activate_watchdog']['activate_watchdog']) . "',
                '" . $this->db->escape($values['write_timestamp']['write_timestamp']) . "',
                '" . $this->db->escape($values['write_thread_id']['write_thread_id']) . "',
                '" . $this->db->escape($values['stats_activate']['stats_activate']) . "',
                '" . $this->db->escape($values['cache_directory']) . "',
                ".$this->db->escape((int)$this->checkEventMaxQueueSizeValue($values['event_queue_max_size'])) . ",
                '" . $this->db->escape($values['command_file']) . "' "
                . ")";
        if (PEAR::isError($this->db->query($query))) {
            return false;
        }

        $iIdServer = $values['ns_nagios_server'];
        $iId = $objMain->insertServerInCfgNagios(-1, $iIdServer, $values['name']);
        if (!empty($iId)) {
            $objMain->insertBrokerDefaultDirectives($iId, 'wizard');
        }
        /*
         * Get the ID
         */
        $query = "SELECT config_id FROM cfg_centreonbroker WHERE config_name = '" . $values['name'] . "'";
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return false;
        }
        $row = $res->fetchRow();
        $id = $row['config_id'];
        $this->updateCentreonBrokerInfos($id, $values);
    }

    /**
     * Update configuration
     *
     * @param int $id The configuration id
     * @param array $values The post array
     * @return bool
     */
    public function updateConfig($id, $values)
    {
        /*
         * Insert the Centreon Broker configuration
         */
        $query = "UPDATE cfg_centreonbroker SET
                config_name = '" . $this->db->escape($values['name']) . "',
                config_filename = '"  . $this->db->escape($values['filename']) . "',
                ns_nagios_server = "  . $this->db->escape($values['ns_nagios_server']) . ",
                config_activate = '"  . $this->db->escape($values['activate']['activate']) . "',
                daemon = '"  . $this->db->escape($values['activate_watchdog']['activate_watchdog']) . "',
                config_write_timestamp = '" . $this->db->escape($values['write_timestamp']['write_timestamp']) . "',
                config_write_thread_id = '" . $this->db->escape($values['write_thread_id']['write_thread_id']) . "',
                stats_activate = '" . $this->db->escape($values['stats_activate']['stats_activate']) . "',
                cache_directory = '" . $this->db->escape($values['cache_directory']) . "',
                event_queue_max_size = " .
                (int)$this->db->escape($this->checkEventMaxQueueSizeValue($values['event_queue_max_size'])) . ",
                command_file = '" . $this->db->escape($values['command_file']) . "'
            WHERE config_id = " . $id;
        if (PEAR::isError($this->db->query($query))) {
            return false;
        }
        $this->updateCentreonBrokerInfos($id, $values);
    }

    /**
     * Update the information for a configuration
     *
     * @param int $id The configuration id
     * @param array $values The post array
     * @return bool
     */
    public function updateCentreonBrokerInfos($id, $values)
    {
        /*
        * Clean the informations for this id
        */
        $query = "DELETE FROM cfg_centreonbroker_info WHERE config_id = " . $id;
        $this->db->query($query);

        $groups_infos = array();
        $groups_infos_multiple = array();
        foreach ($this->getTags() as $group) {
        /*
         * Resort array
         */
            if (isset($values[$group])) {
                foreach ($values[$group] as $infos) {
                    if (!isset($groups_infos[$group])) {
                        $groups_infos[$group] = array();
                    }
                    $newArray = array();
                    foreach ($infos as $key => $info) {
                        $is_multiple = preg_match('/(.+?)_(\d+)$/', $key, $result);
                        if ($is_multiple) {
                            if (!isset($newArray[$result[2]])) {
                                $newArray[$result[2]] = array();
                            }
                            $newArray[$result[2]][$result[1]] = $infos[$key];

                            unset($infos[$key]);
                        }
                    }
                    if (!empty($newArray)) {
                        $groups_infos_multiple[] = $newArray;
                        $infos['multiple_fields'] = $newArray;
                    }
                    $groups_infos[$group][] = $infos;
                }
            }
        }

        foreach ($groups_infos as $group => $groups) {
            foreach ($groups as $gid => $infos) {
                if (isset($infos['blockId'])) {
                    list($tagId, $typeId) = explode('_', $infos['blockId']);
                    $fieldtype = $this->getFieldtypes($typeId);
                    foreach ($infos as $fieldname => $fieldvalue) {
                        $lvl = 0;
                        $grp_id = 'NULL';
                        $parent_id = 'NULL';

                        if ($fieldname == 'multiple_fields' && is_array($fieldvalue)) {
                            foreach ($fieldvalue as $index => $value) {
                                if (isset($fieldtype[$fieldname]) && $fieldtype[$fieldname] == 'radio') {
                                    $value = $value[$fieldname];
                                }
                                if (false === is_array($value)) {
                                    $value = array($value);
                                }
                                foreach ($value as $fieldname2 => $value2) {
                                    if (is_array($value2)) {
                                        $explodedFieldname2 = explode('__', $fieldname2);
                                        if (isset($fieldtype[$explodedFieldname2[1]]) &&
                                            $fieldtype[$explodedFieldname2[1]] == 'radio') {
                                            $value2 = $value2[$explodedFieldname2[1]];
                                        }
                                    }
                                    $query = "INSERT INTO cfg_centreonbroker_info "
                                        . "(config_id, config_key, config_value, config_group, config_group_id, "
                                        . "grp_level, subgrp_id, parent_grp_id, fieldIndex) "
                                        . "VALUES (" . $id . ", '" . $fieldname2 . "', '" . $value2 . "', '"
                                        . $group . "', " . $gid . ", " . $lvl . ", " . $grp_id . ", "
                                        . $parent_id . ", " . $index . ") ";
                                    $this->db->query($query);
                                }
                            }
                            continue;
                        }

                        if (isset($fieldtype[$fieldname]) && $fieldtype[$fieldname] == 'radio') {
                            $fieldvalue = $fieldvalue[$fieldname];
                        }
                        if (false === is_array($fieldvalue)) {
                                $fieldvalue = array($fieldvalue);
                        }
                        /*
                             * Construct xml tree
                         */
                        while (preg_match('/.+__\d+__.+/', $fieldname)) {
                            $info = explode('__', $fieldname, 3);
                            $grp_name = $info[0];
                            $grp_id = $info[1];
                                        $query = "INSERT INTO cfg_centreonbroker_info
                                        (config_id, config_key, config_value, config_group, config_group_id, grp_level,
                                        subgrp_id, parent_grp_id)
                                        VALUES (" . $id . ", '" . $grp_name . "', '', '" . $group . "', " .
                                            $gid . ", " . $lvl . ", " . $grp_id . ", " . $parent_id . ")";
                                        $this->db->query($query);
                            $lvl++;
                            $parent_id = $grp_id;
                            $fieldname = $info[2];
                        }
                        $grp_id = 'NULL';
                        foreach ($fieldvalue as $value) {
                                       $query = "INSERT INTO cfg_centreonbroker_info
                                        (config_id, config_key, config_value, config_group, config_group_id, grp_level,
                                        subgrp_id, parent_grp_id)
                                        VALUES (" . $id . ", '" . $fieldname . "', '" . $value . "', '" .
                                           $group . "', " . $gid . ", " . $lvl . ", " .
                                           $grp_id . ", " . $parent_id . ")";
                                       $this->db->query($query);
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get the list of forms for a config_id
     *
     * @param int $config_id The id of config
     * @param string $tag The tag name
     * @param int $page The page topology
     * @param Smarty $tpl The template Smarty
     * @return array
     */
    public function getForms($config_id, $tag, $page, $tpl)
    {
        $query = "SELECT config_key, config_value, config_group_id, grp_level, parent_grp_id, fieldIndex
            FROM cfg_centreonbroker_info
        WHERE config_id = %d
            AND config_group = '%s'
            AND subgrp_id IS NULL
            ORDER BY config_group_id";
        $res = $this->db->query(sprintf($query, $config_id, $tag));
        if (PEAR::isError($res)) {
            return array();
        }
        $formsInfos = array();
        $arrayMultipleValues = array();
        while ($row = $res->fetchRow()) {
            $fieldname = $tag . '[' . $row['config_group_id'] . '][' .
                $this->getConfigFieldName($config_id, $tag, $row) . ']';
        /* Multi value for a multiselect */
            if (isset($row['fieldIndex']) && !is_null($row['fieldIndex']) && $row['fieldIndex'] != "") {
                $fieldname = $tag . '[' . $row['config_group_id'] . '][' .
                    $this->getConfigFieldName($config_id, $tag, $row) . '_#index#]';
                $arrayMultipleValues[$fieldname][] = $row['config_value'];
            } else {
                if (isset($formsInfos[$row['config_group_id']]['defaults'][$fieldname])) {
                    if (!is_array($formsInfos[$row['config_group_id']]['defaults'][$fieldname])) {
                        $formsInfos[$row['config_group_id']]['defaults'][$fieldname] = array(
                            $formsInfos[$row['config_group_id']]['defaults'][$fieldname]
                        );
                    }
                        $formsInfos[$row['config_group_id']]['defaults'][$fieldname][] = $row['config_value'];
                } else {
                        $formsInfos[$row['config_group_id']]['defaults'][$fieldname] = $row['config_value'];
                        $formsInfos[$row['config_group_id']]['defaults'][$fieldname . '[' . $row['config_key'] . ']'] =
                            $row['config_value']; // Radio button
                }
                if ($row['config_key'] == 'blockId') {
                    $formsInfos[$row['config_group_id']]['blockId'] = $row['config_value'];
                }
            }
        }
        $forms = array();
        $isMultiple = false;
        foreach (array_keys($formsInfos) as $key) {
            $qf = $this->quickFormById($formsInfos[$key]['blockId'], $page, $key, $config_id);
            /*
             * Replace loaded configuration with defaults external values
             */
            list($tagId , $typeId) = explode('_', $formsInfos[$key]['blockId']);
            $tag = $this->getTagName($tagId);
            $fields = $this->getBlockInfos($typeId);
            foreach ($fields as $field) {
                $elementName = $this->getElementName($tag, $key, $field, $isMultiple);
                if (!is_null($field['value']) && $field['value'] != false) {
                    unset($formsInfos[$key]['defaults'][$elementName]); // = $this->getInfoDb($field['value']);
                }
                if (isset($arrayMultipleValues[$elementName])) {
                    if ($isMultiple && $field['group'] !== '') {
                        $parentGroup = $this->getParentGroups($field['group'], $isMultiple);
                        $parentGroup = $parentGroup."_".$key;
                        $arrayMultiple[$parentGroup][$elementName] = $arrayMultipleValues[$elementName];
                    }
                }
            }
            $qf->setDefaults($formsInfos[$key]['defaults']);
            $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
            $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
            $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
            $qf->accept($renderer);
            $forms[] = $renderer->toArray();
        }
        if (isset($arrayMultiple)) {
            foreach ($arrayMultiple as $key => $arrayMultipleS) {
                foreach ($arrayMultipleS as $key2 => $oneElemArray) {
                    $cnt = 0;
                    foreach ($oneElemArray as $oneElem) {
                        $this->arrayMultiple[$key][$cnt][$key2] = $oneElem;
                        $cnt++;
                    }
                }
            }
        }
        $this->generateCdata();

        return $forms;
    }

    /**
     * Get the correlation file
     *
     * @return mixed false in error or does not set, or string the path file
     */
    public function getCorrelationFile()
    {
        $query = "SELECT " .
            "config_id, config_group_id " .
            "FROM cfg_centreonbroker_info " .
            "WHERE config_key = 'type' AND config_value = 'correlation'";
        $res = $this->db->query($query);

        if (PEAR::isError($res) || $res->numRows() == 0) {
            return false;
        }

        $row = $res->fetchRow();
        $configId = $row['config_id'];
        $correlationGroupId = $row['config_group_id'];
        $query = 'SELECT config_value FROM cfg_centreonbroker_info ' .
            'WHERE config_key = "file" ' .
            'AND config_id = ' . $configId .
            ' AND config_group_id = ' . $correlationGroupId;
        $res = $this->db->query($query);

        if (PEAR::isError($res) || $res->numRows() == 0) {
            return false;
        }

        $row = $res->fetchRow();
        return $row['config_value'];
    }

    /**
     * Sort the fields by order display
     *
     * @param array $field1 The first field to sort
     * @param array $field2 The second field to sort
     * @return int
     */
    private function sortField($field1, $field2)
    {
        if ($field1['order'] == $field2['order']) {
            return 0;
        } elseif ($field1['order'] < $field2['order']) {
            return -1;
        } else {
            return 1;
        }
    }

    /**
     * Generate fieldtype array
     *
     * @param int $typeId The type id
     * @return array
     */
    public function getFieldtypes($typeId)
    {
        if (isset($this->fieldtypeCache[$typeId])) {
            return $this->fieldtypeCache[$typeId];
        }
        $fieldtypes = array();
        $block = $this->getBlockInfos($typeId);
        foreach ($block as $fieldInfos) {
            $fieldtypes[$fieldInfos['fieldname']] = $fieldInfos['fieldtype'];
        }
        $this->fieldtypeCache[$typeId] = $fieldtypes;
        return $this->fieldtypeCache[$typeId];
    }

    /**
     * Get helps message for forms
     *
     * @param int $config_id The configuration id
     * @param string $tag The tag of configuration
     * @return array The list of helps order by position in page
     */
    public function getHelps($config_id, $tag)
    {
        $this->nbSubGroup = 1;
        $query = "SELECT config_value, config_group_id
            FROM cfg_centreonbroker_info
            WHERE config_id = %d AND config_group = '%s'
            AND config_key = 'blockId'
            ORDER BY config_group_id";
        $res = $this->db->query(sprintf($query, $config_id, $tag));
        if (PEAR::isError($res)) {
            return array();
        }
        $helps = array();
        while ($row = $res->fetchRow()) {
            list($tagId, $typeId) = explode('_', $row['config_value']);
            $pos = $row['config_group_id'];
            $fields = $this->getBlockInfos($typeId);
            $help = array();
            $help[] = array('name' => $tag . '[' . $pos . '][name]', 'desc' => _('The name of block configuration'));
            $help[] = array('name' => $tag . '[' . $pos . '][type]', 'desc' => _('The type of block configuration'));
            foreach ($fields as $field) {
                $fieldname = '';
                if ($field['group'] !== '') {
                    $fieldname .= $this->getParentGroups($field['group']);
                }
                $fieldname .= $field['fieldname'];
                $help[] = array(
                    'name' => $tag . '[' . $pos . '][' . $fieldname . ']',
                    'desc' => _($field['description'])
                );
            }
            $helps[] = $help;
            $pos++;
        }
        return $helps;
    }

    /**
     * Get the list of values for a select or radio
     *
     * @param int $fieldId The field ID
     * @return array
     */
    private function getListValues($fieldId)
    {
        if (isset($this->listValues[$fieldId])) {
            return $this->listValues[$fieldId];
        }
        $query = "SELECT v.value_name, v.value_value
            FROM cb_list_values v, cb_list l
                WHERE l.cb_list_id = v.cb_list_id AND l.cb_field_id = %d";
        $res = $this->db->query(sprintf($query, $fieldId));
        if (PEAR::isError($res)) {
            return array();
        }
        $ret = array();
        while ($row = $res->fetchRow()) {
            $ret[$row['value_value']] = $row['value_name'];
        }
        $this->listValues[$fieldId] = $ret;
        return $this->listValues[$fieldId];
    }

    /**
     * Get the default value for a list
     *
     * @param int $fieldId The field ID
     * @return string|null
     */
    public function getDefaults($fieldId)
    {
        if (isset($this->defaults[$fieldId])) {
            return $this->defaults[$fieldId];
        }
        $query = "SELECT cbl.default_value, cblv.value_value "
            . "FROM cb_list_values cblv "
            . "LEFT JOIN cb_list cbl ON cblv.cb_list_id = cbl.cb_list_id "
            . "INNER JOIN cb_field cbf ON cbf.cb_field_id = cbl.cb_field_id "
            . "WHERE cbl.cb_field_id = %d "
            . "AND cbf.fieldtype != 'multiselect' ";
        $res = $this->db->query(sprintf($query, $fieldId));
        if (PEAR::isError($res)) {
            return null;
        }
        $row = $res->fetchRow();

        $this->defaults[$fieldId] = null;
        if (!is_null($row)) {
            if (!is_null($row['default_value']) && $row['default_value'] != '') {
                $this->defaults[$fieldId] = $row['default_value'];
            } elseif (!is_null($row['value_value']) && $row['value_value'] != '') {
                $this->defaults[$fieldId] = $row['value_value'];
            }
        } else {
            $externalDefaultValue = $this->getExternalDefaultValue($fieldId);
            if (!is_null($externalDefaultValue) && $externalDefaultValue != '') {
                $this->defaults[$fieldId] = $externalDefaultValue;
            }
        }

        return $this->defaults[$fieldId];
    }

    /**
     *
     * @param type $fieldId
     * @return type
     */
    private function getExternalDefaultValue($fieldId)
    {
        $externalValue = null;
        $query = "SELECT external FROM cb_field WHERE cb_field_id = $fieldId";
        $res = $this->db->query($query);

        if (PEAR::isError($res)) {
            $externalValue = null;
        }

        $row = $res->fetchRow();
        if (!is_null($row)) {
            $finalInfo = $this->getInfoDb($row['external']);
            if (!is_array($finalInfo)) {
                $externalValue = $finalInfo;
            }
        }

        return $externalValue;
    }

    /**
     * Get static information from database
     *
     * @param string $string The string for get information
     * @return mixed Information
     */
    public function getInfoDb($string)
    {
        global $pearDBO;

        if (isset($pearDBO)) {
            $monitoringDb = $pearDBO;
        } else {
            $monitoringDb = new \CentreonDB('centstorage');
        }

        /*
         * Default values
         */
        $s_db = "centreon";
        $s_rpn = null;
        /*
         * Parse string
         */
        $configs = explode(':', $string);
        foreach ($configs as $config) {
            list($key, $value) = explode('=', $config);
            switch ($key) {
                case 'D':
                    $s_db = $value;
                    break;
                case 'T':
                    $s_table = $value;
                    break;
                case 'C':
                    $s_column = $value;
                    break;
                case 'F':
                    $s_filter = $value;
                    break;
                case 'K':
                    $s_key = $value;
                    break;
                case 'CK':
                    $s_column_key = $value;
                    break;
                case 'RPN':
                    $s_rpn = $value;
                    break;
            }
        }
        /*
         * Construct query
         */
        if (!isset($s_table) || !isset($s_column)) {
            return false;
        }
        $query = "SELECT `" . $s_column . "` FROM `" . $s_table . "`";
        if (isset($s_column_key) && isset($s_key)) {
            $query .= " WHERE `" . $s_column_key . "` = '" . $s_key . "'";
        }

        /*
         * Execute the query
         */
        switch ($s_db) {
            case 'centreon':
                $res = $this->db->query($query);
                break;
            case 'centreon_storage':
                $res = $monitoringDb->query($query);
                break;
        }
        if (PEAR::isError($res)) {
            return false;
        }
        $infos = array();
        while ($row = $res->fetchRow()) {
            $val = $row[$s_column];
            if (!is_null($s_rpn)) {
                $val = $this->rpnCalc($s_rpn, $val);
            }
            $infos[] = $val;
        }
        if (count($infos) == 0) {
            return "";
        } elseif (count($infos) == 1) {
            return $infos[0];
        }
        return $infos;
    }

    /**
     * Apply a simple RPN operation
     *
     * The rpn operation begin by the value
     *
     * @param string $rpn The rpn operation
     * @param int $val The value for apply
     * @return The value with rpn apply or the value is errors
     */
    private function rpnCalc($rpn, $val)
    {
        if (!is_numeric($val)) {
            return $val;
        }
        try {
            $val = array_reduce(
                preg_split('/\s+/', $val . ' ' . $rpn),
                array($this, 'rpnOperation')
            );
            return $val[0];
        } catch (InvalidArgumentException $e) {
            return $val;
        }
    }

    /**
     * Apply the operator
     *
     * @param array $result List of numerics
     * @param mixed $item Current item
     * @throws InvalidArgumentException
     * @return array
     */
    private function rpnOperation($result, $item)
    {
        if (in_array($item, array('+', '-', '*', '/'))) {
            if (count($result) < 2) {
                throw new InvalidArgumentException('Not enough arguments to apply operator');
            }
            $a = $result[0];
            $b = $result[1];
            $result = array();
            $result[0] = eval("return $a $item $b;");
        } elseif (is_numeric($item)) {
            $result[] = $item;
        } else {
            throw new InvalidArgumentException('Unrecognized symbol ' . $item);
        }
        return $result;
    }

    /**
     * Check event max queue size value
     *
     * if the value is too small, centreon broker will spend time
     * to write information directly to hard drive. So we prefer to
     * use more memory in order to avoid IO.
     *
     * @param int maximum number of event in the queue
     * @return int maximum number of event in the queue
     *
     */
    private function checkEventMaxQueueSizeValue($value)
    {
        if (!isset($value) || $value == "" || $value < 100000) {
            $value = 100000;
        }
        return $value;
    }

    /**
     * Get the element name for form
     *
     * @param string $tag The tag name
     * @param int $formId The form id
     * @param array $field The field information
     * @return string
     */
    private function getElementName($tag, $formId, $field, &$isMultiple = false)
    {
        $elementName = $tag . '[' . $formId . '][';
        if ($field['group'] !== '') {
            $elementName .= $this->getParentGroups($field['group'], $isMultiple);
        }
        $elementName .= $field['fieldname']. (($isMultiple) ? "_#index#" : "") . ']';
        return $elementName;
    }

    /**
     * Get the string for parent groups
     *
     * @param int $groupId The group id
     * @return string
     */
    public function getParentGroups($groupId, &$isMultiple = false, &$displayName = "")
    {
        $elemStr = '';
        $res = $this->db->query(
            sprintf(
                "SELECT groupname, group_parent_id, multiple, displayname
                FROM cb_fieldgroup WHERE cb_fieldgroup_id = %d",
                $groupId
            )
        );
        if (PEAR::isError($res)) {
            return '';
        }
        $row = $res->fetchRow();
        if ($row['group_parent_id'] !== '') {
            $elemStr .= $this->getParentGroups($row['group_parent_id'], $isMultiple, $displayName);
        }
        if ($row['multiple'] !== '' && $row['multiple'] == 1) {
            $isMultiple = true;
        }
        if (!$isMultiple) {
            $elemStr .=  $row['groupname'] . '__' . $this->nbSubGroup++ . '__';
        } else {
            if ($elemStr != "") {
                $elemStr .=   '__'.$row['groupname']. '__' ;
            } else {
                $elemStr .=   $row['groupname']. '__' ;
            }
        }
        if (!empty($row['displayname'])) {
            $displayName = $row['displayname'];
        }
        return $elemStr;
    }

    /**
     * Get configuration fieldname for loading configuration from database
     *
     * @param int $configId The configuration ID
     * @param string $configGroup The configuration group (tag)
     * @param array $info The information
     * @return string
     */
    private function getConfigFieldName($configId, $configGroup, $info)
    {
        $elemStr = $info['config_key'];
        if ($info['grp_level'] != 0) {
            $res = $this->db->query(sprintf(
                "SELECT config_key, config_value, config_group_id, grp_level, parent_grp_id
               FROM cfg_centreonbroker_info
               WHERE config_id = %d
                   AND config_group = '%s'
           AND subgrp_id = %d
           AND grp_level = %d
           AND config_group_id = %d",
                $configId,
                $configGroup,
                $info['parent_grp_id'],
                $info['grp_level'] - 1,
                $info['config_group_id']
            ));
            if (PEAR::isError($res) || $res->numRows() == 0) {
                return $elemStr;
            }
            $row = $res->fetchRow();
            $elemStr = $this->getConfigFieldName(
                $configId,
                $configGroup,
                $row
            ) . '__' . $info['parent_grp_id'] . '__' . $elemStr;
        }
        return $elemStr;
    }

    /**
     *
     * @return array
     */
    public function isExist($sName)
    {
        $bExist = 0;
        if (empty($sName)) {
            return $bExist;
        }

        $query = "SELECT COUNT(config_id) as nb FROm cfg_centreonbroker
            WHERE config_name = '" . $this->db->escape($sName) . "'";
        $res = $this->db->query($query);
        $row = $res->fetchRow();
        if ($row['nb'] > 0) {
            $bExist = 1;
        }

        return $bExist;
    }
}
