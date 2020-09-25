<?php
/**
 * Copyright 2005-2020 Centreon
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
 */

include_once _CENTREON_PATH_ . "/www/class/centreonAuth.class.php";

class CentreonAuthSSO extends CentreonAuth
{

    protected $ssoOptions = array();
    protected $ssoMandatory = 0;

    public function __construct(
        $dependencyInjector,
        $username,
        $password,
        $autologin,
        $pearDB,
        $CentreonLog,
        $encryptType = 1,
        $token = "",
        $generalOptions = array()
    ) {
        $this->ssoOptions = $generalOptions;

        if (
            isset($this->ssoOptions['sso_enable'])
            && $this->ssoOptions['sso_enable'] == 1
            && !empty($this->ssoOptions['sso_header_username'])
            && isset($_SERVER[$this->ssoOptions['sso_header_username']])
        ) {
            $this->ssoUsername = $_SERVER[$this->ssoOptions['sso_header_username']];
            if ($this->checkSsoClient()) {
                $this->ssoMandatory = 1;
                $username = $this->ssoUsername;
                if (!empty($this->ssoOptions['sso_username_pattern'])) {
                    $username = preg_replace(
                        $this->ssoOptions['sso_username_pattern'],
                        $this->ssoOptions['sso_username_replace'],
                        $username
                    );
                }
            }
        } elseif (
            isset($this->ssoOptions['openid_connect_enable'])
            && (int) $this->ssoOptions['openid_connect_enable'] === 1
            && !empty($this->ssoOptions['openid_connect_base_url'])
            && !empty($this->ssoOptions['openid_connect_authorization_endpoint'])
            && !empty($this->ssoOptions['openid_connect_token_endpoint'])
            && !empty($this->ssoOptions['openid_connect_introspection_endpoint'])
            && !empty($this->ssoOptions['openid_connect_redirect_url'])
            && !empty($this->ssoOptions['openid_connect_client_id'])
            && !empty($this->ssoOptions['openid_connect_client_secret'])
        ) {
            $clientId = $this->ssoOptions['openid_connect_client_id'];
            $clientSecret = $this->ssoOptions['openid_connect_client_secret'];
            $redirectNoEncode = $this->ssoOptions['openid_connect_redirect_url'];
            $baseUrl = rtrim($this->ssoOptions['openid_connect_base_url'], "/");
            $authEndpoint = $baseUrl . rtrim($this->ssoOptions['openid_connect_authorization_endpoint'], "/");
            $tokenEndpoint = $baseUrl . rtrim($this->ssoOptions['openid_connect_token_endpoint'], "/");
            $introspectionEndpoint = $baseUrl . rtrim($this->ssoOptions['openid_connect_introspection_endpoint'], "/");
            if (
                isset($this->ssoOptions['openid_connect_userinfo_endpoint'])
                && $this->ssoOptions['openid_connect_userinfo_endpoint'] != ""
            ) {
                $userInfoEndpoint = $baseUrl . rtrim($this->ssoOptions['openid_connect_userinfo_endpoint'], "/");
            }
            
            $verifyPeer = $this->ssoOptions['openid_connect_verify_peer'];

            $redirect = urlencode($redirectNoEncode);
            $authUrl = $authEndpoint . "?client_id=" . $clientId . "&response_type=code&redirect_uri=" . $redirect;
            if (
                isset($this->ssoOptions['openid_connect_scope'])
                && $this->ssoOptions['openid_connect_scope'] != ""
            ) {
                $authUrl .= "&scope=" . $this->ssoOptions['openid_connect_scope'];
            }

            $inputForce = filter_var(
                $_POST['force'] ?? $_GET['force'] ?? null,
                FILTER_SANITIZE_NUMBER_INT
            );
            if (
                (isset($inputForce) && $inputForce == 1)
                || (isset($this->ssoOptions['openid_connect_mode'])
                && (int) $this->ssoOptions['openid_connect_mode'] === 0)
            ) {
                header('Location: ' . $authUrl);
            }

            $inputCode = filter_var(
                $_POST['code'] ?? $_GET['code'] ?? null,
                FILTER_SANITIZE_STRING
            );

            if (isset($inputCode) && !is_null($inputCode) && $inputCode != '') {
                $keyToken = $this->getOpenIdConnectToken(
                    $tokenEndpoint,
                    $redirectNoEncode,
                    $clientId,
                    $clientSecret,
                    $inputCode,
                    $verifyPeer
                );

                $user = $this->getOpenIdConnectIntrospectionToken(
                    $introspectionEndpoint,
                    $clientId,
                    $clientSecret,
                    $keyToken,
                    $verifyPeer
                );

                if (!isset($user["preferred_username"]) && isset($userInfoEndpoint)) {
                    $user = $this->getOpenIdConnectUserInfo(
                        $userInfoEndpoint,
                        $keyToken,
                        $verifyPeer
                    );
                }

                if (!isset($user['error'])) {
                    $this->ssoUsername = $user["preferred_username"];
                    if ($this->checkSsoClient()) {
                        $this->ssoMandatory = 1;
                        $username = $this->ssoUsername;
                    }
                }
            }
        }

        parent::__construct(
            $dependencyInjector,
            $username,
            $password,
            $autologin,
            $pearDB,
            $CentreonLog,
            $encryptType,
            $token
        );
        if ($this->error != '' && $this->ssoMandatory == 1) {
            $this->error .= " SSO Protection (user=" . $this->ssoUsername . ').';
            global $msg_error;
            $msg_error = "Invalid User. SSO Protection (user=" . $this->ssoUsername . ")";
        }
    }

