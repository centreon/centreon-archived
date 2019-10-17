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

        if (isset($this->ssoOptions['sso_enable'])
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
        } elseif (isset($this->ssoOptions['keycloak_enable'])
            && $this->ssoOptions['keycloak_enable'] == 1
            && !empty($this->ssoOptions['keycloak_url'])
            && !empty($this->ssoOptions['keycloak_redirect_url'])
            && !empty($this->ssoOptions['keycloak_realm'])
            && !empty($this->ssoOptions['keycloak_client_id'])
            && !empty($this->ssoOptions['keycloak_client_secret'])
        ) {
            $clientId = $this->ssoOptions['keycloak_client_id'];
            $clientSecret = $this->ssoOptions['keycloak_client_secret'];
            $realm = $this->ssoOptions['keycloak_realm'];
            $base = $this->ssoOptions['keycloak_url'];
            $redirectNoEncode = $this->ssoOptions['keycloak_redirect_url'];

            $redirect = urlencode($redirectNoEncode);
            $authUrl = $base . "/realms/" . $realm . "/protocol/openid-connect/auth?client_id=" . $clientId
                . "&response_type=code&redirect_uri=" . $redirect;

            $inputForce = filter_var(
                $_POST['force'] ?? $_GET['force'] ?? null,
                FILTER_SANITIZE_NUMBER_INT
            );
            if ((isset($inputForce) && $inputForce == 1)
                || (isset($this->options_sso['keycloak_mode']) && $this->options_sso['keycloak_mode'] == 0)
            ) {
                header('Location: ' . $authUrl);
            }

            $inputCode = filter_var(
                $_POST['code'] ?? $_GET['code'] ?? null,
                FILTER_SANITIZE_STRING
            );
            if (isset($inputCode)) {
                $keyToken = $this->getKeycloakToken(
                    $base,
                    $realm,
                    $redirectNoEncode,
                    $clientId,
                    $clientSecret,
                    $inputCode
                );

                $user = $this->getKeycloakUserInfo($base, $realm, $clientId, $clientSecret, $keyToken);

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
        if (isset($this->ssoOptions['sso_enable'])
            && $this->ssoOptions['sso_enable'] == 1
            && isset($this->ssoOptions['sso_mode'])
            && $this->ssoOptions['sso_mode'] == 1
        ) {
            # Mixed
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
        } elseif (isset($this->options_sso['keycloak_enable'])
            && $this->options_sso['keycloak_enable'] == 1
            && isset($this->options_sso['keycloak_mode'])
            && $this->options_sso['keycloak_mode'] == 1
        ) {
            # Mixed

            $blacklist = explode(',', $this->options_sso['keycloak_blacklist_clients']);
            foreach ($blacklist as $value) {
                $value = trim($value);
                if ($value != "" && preg_match('/' . $value . '/', $_SERVER['REMOTE_ADDR'])) {
                    return 0;
                }
            }

            $whitelist = explode(',', $this->options_sso['keycloak_trusted_clients']);
            if(empty($whitelist[0])) {
                return 1;
            }
            foreach ($whitelist as $value) {
                $value = trim($value);
                if ($value != "" && preg_match('/' . $value . '/', $_SERVER['REMOTE_ADDR'])) {
                    return 1;
                }
            }
        } else {
            # Only SSO (no login from local users)
            return 1;
        }
    }

    protected function checkPassword($password, $token = "", $autoimport = false)
    {
        if ($this->ssoMandatory == 1) {
            # Mode LDAP autoimport. Need to call it
            if ($autoimport) {
                # Password is only because it needs one...
                parent::checkPassword('test', $token, $autoimport);
            }
            # We delete old sessions with same SID
            global $pearDB;
            $pearDB->query("DELETE FROM session WHERE session_id = '" . session_id() . "'");
            $this->passwdOk = 1;
        } else {
            # local connect (when sso not enabled and 'sso_mode' == 1
            return parent::checkPassword($password, $token);
        }
    }


    /**
     * Connect to Keycloak and get token access
     *
     * @param string $base Keycloak Server Url
     * @param string $realm Keycloak Client Realm
     * @param string $redirectUri Keycloak Redirect Url
     * @param string $clientId Keycloak Client ID
     * @param string $clientSecret Keycloak Client Secret
     * @param string $code Keycloak Authorization Code
     *
     * @return string
    */
    public function getKeycloakToken($base, $realm, $redirectUri, $clientId, $clientSecret, $code)
    {
        $url = $base . "/realms/" . $realm . "/protocol/openid-connect/token";
        $data = array(
            "client_id" => $clientId,
            "client_secret" => $clientSecret,
            "grant_type" => "authorization_code",
            "code" => $code,
            "redirect_uri" => $redirectUri
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $result = curl_exec($ch);
        curl_close($ch);

        $resp = json_decode($result, true);

        return $resp["access_token"];
    }

    /**
     * Validate Centreon user on Keycloak
     *
     * @param string $base Keycloak Server Url
     * @param string $realm Keycloak Client Realm
     * @param string $clientId Keycloak Client ID
     * @param string $clientSecret Keycloak Client Secret
     * @param string $token Keycloak Token Access
     *
     * @return string
     */
    public function getKeycloakUserInfo($base, $realm, $clientId, $clientSecret, $token)
    {
        $url = $base . "/realms/" . $realm . "/protocol/openid-connect/token/introspect";
        $data = array(
            "token" => $token,
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization" => "Bearer " . $token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $result = curl_exec($ch);
        curl_close($ch);

        $resp = json_decode($result, true);
        return $resp;
    }
}
