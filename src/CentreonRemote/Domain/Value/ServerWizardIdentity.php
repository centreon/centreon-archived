<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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