    protected function checkSsoClient()
    {
        if (
            isset($this->ssoOptions['sso_enable'])
            && $this->ssoOptions['sso_enable'] == 1
            && isset($this->ssoOptions['sso_mode'])
            && $this->ssoOptions['sso_mode'] == 1
        ) {
            // Mixed
            $blacklist = explode(',', $this->ssoOptions['sso_blacklist_clients']);
            foreach ($blacklist as $value) {
                $value = trim($value);
                if ($value != "" && preg_match('/' . $value . '/', $_SERVER['REMOTE_ADDR'])) {
                    return 0;
                }
            }

            $whitelist = explode(',', $this->ssoOptions['sso_trusted_clients']);
            if (empty($whitelist[0])) {
                return 1;
            }
            foreach ($whitelist as $value) {
                $value = trim($value);
                if ($value != "" && preg_match('/' . $value . '/', $_SERVER['REMOTE_ADDR'])) {
                    return 1;
                }
            }
        } elseif (
            isset($this->ssoOptions['openid_connect_enable'])
            && $this->ssoOptions['openid_connect_enable'] == 1
            && isset($this->ssoOptions['openid_connect_mode'])
            && $this->ssoOptions['openid_connect_mode'] == 1
        ) {
            // Mixed
            $blacklist = explode(',', $this->ssoOptions['openid_connect_blacklist_clients']);
            foreach ($blacklist as $value) {
                $value = trim($value);
                if ($value != "" && preg_match('/' . $value . '/', $_SERVER['REMOTE_ADDR'])) {
                    return 0;
                }
            }

            $whitelist = explode(',', $this->ssoOptions['openid_connect_trusted_clients']);
            if (empty($whitelist[0])) {
                return 1;
            }
            foreach ($whitelist as $value) {
                $value = trim($value);
                if ($value != "" && preg_match('/' . $value . '/', $_SERVER['REMOTE_ADDR'])) {
                    return 1;
                }
            }
        } else {
            // Only SSO (no login from local users)
            return 1;
        }
    }

    protected function checkPassword($password, $token = "", $autoimport = false)
    {
        if ($this->ssoMandatory == 1) {
            // Mode LDAP autoimport. Need to call it
            if ($autoimport) {
                // Password is only because it needs one...
                parent::checkPassword('test', $token, $autoimport);
            }
            // We delete old sessions with same SID
            global $pearDB;
            $pearDB->query("DELETE FROM session WHERE session_id = '" . session_id() . "'");
            $this->passwdOk = 1;
        } else {
            // local connect (when sso not enabled and 'sso_mode' == 1
            return parent::checkPassword($password, $token);
        }
    }


    /**
     * Connect to OpenId Connect and get token access
     *
     * @param string $url          OpenId Connect Client Token endpoint
     * @param string $redirectUri  OpenId Connect Redirect Url
     * @param string $clientId     OpenId Connect Client ID
     * @param string $clientSecret OpenId Connect Client Secret
     * @param string $code         OpenId Connect Authorization Code
     * @param bool   $verifyPeer   disable SSL verify peer
     *
     * @return string
    */
    public function getOpenIdConnectToken($url, $redirectUri, $clientId, $clientSecret, $code, $verifyPeer)
    {
        $data = [
            "client_id" => $clientId,
            "client_secret" => $clientSecret,
            "grant_type" => "authorization_code",
            "code" => $code,
            "redirect_uri" => $redirectUri
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        if ($verifyPeer) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        $resp = json_decode($result, true);

        return $resp["access_token"] ?? null;
    }

    /**
     * Validate Token on OpenId Connect
     *
     * @param string $url          OpenId Connect Introspection Token Endpoint
     * @param string $clientId     OpenId Connect Client ID
     * @param string $clientSecret OpenId Connect Client Secret
     * @param string $token        OpenId Connect Token Access
     * @param bool   $verifyPeer   disable SSL verify peer
     *
     * @return array
     */
    public function getOpenIdConnectIntrospectionToken($url, $clientId, $clientSecret, $token, $verifyPeer)
    {
        $data = [
            "token" => $token,
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization" => "Bearer " . $token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        if ($verifyPeer) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        $resp = json_decode($result, true);
        return $resp;
    }

    /**
     * Get User Information on OpenId Connect
     *
     * @param string $url        OpenId Connect Introspection Token Endpoint
     * @param string $token      OpenId Connect Token Access
     * @param bool   $verifyPeer disable SSL verify peer
     *
     * @return array
     */
    public function getOpenIdConnectUserInfo($url, $token, $verifyPeer)
    {
        $ch = curl_init($url);
        $authentication = "Authorization: Bearer " . trim($token);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [$authentication]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        if ($verifyPeer) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        $resp = json_decode($result, true);
        return $resp;
    }
}
