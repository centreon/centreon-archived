<?php

namespace CentreonRemote\Application\Validator;

use CentreonRemote\Domain\Value\ServerWizardIdentity;

/**
 * class to validate poller wizard forms
 */
class WizardConfigurationRequestValidator
{

    /**
     * validate arguments sent from poller/remote server wizard
     *
     * @return void
     */
    public static function validate(): void
    {
        (new static)->validateServerPostData();
    }

    /**
     * validate post arguments
     *
     * @return void
     */
    public function validateServerPostData(): void
    {
        $isRemoteConnection = (new ServerWizardIdentity)->requestConfigurationIsRemote();

        $this->validateServerGeneralFields();

        // if it is a remote server, validate specific fields (like database connection parameterss)
        if ($isRemoteConnection) {
            $this->validateRemoteSpecificFields();
        }
    }

    /**
     * validate general form fields which are in poller wizard and remote server wizard
     *
     * @return void
     */
    private function validateServerGeneralFields(): void
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

        if (!isset($_POST['centreon_central_ip']) || !$_POST['centreon_central_ip']) {
            throw new \RestBadRequestException(
                sprintf(_($missingParameterMessage), 'centreon_central_ip')
            );
        }
    }

    /**
     * validate form fields which are specific to remote server wizard
     *
     * @return void
     */
    private function validateRemoteSpecificFields(): void
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
