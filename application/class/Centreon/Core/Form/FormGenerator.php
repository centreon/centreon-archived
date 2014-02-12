<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 */

namespace Centreon\Core\Form;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
class FormGenerator
{
    /**
     *
     * @var type 
     */
    private $formName = '';
    
    /**
     *
     * @var type 
     */
    private $formRoute;
    
    /**
     *
     * @var type 
     */
    private $formComponents = array();
    
    /**
     *
     * @var type 
     */
    private $formDefautls = array();
    
    /**
     *
     * @var type 
     */
    private $formHandler;


    /**
     * 
     * @param type $formRoute
     * @param type $advanced
     */
    public function __construct($formRoute, $advanced = 0)
    {
        $this->formRoute = $formRoute;
        $fieldList = $this->getFormFromDatabase($advanced);
        $this->formHandler = new \Centreon\Core\Form($this->formName);
        $this->prepareForm($fieldList);
    }
    
    /**
     * 
     * @param type $formRoute
     * @param type $advanced
     * @return type
     */
    private function getFormFromDatabase($advanced = 0)
    {
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $queryForm = "SELECT id, name FROM form WHERE route = '$this->formRoute'";
        $stmtForm = $dbconn->query($queryForm);
        $formInfo = $stmtForm->fetchAll();
        
        $formId = $formInfo[0]['id'];
        $this->formName = $formInfo[0]['name'];
        
        $fieldQuery = 'SELECT fd.name AS "field_name", '
            . 'fd.label AS "field_label", '
            . 'fd.attributes AS "field_attributes", '
            . 'fd.default_value AS "field_default_value", '
            . 'fd.type AS "field_type", '
            . 'fhf.section AS "field_section", '
            . 'fhf.block AS "field_block", '
            . 'fhf.rank AS "field_rank", '
            . 'v.action AS "field_validator" '
            . 'FROM field fd, form_has_field fhf, validator v '
            . 'WHERE fhf.form_id =\''.$formId.'\''
            . 'AND fd.id = fhf.field_id '
            . 'AND fd.validator_id = v.id '
            . 'AND fd.advanced = \''.$advanced.'\' '
            . 'ORDER BY fhf.rank, fd.name';
        
        $fieldStmt = $dbconn->query($fieldQuery);
        return $fieldStmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 
     * @param type $fieldList
     */
    private function prepareForm($fieldList)
    {
        foreach ($fieldList as $field) {
            $section = $field['field_section'];
            $block = $field['field_block'];
            unset($field['field_section']);
            unset($field['field_block']);
            
            
            $currentSection = array_keys($this->formComponents);
            if (!in_array($section, $currentSection)) {
                $this->formComponents[$section] = array();
            }
            
            $currentBlock = array_keys($this->formComponents[$section]);
            if (!in_array($block, $currentBlock)) {
                $this->formComponents[$section][$block] = array();
            }
            
            $this->addFieldToForm($field);
            $this->formComponents[$section][$block][] = $field['field_name'];
            $this->formDefaults[$field['field_name']] = $field['field_default_value'];
        }
        
        $this->formComponents['General']['Save'][] = 'save_form';
        $this->formHandler->addSubmit('save_form', _("Save"));
    }
    
    private function addFieldToForm($field)
    {
        switch ($field['field_type']) {
            default:
            case 'text':
                $this->formHandler->addText($field['field_name'], $field['field_label']);
                break;
            
            case 'textarea':
                $this->formHandler->addTextarea($field['field_name'], $field['field_label']);
                break;
            
            case 'radio':
                $values = json_decode($field['field_attributes']);
                $radioValues = array();
                foreach ($values as $label=>$value) {
                    $radioValues['list'][] = array(
                        'name' => $label,
                        'label' => $label,
                        'value' => $value
                    );
                }
                $this->formHandler->addRadio(
                    $field['field_name'],
                    $field['field_label'],
                    $field['field_name'],
                    '&nbsp;',
                    $radioValues
                );
                break;
        }
    }
    
    public function generate()
    {
        $finalHtml = $this->generateHtml();
        return $finalHtml;
    }
    
    private function generateHtml()
    {
        $this->formHandler->setDefaults($this->formDefautls);
        $formElements = $this->formHandler->toSmarty();
        
        $htmlRendering = '<div class="row">';
        
        $htmlRendering .= '<div class="bs-callout bs-callout-success" id="formSuccess" style="display: none;">The object has been successfully updated</div>';
        $htmlRendering .= '<div class="bs-callout bs-callout-danger" id="formError" style="display: none;">An error occured</div>';
        
        $htmlRendering .= '<form class="form-horizontal" role="form" '.$formElements['attributes'].'>';
        
        foreach ($this->formComponents as $sectionLabel=>$sectionComponents) {
            $htmlRendering .= '<div>';
            foreach ($sectionComponents as $blockLabel=>$blockComponents) {
                foreach($blockComponents as $component) {
                    $htmlRendering .= $formElements[$component]['html'];
                }
            }
            $htmlRendering .= '</div>';
        }
        
        $htmlRendering .= $formElements['hidden'];
        $htmlRendering .= '</form></div>';
        
        return $htmlRendering;
    }
    
    /**
     * 
     */
    public function generateSubmitValidator()
    {
        $js = '$("#'.$this->formName.'").submit(function (event) {
            $.ajax({
                url: "'.$this->formRoute.'",
                type: "POST",
                data: $(this).serialize(),
                context: document.body
            })
            .success(function(data, status, jqxhr) {
                if (data === "success") {
                    $("#formSuccess").css("display", "block");
                    $("#formError").css("display", "none");
                } else {
                    $("#formError").css("display", "block");
                    $("#formSuccess").css("display", "none");
                }
            });
            return false;
        });';
        
        return $js;
    }
    
    public function addHiddenComponent($name, $value)
    {
        $this->formHandler->addHidden($name, $value);
    }
    
    public function setDefaultValues($defaultValues)
    {
        $this->formHandler->setDefaults($defaultValues);
    }
}
