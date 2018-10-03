<?php

namespace CentreonRemote\Domain\Value;

use Curl\Curl;

class ServerWizardIdentity
{

    public function requestConfigurationIsRemote()
    {
        return isset($_POST['server_type']) && $_POST['server_type'] == 'remote';
    }

    public function requestConfigurationIsPoller()
    {
        return !static::requestConfigurationIsRemote();
    }

    public function fetchIfServerInstalledBam($ip, $centreonPath)
    {
        $centreonPath = trim($centreonPath, '/');
        $url = "{$ip}/{$centreonPath}/api/external.php?object=centreon_modules_webservice&action=getBamModuleInfo";

        try {
            $curl = new Curl;
            $curl->post($url);

            if ($curl->error) {
                return false;
            }
        } catch (\ErrorException $e) {
            return false;
        }

        $data = json_decode($curl->response, true);

        return array_key_exists('enabled', $data) && $data['enabled'];
    }
}
