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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

/**
 * Class for Centreon Broker configuration
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 */
class CentreonConfigCentreonBroker
{
    private $db;
    private $attrText = array("size"=>"30");
    private $attrInt = array("size"=>"10", "class" => "v_number");

    private $tagsCache = null;
    private $typesCache = null;
    private $typesNameCache = null;
    private $blockCache = array();
    private $fieldtypeCache = array();
    private $blockInfoCache = array();
    private $listValues = array();
    private $defaults = array();

    const CORRELATION_STRING = 'correlation_file';

    /**
     * Construtor
     *
     * @param CentreonDB $db The connection to centreon database
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function __sleep()
    {
        $this->db = null;
        return array('attrText', 'attrInt', 'tagsCache', 'typesCache', 'blockCache', 'blockInfoCache', 'listValues', 'defaults', 'fieldtypeCache');
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
        $query = "SELECT cb_tag_id, tagname
        	FROM cb_tag
        	ORDER BY tagname";
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

    /**
     * Create the HTML_QuickForm object with element for a block
     *
     * @param int $blockId The block id ('tag_id'_'type_id')
     * @param int $page The centreon page id
     * @param int $formId The form post
     * @return HTML_QuickForm
     */
    public function quickFormById($blockId, $page, $formId = 1)
    {
        list($tagId, $typeId) = explode('_', $blockId);
        $fields = $this->getBlockInfos($typeId);
        $tag = $this->getTagName($tagId);

        $qf = new HTML_QuickForm('form_' . $formId, 'post', '?p=' . $page);

        $qf->addElement('text', $tag . '[' . $formId . '][name]', _('Name'), array_merge($this->attrText, array(
            'id' => $tag . '[' . $formId . '][name]',
            'class' => 'v_required'
        )));
        //$qf->addRule($tag . '[' . $formId . '][name]', _('Name'), 'required');

        $type = $this->getTypeShortname($typeId);
        $qf->addElement('hidden', $tag . '[' . $formId . '][type]');
        $qf->setDefaults(array($tag . '[' . $formId . '][type]' => $type));

        $typeName = $this->getTypeName($typeId);
        $qf->addElement('header', 'typeName', $typeName);

        $qf->addElement('hidden', $tag . '[' . $formId . '][blockId]');
        $qf->setDefaults(array($tag . '[' . $formId . '][blockId]' => $blockId));

        foreach ($fields as $field) {
            $elementName = $tag . '[' . $formId . '][' . $field['fieldname'] . ']';
            $elementType = null;
            $elementAttr = array();
            $default = null;
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
                   foreach ($this->getListValues($field['id']) as $key => $value) {
    	               $tmpRadio[] = HTML_QuickForm::createElement('radio', $field['fieldname'], null, _($value), $key);
                   }
	               $qf->addGroup($tmpRadio, $elementName, _($field['displayname']), '&nbsp;');
                   $default = $this->getDefaults($field['id']);
                   break;
               case 'password':
                   $elementType = 'password';
                   $elementAttr = $this->attrText;
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
                    $qf->addElement('select', $elementName, _($field['displayname']), $roValue);
                } else {
                    $qf->addElement('text', $elementName, _($field['displayname']), $this->attrText);
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

            /*
             * Add elements
             */
            if (!is_null($elementType)) {
                $qf->addElement($elementType, $elementName, _($field['displayname']), $elementAttr);
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
        $query = "SELECT f.cb_field_id, f.fieldname, f.displayname, f.fieldtype, f.description, f.external, tfr.is_required, tfr.order_display
        	FROM cb_field f, cb_type_field_relation tfr
        		WHERE f.cb_field_id = tfr.cb_field_id AND (tfr.cb_type_id = %d
        			OR tfr.cb_type_id IN (SELECT t.cb_type_id
        				FROM cb_type t, cb_module_relation mr
        				WHERE mr.inherit_config = 1 AND t.cb_module_id IN (SELECT mr2.module_depend_id
        					FROM cb_type t2, cb_module_relation mr2
        					WHERE t2.cb_module_id = mr2.cb_module_id AND mr2.inherit_config = 1 AND t2.cb_type_id = %d)))
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
     * Insert a configuration into the database
     *
     * @param array $values The post array
     * @return bool
     */
    public function insertConfig($values)
    {
    	/*
	     * Insert the Centreon Broker configuration
	     */
	    $query = "INSERT INTO cfg_centreonbroker (config_name, config_filename, config_activate, ns_nagios_server, event_queue_max_size) VALUES (
                            '" . $this->db->escape($values['name']) . "', 
                            '" . $this->db->escape($values['filename']) . "', 
                            '" . $this->db->escape($values['activate']['activate']) . "',
                            " . $this->db->escape($values['ns_nagios_server']) . ", 
                            ".$this->db->escape((int)$values['event_queue_max_size']).")";
	    if (PEAR::isError($this->db->query($query))) {
	        return false;
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
	    return $this->updateCentreonBrokerInfos($id, $values);
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
                config_filename = '" . $this->db->escape($values['filename']) . "', 
                config_activate = '" . $this->db->escape($values['activate']['activate']) . "', 
                ns_nagios_server = " . $this->db->escape($values['ns_nagios_server']) . ",
                event_queue_max_size = ".(int)$this->db->escape($values['event_queue_max_size'])."
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
        foreach ($this->getTags() as $group) {
	        /*
	         * Resort array
	         */
	        if (isset($values[$group])) {
        	    foreach ($values[$group] as $infos) {
        	        if (!isset($groups_infos[$group])) {
        	            $groups_infos[$group] = array();
        	        }
        	        $groups_infos[$group][] = $infos;
        	    }
	        }
	    }

	    foreach ($groups_infos as $group => $groups) {
	        foreach ($groups as $gid => $infos) {
	            $gid = $gid + 1;
	            if (isset($infos['blockId'])) {
	                list($tagId, $typeId) = explode('_', $infos['blockId']);
	                $fieldtype = $this->getFieldtypes($typeId);
    	            foreach ($infos as $fieldname => $fieldvalue) {
    	                if (isset($fieldtype[$fieldname]) && $fieldtype[$fieldname] == 'radio') {
    	                    $fieldvalue = $fieldvalue[$fieldname];
    	                }
    	                $query = "INSERT INTO cfg_centreonbroker_info (config_id, config_key, config_value, config_group, config_group_id)
    	            		VALUES (" . $id . ", '" . $fieldname . "', '" . $fieldvalue . "', '" . $group . "', " . $gid . ")";
    	                $this->db->query($query);
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
        $query = "SELECT config_key, config_value, config_group_id
        	FROM cfg_centreonbroker_info
        	WHERE config_id = %d AND config_group = '%s'
        	ORDER BY config_group_id";
        $res = $this->db->query(sprintf($query, $config_id, $tag));
        if (PEAR::isError($res)) {
            return array();
        }
        $formsInfos = array();
        while ($row = $res->fetchRow()) {
            $fieldname = $tag . '[' . $row['config_group_id'] . '][' . $row['config_key'] . ']';
            $formsInfos[$row['config_group_id']]['defaults'][$fieldname] = $row['config_value'];
            $formsInfos[$row['config_group_id']]['defaults'][$fieldname . '[' . $row['config_key'] . ']'] = $row['config_value']; // Radio button
            if ($row['config_key'] == 'blockId') {
                $formsInfos[$row['config_group_id']]['blockId'] = $row['config_value'];
            }
        }
        $forms = array();
        foreach (array_keys($formsInfos) as $key) {
            $qf = $this->quickFormById($formsInfos[$key]['blockId'], $page, $key);
            /*
             * Replace loaded configuration with defaults external values
             */
            list($tagId , $typeId) = explode('_', $formsInfos[$key]['blockId']);
            $tag = $this->getTagName($tagId);
            $fields = $this->getBlockInfos($typeId);
            foreach ($fields as $field) {
                if (!is_null($field['value']) && $field['value'] != false) {
                    $elementName = $tag . '[' . $key . '][' . $field['fieldname'] . ']';
                    unset($formsInfos[$key]['defaults'][$elementName]); // = $this->getInfoDb($field['value']);
                }
            }

            $qf->setDefaults($formsInfos[$key]['defaults']);
            $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
            $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
        	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
        	$qf->accept($renderer);
            $forms[] = $renderer->toArray();
        }
        return $forms;
    }

    /**
     * Get the correlation file
     *
     * @return mixed false in error or does not set, or string the path file
     */
    public function getCorrelationFile()
    {
        $query = "SELECT config_value
        	FROM cfg_centreonbroker_info
        	WHERE config_key = 'file' AND config_group = 'correlation'";
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
        $query = "SELECT config_value
        	FROM cfg_centreonbroker_info
        	WHERE config_id = %d AND config_group = '%s'
        	AND config_key = 'blockId'
        	ORDER BY config_group_id";
        $res = $this->db->query(sprintf($query, $config_id, $tag));
        if (PEAR::isError($res)) {
            return array();
        }
        $helps = array();
        $pos = 1;
        while ($row = $res->fetchRow()) {
            list($tagId, $typeId) = explode('_', $row['config_value']);
            $fields = $this->getBlockInfos($typeId);
            $help = array();
            $help[] = array('name' => $tag . '[' . $pos . '][name]', 'desc' => _('The name of block configuration'));
        	$help[] = array('name' => $tag . '[' . $pos . '][type]', 'desc' => _('The type of block configuration'));
        	foreach ($fields as $field) {
        	    $help[] = array('name' => $tag . '[' . $pos . '][' . $field['fieldname'] . ']', 'desc' => _($field['description']));
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
    private function getDefaults($fieldId)
    {
        if (isset($this->defaults[$fieldId])) {
            return $this->defaults[$fieldId];
        }
        $query = "SELECT default_value
        	FROM cb_list
        	WHERE cb_field_id = %d";
        $res = $this->db->query(sprintf($query, $fieldId));
        if (PEAR::isError($res)) {
            return null;
        }
        $row = $res->fetchRow();

        $this->defaults[$fieldId] = null;
        if (!is_null($row)) {
            if (!is_null($row['default_value']) && $row['default_value'] != '') {
                $this->defaults[$fieldId] = $row['default_value'];
            }
        }
        return $this->defaults[$fieldId];
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
                $res = $pearDBO->query($query);
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
        if (count($infos) == 1) {
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
}
?>