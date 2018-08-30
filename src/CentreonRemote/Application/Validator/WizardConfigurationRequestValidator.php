<?php

namespace CentreonRemote\Application\Validator;

class WizardConfigurationRequestValidator
{

    public static function validate()
    {
        (new static)->validateServerPostData();
    }

    public function validateServerPostData()
    {
        $isRemoteConnection = isset($_POST['server_type']) && $_POST['server_type'] = 'remote';

        $this->validateServerGeneralFields();

        if ($isRemoteConnection) {
            $this->validateRemoteSpecificFields();
        }
    }

    private function validateServerGeneralFields()
    {
        if (!isset($_POST['server_name']) || !$_POST['server_name']) {
            throw new \RestBadRequestException('You need to send \'server_name\' in the request.');
        }

        if (!isset($_POST['server_ip']) || !$_POST['server_ip']) {
            throw new \RestBadRequestException('You need to send \'server_ip\' in the request.');
        }

        if (!filter_var($_POST['server_ip'], FILTER_VALIDATE_IP)) {
            throw new \RestBadRequestException('\'server_ip\' is not valid.');
        }

        if (!isset($_POST['centreon_central_ip']) || !$_POST['centreon_central_ip']) {
            throw new \RestBadRequestException('You need to send \'centreon_central_ip\' in the request.');
        }

        if (!filter_var($_POST['centreon_central_ip'], FILTER_VALIDATE_IP)) {
            throw new \RestBadRequestException('\'centreon_central_ip\' is not valid.');
        }
    }

    private function validateRemoteSpecificFields()
    {
        if (!isset($_POST['db_user']) || !$_POST['db_user']) {
            throw new \RestBadRequestException('You need to send \'db_user\' in the request.');
        }

        if (!isset($_POST['db_password']) || !$_POST['db_password']) {
            throw new \RestBadRequestException('You need to send \'db_password\' in the request.');
        }
    }
}
