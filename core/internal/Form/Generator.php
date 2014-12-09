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

namespace Centreon\Internal\Form;

use \Centreon\Internal\Exception;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
class Generator
{
    /**
     *
     * @var string 
     */
    protected $formName = '';
    
    /**
     *
     * @var string 
     */
    protected $formRoute;
    
    /**
     *
     * @var string 
     */
    private $formRedirect;
    
    /**
     *
     * @var string 
     */
    private $formRedirectRoute;
    
    /**
     *
     * @var array 
     */
    protected $formComponents = array();
    
    /**
     *
     * @var array 
     */
    protected $formDefaults = array();
    
    /**
     *
     * @var \Centreon\Internal\Form 
     */
    protected $formHandler;
    
    /**
     *
     * @var type 
     */
    private $firstSection;
    
    /**
     *
     * @var array 
     */
    protected $extraParams;

    /**
     * The product version
     *
     * @var string
     */
    protected $productVersion = '';


    /**
     * 
     * @param string $formRoute
     * @param array $extraParams
     * @param string $productVersion The version of product when form accept multi version
     */
    public function __construct($formRoute, $extraParams = array(), $productVersion = '')
    {
        $this->formRoute = $formRoute;
        $this->extraParams = $extraParams;
        $this->productVersion = $productVersion;
        $this->getFormFromDatabase();
    }
    
    /**
     * 
     * @param boolean $advanced
     */
    protected function getFormFromDatabase()
    {
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $queryForm = "SELECT form_id, name, redirect, redirect_route FROM cfg_forms WHERE route = '$this->formRoute'";
        $stmtForm = $dbconn->query($queryForm);
        $formInfo = $stmtForm->fetchAll();

        if (!isset($formInfo[0])) {
            throw new Exception(sprintf('Could not find form with route %s', $this->formRoute));
        }

        $formId = $formInfo[0]['form_id'];
        $this->formName = $formInfo[0]['name'];
        $this->formRedirect = $formInfo[0]['redirect'];
        $this->formRedirectRoute = $formInfo[0]['redirect_route'];
        
        $this->formHandler = new \Centreon\Internal\Form($this->formName);
        
        $sectionQuery = 'SELECT section_id, name '
            . 'FROM cfg_forms_sections '
            . 'WHERE form_id='.$formId.' '
            . 'ORDER BY rank ASC';
        
        $sectionStmt = $dbconn->query($sectionQuery);
        $sectionList = $sectionStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $firstSectionDetected = false;
        
        foreach ($sectionList as $section) {
            if (!$firstSectionDetected) {
                $this->firstSection = $section['name'];
                $firstSectionDetected = true;
            }
            
            $blockQuery = 'SELECT block_id, name '
            . 'FROM cfg_forms_blocks '
            . 'WHERE section_id='.$section['section_id'].' '
            . 'ORDER BY rank ASC';
            
            $blockStmt = $dbconn->query($blockQuery);
            $blockList = $blockStmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->formComponents[$section['name']] = array();
            
            foreach ($blockList as $block) {
                
                $fieldQuery = 'SELECT '
                    . 'f.field_id, f.name, f.label, f.default_value, f.attributes, '
                    . 'f.type, f.help, f.help_url, f.advanced, mandatory, parent_field, child_actions '
                    . 'FROM cfg_forms_fields f, cfg_forms_blocks_fields_relations bfr '
                    . 'WHERE bfr.block_id='.$block['block_id'].' '
		    . 'AND bfr.field_id = f.field_id ' 
		    . "AND bfr.product_version = '" . $this->productVersion . "' " 
                    . 'ORDER BY rank ASC';
                
                $this->formComponents[$section['name']][$block['name']] = array();
                $fieldStmt = $dbconn->query($fieldQuery);
                $fieldList = $fieldStmt->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($fieldList as $field) {
                    
                    $validatorQuery = "SELECT v.action as validator_action, vr.client_side_event as events "
                        . "FROM cfg_forms_validators v, cfg_forms_fields_validators_relations vr "
                        . "WHERE vr.field_id = $field[field_id] "
                        . "AND vr.validator_id = v.validator_id";
                    $validatorStmt = $dbconn->query($validatorQuery);
                    $field['validators'] = $validatorStmt->fetchAll(\PDO::FETCH_ASSOC);
                    
                    $this->addFieldToForm($field);
                    $this->formComponents[$section['name']][$block['name']][] = $field;
                    if ((strstr($field['type'], 'select') === false ||
                        strstr($field['type'], 'deprecated') === false) &&
                        !isset($this->formDefaults[$field['name']])) {
                        $this->formDefaults[$field['name']] = $field['default_value'];
                    }
                }
                
                if (count($this->formComponents[$section['name']][$block['name']]) == 0) {
                    unset($this->formComponents[$section['name']][$block['name']]);
                }
            }
            if (count($this->formComponents[$section['name']]) == 0) {
                unset($this->formComponents[$section['name']]);
            }
        }
        $this->formHandler->addSubmit('save_form', _("Save"));
    }
    
