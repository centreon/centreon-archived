<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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
     * @throws \RestBadRequestException
     */
    public static function validate(): void
    {
        (new static)->validateServerPostData();
    }

    /**
     * validate post arguments
     *
     * @return void
     * @throws \RestBadRequestException
     */
    public function validateServerPostData(): void
    {
        $isRemoteConnection = (new ServerWizardIdentity())->requestConfigurationIsRemote();

        $this->validateServerGeneralFields();

        // if it is a remote server, validate specific fields (like database connection parameters)
        if ($isRemoteConnection) {
            $this->validateRemoteSpecificFields();
        }
    }

    /**
     * validate general form fields which are in poller wizard and remote server wizard
     *
     * @return void
     * @throws \RestBadRequestException
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
     * @throws \RestBadRequestException
     */
    private function validateRemoteSpecificFields(): void
    {
        $missingParameterMessage = "You need to send '%s' in the request.";

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
