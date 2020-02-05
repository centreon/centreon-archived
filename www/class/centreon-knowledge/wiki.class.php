<?php
/*
 * Copyright 2005-2019 CENTREON
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once realpath(__DIR__ . "/../../../config/centreon.config.php");
require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";

class Wiki
{
    private $db;
    private $config = null;

    /**
     * WikiApi constructor.
     */
    public function __construct()
    {
        $this->db = new CentreonDB();
        $this->config = $this->getWikiConfig();
    }

    public function getWikiConfig()
    {
        if (!is_null($this->config)) {
            return $this->config;
        }

        $options = array();

        $res = $this->db->query(
            "SELECT * FROM `options` WHERE options.key LIKE 'kb_%'"
        );
        while ($opt = $res->fetch()) {
            $options[$opt["key"]] = html_entity_decode($opt["value"], ENT_QUOTES, "UTF-8");
        }
        $res->closeCursor();

        if (!isset($options['kb_wiki_url']) || $options['kb_wiki_url'] == '') {
            throw new \Exception(
                'Wiki is not configured. ' .
                'You can disable cron in /etc/cron.d/centreon for wiki synchronization.'
            );
        }

        if (!preg_match('#^http://|https://#', $options['kb_wiki_url'])) {
            $options['kb_wiki_url'] = 'http://' . $options['kb_wiki_url'];
        }

        return $options;
    }
}
