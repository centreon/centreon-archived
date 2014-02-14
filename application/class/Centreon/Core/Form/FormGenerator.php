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
    private $formRedirect;
    
    /**
     *
     * @var type 
     */
    private $formRedirectRoute;
    
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
        $this->formHandler = new \Centreon\Core\Form($this->formName);
        $this->getFormFromDatabase($advanced);
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
        
        $queryForm = "SELECT id, name, redirect, redirect_route FROM form WHERE route = '$this->formRoute'";
        $stmtForm = $dbconn->query($queryForm);
        $formInfo = $stmtForm->fetchAll();
        
        $formId = $formInfo[0]['id'];
        $this->formName = $formInfo[0]['name'];
        $this->formRedirect = $formInfo[0]['redirect'];
        $this->formRedirectRoute = $formInfo[0]['redirect_route'];
        
        $sectionQuery = 'SELECT id, name '
            . 'FROM section '
            . 'WHERE form_id='.$formId.' '
            . 'ORDER BY rank ASC';
        
        $sectionStmt = $dbconn->query($sectionQuery);
        $sectionList = $sectionStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach($sectionList as $section) {
            $blockQuery = 'SELECT id, name '
            . 'FROM block '
            . 'WHERE section_id='.$section['id'].' '
            . 'ORDER BY rank ASC';
            
            $blockStmt = $dbconn->query($blockQuery);
            $blockList = $blockStmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->formComponents[$section['name']] = array();
            
            foreach($blockList as $block) {
                
                $fieldQuery = 'SELECT name, label, default_value, attributes, type, help, mandatory '
                    . 'FROM field f, block_has_field bhf '
                    . 'WHERE bhf.block_id='.$block['id'].' '
                    . 'AND bhf.field_id = f.id '
                    . 'AND advanced = \''.$advanced.'\' '
                    . 'ORDER BY rank ASC';
                
                $this->formComponents[$section['name']][$block['name']] = array();
                $fieldStmt = $dbconn->query($fieldQuery);
                $fieldList = $fieldStmt->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($fieldList as $field) {
                    $this->addFieldToForm($field);
                    $this->formComponents[$section['name']][$block['name']][] = $field['name'];
                    $this->formDefaults[$field['name']] = $field['default_value'];
                }
            }
        }
        $this->formComponents['General']['Save'][] = 'save_form';
        $this->formHandler->addSubmit('save_form', _("Save"));
    }
    
    /**
     * 
     * @param type $field
     */
    private function addFieldToForm($field)
    {
        switch ($field['type']) {
            default:
            case 'text':
                $this->formHandler->addText($field['name'], $field['label']);
                break;
            
            case 'textarea':
                $this->formHandler->addTextarea($field['name'], $field['label']);
                break;
            
            case 'radio':
                $values = json_decode($field['attributes']);
                $radioValues = array();
                foreach ($values as $label=>$value) {
                    $radioValues['list'][] = array(
                        'name' => $label,
                        'label' => $label,
                        'value' => $value
                    );
                }
                $this->formHandler->addRadio(
                    $field['name'],
                    $field['label'],
                    $field['name'],
                    '&nbsp;',
                    $radioValues
                );
                break;
                
            case 'checkbox':
                $this->formHandler->addCheckbox($field['name'], $field['label']);
                break;
        }
    }
    
    /**
     * 
     * @return type
     */
    public function generate()
    {
        $finalHtml = $this->generateHtml();
        return $finalHtml;
    }
    
    /**
     * 
     * @return string
     */
    private function generateHtml()
    {
        $this->formHandler->setDefaults($this->formDefautls);
        $formElements = $this->formHandler->toSmarty();
        
        $htmlRendering = '<div class="row">';
        
        $htmlRendering .= '<div class="bs-callout bs-callout-success" id="formSuccess" style="display: none;">The object has been successfully updated</div>';
        $htmlRendering .= '<div class="bs-callout bs-callout-danger" id="formError" style="display: none;">An error occured</div>';
        
        $htmlRendering .= '<form class="form-horizontal" role="form" '.$formElements['attributes'].'>';
        
        $formRendering = '';
        
        $tabRendering = '<ul class="nav nav-tabs" id="formHeader">';
        
        foreach ($this->formComponents as $sectionLabel=>$sectionComponents) {
            $tabRendering .= '<li><a href="#'.$sectionLabel.'" data-toggle="tab">'.$sectionLabel.'</a></li>';
            $formRendering .= '<div>';
            foreach ($sectionComponents as $blockLabel=>$blockComponents) {
                $formRendering .= '<div>';
                foreach($blockComponents as $component) {
                    $formRendering .= $formElements[$component]['html'];
                }
                $formRendering .= '</div>';
            }
            $formRendering .= '</div>';
        }
        
        $tabRendering .= '</ul>';
        
        $formRendering .= $formElements['hidden'];
        $htmlRendering .= $tabRendering.$formRendering.'</form></div>';
        
        return $htmlRendering;
    }
    
    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->formName;
    }
    
    /**
     * 
     * @return string
     */
    public function getRedirect()
    {
        return $this->formRedirect;
    }
    
    /**
     * 
     * @return string
     */
    public function getRedirectRoute()
    {
        return $this->formRedirectRoute;
    }
    
    /**
     * 
     * @param string $name
     * @param string $value
     */
    public function addHiddenComponent($name, $value)
    {
        $this->formHandler->addHidden($name, $value);
    }
    
    /**
     * 
     * @param array $defaultValues
     */
    public function setDefaultValues($defaultValues)
    {
        $this->formHandler->setDefaults($defaultValues);
    }
}
