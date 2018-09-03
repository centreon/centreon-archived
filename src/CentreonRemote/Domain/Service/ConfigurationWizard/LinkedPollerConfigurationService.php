<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use CentreonRemote\Domain\Value\PollerServer;

class LinkedPollerConfigurationService
{

    public function setPollersConfigurationWithServer(array $pollers, PollerServer $server)
    {
        foreach ($pollers as $poller) {
            // - in the broker config of the poller set
            // - ['central-module']['output'] host to this ip
            // - export xml config of poller
        }
    }
}
