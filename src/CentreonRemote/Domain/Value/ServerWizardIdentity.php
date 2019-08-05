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
     * @param string $centreonUrl URL of Centreon of the remote server
     * @param bool $noCheckCertificate do not check peer SSL certificat
     * @param bool $noProxy don't use configured proxy
     * @return bool if bam is installed on remote server
     */
    public function checkBamOnRemoteServer(
        string $centreonUrl,
        bool $noCheckCertificate = false,
        bool $noProxy = false
    ): bool {
        $centreonUrl .= "/api/external.php?object=centreon_modules_webservice&action=getBamModuleInfo";

        try {
            $curl = new Curl;

            if ($noCheckCertificate) {
                $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
            }

            if ($noProxy) {
                $curl->setOpt(CURLOPT_PROXY, false);
            }

            $curl->post($centreonUrl);

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
