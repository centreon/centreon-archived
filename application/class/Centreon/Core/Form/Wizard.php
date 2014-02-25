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
 * Manage wizard for object
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
class Wizard extends Generator
{
    /**
     * Contructor
     *
     * @see \Centreon\Core\Form\Generator::__construct
     */
    public function __construct($formRoute, $advanced = 0, $extraParams = array())
    {
        parent::__construct($formRoute, $advanced, $extraParams);
    }

    /**
     * Load wizard information from database
     */
    protected function getFormFromDatabase()
    {
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $route = $di->get('router')->request()->pathname();
        $baseUrl = $di->get('config')->get('global', 'base_url');
        $route = str_replace($baseUrl, '/', $route);

        $query = "SELECT w.name as wizard_name, s.name as step_name, s.rank as step_rank,  sf.rank as field_pos,
                f.name as name, f.label, f.default_value, f.attributes, f.type, f.help
            FROM form_wizard w, form_step s, form_step_field_relation sf, form_field f
            WHERE w.route = :route
                AND w.wizard_id = s.wizard_id
                AND s.step_id = sf.step_id
                AND sf.field_id = f.field_id
            ORDER BY s.rank, sf.rank";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':route', $route);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            if ('' === $this->formName) {
                $this->formName = $row['wizard_name'];
		$this->formHandler = new \Centreon\Core\Form($this->formName);
            }
            if (false === isset($this->formComponents[$row['step_name']])) {
                $this->formComponents[$row['step_name']] = array();
                $this->formComponents[$row['step_name']]['default'] = array();
                $this->formDefaults[$row['name']] = $row['default_value'];
            }
            $this->addFieldToForm($row);
            $this->formComponents[$row['step_name']]['default'][] = $row;
        }
    }

    /**
     * Return the wizard HTML
     */
    protected function generateHtml()
    {
        /* Set default values to quickform */
        $this->formHandler->setDefaults($this->formDefautls);
        $formElements = $this->formHandler->toSmarty();

        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');
        $tpl->assign('name', $this->formName);
        $tpl->assign('formElements', $formElements);
        $tpl->assign('steps', $this->formComponents);
        return $tpl->fetch('tools/modalWizard.tpl');
    }
}
