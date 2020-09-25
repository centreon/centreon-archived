<?php

/*
 * Copyright 2005-2020 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once _CENTREON_PATH_ . 'bootstrap.php';

const AUTOLOGIN_FIELDS = array('autologin' , 'useralias', 'token');

if (
    isset($_POST["centreon_token"])
    || (isset($_GET["autologin"]) && $_GET["autologin"]
        && isset($generalOptions["enable_autologin"])
        && $generalOptions["enable_autologin"])
    || (isset($_POST["autologin"]) && $_POST["autologin"]
        && isset($generalOptions["enable_autologin"])
        && $generalOptions["enable_autologin"])
    || (!isset($generalOptions['sso_enable']) || $generalOptions['sso_enable'] == 1)
    || (!isset($generalOptions['openid_connect_enable']) || $generalOptions['openid_connect_enable'] == 1)
) {
    /*
     * Init log class
     */
    $CentreonLog = new CentreonUserLog(-1, $pearDB);

    if (isset($_POST['p'])) {
        $_GET["p"] = $_POST["p"];
    }

    if (isset($_POST['min'])) {
        $_GET["min"] = $_POST["min"];
    }

    /*
     * Check first for Autologin or Get Authentication
     */
    isset($_GET["autologin"]) ? $autologin = $_GET["autologin"] : $autologin = 0;
    isset($_GET["useralias"]) ? $useraliasG = $_GET["useralias"] : $useraliasG = null;
    isset($_GET["password"]) ? $passwordG = $_GET["password"] : $passwordG = null;

    $useraliasP = null;
    $passwordP = null;
    if ($loginValidate) {
        $useraliasP = $form->getSubmitValue('useralias');
        $passwordP = $form->getSubmitValue('password');
    }

    $useraliasG ? $useralias = $useraliasG : $useralias = $useraliasP;
    $passwordG ? $password = $passwordG : $password = $passwordP;

    $token = "";
    if (isset($_REQUEST['token']) && $_REQUEST['token']) {
        $token = $_REQUEST['token'];
    }

    if (!isset($encryptType)) {
        $encryptType = 1;
    }

    $centreonAuth = new CentreonAuthSSO(
        $dependencyInjector,
        $useralias,
        $password,
        $autologin,
        $pearDB,
        $CentreonLog,
        $encryptType,
        $token,
        $generalOptions
    );
    if ($centreonAuth->passwdOk == 1) {
        $centreon = new Centreon($centreonAuth->userInfos);
        // security fix - regenerate the sid after the login to prevent session fixation
        session_regenerate_id(true);
        $_SESSION["centreon"] = $centreon;
        // saving session data in the DB
        $query = "INSERT INTO `session` (`session_id` , `user_id` , `current_page` , `last_reload`, `ip_address`) " .
            "VALUES (?, ?, ?, ?, ?)";
        $dbResult = $pearDB->prepare($query);
        $pearDB->execute(
            $dbResult,
            array(session_id(), $centreon->user->user_id, '1', time(), $_SERVER["REMOTE_ADDR"])
        );

        if (!isset($_POST["submit"])) {
            $headerRedirection = "./main.php";
            if (!empty($_GET["p"])) {
                $headerRedirection .= "?p=" . $_GET["p"];
                unset($_GET["p"]);
                foreach ($_GET as $parameter => $value) {
                    if (!in_array($parameter, AUTOLOGIN_FIELDS)) {
                        $headerRedirection .= '&' . $parameter . '=' . $value;
                    }
                }
            } elseif (isset($centreon->user->default_page) && $centreon->user->default_page != '') {
                // get more details about the default page
                $stmt = $pearDB->prepare(
                    "SELECT topology_url, is_react FROM topology WHERE topology_page = ? LIMIT 0, 1"
                );
                $pearDB->execute($stmt, [$centreon->user->default_page]);

                if ($stmt->rowCount() && ($topologyData = $stmt->fetch()) && $topologyData['is_react']) {
                    // redirect to the react path
                    $headerRedirection = '.' . $topologyData['topology_url'];
                } else {
                    $headerRedirection .= "?p=" . $centreon->user->default_page;

                    if (isset($_GET["min"]) && $_GET["min"] == '1') {
                        $headerRedirection .= '&min=1';
                    }
                }
            }
        }
        header("Location: " . $headerRedirection);
        $connect = true;
    } else {
        $connect = false;
    }
}
