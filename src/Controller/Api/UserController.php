<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Definition\ErrorMessage;
use App\Entity\AppUser;
use App\Security\Hasher\CustomHasher;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends BaseController
{
    private $password_hasher;

    public function __construct(
        ManagerRegistry $manager_registry,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CustomHasher $password_hasher,
    ) {
        parent::__construct($serializer, $validator, $manager_registry);
        $this->password_hasher = $password_hasher;
    }

    #[Route('api/register', methods: 'post', name: 'register')]
    public function register(Request $request)
    {
        try {
            $app_user = $this->serializer->deserialize($request->getContent(), AppUser::class, 'json');
        }

        catch (Exception) {

            return new JsonResponse([
                'error' => ErrorMessage::DESERIALIZATION_FAILURE,
            ], Response::HTTP_BAD_REQUEST);

        }

        assert($app_user instanceof AppUser);
        
        $validation = $this->validator->validate($app_user);

        if (count($validation) != 0) {

            return new JsonResponse([
                'error' => ErrorMessage::VALIDATION_FAILURE,
                'fields' => $this->toValidationErrorMap($validation)
            ], Response::HTTP_BAD_REQUEST);

        }

        $other_app_users = $this->manager_registry->getRepository(AppUser::class)
        ->findBy(['username' => $app_user->username]);

        if (count($other_app_users) != 0) {

            return new JsonResponse([
                'error' => ErrorMessage::DUPLICATE_RECORD,
            ], Response::HTTP_BAD_REQUEST);

        }

        $hashed_password = $this->password_hasher->hash(
            $app_user->password,
            $app_user->username,
        );

        $app_user->password = $hashed_password;

        $manager = $this->manager_registry->getManager();

        $manager->persist($app_user);
        $manager->flush();
        
        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
