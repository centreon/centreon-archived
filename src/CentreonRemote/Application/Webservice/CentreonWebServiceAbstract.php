<?php
namespace CentreonRemote\Application\Webservice;

use Pimple\Container;

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
abstract class CentreonWebServiceAbstract extends \CentreonWebService
{

    /** @var Container */
    protected $di;

    abstract public static function getName(): string;

    public function getDi(): Container
    {
        return $this->di;
    }

    public function setDi(Container $di)
    {
        $this->di = $di;
    }

    public function query(): array
    {
        $request = $_GET ?? [];

        return $request;
    }

    public function payload(): array
    {
        $request = json_decode(file_get_contents('php://input'), true);

        return $request;
    }
}
