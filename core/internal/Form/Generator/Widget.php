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

namespace Centreon\Internal\Form\Generator;

use CentreonCustomview\Repository\WidgetRepository;
use CentreonCustomview\Repository\CustomviewRepository;
use Centreon\Internal\Di;
use Centreon\Internal\Form;
use Centreon\Internal\Form\Generator\Generator;

/**
 * Manage widget settings
 *
 * @author Sylvetre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Widget extends Generator
{
    /**
     * Constructor
     *
     * @see \Centreon\Form\Generator::__construct
     */
    public function __construct($widgetId, $extraParams = array())
    {
        parent::__construct($widgetId, $extraParams);
    }

    /**
     * Convert type
     *
     * @param array $data
     * @return array
     */
    protected function convertType($data)
    {
        $db = Di::getDefault()->get('db_centreon');
        $attr = array();
        switch ($data['type']) {
            case 'list':
                $type = 'select';
                $res = WidgetRepository::getParameterOptions($data['parameter_id']);
                $options = array();
                $i = 0;
                $preferences = array();
                foreach ($res as $id => $name) {
                    $options[$i]['id'] = $id;
                    $options[$i]['text'] = $name;
                    if ($data['preference_value'] == $id) {
                        $preferences = $options[$i];
                    }
                    $i++;
                }
                $attr = json_encode(
                    array(
                        'selectData' => json_encode($options),
                        'selectDefault' => json_encode($preferences)
                    )
                );
                break;
            case 'boolean':
                $type = 'checkbox';
                $attr = json_encode(array('choices' => array(null => 1)));
                break;
            case 'hidden':
                $type = 'text';
                break;
            case 'range':
                $type = 'text';
                break;
            case 'compare':
                $type = 'text';
                break;
            case 'sort':
                $type = 'text';
                break;
            case 'date':
                $type = 'text';
                break;
            case 'host':
                $type = 'select';
                $attr = json_encode(
                    array(
                        'object_type' => 'object',
                        'defaultValuesRoute' => '/centreon-configuration/host/formlist'
                    )
                );
                break;
            case 'hostTemplate':
                $type = 'select';
                $attr = json_encode(
                    array(
                        'object_type' => 'object',
                        'defaultValuesRoute' => '/centreon-configuration/hosttemplate/formlist'
                    )
                );
                break;
            case 'serviceTemplate':
                $type = 'select';
                $attr = json_encode(
                    array(
                        'object_type' => 'object',
                        'defaultValuesRoute' => '/centreon-configuration/servicetemplate/formlist'
                    )
                );
                break;
            case 'hostgroup':
                $type = 'select';
                $attr = json_encode(
                    array(
                        'object_type' => 'object',
                        'defaultValuesRoute' => '/centreon-configuration/hostgroup/formlist'
                    )
                );
                break;
            case 'servicegroup':
                $type = 'select';
                $attr = json_encode(
                    array(
                        'object_type' => 'object',
                        'defaultValuesRoute' => '/centreon-configuration/servicegroup/formlist'
                    )
                );
                break;
            case 'service':
                $type = 'select';
                $attr = json_encode(
                    array(
                        'object_type' => 'object',
                        'defaultValuesRoute' => '/centreon-configuration/service/formlist'
                    )
                );
                break;
            default:
                $type = 'text';
                break;
        }
        return array($type, $attr);
    }

    /**
     * Load wizard information from database
     */
    protected function getFormFromDatabase()
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $widgetId = $this->formRoute;
        $baseUrl = $di->get('config')->get('global', 'base_url');

        $query = "SELECT w.title as wizard_name, header_title, parameter_name as label, wp.is_filter,
            parameter_code_name as name, default_value, ft_typename as type, is_connector,
            wp.parameter_id, wr.preference_value, wr.comparator
            FROM cfg_widgets w, cfg_widgets_models wm, cfg_widgets_parameters_fields_types ft, cfg_widgets_parameters wp
            LEFT JOIN cfg_widgets_preferences wr ON (wr.parameter_id = wp.parameter_id AND wr.widget_id = :widget_id)
            WHERE w.widget_id = :widget_id
            AND w.widget_model_id = wm.widget_model_id
            AND wm.widget_model_id = wp.widget_model_id
            AND wp.field_type_id = ft.field_type_id
            ORDER BY parameter_order";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':widget_id', $widgetId);
        $stmt->execute();
        $header = _("Settings");
        while ($row = $stmt->fetch()) {
            if ('' === $this->formName) {
                $this->formName = $row['wizard_name'];
                $this->formHandler = new Form($this->formName);
            }
            if ($row['header_title'] && !isset($this->formComponents[$row['header_title']])) {
                $this->formComponents[$row['header_title']] = array();
                $this->formComponents[$row['header_title']]['default'] = array();
                $header = $row['header_title'];
            }
            $this->formDefaults[$row['name']] = $row['preference_value'];
            $row['mandatory'] = 0;
            if (!$row['is_filter']) {
                list($row['type'], $row['attributes']) = $this->convertType($row);
            } else {
                $tmp = $row;
                $tmp['type'] = 'text';
                $tmp['name'] = 'cmp-'.$row['name'];
                $tmp['label'] = $row['comparator'];
                $this->addFieldToForm($tmp);
                $this->formComponents[$header]['default'][] = $tmp;
                $row['type'] = 'text';
                $row['attributes'] = array();
            }
            $this->addFieldToForm($row);
            $this->formComponents[$header]['default'][] = $row;
        }
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
        $tpl->assign('formElements', $formElements);
        $tpl->assign('steps', $this->formComponents);
        $options[CustomviewRepository::EQUAL] = _('equal');
        $options[CustomviewRepository::NOT_EQUAL] = _('not equal');
        $options[CustomviewRepository::CONTAINS] = _('contains');
        $options[CustomviewRepository::NOT_CONTAINS] = _('not contains');
        $options[CustomviewRepository::GREATER] = _('greater than');
        $options[CustomviewRepository::GREATER_EQUAL] = _('greater or equal');
        $options[CustomviewRepository::LESSER] = _('lesser than');
        $options[CustomviewRepository::LESSER_EQUAL] = _('lesser or equal');
        $tpl->assign('cmpOptions', $options);
        return $tpl->fetch('tools/modalWidget.tpl');
    }
}
