<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/monitoring/status/Services/service.php $
 * SVN : $Id: service.php 8549 2009-07-01 16:20:26Z shotamchay $
 *
 */

    $centreon_path = realpath(dirname(__FILE__) . '/../../../../');
    require_once $centreon_path."/config/centreon.config.php";

    set_include_path(
        get_include_path() .
        PATH_SEPARATOR . $centreon_path . "www/include/configuration/configKnowledge/" .
        PATH_SEPARATOR . $centreon_path."www/class/" .
        PATH_SEPARATOR . $centreon_path."www/"
    );

    require_once "DB.php";
    require_once "include/common/common-Func.php";

    require_once "class/centreonLog.class.php";
    require_once "class/centreonDB.class.php";

    $pearDB = new CentreonDB();

    $conf = getWikiConfig($pearDB);

    if (isset($_GET["session_id"]) && $_GET["session_id"] != "") {
        $path = "core/display/";

        require_once "centreon-knowledge/procedures_DB_Connector.class.php";
        require_once "centreon-knowledge/procedures.class.php";

        /*
             * Init procedures Object
         */
        $proc = new procedures(
            3,
            $conf['kb_db_user'],
            $conf['kb_db_host'],
            $conf['kb_db_password'],
            $pearDB,
            $conf['kb_db_prefix']
        );
        $proc->setHostInformations();
        $proc->setServiceInformations();
        $wikiContent = $proc->getProcedures();

        if ($_GET["type"] == 0) {
            $diff = $proc->getDiff($proc->hostTplList, 2);
            $hostTplListForAdd = array(null => null);
            foreach ($diff as $key => $value) {
                if ($value) {
                    $hostTplListForAdd[trim($key)] = $value;
                }
            }

            /*
                 * HTML
             */
            print "<form method='GET' action='./popup.php'>";
            print "Based Template : ";
            print "<select name='template'>";
            foreach ($hostTplListForAdd as $key => $value) {
                print "<option value='$key'>$key</option>";
            }
            print "</select>";
            print "<input type='hidden' name='object' value='".htmlentities($_GET["type"], ENT_QUOTES)."' />";
            print "<input type='hidden' name='object' value='".htmlentities($_GET["object"], ENT_QUOTES)."' />";
            print "<input type='submit' name='create' value='"._("Create wiki page")."' />";
            print "</form>";
        } elseif ($_GET["type"] == 2) {
            $diff = $proc->getDiff($proc->hostTplList, 2);
            $hostTplListForAdd = array(null => null);
            foreach ($diff as $key => $value) {
                if ($value) {
                    $hostTplListForAdd["H-TPL-".trim($key)] = $value;
                }
            }
            /*
                 * HTML
             */
            print "<form method='GET' action='./popup.php'>";
            print "Based Template : ";
            print "<select name='template'>";
            foreach ($hostTplListForAdd as $key => $value) {
                print "<option value='$key'>$key</option>";
            }
            print "</select>";
            print "<input type='hidden' name='object' value='".htmlentities($_GET["type"], ENT_QUOTES)."' />";
            print "<input type='hidden' name='object' value='".htmlentities($_GET["object"], ENT_QUOTES)."' />";
            print "<input type='submit' name='create' value='"._("Create wiki page")."' />";
            print "</form>";
        } elseif ($_GET["type"] == 1) {
            $diff = $proc->getDiff($proc->serviceTplList, 3);
            $svcListForAdd = array(null => null);
            foreach ($diff as $key => $value) {
                if ($value) {
                    $svcListForAdd[$key] = $value;
                }
            }

            /*
                 * HTML
             */
            print "<form method='GET' action='./popup.php'>";
            print "Based Template : ";
            print "<select name='template'>";
            foreach ($svcListForAdd as $key => $value) {
                print "<option value='$key'>$key</option>";
            }
            print "</select>";
            print "<input type='hidden' name='object' value='".htmlentities($_GET["type"], ENT_QUOTES)."' />";
            print "<input type='hidden' name='object' value='".htmlentities($_GET["object"], ENT_QUOTES)."' />";
            print "<input type='submit' name='create' value='"._("Create wiki page")."' />";
            print "</form>";

        } elseif ($_GET["type"] == 3) {
            $diff = $proc->getDiff($proc->serviceTplList, 3);
            $svcTplListForAdd = array(null => null);
            foreach ($diff as $key => $value) {
                if ($value) {
                    $svcTplListForAdd[$key] = $value;
                }
            }

            /*
                 * HTML
             */
            print "<form method='GET' action='./popup.php'>";
            print "Based Template : ";
            print "<select name='template'>";
            foreach ($svcTplListForAdd as $key => $value) {
                print "<option value='$key'>$key</option>";
            }
            print "</select>";
            print "<input type='hidden' name='object' value='".htmlentities($_GET["type"], ENT_QUOTES)."' />";
            print "<input type='hidden' name='object' value='".htmlentities($_GET["object"], ENT_QUOTES)."' />";
            print "<input type='submit' name='create' value='"._("Create wiki page")."' />";
            print "</form>";
        }
    } else {
        print "No session open or session id not known";
        exit();
    }
