<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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
