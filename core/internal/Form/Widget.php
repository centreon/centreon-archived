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

use \CentreonCustomview\Repository\WidgetRepository;
use \CentreonCustomview\Repository\CustomviewRepository;

/**
 * Manage widget settings
 *
 * @author Sylvetre Ho <sho@merethis.com>
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
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
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
        $di = \Centreon\Internal\Di::getDefault();
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
                $this->formHandler = new \Centreon\Internal\Form($this->formName);
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

        $di = \Centreon\Internal\Di::getDefault();
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
