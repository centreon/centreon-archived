<?php

namespace Centreon\Internal\Form;

class Installer
{
    protected static $forms;
    protected static $sections;
    protected static $blocks;
    protected static $fields;
    protected static $blockFields;
    protected static $steps;
    protected static $wizards;
    protected static $stepFields;

    /**
     * Init arrays
     *
     * @param string $formName
     * @param string $wizardName
     */
    public static function initForm($formName)
    {
        $sql = "SELECT f.form_id, f.name as form_name, 
            s.section_id, s.name as section_name, 
            b.block_id, b.name as block_name,
            d.field_id, d.name as field_name
            FROM form f, form_section s, form_block b, form_field d, form_block_field_relation r
            WHERE f.form_id = s.form_id
            AND s.section_id = b.section_id
            AND b.block_id = r.block_id
            AND r.field_id = d.field_id
            AND f.name = ?";
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array($formName));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        self::$forms = array();
        self::$sections = array();
        self::$blocks = array();
        self::$fields = array();
        self::$blockFields = array();
        foreach ($rows as $row) {
            $form_key = $row['form_name'];
            $section_key = $form_key . ';' . $row['section_name'];
            $block_key = $section_key . ';' . $row['block_name'];
            $field_key = $row['field_name'];
            $block_field_key = $block_key . ';' . $row['field_name'];
            if (!isset(self::$forms[$form_key])) {
                self::$forms[$form_key] = $row['form_id'];
            }
            if (!isset(self::$sections[$section_key])) {
                self::$sections[$section_key] = $row['section_id'];
            }
            if (!isset(self::$blocks[$block_key])) {
                self::$blocks[$block_key] = $row['block_id'];
            }
            if (!isset(self::$fields[$field_key])) {
                self::$fields[$field_key] = $row['field_id'];
            }
            if (!isset(self::$blockFields[$block_field_key])) {
                self::$blockFields[$block_field_key] = $row['block_id'] . ';' . $row['field_id'];
            }
        }
    }

    /**
     * Init arrays
     *
     * @param string $wizardName
     */
    public static function initWizard($wizardName)
    {
        $sql = "SELECT w.wizard_id, w.name as wizard_name, 
            s.step_id, s.name as step_name,
            d.field_id, d.name as field_name
            FROM form_wizard w, form_step s, form_step_field_relation r, form_field d
            WHERE w.wizard_id = s.wizard_id
            AND s.step_id = r.step_id
            AND r.field_id = d.field_id
            AND w.name = ?";
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array($wizardName));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        self::$wizards = array();
        self::$steps = array();
        self::$stepFields = array();
        foreach ($rows as $row) {
            $wizard_key = $row['wizard_name'];
            $step_key = $wizard_key . ';' . $row['step_name'];
            $field_key = $row['field_name'];
            $step_field_key = $step_key . ';' . $row['field_name'];
            if (!isset(self::$wizards[$wizard_key])) {
                self::$wizards[$wizard_key] = $row['wizard_id'];
            }
            if (!isset(self::$steps[$step_key])) {
                self::$steps[$step_key] = $row['step_id'];
            }
            if (!isset(self::$stepFields[$step_field_key])) {
                self::$stepFields[$step_field_key] = $row['step_id'] . ';' . $row['field_id'];
            }
        }
    }
    

    /**
     * Insert a form into db
     *
     * @param array $data
     */
    public static function insertForm($data)
    {
        $key = $data['name'];
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        if (!isset(self::$forms[$key])) {
            $sql = 'INSERT INTO form (name, route, redirect, redirect_route) 
              VALUES (:name, :route, :redirect, :redirect_route)';
        } else {
            $sql = 'UPDATE form SET route = :route,
                redirect = :redirect,
                redirect_route = :redirect_route
                WHERE name = :name';
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':route', $data['route']);
        $stmt->bindParam(':redirect', $data['redirect']);
        $stmt->bindParam(':redirect_route', $data['redirect_route']);
        $stmt->execute();
        if (!isset(self::$forms[$key])) {
            self::$forms[$key] = $db->lastInsertId('form', 'form_id');
        }
    }

    /**
     * Insert section into db
     *
     * @param array $data
     */
    public static function insertSection($data)
    {
        $key = $data['form_name'] . ';' . $data['name'];
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        if (!isset(self::$sections[$key])) {
            $sql = 'INSERT INTO form_section (name, rank, form_id) 
                VALUES (:name, :rank, :form_id)';
        } else {
            $sql = 'UPDATE form_section SET rank = :rank,
                form_id = :form_id
                WHERE name = :name
                AND section_id = :section_id';
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':rank', $data['rank'], \PDO::PARAM_INT);
        if (isset(self::$sections[$key])) {
            $stmt->bindParam(':section_id', $sections[$key]);
        }
        $stmt->bindParam(':form_id', self::$forms[$data['form_name']], \PDO::PARAM_INT);
        $stmt->execute();
        if (!isset(self::$sections[$key])) {
            self::$sections[$key] = $db->lastInsertId('form_section', 'section_id');
        }
    }

    /**
     * Insert block into db
     *
     * @param array $data
     */
    public static function insertBlock($data)
    {
        $sectionKey = $data['form_name'] . ';' . $data['section_name'];
        $key = implode(';', array($data['form_name'], $data['section_name'], $data['name']));
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        if (!isset(self::$blocks[$key])) {
            $sql = 'INSERT INTO form_block (name, rank, section_id) 
                VALUES (:name, :rank, :section_id)';
        } else {
            $sql = 'UPDATE form_block SET rank = :rank,
                section_id = :section_id
                WHERE name = :name
                AND block_id = :block_id';
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':rank', $data['rank'], \PDO::PARAM_INT);
        if (isset(self::$blocks[$key])) {
            $stmt->bindParam(':block_id', self::$blocks[$key]);
        }
        $stmt->bindParam(':section_id', self::$sections[$sectionKey], \PDO::PARAM_INT);
        $stmt->execute();
        if (!isset(self::$blocks[$key])) {
            self::$blocks[$key] = $db->lastInsertId('form_block', 'block_id');
        }
    }

    /**
     * Insert field into db
     *
     * @param array $data
     */
    public static function insertField($data)
    {
        $key = $data['name'];
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        if (!isset(self::$fields[$key])) {
            $sql = 'INSERT INTO form_field 
                (name, label, default_value, attributes, advanced, type, 
                help, module_id, parent_field, child_actions) VALUES 
                (:name, :label, :default_value, :attributes, :advanced, 
                :type, :help, :module_id, :parent_field, :child_actions)';
        } else {
            $sql = 'UPDATE form_field SET label = :label,
                default_value = :default_value,
                attributes = :attributes,
                advanced = :advanced,
                type = :type,
                help = :help,
                module_id = :module_id,
                parent_field = :parent_field,
                child_actions = :child_actions
                WHERE name = :name
                AND field_id = :field_id';
        }
        $stmt = $db->prepare($sql);
        if (isset(self::$fields[$key])) {
            $stmt->bindParam(':field_id', self::$fields[$key]);
        }
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':label', $data['label']);
        $stmt->bindParam(':default_value', $data['default_value']);
        $stmt->bindParam(':attributes', $data['attributes']);
        $stmt->bindParam(':advanced', $data['advanced']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':help', $data['help']);
        $stmt->bindParam(':module_id', $data['module_id']);
        $stmt->bindParam(':parent_field', $data['parent_field']);
        $stmt->bindParam(':child_actions', $data['child_actions']);
        $stmt->execute();
        if (!isset(self::$fields[$key])) {
            self::$fields[$key] = $db->lastInsertId('form_field', 'field_id');
        }
    }

    /**
     * Add field to a block
     *
     * @param array $data
     */
    public static function addFieldToBlock($data)
    {
        $fname = $data['field_name'];
        $key = implode(';', array($data['form_name'], $data['section_name'], $data['block_name']));
        if (isset(self::$blocks[$key]) && isset(self::$fields[$fname])) {
            $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare('DELETE FROM form_block_field_relation 
                WHERE block_id = :block_id AND field_id = :field_id');
            $stmt->bindParam(':block_id', self::$blocks[$key]);
            $stmt->bindParam(':field_id', self::$fields[$fname]);
            $stmt->execute();

            $stmt = $db->prepare('REPLACE INTO form_block_field_relation (block_id, field_id, rank, mandatory) 
                VALUES (:block_id, :field_id, :rank, :mandatory)');
            $stmt->bindParam(':block_id', self::$blocks[$key]);
            $stmt->bindParam(':field_id', self::$fields[$fname]);
            $stmt->bindParam(':rank', $data['rank']);
            $stmt->bindParam(':mandatory', $data['mandatory']);
            $stmt->execute();
        }
        $tmp = $key . ';' . $fname;
        self::$blockFields[$tmp] = self::$blocks[$key] . ';' . self::$fields[$fname];
    }

    /**
     * Install form from XML string
     *
     * @param string $xmlFile
     */
    public static function installFromXml($moduleId, $xmlFile = "")
    {
        $xml = simplexml_load_file($xmlFile);
        foreach ($xml as $form) {
            if ($form->getName() == 'form') {
                self::processForm($moduleId, $form);
            } elseif ($form->getName() == 'wizard') {
                self::processWizard($form);
            }
        }
    }

    /**
     * Insert wizard
     *
     * @param array $data
     */
    protected static function insertWizard($data)
    {
        $key = $data['name'];
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        if (!isset(self::$wizards[$key])) {
            $sql = 'INSERT INTO form_wizard (name, route) 
              VALUES (:name, :route)';
        } else {
            $sql = 'UPDATE form_wizard SET route = :route
                WHERE name = :name 
                AND wizard_id = :wizard_id';
        }
        $stmt = $db->prepare($sql);
        if (isset(self::$wizards[$key])) {
            $stmt->bindParam(':wizard_id', self::$wizards[$key]);
        }
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':route', $data['route']);
        $stmt->execute();
        if (!isset(self::$wizards[$key])) {
            self::$wizards[$key] = $db->lastInsertId('form_wizard', 'wizard_id');
        }
    }

    /**
     * Insert step
     *
     * @param array $data
     */
    protected static function insertStep($data)
    {
        $key = implode(';', array($data['wizard_name'], $data['name']));
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        if (!isset(self::$steps[$key])) {
            $sql = 'INSERT INTO form_step (name, rank, wizard_id) 
                VALUES (:name, :rank, :wizard_id)';
        } else {
            $sql = 'UPDATE form_step SET rank = :rank,
                wizard_id = :wizard_id
                WHERE name = :name
                AND step_id = :step_id'; 
        }
        $stmt = $db->prepare($sql);
        if (isset(self::$steps[$key])) {
            $stmt->bindParam(':step_id', self::$steps[$key]);
        }
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':rank', $data['rank'], \PDO::PARAM_INT);
        $stmt->bindParam(':wizard_id', self::$wizards[$data['wizard_name']], \PDO::PARAM_INT);
        $stmt->execute();
        if (!isset(self::$steps[$key])) {
            self::$steps[$key] = $db->lastInsertId('form_step', 'step_id'); 
        }
    }

    /**
     * Add field to step
     *
     * @param array $data
     */
    protected static function addFieldToStep($data)
    {
        $fname = $data['field_name'];
        $key = implode(';', array($data['wizard_name'], $data['step_name']));
        if (isset(self::$steps[$key]) && isset(self::$fields[$fname])) {
            $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare('REPLACE INTO form_step_field_relation (step_id, field_id, rank, mandatory) 
                VALUES (:step_id, :field_id, :rank, :mandatory)');
            $stmt->bindParam(':step_id', self::$steps[$key]);
            $stmt->bindParam(':field_id', self::$fields[$fname]);
            $stmt->bindParam(':rank', $data['rank']);
            $stmt->bindParam(':mandatory', $data['mandatory']);
            $stmt->execute();
        }
        $tmp = $key . ';' . $fname;
        self::$stepFields[$tmp] = self::$steps[$key] . ';' . self::$fields[$fname];
    }

    /**
     * Process wizard
     *
     * @param SimpleXMLElement $wizard
     */
    protected static function processWizard($wizard)
    {
        $insertedSteps = array();
        $insertedFields = array();
        self::initWizard($wizard['name']);
        $wizardData = array(
            'name' => $wizard['name'],
            'route' => $wizard->route
        );
        self::insertWizard(array_map('strval', $wizardData));
        $stepRank = 1;
        foreach ($wizard->step as $step) {
            $stepData = array(
                'name' => $step['name'],
                'wizard_name' => $wizard['name'],
                'rank' => $stepRank
            );
            self::insertStep(array_map('strval', $stepData));
            $stepRank++;
            $fieldRank = 1;
            foreach ($step->field as $field) {
                $stepFieldData = array(
                    'wizard_name' => $wizard['name'],
                    'step_name' => $step['name'],
                    'field_name' => $field['name'],
                    'mandatory' => $field['mandatory'],
                    'rank' => $fieldRank
                );
                self::addFieldToStep(array_map('strval', $stepFieldData));
                $fieldRank++;
                $insertedFields[] = implode(';', array($wizard['name'], $step['name'], $field['name']));
            }
            $insertedSteps[] = implode(';', array($wizard['name'], $step['name']));
        }
        self::purgeSteps($insertedSteps);
        self::purgeStepFields($insertedFields);
    }

    /**
     * Process form
     *
     * @param SimpleXMLElement $form
     */
    protected static function processForm($moduleId, $form)
    {
        $insertedSections = array();
        $insertedBlocks = array();
        $insertedFields = array();
        self::initForm($form['name']);
        $formData = array(
            'name' => $form['name'],
            'route' => $form->route,
            'redirect' => $form->redirect,
            'redirect_route' => $form->redirect_route
        );
        self::insertForm(array_map('strval', $formData));
        $sectionRank = 1;
        foreach ($form->section as $section) {
            $sectionData = array(
                'name' => $section['name'],
                'form_name' => $form['name'],
                'rank' => $sectionRank
            );
            self::insertSection(array_map('strval', $sectionData));
            $sectionRank++;
            $blockRank = 1;
            foreach ($section->block as $block) {
                $blockData = array(
                    'name' => $block['name'],
                    'form_name' => $form['name'],
                    'section_name' => $section['name'],
                    'rank' => $blockRank
                );
                self::insertBlock(array_map('strval', $blockData));
                $blockRank++;
                $fieldRank = 1;
                foreach ($block->field as $field) {
                    $attributes = array();
                    if (isset($field->attributes)) {
                        $attributes = self::parseAttributes($field->attributes);
                    }
                    $attributes = json_encode($attributes);
                    $fieldData = array(
                        'name' => $field['name'],
                        'label' => $field['label'],
                        'default_value' => $field['default_value'],
                        'advanced' => $field['advanced'],
                        'type' => $field['type'],
                        'parent_field' => $field['parent_field'],
                        'module_id' => $moduleId,
                        'child_actions' => $field->child_actions,
                        'attributes' => $attributes,
                        'help' => $field->help
                    );
                    self::insertField(array_map('strval', $fieldData));
                    $blockFieldData = array(
                        'form_name' => $form['name'],
                        'section_name' => $section['name'],
                        'block_name' => $block['name'], 
                        'field_name' => $field['name'],
                        'mandatory' => $field['mandatory'],
                        'rank' => $fieldRank
                    );
                    self::addFieldToBlock(array_map('strval', $blockFieldData));
                    $fieldRank++;
                    $insertedFields[] = implode(
                        ';', 
                        array($form['name'], $section['name'], $block['name'], $field['name'])
                    );
                }
                $insertedBlocks[] = implode(';', array($form['name'], $section['name'], $block['name']));
            }
            $insertedSections[] = implode(';', array($form['name'], $section['name']));
        }
        self::purgeFields($insertedFields);
        self::purgeBlocks($insertedBlocks);
        self::purgeSections($insertedSections);
    }
    
    /**
     * 
     * @param type $attributes
     * @return boolean
     */
    protected static function parseAttributes($attributes)
    {
        $finalAttributes = array();
        foreach($attributes->children() as $attr) {
            
            $attrName = $attr->getName();
            if (isset($attr['name']) && $attr['name']) {
                $attrName = $attr['name'];
            }
            
            if (count($attr->children()) > 0) {
                $finalAttributes[$attrName] = self::parseAttributes($attr);
            } else {
                $finalAttributes[$attrName] = $attr->__toString();
                if ($finalAttributes[$attrName] == "true") {
                    $finalAttributes[$attrName] = true;
                } elseif ($finalAttributes[$attrName] == "false") {
                    $finalAttributes[$attrName] = false;
                }
            }
        }
        return $finalAttributes;
    }

    /**
     * Purge fields
     *
     * @param array $insertedFields
     */
    protected static function purgeFields($insertedFields)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $db->beginTransaction();
        $stmt = $db->prepare("DELETE FROM form_block_field_relation WHERE CONCAT_WS(';', block_id, field_id) = ?");
        foreach (self::$blockFields as $key => $value) {
            if (!in_array($key, $insertedFields)) {
                $stmt->execute(array($value));
            }
        }
        $db->commit();
        $stmt = $db->prepare("DELETE FROM form_field 
            WHERE NOT EXISTS
            (SELECT field_id FROM form_block_field_relation r WHERE r.field_id = form_field.field_id)");
        $stmt->execute();
    }

    /**
     * Purge blocks
     *
     * @param array $insertedBlocks
     */
    protected static function purgeBlocks($insertedBlocks)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $db->beginTransaction();
        $stmt = $db->prepare("DELETE FROM form_block WHERE block_id = ?");
        foreach (self::$blocks as $key => $value) {
            if (!in_array($key, $insertedBlocks)) {
                $stmt->execute(array($value));
            }
        }
        $db->commit();
    }

    /**
     * Purge sections
     *
     * @param array $insertedSections
     */
    protected static function purgeSections($insertedSections)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $db->beginTransaction();
        $stmt = $db->prepare("DELETE FROM form_section WHERE section_id = ?");
        foreach (self::$sections as $key => $value) {
            if (!in_array($key, $insertedSections)) {
                $stmt->execute(array($value));
            }
        }
        $db->commit();
    }

    /**
     * Purge steps
     *
     * @param array $insertedSteps
     */
    protected static function purgeSteps($insertedSteps)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $db->beginTransaction();
        $stmt = $db->prepare("DELETE FROM form_step WHERE step_id = ?");
        foreach (self::$steps as $key => $value) {
            if (!in_array($key, $insertedSteps)) {
                $stmt->execute(array($value));
            }
        }
        $db->commit();
    }

    /**
     * Purge step fields
     *
     * @param array $insertedFields
     */
    protected static function purgeStepFields($insertedFields)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $db->beginTransaction();
        $stmt = $db->prepare("DELETE FROM form_step_field_relation WHERE CONCAT_WS(';', step_id, field_id) = ?");
        foreach (self::$stepFields as $key => $value) {
            if (!in_array($key, $insertedFields)) {
                $stmt->execute(array($value));
            }
        }
        $db->commit();
    }
    
    /**
     * 
     */
    public static function cleanDb($moduleId)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        
        // Remove the field first
        $sqlFields = "DELETE FROM form_field WHERE module_id = '$moduleId'";
        $stmtRemoveFields = $db->query($sqlFields);
        
        // First clean the block
        $sqlBlock = "DELETE FROM form_block WHERE NOT EXISTS (SELECT block_id FROM form_block_field_relation)";
        $stmtRemoveBlock = $db->query($sqlBlock);
        
        // Second clean the section
        $sqlSection = "DELETE FROM form_section WHERE NOT EXISTS (SELECT section_id FROM form_block)";
        $stmtRemoveSection = $db->query($sqlSection);
        
        
        // Then last but not least clean the form
        $sqlForm = "DELETE FROM form WHERE NOT EXISTS (SELECT form_id FROM form_section)";
        $stmtForm = $db->query($sqlForm);
        
        
    }
}
