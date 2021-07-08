<?php
/*
 * Copyright 2005-2015 Centreon
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

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once _CENTREON_PATH_ . 'www/class/centreon.class.php';
require_once dirname(__FILE__) . '/class/webService.class.php';
require_once dirname(__FILE__) . '/interface/di.interface.php';

error_reporting(-1);
ini_set('display_errors', 0);

$pearDB = $dependencyInjector['configuration_db'];

$kernel = \App\Kernel::createForWeb();

/* Test if the call is for authenticate */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'authenticate') {
    if (false === isset($_POST['username']) || false === isset($_POST['password'])) {
        CentreonWebService::sendResult("Bad parameters", 400);
    }

    $credentials = [
        "login" => $_POST['username'],
        "password" => $_POST['password'],
    ];
    $authenticateApiUseCase = $kernel->getContainer()->get(
        \Centreon\Domain\Authentication\UseCase\AuthenticateApi::class
    );
    $request = new \Centreon\Domain\Authentication\UseCase\AuthenticateApiRequest(
        $credentials['login'], $credentials['password']
    );
    $response = new \Centreon\Domain\Authentication\UseCase\AuthenticateApiResponse();
    $authenticateApiUseCase->execute($request, $response);

    if (!empty($response->getApiAuthentication()['security']['token'])) {
        CentreonWebService::sendResult(['authToken' => $response->getApiAuthentication()['security']['token']]);
    } else {
        CentreonWebService::sendResult('Invalid credentials', 403);
    }
} else { // Purge old tokens
    $authenticationService = $kernel->getContainer()->get(
        \Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface::class
    );
    $authenticationService->deleteExpiredSecurityTokens();
}

/* Test authentication */
if (false === isset($_SERVER['HTTP_CENTREON_AUTH_TOKEN'])) {
    CentreonWebService::sendResult("Unauthorized", 401);
}

/* Create the default object */
try {
    $contactStatement = $pearDB->prepare(
        "SELECT c.*
        FROM security_authentication_tokens sat, contact c
        WHERE c.contact_id = sat.user_id
        AND sat.token = :token"
    );
    $contactStatement->bindValue(':token', $_SERVER['HTTP_CENTREON_AUTH_TOKEN'], \PDO::PARAM_STR);
    $contactStatement->execute();
} catch (\PDOException $e) {
    CentreonWebService::sendResult("Database error", 500);
}
$userInfos = $contactStatement->fetch();
if (is_null($userInfos)) {
    CentreonWebService::sendResult("Unauthorized", 401);
}

$centreon = new \Centreon($userInfos);
$oreon = $centreon;

CentreonWebService::router($dependencyInjector, $centreon->user);
