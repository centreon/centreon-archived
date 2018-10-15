<?php

namespace CentreonRemote\Application\Validator;

use CentreonRemote\Domain\Value\ServerWizardIdentity;

class WizardConfigurationRequestValidator
{

    public static function validate()
    {
        (new static)->validateServerPostData();
    }

    public function validateServerPostData()
    {
        $isRemoteConnection = (new ServerWizardIdentity)->requestConfigurationIsRemote();

        $this->validateServerGeneralFields();

        if ($isRemoteConnection) {
            $this->validateRemoteSpecificFields();
        }
    }

    private function validateServerGeneralFields()
    {
        $missingParameterMessage = "You need to send '%s' in the request.";
        
        if (!isset($_POST['server_name']) || !$_POST['server_name']) {
            throw new \RestBadRequestException(
                sprintf(_($missingParameterMessage), 'server_name')
            );
        }

        if (!isset($_POST['server_ip']) || !$_POST['server_ip']) {
            throw new \RestBadRequestException(
                sprintf(_($missingParameterMessage), 'server_ip')
            );
        }

        if (!filter_var($_POST['server_ip'], FILTER_VALIDATE_IP)) {
            throw new \RestBadRequestException(
                sprintf(_('%s is not valid.'), 'server_ip')
            );
        }

        if (!isset($_POST['centreon_central_ip']) || !$_POST['centreon_central_ip']) {
            throw new \RestBadRequestException(
                sprintf(_($missingParameterMessage), 'centreon_central_ip')
            );
        }

        if (!filter_var($_POST['centreon_central_ip'], FILTER_VALIDATE_IP)) {
            throw new \RestBadRequestException(
                sprintf(_('%s is not valid.'), 'centreon_central_ip')
            );
        }
    }

    private function validateRemoteSpecificFields()
    {
        if (!isset($_POST['db_user']) || !$_POST['db_user']) {
            throw new \RestBadRequestException(
                sprintf(_($missingParameterMessage), 'db_user')
            );
        }

        if (!isset($_POST['db_password']) || !$_POST['db_password']) {
            throw new \RestBadRequestException(
                sprintf(_($missingParameterMessage), 'db_password')
            );
        }

        if (!isset($_POST['centreon_folder']) || !$_POST['centreon_folder']) {
            throw new \RestBadRequestException(
                sprintf(_($missingParameterMessage), 'centreon_folder')
            );
        }
    }
}
