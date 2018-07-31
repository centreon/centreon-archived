<?php

namespace CentreonRemote\Application\Webservice;

use Centreon\Infrastructure\Service\CentreonWebserviceServiceInterface;

class CentreonConfigurationRemote extends \CentreonWebService implements CentreonWebserviceServiceInterface
{

    /**
     * Name of web service object
     * 
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_configuration_remote';
    }

    /**
     * Get remotes servers waitlist
     * 
     * @return string
     */
    public function postGetWaitList(): string
    {
        //TODO
        throw new \RestConflictException('Try again later.');

        return json_encode([]);
    }

    /**
     * Authorize to access to the action
     *
     * @param string $action The action name
     * @param \CentreonUser $user The current user
     * @param boolean $isInternal If the api is call in internal
     *
     * @return boolean If the user has access to the action
     */
    public function authorize($action, $user, $isInternal = false)
    {
        if (parent::authorize($action, $user, $isInternal)) {
            return true;
        }

        return $user && $user->hasAccessRestApiConfiguration();
    }
}
