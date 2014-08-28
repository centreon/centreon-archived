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

use \Centreon\Internal\Exception;

/**
 * Factory for ConfigGenerate Engine
 *
 * @author Julien Mathis <jmathis@merethis.com>
 * @version 3.0.0
 */

class ConfigGenerateRepository extends ConfigRepositoryAbstract
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
    private $path;
    
    /**
     *
     * @var array
     */
    private $filesDir;

    /**
     * Method tests
     * 
     * @param int $pollerId
     * @return type
     */
    public function __construct($pollerId)
    {
        parent::__construct($pollerId);
        $this->path = $this->di->get('config')->get('global', 'centreon_generate_tmp_dir');
        $this->filesDir = array();
        $this->output[] = sprintf(_("Generating temporary configuration files of poller %s"), $pollerId);
    }

    /**
     * Generate all configuration files
     *
     */
    public function generate()
    {
        try {
            $this->checkPollerInformations();
            $this->generateObjectsFiles();
            $this->generateMainFiles();
        } catch (Exception $e) {
            $this->output[] = $e->getMessage();
            $this->status = false;
        }
    }

    /**
     * Generate main configuration file
     */
    public function generateMainFiles()
    {
        /* Generate Main File */
        ConfigGenerateMainRepository::generate(
            $this->filesDir, 
            $this->pollerId, 
            $this->path, 
            "centengine.cfg"
        );
        $this->output[] = _("Generated centengine.cfg");

        /* Generate Debugging Main File */
        ConfigGenerateMainRepository::generate(
            $this->filesDir,
            $this->pollerId,
            $this->path,
            "centengine-testing.cfg",
            1
        );
        $this->output[] = _("Generated centengine-testing.cfg");

        /* Correlation */
        ConfigCorrelationRepository::generate($this->pollerId);
        $this->output[] = _("Generated correlation files");
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
        CommandRepository::generate(
            $this->filesDir, 
            $this->pollerId, 
            $this->path, 
            "check-command.cfg",
            CommandRepository::CHECK_TYPE
        );
        $this->output[] = _("Generated check-command.cfg");

        CommandRepository::generate(
            $this->filesDir, 
            $this->pollerId, 
            $this->path, 
            "misc-command.cfg",
            CommandRepository::NOTIF_TYPE
        );
        $this->output[] = _("Generated misc-command.cfg");

        ConfigGenerateResourcesRepository::generate(
            $this->filesDir, 
            $this->pollerId, 
            $this->path, 
            "resources.cfg"
        );
        $this->output[] = _("Generated resources.cfg");

        TimeperiodRepository::generate($this->filesDir, $this->pollerId, $this->path, "timeperiods.cfg");
        $this->output[] = _("Generated timeperiods.cfg");

        ConnectorRepository::generate($this->filesDir, $this->pollerId, $this->path, "connectors.cfg");
        $this->output[] = _("Generated connectors.cfg");

        UserRepository::generate($this->filesDir, $this->pollerId, $this->path, "objects/contacts.cfg");
        $this->output[] = _("Generated contacts.cfg");

        UsergroupRepository::generate(
            $this->filesDir, 
            $this->pollerId, 
            $this->path, 
            "objects/contactgroups.cfg"
        );
        $this->output[] = _("Generated contactgroups.cfg");

        /* Generate config Object */
        HostgroupRepository::generate($this->filesDir, $this->pollerId, $this->path, "objects/hostgroups.cfg");
        $this->output[] = _("Generated hostgroups.cfg");

        ServicegroupRepository::generate(
            $this->filesDir,
            $this->pollerId,
            $this->path,
            "objects/servicegroups.cfg"
        );
        $this->output[] = _("Generated servicegroups.cfg");

        /* Templates config files */
        HostTemplateRepository::generate(
            $this->filesDir,
            $this->pollerId,
            $this->path,
            "objects/hostTemplates.cfg"
        );
        $this->output[] = _("Generated hostTemplates.cfg");

        ServicetemplateRepository::generate(
            $this->filesDir,
            $this->pollerId,
            $this->path,
            "objects/serviceTemplates.cfg"
        );
        $this->output[] = _("Generated serviceTemplates.cfg");

        /* Monitoring Resources files */
        HostRepository::generate($this->filesDir, $this->pollerId, $this->path, "resources/");
        $this->output[] = _("Generated host configuration files");
    }

    /**
     * 
     * @return array
     */
    public function checkPollerInformations()
    {
        $dbconn = $this->di->get('db_centreon');
        $query = "SELECT * FROM engine_server WHERE id = ?";
        $stmt = $dbconn->prepare($query);
        $stmt->execute(array($this->pollerId));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!isset($row)) {
            $this->output[] = "Poller {$this->pollerId} is not defined or not enabled.";
        }
    }
}
