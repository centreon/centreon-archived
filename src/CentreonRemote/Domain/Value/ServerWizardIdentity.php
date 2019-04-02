<?php

namespace CentreonRemote\Domain\Value;

use Curl\Curl;

/**
 * Check wizard type
 */
class ServerWizardIdentity
{
    /**
     * check wizard type (remote server / poller)
     *
     * @return bool true if it is remote server wizard
     */
    public function requestConfigurationIsRemote(): bool
    {
        return isset($_POST['server_type']) && $_POST['server_type'] == 'remote';
    }

    /**
     * check wizard type (remote server / poller)
     *
     * @return bool true if it is poller wizard
     */
    public function requestConfigurationIsPoller(): bool
    {
        return !static::requestConfigurationIsRemote();
    }

    /**
     * check if bam is installed on remote server
     *
     * @param string $ip ip address of the remote server
     * @param string $centreonPath centreon web path on remote server
     * @return bool if bam is installed on remote server
     */
    public function checkBamOnRemoteServer(string $ip, string $centreonPath): bool
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
