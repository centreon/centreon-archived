<?php

include_once ($centreon_path."/www/class/centreonAuth.class.php");

class CentreonAuthSSO extends CentreonAuth {

    protected $options_sso = array();
    protected $sso_mandatory = 0;

    public function __construct($username, $password, $autologin, $pearDB, $CentreonLog, $encryptType = 1, $token = "", $generalOptions) {
        $this->options_sso = $generalOptions;
        # var
        #$this->options_sso['sso_enable'] = 1;
        #$this->options_sso['sso_mode'] = 1;
        #$this->options_sso['sso_trusted_clients'] = '10.30.3.53';
        #$this->options_sso['sso_header_username'] = 'HTTP_AUTH_USER';

        if (isset($this->options_sso['sso_enable']) && $this->options_sso['sso_enable'] == 1) {
                if (!isset($this->options_sso['sso_header_username']) || $this->options_sso['sso_header_username'] == '') {
                        $this->options_sso['sso_enable'] = 0;
                } else {
                        $this->sso_username = $_SERVER[$this->options_sso['sso_header_username']];
                        if ($this->check_sso_client()) {
                                $this->sso_mandatory = 1;
                                $username = $this->sso_username;
                        }
                }
        }
        parent::__construct($username, $password, $autologin, $pearDB, $CentreonLog, $encryptType, $token);
        if ($this->error != '' && $this->sso_mandatory == 1) {
                $this->error .= " SSO Protection (user=" . $this->sso_username . ').';
                global $msg_error;
                $msg_error = "Invalid User. SSO Protection (user=" . $this->sso_username . ")";
        }
    }

    protected function check_sso_client() {
        if (isset($this->options_sso['sso_mode']) && $this->options_sso['sso_mode'] == 1) {
                # Mixed. Only trusted site for sso.
                if (preg_match('/' . $_SERVER['REMOTE_ADDR'] . '(\s|,|$)/', $this->options_sso['sso_trusted_clients'])) {
                        # SSO
                        return 1;
                }
                return 0;
        } else {
                # Only SSO (no login from local users)
                return 1;
        }
    }

    protected function checkPassword($password, $token, $autoimport = false) {
        if (isset($this->options_sso['sso_enable']) && $this->options_sso['sso_enable'] == 1 &&
           $this->login) {
           # Mode LDAP autoimport. Need to call it
           if ($autoimport) {
                # Password is only because it needs one...
                parent::checkPassword('test', $token, $autoimport);
           }
           # We delete old sessions with same SID
           global $pearDB;
           $pearDB->query("DELETE FROM session WHERE session_id = '".session_id()."'");
           $this->passwdOk = 1;
        } else {
            # local connect (when sso not enabled and 'sso_mode' == 1
            return parent::checkPassword($password, $token);
        }
    }
}

?>