    /**
     * 
     * @param array $field
     */
    protected function addFieldToForm($field)
    {
        switch ($field['type']) {
            default:
                $this->formHandler->addStatic($field, $this->extraParams);
                break;
        }
    }
    
    /**
     * 
     * @return string
     */
    public function generate()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $tpl = $di->get('template');
        $finalHtml = $this->generateHtml();
        $tpl->assign('formRedirect', $this->formRedirect);
        $tpl->assign('formRedirectRoute', $this->formRedirectRoute);
        $tpl->assign('customValuesGetter', $this->formHandler->getCustomValidator());
        return $finalHtml;
    }
    
    /**
     * 
     * @return \Centreon\Internal\Form
     */
    public function getFormHandler()
    {
        return $this->formHandler;
    }

    /**
     * 
     * @return type
     */
    public function getFormComponents()
    {
        return $this->formComponents;
    }
    
    /**
     * 
     * @return string
     */
    protected function generateHtml()
    {
        $formElements = $this->formHandler->toSmarty();

        
        $htmlRendering = '<div class="row">';

        $htmlRendering = '<div class="col-sm-offset-1 col-sm-10">';
        
        $htmlRendering .= '<div '
            . 'class="bs-callout bs-callout-success" '
            . 'id="formSuccess" '
            . 'style="display: none;">'
            . 'The object has been successfully updated'
            . '</div>';
        $htmlRendering .= '<div '
            . 'class="bs-callout bs-callout-danger" '
            . 'id="formError" '
            . 'style="display: none;">'
            . 'An error occured'
            . '</div>';
        
        $htmlRendering .= '<form class="form-horizontal" role="form" '.$formElements['attributes'].'>';
        
        $formRendering = '';

        $tabRendering = '<div class="form-tabs-header">'
            . '<div class="row">'
            . '<div class="col-xs-12 col-sm-10">'
            . '<ul class="nav nav-tabs" id="formHeader">';
        
        $first = true;
        foreach ($this->formComponents as $sectionLabel => $sectionComponents) {
            $tabRendering .= '<li';
            if ($first) {
                $first = false;
                $tabRendering .= ' class="active"';
            }
            $tabRendering .= '>'
                . '<a '
                . 'href="#'.str_replace(' ', '', $sectionLabel).'" '
                . 'data-toggle="tab">'
                .$sectionLabel
                .'</a>'
                . '</li>';
        }
        $formRendering .= '</ul></div>'
            . '<div class="clearfix visible-xs-block"></div>'
            . '</div></div>';

        $formRendering .= '<div class="tab-content">';
        $first = true;
        foreach ($this->formComponents as $sectionLabel => $sectionComponents) {
            $formRendering .= '<div class="tab-pane';
            if ($first) {
                $first = false;
                $formRendering .= ' active';
            }
            $formRendering .= '" id="'.str_replace(' ', '', $sectionLabel).'">';
            foreach ($sectionComponents as $blockLabel => $blockComponents) {
                $formRendering .= '<h4 class="page-header" style="padding-top:0px;">'.$blockLabel.'</h4>';
                $formRendering .= '<div class="panel-body">';
                foreach ($blockComponents as $component) {
                    if (isset($formElements[$component['name']]['html'])) {
                        $formRendering .= '<div class="col-xs-12 col-sm-8">';
                        $formRendering .= $formElements[$component['name']]['html'];
                        $formRendering .= '</div>'
                            . '<div class="clearfix visible-xs-block"></div>'
                            . '<div class="col-xs-12 col-sm-4';
                        if ($component['advanced'] == '1') {
                            $formRendering .= ' advanced';
                        }
                        $formRendering .= '">'
                            . '<span class="inheritance" id="' . $component['name'] . '_inheritance"></span>'
                            . '</div>';
                    }
                }
                $formRendering .= '</div>';
            }
            $formRendering .= '</div>';
        }
        $formRendering .= '</div>';
        
        $formRendering .= '<div>'.$formElements['save_form']['html'].'</div>';
        
        $formRendering .= $formElements['hidden'];
        $htmlRendering .= $tabRendering.$formRendering.'</form></div></div>';
        
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
    public function setDefaultValues($defaultValues, $objectId = "")
    {
        if (is_string($defaultValues)) {
            // Get the mapped columns for the object
            $objectColumns = $defaultValues::getColumns();
            $fields = implode(',', array_intersect($objectColumns, array_keys($this->formDefaults)));
            
            // Get the mapped values and if no value saved for the field, the default one is set
            $myValues = $defaultValues::getParameters($objectId, $fields);
            foreach ($myValues as $key => &$value) {
                if (is_null($value)) {
                    $value = $this->formDefaults[$key];
                }
            }
            
            // Merging with non-mapped form field and returend the values combined
            $this->formHandler->setDefaults(array_merge($myValues, array_diff_key($this->formDefaults, $myValues)));
        } elseif (is_array($defaultValues)) {
            foreach ($defaultValues as $k => $v) {
                $this->formDefaults[$k] = $v;
            }
            $this->formHandler->setDefaults($defaultValues);
        }
    }
    
    public function getFormFieldsWithValidators($route)
    {
        
    }
}
