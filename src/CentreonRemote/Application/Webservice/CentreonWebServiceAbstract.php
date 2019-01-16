<?php
namespace CentreonRemote\Application\Webservice;

use Pimple\Container;

/**
 * @OA\Server(
 *      url="{protocol}://{host}/centreon/api",
 *      variables={
 *          "protocol": {"enum": {"http", "https"}, "default": "http"},
 *          "host": {"default": "centreon-dev"},
 *      }
 * )
 * @OA\Info(
 *      title="Centreon Server API",
 *      version="0.1"
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
        $request = $_GET;

        return $request;
    }

    public function payload(): array
    {
        $request = json_decode(file_get_contents('php://input'), true);

        return $request;
    }
}
