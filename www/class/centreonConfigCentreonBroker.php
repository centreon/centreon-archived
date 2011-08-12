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

class CentreonConfigCentreonBroker
{
    private $db;
    private $tags = array();
    private $attrText = array("size"=>"30");
    private $attrInt = array("size"=>"10");
    
    private $blockInfoCache = array();
    private $listValues = array();
    
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
        return array('tags', 'attrText', 'attrInt', 'blockInfoCache', 'listValues');
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
     * Get the list of tags
     * 
     * @param int $blockId The block id
     * @return array
     */
    public function getListTagsByBlockId($blockId)
    {
        if (!is_null($this->tags[$blockId])) {
            return $this->tags[$blockId];
        }
        $this->tags[$blockId] = array();
        $query = "SELECT ct.tagname
        	FROM cb_config_tag ct, cb_config_block_tag_rel cbt
        	WHERE ct.cb_config_tag_id = cbt.cb_config_tag_id AND cbt.cb_config_block_id = %d
        	ORDER BY ct.tagname";
        $res = $this->db->query(sprintf($query, $blockId));
        if (!PEAR::isError($res)) {
            while ($row = $res->fetchRow()) {
                $this->tags[$blockId][] = $row['tagname'];
            }
        }
        return $this->tags[$blockId];
    }
    
    /**
     * Create the HTML_QuickForm object with element for a block
     * 
     * @param int $blockId The block id
     * @param string $tag The tag name
     * @param int $page The centreon page id
     * @param int $formId The form post
     * @return HTML_QuickForm
     */
    public function quickFormById($blockId, $tag, $page, $formId = 1)
    {
        $infos = $this->getBlockInfos($blockId);
        
        $qf = new HTML_QuickForm('form_' . $formId, 'post', '?p=' . $page);
        
        $qf->addElement('text', $tag . '[' . $formId . '][name]', _('Name'), $this->attrText);
        $qf->addRule($tag . '[' . $formId . '][name]', _('Name'), 'required');
        $qf->addElement('select', $tag . '[' . $formId . '][type]', _('Type'), $infos['types']);
        
        foreach ($infos['fields'] as $field) {
            $elementName = $tag . '[' . $formId . '][' . $field['fieldname'] . ']';
            $elementType = null;
            $elementAttr = array();
            switch ($field['fieldtype']) {
               case 'int':
                   $elementType = 'text';
                   $elementAttr = $this->attrInt;
                   break;
               case 'select':
                   $elementType = 'select';
                   $elementAttr = $this->getListValues($field['id']);
                   break;
               case 'radio':
                   $tmpRadio = array();
                   foreach ($this->getListValues($field['id']) as $key => $value) {
    	               $tmpRadio[] = HTML_QuickForm::createElement('radio', $field['name'], null, _($value), $key);
                   }
	               $qf->addGroup($tmpRadio, $elementName, _($field['displayname']), '&nbsp;');
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
            if (!is_null($field['value']) && $field['value'] === false) {
                $elementType = 'hidden';
                $roValue = $this->getInfoDb($field['value']);
                $field['value'] = 'DB[' . $field['value'] . ']';
                if (is_array($roValue)) {
                    $qf->addElement('select', $tag . '_' . $formId . '_' . $field['name'], _($field['displayname']), $roValue);
                } else {
                    $qf->addElement('text', $tag . '_' . $formId . '_' . $field['name'] , _($field['displayname']), $this->attrText);
                }
                $qf->freeze($roElementName);
            }
            
            /*
             * Add elements
             */
            if (!is_null($elementType)) {
                $qf->addElement($elementType, $elementName, _($field['displayname']), $elementAttr);
            }
            
            /*
             * If required
             */
            if ($field['required'] && is_null($field['value'])) {
                $qf->addRule($elementName, _($field['displayname']), 'required');
            }
            
            /*
             * Defaults values
             */
            if (!is_null($field['value']) && $field['value'] === false) {
                $qf->setDefaults(array($elementName, $field['value']));
            }
        }
        return $qf;
    }
    
    /**
     * Get informations for a block
     * 
     * @param int $blockId The block id
     * @return array
     */
    public function getBlockInfos($blockId)
    {
        if (isset($this->blockInfoCache[$blockId])) {
            return $this->blockInfoCache[$blockId];
        }
        /*
         * Get list of modules
         */
        $modules = array();
        $query = "SELECT m.cb_module_id
			FROM cb_module m, cb_config_block b, cb_config_block_module_rel mbr
				WHERE m.is_activated = 1
					AND (m.cb_module_id = mbr.cb_module_id AND b.cb_config_block_id = mbr.cb_config_block_id AND m.is_bundle = 0 AND b.cb_config_block_id = %d)
					OR (m.bundle = (
						SELECT m2.cb_module_id
							FROM cb_module m2, cb_config_block b2, cb_config_block_module_rel mbr2
							WHERE m2.is_activated = 1
								AND m2.cb_module_id = mbr2.cb_module_id AND b2.cb_config_block_id = mbr2.cb_config_block_id AND m2.is_bundle = 1 AND b2.cb_config_block_id = %d))";
        $res = $this->db->query(sprintf($query, $blockId, $blockId));
        if (PEAR::isError($res)) {
            return false;
        }
        while ($row = $res->fetchRow()) {
            $modules[] = $row['cb_module_id'];
        }
        
        /*
         * Get the list of type for a block
         */
        $types = array();
        $query = "SELECT type_name, type_shortname
        	FROM cb_type
        	WHERE module_id IN (%s)";
        $res = $this->db->query(sprintf($query, join(', ', $modules)));
        if (PEAR::isError($res)) {
            return false;
        }
        while ($row = $res->fetchRow()) {
            $types[$row['type_shortname']] = $row['type_name'];
        }
        
        /*
         * Get the list of fields for a block
         */
        $fields = array();
        $query = "SELECT f.cb_field_id, f.fieldname, f.displayname, f.fieldtype, f.description, f.external, mfr.is_required, mfr.order_display
        	FROM cb_field f, cb_module_field_rel mfr
        		WHERE f.cb_field_id = mfr.cb_field_id AND mfr.cb_module_id IN (%s)";
        $res = $this->db->query(sprintf($query, join(', ', $modules)));
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
        $this->blockInfoCache[$blockId] = array('types' => $types, 'fields' => $fields);
        return $this->blockInfoCache[$blockId];
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
     * Get static information from database
     * 
     * @param string $string The string for get information
     * @return mixed Information
     */
    private function getInfoDb($string)
    {
        global $pearDBO;
        
        /*
         * Default values
         */
        $s_db = "centreon";
        /*
         * Parse string
         */
        $values = explode(':', $string);
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
            }
        }
        /*
         * Construct query 
         */
        if (!isset($s_table) || !isset($s_column)) {
            return false;
        }        
        $query = "SELECT " . $s_column . " FROM " . $s_table;
        if (isset($s_column_key) && isset($s_key)) {
            $query .= " WHERE " . $s_column_key . " = '" . $s_key . "'";
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
            $infos[] = $row[$s_column];
        }
        if (count($infos) == 1) {
            return $infos[0];
        }
        return $infos;
    }    
}
?>