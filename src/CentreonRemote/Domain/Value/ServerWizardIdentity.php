<?php

namespace CentreonRemote\Domain\Value;

class ServerWizardIdentity
{

    public static function requestConfigurationIsRemote()
    {
        return isset($_POST['server_type']) && $_POST['server_type'] == 'remote';
    }

    public static function requestConfigurationIsPoller()
    {
        return !static::requestConfigurationIsRemote();
    }
}
