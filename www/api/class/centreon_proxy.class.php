<?php

require_once dirname(__FILE__) . "/webService.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonRestHttp.class.php";

class CentreonProxy extends CentreonWebService
{
    public function postCheckConfiguration()
    {
        $proxyAddress = $this->arguments['url'];
        $proxyPort = $this->arguments['port'];
        try {
            $testUrl = 'http://ci.int.centreon.com:3000/api/pluginpack/pluginpack';
            $restHttpLib = new \CentreonRestHttp();
            $restHttpLib->setProxy($proxyAddress, $proxyPort);
            $restHttpLib->call($testUrl);
            $outcome = true;
            $message = _('Connection Successful');
        } catch (\Exception $e) {
            $outcome = false;
            $message = $e->getMessage();
        }

        return array(
            'outcome' => $outcome,
            'message' => $message
        );
    }
}
