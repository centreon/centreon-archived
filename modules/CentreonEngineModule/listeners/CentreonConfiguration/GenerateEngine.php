<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonEngine\Listeners\CentreonConfiguration;

use Centreon\Internal\Di;
use CentreonEngine\Repository\ConfigGenerateMainRepository;
use CentreonEngine\Repository\CommandRepository;
use CentreonEngine\Repository\TimePeriodRepository;
use CentreonEngine\Repository\HostRepository;
use CentreonEngine\Repository\HostTemplateRepository;
use CentreonEngine\Repository\HostgroupRepository;
use CentreonEngine\Repository\ConnectorRepository;
use CentreonEngine\Repository\ServicetemplateRepository;
use CentreonEngine\Repository\ServicegroupRepository;
use CentreonEngine\Repository\UserRepository;
use CentreonEngine\Repository\UsergroupRepository;
use CentreonEngine\Repository\ConfigGenerateResourcesRepository;
use CentreonConfiguration\Events\GenerateEngine as GenerateEngineEvent;

class GenerateEngine
{
    private static $event;
    private static $path;
    private static $fileList; 

    /**
     *
     * @param \CentreonConfiguration\Events\GenerateEngine
     */
    public static function execute(GenerateEngineEvent $event)
    {
        static::$event = $event;
        $config = Di::getDefault()->get('config');
        static::$path = $config->get('global', 'centreon_generate_tmp_dir');
        static::$path = rtrim(static::$path, '/') . '/engine/';
        static::$fileList = array();

        system("rm -rf " . static::$path . $event->getPollerId() . "/resources/");

        $event->setOutput(
            sprintf(
                _('Generating temporary configuration files for poller %s:'), $event->getPollerId()
            )
        );

        static::generateObjectsFiles();
        static::generateMainFiles();
    }

    /**
     * Generate main configuration files
     */
    public static function generateMainFiles()
    {
        $event = static::$event;
        /* Generate Main File */
        ConfigGenerateMainRepository::generate(
            static::$fileList, 
            $event->getPollerId(), 
            static::$path, 
            "centengine.cfg"
        );
        $event->setOutput('centengine.cfg');

        /* Generate Debugging Main File */
        ConfigGenerateMainRepository::generate(
            static::$fileList,
            $event->getPollerId(),
            static::$path,
            "centengine-testing.cfg",
            1
        );
        $event->setOutput('centengine-testing.cfg');
    }

    /**
     * Generate all object files (host, service, contacts etc...)
     *
     */
    public static function generateObjectsFiles()
    {
        $event = static::$event;
        /* Generate Configuration files */
        CommandRepository::generate(
            static::$fileList, 
            $event->getPollerId(), 
            static::$path, 
            "check-command.cfg",
            CommandRepository::CHECK_TYPE
        );
        $event->setOutput('check-command.cfg');

        CommandRepository::generate(
            static::$fileList, 
            $event->getPollerId(), 
            static::$path, 
            "misc-command.cfg",
            CommandRepository::NOTIF_TYPE
        );
        $event->setOutput('misc-command.cfg');

        ConfigGenerateResourcesRepository::generate(
            static::$fileList, 
            $event->getPollerId(), 
            static::$path, 
            "resources.cfg"
        );
        $event->setOutput('resources.cfg');

        TimePeriodRepository::generate(static::$fileList, $event->getPollerId(), static::$path, "timeperiods.cfg");
        $event->setOutput('timeperiods.cfg');

        ConnectorRepository::generate(static::$fileList, $event->getPollerId(), static::$path, "connectors.cfg");
        $event->setOutput('connectors.cfg');

        UserRepository::generate(static::$fileList, $event->getPollerId(), static::$path, "objects/contacts.cfg");
        $event->setOutput('contacts.cfg');

        UsergroupRepository::generate(
            static::$fileList, 
            $event->getPollerId(), 
            static::$path, 
            "objects/contactgroups.cfg"
        );
        $event->setOutput('contactgroups.cfg');

        /* Generate config Object */
        HostgroupRepository::generate(static::$fileList, $event->getPollerId(), static::$path, "objects/hostgroups.cfg");
        $event->setOutput('hostgroups.cfg');

        ServicegroupRepository::generate(
            static::$fileList,
            $event->getPollerId(),
            static::$path,
            "objects/servicegroups.cfg"
        );
        $event->setOutput('servicegroups.cfg');

        /* Templates config files */
        HostTemplateRepository::generate(
            static::$fileList,
            $event->getPollerId(),
            static::$path,
            "objects/hostTemplates.cfg"
        );
        $event->setOutput('hostTemplates.cfg');

        ServicetemplateRepository::generate(
            static::$fileList,
            $event->getPollerId(),
            static::$path,
            "objects/serviceTemplates.cfg"
        );
        $event->setOutput('serviceTemplate.cfg');

        /* Monitoring Resources files */
        HostRepository::generate(static::$fileList, $event->getPollerId(), static::$path, "resources/");
        $event->setOutput('host configuration files');
    } 
}
