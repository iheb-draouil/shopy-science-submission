<?php

namespace App\Controller;

use App\Definition\ErrorMessage;
use App\Entity\AppUser;
use App\Security\Hasher\CustomHasher;
use App\Service\JWTHandler;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        SerializerInterface $serializer_interface,
        ValidatorInterface $validator,
        CustomHasher $password_hasher,
        ManagerRegistry $doctrine,
        JWTHandler $jwt_handler,
    ) {
        parent::__construct($serializer_interface, $validator, $doctrine);
        $this->password_hasher = $password_hasher;
        $this->jwt_handler = $jwt_handler;
    }

    #[Route('register', methods: 'GET', name: 'register-page')]
    public function registerPage()
    {
        return $this->render('register.html.twig');
    }
    
}
