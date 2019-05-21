<?php


namespace Centreon\Application\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractFOSRestController
{

    /**
     * The pagination data had been created by a custom event listener.
     * See the App\Service\MonitoringService constructor to see how to inject it
     *
     * @IsGranted("ROLE_USER")
     *
     * @Rest\Get("/test")
     *
     * si view_response_listener = true il faut mettre l'annotation suivante, sinon c'est inutile
     * @Rest\View(populateDefaultVars=false)
     */
    public function test()
    {
        return $this->getUser();
    }
}