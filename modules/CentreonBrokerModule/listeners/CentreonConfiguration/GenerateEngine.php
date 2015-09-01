<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace CentreonBroker\Listeners\CentreonConfiguration;

use CentreonConfiguration\Events\GenerateEngine as GenerateEngineEvent;
use CentreonBroker\Repository\ConfigCorrelationRepository;
use CentreonBroker\Repository\ConfigGenerateRepository;
use CentreonBroker\Repository\TimePeriodRepository;
use CentreonBroker\Repository\DowntimeRepository;
use Centreon\Internal\Di;

class GenerateEngine
{
    private static $event;
    private static $path;
    private static $fileList;

    /**
     *
     * @param \CentreonConfiguration\Events\GenerateEngine $event
     */
    public static function execute(GenerateEngineEvent $event)
    {
        static::$event = $event;
        $config = Di::getDefault()->get('config');
        static::$path = $config->get('global', 'centreon_generate_tmp_dir');
        static::$path = rtrim(static::$path, '/') . '/broker/generate/';
        static::$fileList = array();

        $config = Di::getDefault()->get('config');
        $path = $config->get('global', 'centreon_generate_tmp_dir');
        $path = rtrim($path, '/') . '/broker/generate/';

        $output = array();
        exec("rm -rf " . $path . $event->getPollerId() . "/* 2>&1", $output, $statusDelete);
        if ($statusDelete) {
            $event->setOutput(_('Error while deleting Broker temporary configuration files') . "\n" . implode("\n", $output));
        }

        $configBroker = new ConfigGenerateRepository();
        $configBroker->generate($event->getPollerId());
        ConfigCorrelationRepository::generate($event->getPollerId());

        static::generateObjectsFiles();
    }

    /**
     * Generate all object files (timeperiods, downtimes)
     *
     */
    public static function generateObjectsFiles()
    {
        $event = static::$event;

        /* Generate Configuration files */

        TimePeriodRepository::generate(
            static::$fileList,
            $event->getPollerId(),
            static::$path,
            "timeperiods.cfg"
        );
        $event->setOutput('Centreon-Broker : timeperiods.cfg');

        DowntimeRepository::generate(
            static::$fileList,
            $event->getPollerId(),
            static::$path,
            "downtimes.cfg"
        );
        $event->setOutput('Centreon-Broker : downtimes.cfg');
    }
}
