<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

class LinkedPollerConfigurationService
{

    public function setPollersConfigurationWithServer(array $pollers, $server)
    {
        foreach ($pollers as $poller) {
            // - in the broker config of the poller set
            // - ['central-broker']['output_forward'] host to this ip
        }
    }
}
