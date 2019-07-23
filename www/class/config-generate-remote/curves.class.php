<?php

/*
 * Copyright 2005-2015 Centreon
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

namespace ConfigGenerateRemote;

use \PDO;

class Curves extends AbstractObject
{
    private $curves = null;
    protected $table = 'giv_components_template';
    protected $generate_filename = 'giv_components_template.infile';
    protected $attributes_select = '
        compo_id,
        host_id,
        service_id,
        name,
        ds_order,
        ds_hidecurve, 
        ds_name,
        ds_color_line,
        ds_color_line_mode,
        ds_color_area,
        ds_color_area_warn,
        ds_color_area_crit,
        ds_filled,
        ds_max,
        ds_min,
        ds_minmax_int,
        ds_average,
        ds_last,
        ds_total,
        ds_tickness,
        ds_transparency,
        ds_invert,
        ds_legend,
        ds_jumpline,
        ds_stack,
        default_tpl1,
        comment 
    ';
    protected $attributes_write = array(
        'compo_id',
        'host_id',
        'service_id',
        'name',
        'ds_order',
        'ds_hidecurve', 
        'ds_name',
        'ds_color_line',
        'ds_color_line_mode',
        'ds_color_area',
        'ds_color_area_warn',
        'ds_color_area_crit',
        'ds_filled',
        'ds_max',
        'ds_min',
        'ds_minmax_int',
        'ds_average',
        'ds_last',
        'ds_total',
        'ds_tickness',
        'ds_transparency',
        'ds_invert',
        'ds_legend',
        'ds_jumpline',
        'ds_stack',
        'default_tpl1',
        'comment' 
    );

    private function getCurves()
    {
        $query = "SELECT $this->attributes_select FROM giv_components_template";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->execute();
        $this->curves = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    public function generateObjects()
    {
        if (is_null($this->curves)) {
            $this->getCurves();
        }

        $instanceService = Service::getInstance($this->dependencyInjector);
        foreach ($this->curves as $id => $value) {
            if ($this->checkGenerate($id)) {
                continue;
            }

            if (is_null($value['service_id']) ||
                $instanceService->checkGenerate($value['host_id'] . '.' . $value['service_id'])) {
                $value['compo_id'] = $id;
                $this->generateObjectInFile($value, $id);
            }
        }
    }
}
