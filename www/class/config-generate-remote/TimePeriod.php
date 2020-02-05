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

class TimePeriod extends AbstractObject
{
    private $timeperiods = null;
    protected $table = 'timeperiod';
    protected $generateFilename = 'timeperiods.infile';
    protected $attributesSelect = '
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
    protected $attributesWrite = [
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
    ];
    protected $stmtExtend = [
        'include' => null,
        'exclude' => null
    ];

    /**
     * Build cache of timeperiods
     *
     * @return void
     */
    public function getTimeperiods()
    {
        $query = "SELECT $this->attributesSelect FROM timeperiod";
        $stmt = $this->backendInstance->db->prepare($query);
        $stmt->execute();
        $this->timeperiods = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    /**
     * Get timeperiod exceptions from id
     *
     * @param integer $timeperiodId
     * @return void|int
     */
    protected function getTimeperiodExceptionFromId(int $timeperiodId)
    {
        if (isset($this->timeperiods[$timeperiodId]['exceptions'])) {
            return 1;
        }

        $query = "SELECT days, timerange FROM timeperiod_exceptions WHERE timeperiod_id = :timeperiodId";
        $stmt = $this->backendInstance->db->prepare($query);
        $stmt->bindParam(':timeperiodId', $timeperiodId, PDO::PARAM_INT);
        $stmt->execute();
        $exceptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($exceptions as $exception) {
            $exception['timeperiod_id'] = $timeperiodId;
            Relations\TimePeriodExceptions::getInstance($this->dependencyInjector)->add($exception, $timeperiodId);
        }
    }

    /**
     * Generate timeperiod from id
     *
     * @param null|integer $timeperiodId
     * @return null|string
     */
    public function generateFromTimeperiodId(?int $timeperiodId)
    {
        if (is_null($timeperiodId)) {
            return null;
        }
        if (is_null($this->timeperiods)) {
            $this->getTimeperiods();
        }

        if (!isset($this->timeperiods[$timeperiodId])) {
            return null;
        }
        if ($this->checkGenerate($timeperiodId)) {
            return $this->timeperiods[$timeperiodId]['tp_name'];
        }

        $this->getTimeperiodExceptionFromId($timeperiodId);

        $this->timeperiods[$timeperiodId]['tp_id'] = $timeperiodId;
        $this->generateObjectInFile($this->timeperiods[$timeperiodId], $timeperiodId);
        return $this->timeperiods[$timeperiodId]['tp_name'];
    }
}
