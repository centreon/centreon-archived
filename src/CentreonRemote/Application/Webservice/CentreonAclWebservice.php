<?php
namespace CentreonRemote\Application\Webservice;

use Centreon\Domain\Repository\TopologyRepository;

class CentreonAclWebservice extends CentreonWebServiceAbstract
{
    /**
     * Name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_acl_webservice';
    }

    public function getGetCurrentAcl()
    {
        $user = $_SESSION['centreon']->user;
        if (empty($user)){
            return [];
        }
        return $this->getDi()['centreon.db-manager']->getRepository(TopologyRepository::class)->getReactTopologiesPerUserWithAcl($user);
    }

}