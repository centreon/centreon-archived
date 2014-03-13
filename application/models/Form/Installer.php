<?php

namespace Models\Form;

class Installer
{
    protected static $forms = array();
    protected static $sections = array();
    protected static $blocks = array();
    protected static $fields = array();

    /**
     * Init arrays
     *
     */
    public static function init()
    {
        $sql = "SELECT f.form_id, f.name as form_name, 
            s.section_id, s.name as section_name, 
            b.block_id, b.name as block_name,
            d.field_id, d.name as field_name
            FROM form f, form_section s, form_block b, form_field d, form_block_field_relation r
            WHERE f.form_id = s.form_id
            AND s.section_id = b.section_id
            AND b.block_id = r.block_id
            AND r.field_id = d.field_id";
        $db = \Centreon\Core\Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $form_key = $row['form_name'];
            $section_key = $form_key . ';' . $row['section_name'];
            $block_key = $section_key . ';' . $row['block_name'];
            $field_key = $block_key . ';' . $row['field_name'];
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
        $db = \Centreon\Core\Di::getDefault()->get('db_centreon');
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
        self::$forms[$key] = $db->lastInsertId('form', 'form_id');
    }

    /**
     * Insert section into db
     *
     * @param array $data
     */
    public static function insertSection($data)
    {
        $key = $data['form_name'] . ';' . $data['name'];
        $db = \Centreon\Core\Di::getDefault()->get('db_centreon');
        if (!isset(self::$sections[$key])) {
            $sql = 'INSERT INTO form_section (name, rank, form_id) 
                VALUES (:name, :rank, :form_id)';
        } else {
            $sql = 'UPDATE form_section SET rank = :rank,
                form_id = :form_id
                WHERE name = :name';
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':rank', $data['rank'], \PDO::PARAM_INT);
        $stmt->bindParam(':form_id', self::$forms[$data['form_name']], \PDO::PARAM_INT);
        $stmt->execute();
        self::$sections[$key] = $db->lastInsertId('form_section', 'section_id');
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
        $db = \Centreon\Core\Di::getDefault()->get('db_centreon');
        if (!isset(self::$blocks[$key])) {
            $sql = 'INSERT INTO form_block (name, rank, section_id) 
                VALUES (:name, :rank, :section_id)';
        } else {
            $sql = 'UPDATE form_block SET rank = :rank,
                section_id = :section_id
                WHERE name = :name'; 
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':rank', $data['rank'], \PDO::PARAM_INT);
        $stmt->bindParam(':section_id', self::$sections[$sectionKey], \PDO::PARAM_INT);
        $stmt->execute();
        self::$blocks[$key] = $db->lastInsertId('form_block', 'block_id');
    }

    /**
     * Insert field into db
     *
     * @param array $data
     */
    public static function insertField($data)
    {
        $key = $data['name'];
        $db = \Centreon\Core\Di::getDefault()->get('db_centreon');
        if (!isset(self::$fields[$key])) {
            $sql = 'INSERT INTO form_field (name, label, default_value, attributes, advanced, type, help, module_id, parent_field, child_actions) 
                VALUES (:name, :label, :default_value, :attributes, :advanced, :type, :help, :module_id, :parent_field, :child_actions)';
        } else {
            $sql = 'UPDATE form_field SET label = :label,
                default_value = :default_value,
                attributes = :attributes,
                advanced = :advanced,
                type = :type,
                help = :help,
                module_id = :module_id,
                parent_fields = :parant_field,
                child_actions = :child_actions
                WHERE name = :name';
        }
        $stmt = $db->prepare($sql);
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
        self::$fields[$key] = $db->lastInsertId('form_field', 'field_id');
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
            $db = \Centreon\Core\Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare('REPLACE INTO form_block_field_relation (block_id, field_id, rank, mandatory) 
                VALUES (:block_id, :field_id, :rank, :mandatory)');
            $stmt->bindParam(':block_id', self::$blocks[$key]);
            $stmt->bindParam(':field_id', self::$fields[$fname]);
            $stmt->bindParam(':rank', $data['rank']);
            $stmt->bindParam(':mandatory', $data['mandatory']);
            $stmt->execute();
        }
    }

    /**
     * Install form from XML string
     *
     * @param string $xmlFile
     */
    public static function installFromXml($xmlFile = "")
    {
        $xml = simplexml_load_file($xmlFile);
        self::init();
        foreach ($xml as $form) {
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
                        $fieldData = array(
                            'name' => $field['name'],
                            'label' => $field['label'],
                            'default_value' => $field['default_value'],
                            'advanced' => $field['advanced'],
                            'type' => $field['type'],
                            'parent_field' => $field['parent_field'],
                            'module_id' => $field['module_id'],
                            'child_actions' => $field->child_actions,
                            'attributes' => $field->attributes
                        );
                        self::insertField(array_map('strval', $fieldData));
                        self::addFieldToBlock(
                            array_map(
                                'strval', 
                                array(
                                    'form_name' => $form['name'],
                                    'section_name' => $section['name'],
                                    'block_name' => $block['name'], 
                                    'field_name' => $field['name'],
                                    'mandatory' => $field['mandatory'],
                                    'rank' => $fieldRank
                                )
                            )
                        );
                        $fieldRank++;
                    }
                }
            }
        }
    }
}
