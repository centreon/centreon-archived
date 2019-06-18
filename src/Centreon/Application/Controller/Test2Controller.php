<?php


namespace Centreon\Application\Controller;

use App\Contact;
use Centreon\Domain\Entity\TestEntity;
use Centreon\Domain\EntityValidator;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Test2Controller extends AbstractFOSRestController
{
    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Rest\Post("/test2")
     * ParamConverter("test", converter="fos_rest.request_body")
     * @return \FOS\RestBundle\View\View
     */
    public function test(Request $request, SerializerInterface $serializer, EntityValidator $entityValidator)
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
}