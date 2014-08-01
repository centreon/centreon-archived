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

namespace  CentreonConfiguration\Repository;

use \Centreon\Internal\Di;

/**
 * Factory for ConfigGenerate Engine
 *
 * @author Julien Mathis <jmathis@merethis.com>
 * @version 3.0.0
 */

class ConfigGenerateRepository
{
    /**
     *
     * @var type
     */
    private $objCache;
    
    /**
     *
     * @var type
     */
    private $di;

    /**
     *
     * @var int
     */
    private $status;

    /**
     *
     * @var array
     */
    private $output;
    
    /**
     *
     * @var type
     */
    private $path;
    
    /**
     *
     * @var array
     */
    private $filesDir;

    /**
     *
     * @var int
     */
    private $pollerId;

    /**
     * Method tests
     * 
     * @param int $pollerId
     * @return type
     */
    public function __construct($pollerId)
    {
        $this->di = Di::getDefault();
        $this->output = array();
        $this->path = "/var/lib/centreon/tmp/";
        $this->filesDir = array();
        $this->pollerId = $pollerId;
    }

    /**
     * Generate all configuration files
     *
     */
    public function generate()
    {
        $this->checkPollerInformations();
        $this->generateObjectsFiles();
        $this->generateMainFiles();
    }

    /**
     * Generate main configuration file
     */
    public function generateMainFiles()
    {
        /* Generate Main File */
        $res = ConfigGenerateMainRepository::generate(
            $this->filesDir, 
            $this->pollerId, 
            $this->path, 
            "centengine.cfg"
        );
        if ($res) {
            $this->output[] = _("Generated centengine.cfg");
        }

        /* Generate Debugging Main File */
        $res = ConfigGenerateMainRepository::generate(
            $this->filesDir,
            $this->pollerId,
            $this->path,
            "centengine-testing.cfg",
            1
        );
        if ($res) {
            $this->output[] = _("Generated centengine-testing.cfg");
        }

        /* Correlation */
        $res = ConfigCorrelationRepository::generate($this->pollerId);
        if ($res) {
            $this->output[] = _("Generated correlation files");
        }
    }
    
    /**
     * Generate user macros
     *
     */
    public function generateResourcesFileConfigurations()
    {

    }
    
    /**
     * Generate all object files (host, service, contacts etc...)
     *
     */
    public function generateObjectsFiles()
    {
         /* Generate Configuration files */
        $res = CommandRepository::generate(
            $this->filesDir, 
            $this->pollerId, 
            $this->path, 
            "check-command.cfg",
            CommandRepository::CHECK_TYPE
        );
        if ($res) {
            $this->output[] = _("Generated check-command.cfg");
        }

        $res = CommandRepository::generate(
            $this->filesDir, 
            $this->pollerId, 
            $this->path, 
            "misc-command.cfg",
            CommandRepository::NOTIF_TYPE
        );
        if ($res) {
            $this->output[] = _("Generated misc-command.cfg");
        }

        $res = ConfigGenerateResourcesRepository::generate(
            $this->filesDir, 
            $this->pollerId, 
            $this->path, 
            "resources.cfg"
        );
        if ($res) {
            $this->output[] = _("Generated resources.cfg");
        }

        $res = TimeperiodRepository::generate($this->filesDir, $this->pollerId, $this->path, "timeperiods.cfg");
        if ($res) {
            $this->output[] = _("Generated timeperiods.cfg");
        }

        $res = ConnectorRepository::generate($this->filesDir, $this->pollerId, $this->path, "connectors.cfg");
        if ($res) {
            $this->output[] = _("Generated connectors.cfg");
        }

        $res = UserRepository::generate($this->filesDir, $this->pollerId, $this->path, "objects/contacts.cfg");
        if ($res) {
            $this->output[] = _("Generated contacts.cfg");
        }

        $res = UsergroupRepository::generate(
            $this->filesDir, 
            $this->pollerId, 
            $this->path, 
            "objects/contactgroups.cfg"
        );
        if ($res) {
            $this->output[] = _("Generated contactgroups.cfg");
        }

        /* Generate config Object */
        $res = HostgroupRepository::generate($this->filesDir, $this->pollerId, $this->path, "objects/hostgroups.cfg");
        if ($res) {
            $this->output[] = _("Generated hostgroups.cfg");
        }

        $res = ServicegroupRepository::generate(
            $this->filesDir,
            $this->pollerId,
            $this->path,
            "objects/servicegroups.cfg"
        );
        if ($res) {
            $this->output[] = _("Generated servicegroups.cfg");
        }

        /* Templates config files */
        $res = HosttemplateRepository::generate(
            $this->filesDir,
            $this->pollerId,
            $this->path,
            "objects/hostTemplates.cfg"
        );
        if ($res) {
            $this->output[] = _("Generated hostTemplates.cfg");
        }

        $res = ServicetemplateRepository::generate(
            $this->filesDir,
            $this->pollerId,
            $this->path,
            "objects/serviceTemplates.cfg"
        );
        if ($res) {
            $this->output[] = _("Generated serviceTemplates.cfg");
        }

        /* Monitoring Resources files */
        $res = HostRepository::generate($this->filesDir, $this->pollerId, $this->path, "resources/");
        if ($res) {
            $this->output[] = _("Generated host configuration files");
        }
    }

    /**
     * 
     * @return array
     */
    public function checkPollerInformations()
    {
        $dbconn = $this->di->get('db_centreon');
        
        $query = "SELECT * FROM nagios_server WHERE id = ?";
        $stmt = $dbconn->prepare($query);
        $stmt->execute(array($this->pollerId));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!isset($row)) {
            $this->output[] = "Poller {$this->pollerId} is not defined or not enabled.";
        }
    }

    /**
     * Get output
     *
     * @param string $glue
     * @return string
     */
    public function getOutput($glue = "\n")
    {
        return implode($glue, $this->output);
    }
}
