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
use ConfigGenerateRemote\Abstracts\AbstractObject;

class Graph extends AbstractObject
{
    private $graphs = null;
    protected $table = 'giv_graphs_template';
    protected $generateFilename = 'graph.infile';
    protected $attributesSelect = '
        graph_id,
        name,
        vertical_label,
        width,
        height,
        base,
        lower_limit,
        upper_limit,
        size_to_max,
        default_tpl1,
        stacked,
        split_component,
        scaled,
        comment
    ';
    protected $attributesWrite = [
        'graph_id',
        'name',
        'vertical_label',
        'width',
        'height',
        'base',
        'lower_limit',
        'upper_limit',
        'size_to_max',
        'default_tpl1',
        'stacked',
        'split_component',
        'scaled',
        'comment'
    ];

    /**
     * Get graph
     *
     * @return void
     */
    private function getGraph()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT $this->attributesSelect
            FROM giv_graphs_template"
        );
        $stmt->execute();
        $this->graphs = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    /**
     * Generate and get graph from id
     *
     * @param null|integer $graphId
     * @return string|null
     */
    public function getGraphFromId(?int $graphId)
    {
        if (is_null($this->graphs)) {
            $this->getGraph();
        }

        $result = null;
        if (!is_null($graphId) && isset($this->graphs[$graphId])) {
            $result = $this->graphs[$graphId]['name'];
            if ($this->checkGenerate($graphId)) {
                return $result;
            }
            $this->generateObjectInFile($this->graphs[$graphId], $graphId);
        }

        return $result;
    }
}
