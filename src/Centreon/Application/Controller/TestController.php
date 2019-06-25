<?php


namespace Centreon\Application\Controller;

use Centreon\Domain\Entity\TestEntity;
use Centreon\Domain\Entity\EntityValidator;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractFOSRestController
{
    /**
     * @Route
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Rest\Post("/test/entity")
     * ParamConverter("test", converter="fos_rest.request_body")
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityValidator $entityValidator
     * @return \FOS\RestBundle\View\View
     */
    public function testEntity(Request $request, SerializerInterface $serializer, EntityValidator $entityValidator)
    {
        $errors = $entityValidator->validateEntityByArray(
            TestEntity::class,
            json_decode($request->getContent(), true)
        );
        if ($errors->count() > 0) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        } else {
            /**
             * @var $test TestEntity
             */
            $test = $serializer->deserialize($request->getContent(), TestEntity::class, 'json');
            return $this->view($test);
        }
    }

    /**
     * The pagination data had been created by a custom event listener.
     * See the App\Service\MonitoringService constructor to see how to inject it
     *
     * @IsGranted("ROLE_USER")
     *
     * @Rest\Get("/test/user")
     *
     * si view_response_listener = true il faut mettre l'annotation suivante, sinon c'est inutile
     * @Rest\View(populateDefaultVars=false)
     */
    public function testUser()
    {
        return $this->getUser();
    }
}