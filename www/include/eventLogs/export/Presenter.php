<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

declare(strict_types=1);

class Presenter
{
    private const DELIMITER = ';';

    private array $heads = [];
    private iterable $logs;
    private array $metaData;

    public function setHeads(array $heads): void
    {
        $this->heads = $heads;
    }

    public function setMetaData(array $metaData): void
    {
        $this->metaData = $metaData;
    }

    public function setLogs(iterable $logs): void
    {
        $this->logs = $logs;
    }

    public function render()
    {
        header('Content-Disposition: attachment;filename="EventLogs.csv";');
        header('Content-Type: application/csv; charset=UTF-8');
        header("Cache-Control: cache, must-revalidate");
        header("Pragma: public");

        $f = fopen('php://output', 'w');

        //print meta data
        foreach ($this->metaData as $metaData) {
            fputcsv($f, $metaData, self::DELIMITER);
        }

        //print heads
        fputcsv($f, $this->heads, self::DELIMITER);

        //print data
        foreach ($this->logs as $log) {
            fputcsv($f, $log, self::DELIMITER);
        }

        fclose($f);
    }
}