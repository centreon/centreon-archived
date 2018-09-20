<?php

namespace CentreonRemote\Application\Webservice;

use Pimple\Container;

/**
 * @SWG\Info(title="Centreon Remote Server API", version="0.1")
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
}
