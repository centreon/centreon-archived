<?php

/**
 * Copyright 2005-2021 Centreon
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
require_once _CENTREON_PATH_ . "/www/class/centreonRestHttp.class.php";

class CentreonAuthSSO extends CentreonAuth
{

    protected $ssoOptions = array();
    protected $ssoMandatory = 0;

    /**
     * @var using a proxy
     */
    private $proxy = null;

    /**
     * @var proxy authentication information
     */
    private $proxyAuthentication = null;

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
        $this->CentreonLog = $CentreonLog;
        $this->getProxy();
        $this->debug = $this->getLogFlag();

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
            $this->source = "OpenId";

            # Get configured values
            $clientBasicAuth = $this->ssoOptions['openid_connect_client_basic_auth'];
            $clientId = $this->ssoOptions['openid_connect_client_id'];
            $clientSecret = $this->ssoOptions['openid_connect_client_secret'];
            $redirectNoEncode = $this->ssoOptions['openid_connect_redirect_url'];
            $verifyPeer = $this->ssoOptions['openid_connect_verify_peer'];

            # Build endpoint urls
            $baseUrl = rtrim($this->ssoOptions['openid_connect_base_url'], "/");
            $authEndpoint = $baseUrl . rtrim($this->ssoOptions['openid_connect_authorization_endpoint'], "/");
            $tokenEndpoint = $baseUrl . rtrim($this->ssoOptions['openid_connect_token_endpoint'], "/");
            $introspectionEndpoint = $baseUrl . rtrim($this->ssoOptions['openid_connect_introspection_endpoint'], "/");
            if (!empty($this->ssoOptions['openid_connect_userinfo_endpoint'])) {
                $userInfoEndpoint = $baseUrl . rtrim($this->ssoOptions['openid_connect_userinfo_endpoint'], "/");
            }
            if (!empty($this->ssoOptions['openid_connect_end_session_endpoint'])) {
                $endSessionEndpoint = $baseUrl . rtrim($this->ssoOptions['openid_connect_end_session_endpoint'], "/");
            }
            $redirect = urlencode($redirectNoEncode);
            $authUrl = $authEndpoint . "?client_id=" . $clientId . "&response_type=code&redirect_uri=" . $redirect;
            if (!empty($this->ssoOptions['openid_connect_scope'])) {
                $authUrl .= "&scope=" . urlencode($this->ssoOptions['openid_connect_scope']);
            }
            $authUrl .= "&state=" . uniqid();

            # Authnetication is OpenId only or mixed mode?
            $inputForce = filter_var(
                $_POST['force'] ?? $_GET['force'] ?? null,
                FILTER_SANITIZE_NUMBER_INT
            );

            # Access to IdP authentication page
            if (
                (isset($inputForce) && $inputForce == 1)
                || (isset($this->ssoOptions['openid_connect_mode'])
                && (int) $this->ssoOptions['openid_connect_mode'] === 0)
            ) {
                header('Location: ' . $authUrl);
            }

            # Reception of the IDP code
            $inputCode = filter_var(
                $_POST['code'] ?? $_GET['code'] ?? null,
                FILTER_SANITIZE_STRING
            );

            if (!empty($inputCode)) {
                # Retrieving the connection token
                $tokenInfo = $this->getOpenIdConnectToken(
                    $tokenEndpoint,
                    $redirectNoEncode,
                    $clientId,
                    $clientSecret,
                    $inputCode,
                    $clientBasicAuth,
                    $verifyPeer
                );

                # Checking the token expiration
                if (
                    (!empty($tokenInfo['expires_in']) && (int) $tokenInfo['expires_in'] < 0)
                    || (!empty($tokenInfo['active']) && (int) $tokenInfo['active'] !== 1)
                ) {
                    # If previsous session is expired, refresh request
                    $result = $this->refreshToken(
                        $tokenEndpoint,
                        $clientId,
                        $clientSecret,
                        $tokenInfo['refresh_token'],
                        $clientBasicAuth,
                        $verifyPeer,
                        !empty($this->ssoOptions['openid_connect_scope'])
                            ? $this->ssoOptions['openid_connect_scope']
                            : null
                    );
                    if (empty($result['error']) && !empty($result)) {
                        $tokenInfo = $result;
                    } else {
                        $this->CentreonLog->insertLog(
                            1,
                            "[" . $this->source . "] [Error] Refresh Token Info: " . json_encode($result)
                        );

                        if (!empty($endSessionEndpoint)) {
                            $result = $this->logout(
                                $endSessionEndpoint,
                                $clientId,
                                $clientSecret,
                                $tokenInfo['refresh_token'],
                                $clientBasicAuth,
                                $verifyPeer
                            );
                        }
                        $tokenInfo = null;
                        $inputCode = null;
                    }
                }

                # Retrieving user information
                if (!empty($tokenInfo['access_token'])) {
                    $user = $this->getOpenIdConnectIntrospectionToken(
                        $introspectionEndpoint,
                        $clientId,
                        $clientSecret,
                        $tokenInfo['access_token'],
                        $verifyPeer
                    );
                }

                # Login retrieval
                $loginClaimValue = !empty($this->ssoOptions['openid_connect_login_claim'])
                    ? $this->ssoOptions['openid_connect_login_claim']
                    : 'preferred_username';

                # If no login, retrieve additional information
                if (!isset($user[$loginClaimValue]) && isset($userInfoEndpoint) && isset($tokenInfo['access_token'])) {
                    $user = $this->getOpenIdConnectUserInfo(
                        $userInfoEndpoint,
                        $tokenInfo['access_token'],
                        $verifyPeer
                    );
                }

                # User authentication
                if (!isset($user['error']) && isset($user[$loginClaimValue])) {
                    $this->ssoUsername = $user[$loginClaimValue];
                    if ($this->checkSsoClient()) {
                        $this->ssoMandatory = 1;
                        $username = $this->ssoUsername;
                    }
                } elseif (isset($user['error'])) {
                    $this->CentreonLog->insertLog(
                        1,
                        "[" . $this->source . "] [Error] Can't authenticate user: " . $user['error']
                    );
                } elseif (!isset($user[$loginClaimValue])) {
                    $this->CentreonLog->insertLog(
                        1,
                        "[" . $this->source . "] [Error] Unable to get login from claim: " . $loginClaimValue
                    );
                }
            } else {
                $error = $_POST['error'] ?? $_GET['error'] ?? null;
                $errorDescription = $_POST['error_description'] ?? $_GET['error_description'] ?? null;
                if (isset($error)) {
                    $this->CentreonLog->insertLog(
                        1,
                        sprintf(
                            "[%s] [Error] Authorize error: %s, description: %s",
                            $this->source,
                            $error,
                            urldecode($errorDescription)
                        )
                    );
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
     * @param bool   $verifyPeer   Disable SSL verify peer
     *
     * @return array|null
    */
    public function getOpenIdConnectToken(
        string $url,
        string $redirectUri,
        string $clientId,
        string $clientSecret,
        string $code,
        bool $clientBasicAuth,
        bool $verifyPeer
    ): ?array {
        $data = [
            "grant_type" => "authorization_code",
            "code" => $code,
            "redirect_uri" => $redirectUri
        ];
        if ($clientBasicAuth) {
            $authentication =  "Authorization: Basic " . base64_encode($clientId . ":" . $clientSecret);
        } else {
            $data["client_id"] = $clientId;
            $data["client_secret"] = $clientSecret;
        }

        $restHttp = new \CentreonRestHttp('application/x-www-form-urlencoded');
        try {
            $result = $restHttp->call(
                $url,
                'POST',
                $data,
                $clientBasicAuth ? [$authentication] : null,
                true,
                $verifyPeer
            );
        } catch (Exception $e) {
            $this->CentreonLog->insertLog(
                1,
                sprintf(
                    "[%s] [Error] Unable to get Token Access Information: %S, message: %s",
                    $this->source,
                    get_class($e),
                    $e->getMessage()
                )
            );
        }

        if ($this->debug && isset($result)) {
            $this->CentreonLog->insertLog(
                1,
                "[" . $this->source . "] [Debug] Token Access Information: " . json_encode($result)
            );
        }

        return $result ?? null;
    }

    /**
     * Validate Token on OpenId Connect
     *
     * @param string $url          OpenId Connect Introspection Token Endpoint
     * @param string $clientId     OpenId Connect Client ID
     * @param string $clientSecret OpenId Connect Client Secret
     * @param string $token        OpenId Connect Token Access
     * @param bool   $verifyPeer   Disable SSL verify peer
     *
     * @return array|null
     */
    public function getOpenIdConnectIntrospectionToken(
        string $url,
        string $clientId,
        string $clientSecret,
        string $token,
        bool $verifyPeer
    ): ?array {
        $data = [
            "token" => $token,
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        ];

        $restHttp = new \CentreonRestHttp('application/x-www-form-urlencoded');
        try {
            $authentication = "Authorization: Bearer " . trim($token);
            $result = $restHttp->call(
                $url,
                'POST',
                $data,
                [$authentication],
                true,
                $verifyPeer
            );
        } catch (Exception $e) {
            $this->CentreonLog->insertLog(
                1,
                sprintf(
                    "[%s] [Error] Unable to get Token Introspection Information: %s, message: %s",
                    $this->source,
                    get_class($e),
                    $e->getMessage()
                )
            );
        }

        if ($this->debug && isset($result)) {
            $this->CentreonLog->insertLog(
                1,
                "[" . $this->source . "] [Debug] Token Introspection Information: " . json_encode($result)
            );
        }

        return $result ?? null;
    }

    /**
     * Get User Information on OpenId Connect
     *
     * @param string $url        OpenId Connect Introspection Token Endpoint
     * @param string $token      OpenId Connect Token Access
     * @param bool   $verifyPeer Disable SSL verify peer
     *
     * @return array|null
     */
    public function getOpenIdConnectUserInfo(
        string $url,
        string $token,
        bool $verifyPeer
    ): ?array {
        $authentication = "Authorization: Bearer " . trim($token);
        $restHttp = new \CentreonRestHttp('application/x-www-form-urlencoded');
        try {
            $result = $restHttp->call(
                $url,
                'POST',
                $data,
                [$authentication],
                true,
                $verifyPeer
            );
        } catch (Exception $e) {
            $this->CentreonLog->insertLog(
                1,
                sprintf(
                    "[%s] [Error] Unable to get User Information: %s, message: %s",
                    $this->source,
                    get_class($e),
                    $e->getMessage()
                )
            );
        }

        if ($this->debug && isset($result)) {
            $this->CentreonLog->insertLog(
                1,
                "[" . $this->source . "] [Debug] User Information: " . json_encode($result)
            );
        }

        return $result ?? null;
    }

    /**
     * Refresh the OpenId Connect token
     *
     * @param string      $url          OpenId Connect Introspection Token Endpoint
     * @param string      $clientId     OpenId Connect Client ID
     * @param string      $clientSecret OpenId Connect Client Secret
     * @param string      $refreshToken OpenId Connect Refresh Token Access
     * @param bool        $verifyPeer   Disable SSL verify peer
     * @param string|null $scope        The scope
     *
     * @return array|null
     */
    public function refreshToken(
        string $url,
        string $clientId,
        string $clientSecret,
        string $refreshToken,
        bool $clientBasicAuth,
        bool $verifyPeer,
        string $scope = null
    ): ?array {
        $data = [
            "grant_type" => "refresh_token",
            "refresh_token" => $refreshToken,
            "scope" => $scope
        ];
        if ($clientBasicAuth) {
            $authentication =  "Authorization: Basic " . base64_encode($clientId . ":" . $clientSecret);
        } else {
            $data["client_id"] = $clientId;
            $data["client_secret"] = $clientSecret;
        }

        $restHttp = new \CentreonRestHttp('application/x-www-form-urlencoded');
        try {
            $result = $restHttp->call(
                $url,
                'POST',
                $data,
                $clientBasicAuth ? [$authentication] : null,
                true,
                $verifyPeer
            );
        } catch (Exception $e) {
            $this->CentreonLog->insertLog(
                1,
                sprintf(
                    "[%s] [Error] Unable to refresh token: %s, message: %s",
                    $this->source,
                    get_class($e),
                    $e->getMessage()
                )
            );
        }

        if ($this->debug && isset($result)) {
            $this->CentreonLog->insertLog(
                1,
                "[" . $this->source . "] [Debug] Refresh Token Information: " . json_encode($result)
            );
        }

        return $result ?? null;
    }

    /**
     * Logout the OpenId session
     *
     * @param string $url          OpenId Connect Introspection Token Endpoint
     * @param string $clientId     OpenId Connect Client ID
     * @param string $clientSecret OpenId Connect Client Secret
     * @param string $refreshToken OpenId Connect Refresh Token Access
     * @param bool   $verifyPeer   Disable SSL verify peer
     *
     * @return array|null
     */
    public function logout(
        string $url,
        string $clientId,
        string $clientSecret,
        string $refreshToken,
        bool $clientBasicAuth,
        bool $verifyPeer
    ): ?array {
        $data = [
            "refresh_token" => $refreshToken
        ];
        if ($clientBasicAuth) {
            $authentication =  "Authorization: Basic " . base64_encode($clientId . ":" . $clientSecret);
        } else {
            $data["client_id"] = $clientId;
            $data["client_secret"] = $clientSecret;
        }

        $restHttp = new \CentreonRestHttp('application/x-www-form-urlencoded');
        try {
            $result = $restHttp->call(
                $url,
                'POST',
                $data,
                $clientBasicAuth ? [$authentication] : null,
                true,
                $verifyPeer
            );
        } catch (Exception $e) {
            $this->CentreonLog->insertLog(
                1,
                sprintf(
                    "[%s] [Error] Unable to logout the user: %s, message: %s",
                    $this->source,
                    get_class($e),
                    $e->getMessage()
                )
            );
        }

        if ($this->debug && isset($result)) {
            $this->CentreonLog->insertLog(
                1,
                "[" . $this->source . "] [Debug] Logout user Information: " . json_encode($result)
            );
        }

        return $result ?? null;
    }

    /**
     * get proxy data
     *
     */
    private function getProxy()
    {
        global $pearDB;
        $query = 'SELECT `key`, `value` '
            . 'FROM `options` '
            . 'WHERE `key` IN ( '
            . '"proxy_url", "proxy_port", "proxy_user", "proxy_password" '
            . ') ';
        $res = $pearDB->query($query);
        while ($row = $res->fetchRow()) {
            $dataProxy[$row['key']] = $row['value'];
        }

        if (isset($dataProxy['proxy_url']) && !empty($dataProxy['proxy_url'])) {
            $this->proxy = $dataProxy['proxy_url'];
            if ($dataProxy['proxy_port']) {
                $this->proxy .= ':' . $dataProxy['proxy_port'];
            }

            /* Proxy basic authentication */
            if (
                isset($dataProxy['proxy_user'])
                && !empty($dataProxy['proxy_user'])
                && isset($dataProxy['proxy_password'])
                && !empty($dataProxy['proxy_password'])
            ) {
                $this->proxyAuthentication = $dataProxy['proxy_user'] . ':' . $dataProxy['proxy_password'];
            }
        }
    }

    /**
     * Log enabled
     *
     * @return int
     */
    protected function getLogFlag()
    {
        global $pearDB;
        $res = $pearDB->query("SELECT value FROM options WHERE `key` = 'debug_auth'");
        $data = $res->fetch();
        if (isset($data["value"])) {
            return (int) $data["value"];
        }
        return 0;
    }
}
