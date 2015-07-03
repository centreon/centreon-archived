<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace Centreon\Internal\Form\Generator\Web;

use Centreon\Internal\Exception;
use Centreon\Internal\Di;
use Centreon\Internal\Form;
use Centreon\Internal\Form\Generator\Generator;
use Centreon\Events\LoadFormDatas as LoadFormDatasEvent;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Full extends Generator
{

    /**
     * 
     * @param string $formRoute
     * @param array $extraParams
     * @param string $productVersion The version of product when form accept multi version
     */
    public function __construct($formRoute, $extraParams = array(), $productVersion = '')
    {
        parent::__construct($formRoute, $extraParams, $productVersion);
    }
    
    /**
     * 
     * @throws Exception
     */
    public function getFormFromDatabase()
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $queryForm = "SELECT form_id, name, redirect, redirect_route FROM cfg_forms WHERE route = :route";
        $stmtForm = $dbconn->prepare($queryForm);
        $stmtForm->bindParam(':route', $this->formRoute, \PDO::PARAM_STR);
        $stmtForm->execute();
        $formInfo = $stmtForm->fetchAll();

        if (!isset($formInfo[0])) {
            throw new Exception(sprintf('Could not find form with route %s', $this->formRoute));
        }

        $formId = $formInfo[0]['form_id'];
        $this->formName = $formInfo[0]['name'];
        $this->formRedirect = $formInfo[0]['redirect'];
        $this->formRedirectRoute = $formInfo[0]['redirect_route'];
        
        $this->formHandler = new Form($this->formName);
        
        $sectionQuery = 'SELECT section_id, name '
            . 'FROM cfg_forms_sections '
            . 'WHERE form_id = :formId '
            . 'ORDER BY rank ASC';
        
        $sectionStmt = $dbconn->prepare($sectionQuery);
        $sectionStmt->bindParam(':formId', $formId, \PDO::PARAM_INT);
        $sectionStmt->execute();
        $sectionList = $sectionStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $firstSectionDetected = false;
        
        $blockQuery = 'SELECT block_id, name '
            . 'FROM cfg_forms_blocks '
            . 'WHERE section_id= :sectionId '
            . 'ORDER BY rank ASC';
            
        $blockStmt = $dbconn->prepare($blockQuery);
        foreach ($sectionList as $section) {
            if (!$firstSectionDetected) {
                $this->firstSection = $section['name'];
                $firstSectionDetected = true;
            }
            
            $blockStmt->bindParam(':sectionId', $section['section_id'], \PDO::PARAM_INT);
            $blockStmt->execute();
            $blockList = $blockStmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->formComponents[$section['name']] = array();
            
            $fieldQuery = 'SELECT '
                . 'f.field_id, f.name, f.label, f.default_value, f.attributes, f.show_label, '
                . 'f.type, f.help, f.help_url, f.advanced, f.mandatory, f.parent_field, '
                . 'f.parent_value, f.child_actions, f.child_mandatory, f.width '
                . 'FROM cfg_forms_fields f, cfg_forms_blocks_fields_relations bfr '
                . 'WHERE bfr.block_id= :blockId '
		            . 'AND bfr.field_id = f.field_id ' 
		            . "AND bfr.product_version = :productVersion "
                    . 'ORDER BY rank ASC';
            $fieldStmt = $dbconn->prepare($fieldQuery);
            foreach ($blockList as $block) {
                $this->formComponents[$section['name']][$block['name']] = array();
                $fieldStmt->bindParam(':blockId', $block['block_id'], \PDO::PARAM_INT);
                $fieldStmt->bindParam(':productVersion', $this->productVersion, \PDO::PARAM_STR);
                $fieldStmt->execute();
                $fieldList = $fieldStmt->fetchAll(\PDO::FETCH_ASSOC);
                
                $validatorQuery = "SELECT v.route as validator_action, vr.params as params, vr.client_side_event as rules "
                    . "FROM cfg_forms_validators v, cfg_forms_fields_validators_relations vr "
                    . "WHERE vr.field_id = :fieldId "
                    . "AND vr.validator_id = v.validator_id";
                $validatorStmt = $dbconn->prepare($validatorQuery);
                foreach ($fieldList as $field) {
                    $validatorStmt->bindParam(':fieldId', $field['field_id'], \PDO::PARAM_INT);
                    $validatorStmt->execute();
                    while ($validator = $validatorStmt->fetch()) {
                        $validator['params'] = json_decode($validator['params'], true);
                        $field['validators'][] = $validator;
                    }
                    
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
            case 'hidden':
                $this->formHandler->addHidden($field['name'], $field['default_value']);
                break;
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
        $di = Di::getDefault();
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

        $htmlRendering = '<div class="formWrapper">';
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
        
        $htmlRendering .= '<form class="CentreonForm" role="form" '.$formElements['attributes'].' data-route="'.$this->formRoute.'" novalidate>';

        $formRendering = '';

        $tabRendering = '<div class="form-tabs-header">'
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
        $formRendering .= '</ul>' // end col-md-12
            . '</div>'; // end form-tabs-header

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
                $formRendering .= '<div class="panel panel-default">'.'<div class="panel-heading"><h5>'.$blockLabel.'</h5></div>';
                $formRendering .= '<div class="panel-body">';
                foreach ($blockComponents as $component) {
                    if (isset($formElements[$component['name']]['html'])) {
                        $componentWidth = '6';
                        if (isset($component['width'])) {
                            if ($component['width'] == 1) {
                                $componentWidth = 4;
                            } elseif ($component['width'] == 3) {
                                $componentWidth = 12;
                            }
                        }
                        $formRendering .= '<div class="col-md-' . $componentWidth . '">';
                        $formRendering .= $formElements[$component['name']]['html'];

                        $formRendering .= '</div>';
                    }
                }
                $formRendering .= '</div>';
                $formRendering .= '</div>';
            }
            $formRendering .= '</div>';
        }
        $formRendering .= '</div>';
        
        $formRendering .= '<div>'.$formElements['save_form']['html'].'</div>';
        
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
    public function setDefaultValues($defaultValues, $objectId = "")
    {
        if (isset($objectId) && is_string($defaultValues)) {
            // Get the mapped columns for the object
            $objectColumns = $defaultValues::getColumns();
            $fields = array_intersect($objectColumns, array_keys($this->formDefaults));
            $fields = array_map(function ($field) {
                return "`" . $field . "`";
            }, $fields);
            
            // Get the mapped values and if no value saved for the field, the default one is set
            $myValues = $defaultValues::getParameters($objectId, join(", ", $fields));
            foreach ($myValues as $key => &$value) {
                if (is_null($value)) {
                    $value = $this->formDefaults[$key];
                }
            }
            
            // Merging with non-mapped form field and returns the values combined
            $eventDefaultValues = array_merge($myValues, array_diff_key($this->formDefaults, $myValues));

            $events = Di::getDefault()->get('events');
            $loadFormDatasEvent = new LoadFormDatasEvent($this->formRoute, $objectId, $eventDefaultValues);
            $events->emit('core.load.form.datas', array($loadFormDatasEvent));

            $finalDefaultValues = $loadFormDatasEvent->getParameters();

            $this->formHandler->setDefaults($finalDefaultValues);
        } elseif (is_array($defaultValues)) {
            foreach ($defaultValues as $k => $v) {
                $this->formDefaults[$k] = $v;
            }
            $this->formHandler->setDefaults($defaultValues);
        }
    }

    /**
     *
     * @param array $param
     */
    public function setValues($defaultValue, $objectId = "", $params)
    {
        // Get the mapped columns for the object
        $objectColumns = $defaultValue::getColumns();
        $fields = implode(',', array_intersect($objectColumns, array_keys($this->formDefaults)));

        // Get the mapped values and if no value saved for the field, the default one is set
        $myValues = $defaultValue::getParameters($objectId, $fields);

        // Set value for parameter
        foreach ($params as $key => $value) {
            $myValues[$key] = $value;
        }

        // Merging with non-mapped form field and returend the values combined
        $this->formHandler->setDefaults(array_merge($myValues, array_diff_key($this->formDefaults, $myValues)));
    }
    
    /**
     * 
     * @param type $origin
     * @param type $uri
     */
    public function getValidators()
    {
        $validatorsQuery = $this->buildValidatorsQuery();
        
        $stmt = $this->dbconn->query($validatorsQuery);
        $validatorsRawList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $validatorsFinalList = array();
        
        foreach ($validatorsRawList as $validator) {
            $validatorsFinalList[$validator['field_name']][] = array(
                'call' => $validator['validator_name'],
                'params' => $validator['params'],
                'label' => $validator['field_label']
            );
        }
        return array('fieldScheme' => $validatorsFinalList);
    }
    
    /**
     * 
     * @return type
     */
    protected function buildValidatorsQuery()
    {
        $di = Di::getDefault();
        $this->dbconn = $di->get('db_centreon');
        $baseUrl = $di->get('config')->get('global', 'base_url');
        $finalRoute = "/".ltrim(substr($this->formRoute, strlen($baseUrl)), "/");

        $validatorsQuery = "SELECT
                fv.`name` as validator_name, `route` as `validator`, ffv.`params` as `params`,
                ff.`name` as `field_name`, ff.`label` as `field_label`
            FROM
                cfg_forms_validators fv, cfg_forms_fields_validators_relations ffv, cfg_forms_fields ff
            WHERE
                ffv.validator_id = fv.validator_id
            AND
                ffv.server_side = '1'
            AND
                ff.field_id = ffv.field_id
            AND
                ffv.field_id IN (
                    SELECT
                        fi.field_id
                    FROM
                        cfg_forms_fields fi, cfg_forms_blocks fb, cfg_forms_blocks_fields_relations fbf, cfg_forms_sections fs, cfg_forms f
                    WHERE
                        fi.field_id = fbf.field_id
                    AND
                        fbf.block_id = fb.block_id
                    AND
                        fb.section_id = fs.section_id
                    AND
                        fs.form_id = f.form_id
                    AND
                        f.route = '$finalRoute'
            );";
        
        return $validatorsQuery;
    }
}
