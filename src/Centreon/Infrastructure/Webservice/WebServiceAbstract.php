<?php
/*
 * Copyright 2005-2019 Centreon
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
 *
 */

namespace Centreon\Infrastructure\Webservice;

use Pimple\Container;
use Symfony\Component\Serializer;
use JsonSerializable;
use Centreon\Application\DataRepresenter;
use Centreon\ServiceProvider;

/**
 * @OA\Server(
 *      url="{protocol}://{host}/centreon/api",
 *      variables={
 *          "protocol": {"enum": {"http", "https"}, "default": "http"},
 *          "host": {"default": "centreon-dev"}
 *      }
 * )
 */
/**
 * @OA\Info(
 *      title="Centreon Server API",
 *      version="0.1"
 * )
 */
/**
 * @OA\ExternalDocumentation(
 *      url="https://documentation.centreon.com/docs/centreon/en/18.10/api/api_rest/index.html",
 *      description="Official Centreon documentation about REST API"
 * )
 */

/**
 * @OA\Components(
 *      securitySchemes={
 *          "Session": {
 *              "type": "apiKey",
 *              "in": "cookie",
 *              "name": "centreon",
 *              "description": "This type of authorization is mostly used for needs of Centreon Web UI"
 *          },
 *          "AuthToken": {
 *              "type": "apiKey",
 *              "in": "header",
 *              "name": "HTTP_CENTREON_AUTH_TOKEN",
 *              "description": "For external access to the resources that require authorization"
 *          }
 *      }
 * )
 */
abstract class WebServiceAbstract extends \CentreonWebService
{
    /** @var Container */
    protected $di;

    /**
     * Name of the webservice (the value that will be in the object parameter)
     *
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * Getter for DI container
     *
     * @return \Pimple\Container
     */
    public function getDi(): Container
    {
        return $this->di;
    }

    /**
     * Setter for DI container
     *
     * @param \Pimple\Container $di
     */
    public function setDi(Container $di)
    {
        $this->di = $di;
    }

    /**
     * Get URL parameters
     *
     * @return array
     */
    public function query(): array
    {
        $request = $_GET ?? [];

        return $request;
    }

    /**
     * Get body of request as string
     *
     * @return string
     */
    public function payloadRaw(): string
    {
        $content = file_get_contents('php://input');

        return $content ? (string) $content : '';
    }

    /**
     * Get body of request as decoded JSON
     *
     * @return array
     */
    public function payload(): array
    {
        $request = [];
        $content = $this->payloadRaw();

        if ($content) {
            $request = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $request = [];
            }
        }

        return $request;
    }

    /**
     * Get the Serializer service
     *
     * @return \Symfony\Component\Serializer\Serializer
     */
    public function getSerializer(): Serializer\Serializer
    {
        return $this->di[ServiceProvider::SERIALIZER];
    }

    /**
     * Return success response
     *
     * @param mixed $data
     * @param array $context the context for Serializer
     * @return JsonSerializable
     */
    public function success($data, array $context = []): JsonSerializable
    {
        return new DataRepresenter\Response(
            $this->getSerializer()->normalize($data, null, $context)
        );
    }
}
