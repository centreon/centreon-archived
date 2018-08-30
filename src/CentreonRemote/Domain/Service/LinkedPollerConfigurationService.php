<?php

namespace CentreonRemote\Domain\Service;

use CentreonRemote\Domain\Value\ServerWizardIdentity;

class LinkedPollerConfigurationService
{

    public function manage()
    {
        $isRemoteConnection = ServerWizardIdentity::requestConfigurationIsRemote();

        // IF CONNECTING REMOTE
        // I can have (not required, can be empty) a $_POST list of poller ips from this current centreon
        // - then I need to make each of these pollers managed by the remote server I just inserted
        // - then export configuration xml file and restart
        // IF CONNECTING POLLER
        // I can have (not required, can be empty) a $_POST remote server ip linked to this centreon
        // - then I need to set the poller which I just inserted to be managed by this remote
        // - then export configuration xml file and restart
    }
}
