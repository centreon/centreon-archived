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

class Manifest extends AbstractObject
{
    protected $generate_filename = 'manifest.json';
    protected $manifest = array();
    protected $type = 'manifest';
    protected $subdir = '';

    protected function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        #$this->createFile($this->backend_instance->getPath());

        $this->manifest['date'] = date('l jS \of F Y h:i:s A');
        $this->manifest['pollers'] = array();
        $this->manifest['import'] = array(
            'infile_clauses' => array(
                'fields_clause' => array(
                    'terminated_by' =>  $this->fieldSeparatorInfile,
                    'enclosed_by' =>  '',
                    'escaped_by' =>  '',
                ),
                'lines_clause' => array( 
                    'terminated_by' =>  $this->lineSeparatorInfile,
                    'starting_by' => '',
                )
            ),
            'data' => array()
        );
    }

    public function __destruct() {
        #fwrite($this->fp, json_encode($this->manifest));
        #parent::__destruct();
    }

    public function getManifest() {
        return $this->manifest;
    }

    public function addRemoteServer($remote_id) {
        $this->manifest['remote_server'] = $remote_id;
    }
    
    public function addPoller($poller_id) {
        $this->manifest['pollers'][] = $poller_id;
    }

    public function addFile($filename, $type, $table, $columns) {
        $this->manifest['import']['data'][] = array(
            'filename' => $filename,
            'type' => $type,
            'table' => $table,
            'columns' => $columns
        );
    }
}
