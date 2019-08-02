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

class Timeperiod extends AbstractObject
{
    private $timeperiods = null;
    protected $table = 'timeperiod';
    protected $generate_filename = 'timeperiods.infile';
    protected $attributes_select = '
        tp_id,
        tp_name,
        tp_alias,
        tp_sunday,
        tp_monday,
        tp_tuesday,
        tp_wednesday,
        tp_thursday,
        tp_friday,
        tp_saturday
    ';
    protected $attributes_write = array(
        'tp_id',
        'tp_name',
        'tp_alias',
        'tp_sunday',
        'tp_monday',
        'tp_tuesday',
        'tp_wednesday',
        'tp_thursday',
        'tp_friday',
        'tp_saturday',
    );
    protected $stmt_extend = array('include' => null, 'exclude' => null);

    public function getTimeperiods()
    {
        $query = "SELECT $this->attributes_select FROM timeperiod";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->execute();
        $this->timeperiods = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    protected function getTimeperiodExceptionFromId($timeperiod_id)
    {
        if (isset($this->timeperiods[$timeperiod_id]['exceptions'])) {
            return 1;
        }

        $query = "SELECT days, timerange FROM timeperiod_exceptions WHERE timeperiod_id = :timeperiod_id";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->bindParam(':timeperiod_id', $timeperiod_id, PDO::PARAM_INT);
        $stmt->execute();
        $exceptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($exceptions as $exception) {
            $exception['timeperiod_id'] = $timeperiod_id;
            timeperiodExceptions::getInstance($this->dependencyInjector)->add($exception, $timeperiod_id);
        }
    }

    protected function getTimeperiodExtendFromId($timeperiod_id, $db_label)
    {
        if (!isset($this->timeperiods[$timeperiod_id][$label . '_cache'])) {
            if (is_null($this->stmt_extend[$db_label])) {
                $query = "SELECT timeperiod_" . $db_label . "_id as period_id FROM timeperiod_" . $db_label .
                    "_relations WHERE timeperiod_id = :timeperiod_id";
                $this->stmt_extend[$db_label] = $this->backend_instance->db->prepare($query);
            }
            $this->stmt_extend[$db_label]->bindParam(':timeperiod_id', $timeperiod_id, PDO::PARAM_INT);
            $this->stmt_extend[$db_label]->execute();
            $this->timeperiods[$timeperiod_id][$label . '_cache'] =
                $this->stmt_extend[$db_label]->fetchAll(PDO::FETCH_COLUMN);
        }

        $this->timeperiods[$timeperiod_id][$label] = array();
        foreach ($this->timeperiods[$timeperiod_id][$label . '_cache'] as $period_id) {
            $this->timeperiods[$timeperiod_id][$label][] = $this->generateFromTimeperiodId($period_id);
        }
    }

    public function generateFromTimeperiodId($timeperiod_id)
    {
        if (is_null($timeperiod_id)) {
            return null;
        }
        if (is_null($this->timeperiods)) {
            $this->getTimeperiods();
        }

        if (!isset($this->timeperiods[$timeperiod_id])) {
            return null;
        }
        if ($this->checkGenerate($timeperiod_id)) {
            return $this->timeperiods[$timeperiod_id]['tp_name'];
        }

        $this->getTimeperiodExceptionFromId($timeperiod_id);
        # Not used any more in centreon-web
        #$this->getTimeperiodExtendFromId($timeperiod_id, 'exclude');
        #$this->getTimeperiodExtendFromId($timeperiod_id, 'include');

        $this->timeperiods[$timeperiod_id]['tp_id'] = $timeperiod_id;
        $this->generateObjectInFile($this->timeperiods[$timeperiod_id], $timeperiod_id);
        return $this->timeperiods[$timeperiod_id]['tp_name'];
    }
}
