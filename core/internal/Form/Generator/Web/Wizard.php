<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Internal\Form\Generator\Web;

use Centreon\Internal\Di;
use Centreon\Internal\Form;

/**
 * Manage wizard for object
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Wizard extends Full
{
    /**
     * Constructor
     *
     * @see \Centreon\Internal\Form\Generator\Web\Full::__construct
     */
    public function __construct($formRoute, $extraParams = array())
    {
        parent::__construct($formRoute, $extraParams);
    }

    /**
     * Load wizard information from database
     */
    public function getFormFromDatabase()
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $route = $this->formRoute;
        /*$baseUrl = rtrim($di->get('config')->get('global', 'base_url'), '/');
        $route = str_replace($baseUrl, '', $route);*/

        $query = "SELECT f.field_id as field_id, w.name as wizard_name, s.name as step_name, f.show_label,
            s.rank as step_rank, f.mandatory as mandatory, f.parent_field as parent_field, f.parent_value as parent_value,
            f.child_mandatory as child_mandatory, f.child_actions as child_actions, sf.rank as field_pos,
            f.name as name, f.label, f.default_value, f.attributes, f.type, f.help, f.width
            FROM cfg_forms_wizards w, cfg_forms_steps s, cfg_forms_steps_fields_relations sf, cfg_forms_fields f
            WHERE w.route = :route
                AND w.wizard_id = s.wizard_id
                AND s.step_id = sf.step_id
                AND sf.field_id = f.field_id
            ORDER BY s.rank, sf.rank";
        //echo $query . '<br />' . $route;
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':route', $route);
        $stmt->execute();

        $validatorQuery = "SELECT v.route as validator_action, vr.params as params, vr.client_side_event as rules "
                    . "FROM cfg_forms_validators v, cfg_forms_fields_validators_relations vr "
                    . "WHERE vr.field_id = :fieldId "
                    . "AND vr.validator_id = v.validator_id";
        $validatorStmt = $dbconn->prepare($validatorQuery);
        while ($row = $stmt->fetch()) {

            $validatorStmt->bindParam(':fieldId', $row['field_id'], \PDO::PARAM_INT);
            $validatorStmt->execute();

            // Get validators
            while ($validator = $validatorStmt->fetch()) {
                $validator['params'] = json_decode($validator['params'], true);
                $row['validators'][] = $validator;
            }
            
            if ('' === $this->formName) {
                $this->formName = $row['wizard_name'];
                $this->formHandler = new Form($this->formName);
                $this->formHandler->setFormId("wizard_form");
            }

            if (false === isset($this->formComponents[$row['step_name']])) {
                $this->formComponents[$row['step_name']] = array();
                $this->formComponents[$row['step_name']]['default'] = array();
            }

            if (!isset($row['width']) || (($row['width'] != 4) && ($row['width'] != 6))) {
                $row['width'] = '12';
            }

            $this->formDefaults[$row['name']] = $row['default_value'];
            $this->addFieldToForm($row);
            if ($row['type'] != 'hidden') {
                $this->formComponents[$row['step_name']]['default'][] = $row;
            }
        }
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
                                cfg_forms_fields fi, cfg_forms_steps fs, cfg_forms_steps_fields_relations fsf, cfg_forms_wizards fw
                            WHERE
                                fi.field_id = fsf.field_id
                            AND
                                fsf.step_id = fs.step_id
                            AND
                                fs.wizard_id = fw.wizard_id
                            AND
                                fw.route = '$finalRoute'
                    );";
        

        return $validatorsQuery;
    }

    /**
     * Return the wizard HTML
     * @return string
     */
    protected function generateHtml()
    {
        /* Set default values to quickform */
        $this->formHandler->setDefaults($this->formDefaults);
        $formElements = $this->formHandler->toSmarty();
        $di = Di::getDefault();
        $tpl = $di->get('template');
        $tpl->assign('name', $this->formName);
        $tpl->assign('formName', $this->formName);
        $tpl->assign('formElements', $formElements);
        $tpl->assign('steps', $this->formComponents);
        return $tpl->fetch('tools/modalWizard.tpl');
    }
}
