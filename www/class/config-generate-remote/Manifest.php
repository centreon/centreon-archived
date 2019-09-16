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

use ConfigGenerateRemote\Abstracts\AbstractObject;

class Manifest extends AbstractObject
{
    protected $generateFilename = 'manifest.json';
    protected $manifest = [];
    protected $type = 'manifest';
    protected $subdir = '';

    protected function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);

        $this->manifest['date'] = date('l jS \of F Y h:i:s A');
        $this->manifest['pollers'] = [];
        $this->manifest['import'] = [
            'infile_clauses' => [
                'fields_clause' => [
                    'terminated_by' => $this->fieldSeparatorInfile,
                    'enclosed_by' => '',
                    'escaped_by' => '',
                ],
                'lines_clause' => [
                    'terminated_by' => $this->lineSeparatorInfile,
                    'starting_by' => '',
                ]
            ],
            'data' => []
        ];
    }

    public function __destruct()
    {
        #fwrite($this->fp, json_encode($this->manifest));
        #parent::__destruct();
    }

    public function getManifest()
    {
        return $this->manifest;
    }

    public function addRemoteServer($remoteId)
    {
        $this->manifest['remote_server'] = $remoteId;
    }

    public function addPoller($pollerId)
    {
        $this->manifest['pollers'][] = $pollerId;
    }

    public function addFile($filename, $type, $table, $columns)
    {
        $this->manifest['import']['data'][$filename] = [
            'filename' => $filename,
            'type' => $type,
            'table' => $table,
            'columns' => $columns
        ];
    }
}
