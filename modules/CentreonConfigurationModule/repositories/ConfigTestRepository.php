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

/**
 * Factory for ConfigTest Engine
 *
 * @author Julien Mathis <jmathis@merethis.com>
 * @version 3.0.0
 */

class ConfigTestRepository
{
    private $di;
    private $enginepath;
    private $stdout;
    private $status;
    private $warning;
    
    /*
     * Methode tests
     * @return value
     */
    public function __construct($poller_id) 
    {
        $this->di = \Centreon\Internal\Di::getDefault();
        $this->enginepath = '/usr/sbin/centengine';
        $this->status = true;
        $this->warning = false;
    }

    public function checkConfig($poller_id) 
    {
        $this->di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $this->di->get('db_centreon');

        $path = "/var/lib/centreon/tmp/$poller_id/centengine-testing.cfg";
        $command = "sudo ".$this->enginepath." -v $path 2>&1";
        
        /* Check */
        $this->stdout = shell_exec($command);
        $this->stdout = htmlentities($this->stdout);

        /* Catch Errors */
        if (preg_match_all("/Total (Errors|Warnings)\:[ ]+([0-9]+)/", $this->stdout, $globalMatches, PREG_SET_ORDER)) {
            foreach ($globalMatches as $matches) {
                if ($matches[2] != "0") {
                    if ($matches[1] == "Errors") {
                        $this->status = false;
                    } elseif($matches[1] == "Warnings") {
                        $this->warning = true;
                    }
                }
            }
        } else {
            /* If the string is not found, the test is not ok */
            $this->status = false;
        }   
        
        /* return status */
        return array(
                     'status' => $this->status, 
                     'warning' => $this->warning, 
                     'command_line' => $command,
                     'stdout' => $this->stdout);
    }
}
