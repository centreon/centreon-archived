<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace CentreonRemote\Application\Webservice;

/**
 * @OA\Tag(name="centreon_remote_server", description="")
 */
class CentreonRemoteServer extends CentreonWebServiceAbstract
{

    /**
     * Name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_remote_server';
    }

    /**
     * @OA\Post(
     *   path="/external.php?object=centreon_remote_server&action=addToWaitList",
     *   description="Add remote Centreon instance in waiting list",
     *   tags={"centreon_remote_server"},
     *   @OA\Parameter(
     *       in="query",
     *       name="object",
     *       @OA\Schema(
     *          type="string",
     *          enum={"centreon_remote_server"},
     *          default="centreon_remote_server"
     *       ),
     *       description="the name of the API object class",
     *       required=true
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="action",
     *       @OA\Schema(
     *          type="string",
     *          enum={"addToWaitList"},
     *          default="addToWaitList"
     *       ),
     *       description="the name of the action in the API class",
     *       required=true
     *   ),
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          required={
     *              "app_key",
     *              "version"
     *          },
     *          @OA\Property(
     *              property="app_key",
     *              type="string",
     *              description="the unique app key of the Centreon instance"
     *          ),
     *          @OA\Property(
     *              property="version",
     *              type="string",
     *              description="the app version Centreon instance"
     *          )
     *       )
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="Empty string",
     *       @OA\JsonContent(
     *          @OA\Property(type="string")
     *       )
     *   )
     * )
     *
     * Add remote Centreon instance in waiting list
     *
     * @return string
     * @throws \RestBadRequestException
     * @throws \RestConflictException
     */
    public function postAddToWaitList(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        /**
         * Check mandatory parameters and request arguments
         */
        if (!$ip) {
            throw new \RestBadRequestException('Can not access your address.');
        }

        if (
            !isset($_POST['version'])
            || !$_POST['version']
            || empty($version = filter_var($_POST['version'], FILTER_SANITIZE_FULL_SPECIAL_CHARS))
        ) {
            throw new \RestBadRequestException('Please send \'version\' in the request.');
        }

        $filterOptions = ['options' => ['min_range' => 1, 'max_range' => 65535]];
        if (
            !isset($_POST['http_port'])
            || !$_POST['http_port']
            || false === ($httpPort = filter_var($_POST['http_port'], FILTER_VALIDATE_INT, $filterOptions))
        ) {
            throw new \RestBadRequestException('Inconsistent \'http port\' in the request.');
        }

        $noCheckCertificate = ($_POST['no_check_certificate'] === '1' ? '1' : '0');
        $httpScheme = ($_POST['http_method'] === 'https' ? 'https' : 'http');

        $statement = $this->pearDB->prepare('SELECT COUNT(id) as count FROM `remote_servers` WHERE `ip` = :ip');
        $statement->bindValue(':ip', $ip, \PDO::PARAM_STR);
        $statement->execute();
        $result = $statement->fetch();

        if ((bool)$result['count']) {
            throw new \RestConflictException('Address already in wait list.');
        }

        try {
            $createdAt = date('Y-m-d H:i:s');
            $insertQuery = "INSERT INTO `remote_servers` (`ip`, `version`, `is_connected`,
                `created_at`, `http_method`, `http_port`, `no_check_certificate`)
                VALUES (:ip, :version, 0, :created_at,
                    :http_method, :http_port, :no_check_certificate
                )";

            $insert = $this->pearDB->prepare($insertQuery);
            $insert->bindValue(':ip', $ip, \PDO::PARAM_STR);
            $insert->bindValue(':version', $version, \PDO::PARAM_STR);
            $insert->bindValue(':created_at', $createdAt, \PDO::PARAM_STR);
            $insert->bindValue(':http_method', $httpScheme, \PDO::PARAM_STR);
            $insert->bindValue(':http_port', $httpPort, \PDO::PARAM_INT);
            $insert->bindValue(':no_check_certificate', $noCheckCertificate, \PDO::PARAM_STR);
            $insert->execute();
        } catch (\Exception $e) {
            throw new \RestBadRequestException('There was an error while saving the data.');
        }

        return '';
    }

    /**
     * Authorize to access to the action
     *
     * @param string $action The action name
     * @param \CentreonUser $user The current user
     * @param bool $isInternal If the api is call in internal
     *
     * @return bool If the user has access to the action
     */
    public function authorize($action, $user, $isInternal = false)
    {
        return true;
    }
}
