<?php
/**
 * Copyright 2005-2019 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonLegacy\Core\Widget;

class Installer extends Widget
{
    /**
     *
     * @return int
     * @throws \Exception
     */
    public function install()
    {
        if ($this->informationObj->isInstalled($this->widgetName)) {
            throw new \Exception('Widget is already installed.');
        }


        $id = $this->installConfiguration();
        $this->installPreferences($id);

        return $id;
    }

    /**
     *
     * @return int
     */
    protected function installConfiguration()
    {
        $query = 'INSERT INTO widget_models ' .
            '(title, description, url, version, directory, author, ' .
            'email, website, keywords, thumbnail, autoRefresh) ' .
            'VALUES (:title, :description, :url, :version, :directory, :author, ' .
            ':email, :website, :keywords, :thumbnail, :autoRefresh) ';

        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindParam(':title', $this->widgetConfiguration['title'], \PDO::PARAM_STR);
        $sth->bindParam(':description', $this->widgetConfiguration['description'], \PDO::PARAM_STR);
        $sth->bindParam(':url', $this->widgetConfiguration['url'], \PDO::PARAM_STR);
        $sth->bindParam(':version', $this->widgetConfiguration['version'], \PDO::PARAM_STR);
        $sth->bindParam(':directory', $this->widgetConfiguration['directory'], \PDO::PARAM_STR);
        $sth->bindParam(':author', $this->widgetConfiguration['author'], \PDO::PARAM_STR);
        $sth->bindParam(':email', $this->widgetConfiguration['email'], \PDO::PARAM_STR);
        $sth->bindParam(':website', $this->widgetConfiguration['website'], \PDO::PARAM_STR);
        $sth->bindParam(':keywords', $this->widgetConfiguration['keywords'], \PDO::PARAM_STR);
        $sth->bindParam(':thumbnail', $this->widgetConfiguration['thumbnail'], \PDO::PARAM_STR);
        $sth->bindParam(':autoRefresh', $this->widgetConfiguration['autoRefresh'], \PDO::PARAM_INT);

        $sth->execute();

        return $this->informationObj->getIdByName($this->widgetName);
    }

    /**
     *
     * @param int $id
     * @return type
     * @throws \Exception
     */
    protected function installPreferences($id)
    {
        if (!isset($this->widgetConfiguration['preferences'])) {
            return null;
        }

        $types = $this->informationObj->getTypes();

        foreach ($this->widgetConfiguration['preferences'] as $preferences) {
            if (!is_array($preferences)) {
                continue;
            }
            $order = 1;
            if (isset($preferences['@attributes'])) {
                $preferences = array($preferences['@attributes']);
            }

            foreach ($preferences as $preference) {
                $attr = $preference['@attributes'];
                if (!isset($types[$attr['type']])) {
                    throw new \Exception('Unknown type : ' . $attr['type'] . ' found in configuration file');
                }
                $attr['requirePermission'] = isset($attr['requirePermission']) ? $attr['requirePermission'] : 0;
                $attr['defaultValue'] = isset($attr['defaultValue']) ? $attr['defaultValue'] : '';
                $attr['header'] = (isset($attr['header']) && $attr['header'] != "") ? $attr['header'] : null;
                $attr['order'] = $order;
                $attr['type'] = $types[$attr['type']];

                $this->installParameters($id, $attr, $preference);
                $order++;
            }
        }
    }

    /**
     *
     * @param int $id
     * @param array $parameters
     * @param array $preference
     */
    protected function installParameters($id, $parameters, $preference)
    {
        $query = 'INSERT INTO widget_parameters ' .
            '(widget_model_id, field_type_id, parameter_name, parameter_code_name, ' .
            'default_value, parameter_order, require_permission, header_title) ' .
            'VALUES ' .
            '(:widget_model_id, :field_type_id, :parameter_name, :parameter_code_name, ' .
            ':default_value, :parameter_order, :require_permission, :header_title) ';

        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindParam(':widget_model_id', $id, \PDO::PARAM_INT);
        $sth->bindParam(':field_type_id', $parameters['type']['id'], \PDO::PARAM_INT);
        $sth->bindParam(':parameter_name', $parameters['label'], \PDO::PARAM_STR);
        $sth->bindParam(':parameter_code_name', $parameters['name'], \PDO::PARAM_STR);
        $sth->bindParam(':default_value', $parameters['defaultValue'], \PDO::PARAM_STR);
        $sth->bindParam(':parameter_order', $parameters['order'], \PDO::PARAM_STR);
        $sth->bindParam(':require_permission', $parameters['requirePermission'], \PDO::PARAM_STR);
        $sth->bindParam(':header_title', $parameters['header'], \PDO::PARAM_STR);

        $sth->execute();

        $lastId = $this->informationObj->getParameterIdByName($parameters['name'], $id);

        switch ($parameters['type']['name']) {
            case "list":
            case "sort":
                $this->installMultipleOption($lastId, $preference);
                break;
            case "range":
                $this->installRangeOption($lastId, $parameters);
                break;
        }
    }

    /**
     *
     * @param int $paramId
     * @param array $preference
     * @return type
     */
    protected function installMultipleOption($paramId, $preference)
    {
        if (!isset($preference['option'])) {
            return null;
        }

        $query = 'INSERT INTO widget_parameters_multiple_options ' .
            '(parameter_id, option_name, option_value) VALUES ' .
            '(:parameter_id, :option_name, :option_value) ';

        $sth = $this->services->get('configuration_db')->prepare($query);

        foreach ($preference['option'] as $option) {
            if (isset($option['@attributes'])) {
                $opt = $option['@attributes'];
            } else {
                $opt = $option;
            }

            $sth->bindParam(':parameter_id', $paramId, \PDO::PARAM_INT);
            $sth->bindParam(':option_name', $opt['label'], \PDO::PARAM_STR);
            $sth->bindParam(':option_value', $opt['value'], \PDO::PARAM_STR);

            $sth->execute();
        }
    }

    /**
     *
     * @param int $paramId
     * @param array $parameters
     */
    protected function installRangeOption($paramId, $parameters)
    {
        $query = 'INSERT INTO widget_parameters_range (parameter_id, min_range, max_range, step) ' .
            'VALUES (:parameter_id, :min_range, :max_range, :step) ';

        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindParam(':parameter_id', $paramId, \PDO::PARAM_INT);
        $sth->bindParam(':min_range', $parameters['min'], \PDO::PARAM_INT);
        $sth->bindParam(':max_range', $parameters['max'], \PDO::PARAM_INT);
        $sth->bindParam(':step', $parameters['step'], \PDO::PARAM_INT);

        $sth->execute();
    }
}
