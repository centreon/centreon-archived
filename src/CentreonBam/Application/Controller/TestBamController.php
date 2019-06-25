<?php

namespace CentreonBam\Application\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class TestBamController extends AbstractFOSRestController
{
    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Rest\Get("/bam/test")
     * @return \FOS\RestBundle\View\View
     */
    public function test()
    {
        return $this->view('Bam controler ok');
    }
}