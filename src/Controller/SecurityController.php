<?php

namespace App\Controller;

use App\Definition\ErrorMessage;
use App\Definition\JWTCookieNames;
use App\Entity\ActiveSession;
use App\Entity\AppUser;
use App\Security\Hasher\CustomHasher;
use App\Definition\SecurityUser;
use App\Service\JWTHandler;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Routing\Annotation\Route;

use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends BaseController
{
    private $password_hasher;
    private $jwt_handler;

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

    private function createPayload(int $id, string $username)
    {
        return ['id' => $id, 'username' => $username];
    }

    #[Route('login', methods: 'GET', name: 'login-page')]
    public function loginPage()
    {
        return $this->render('login.html.twig');
    }
}