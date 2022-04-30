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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends BaseController
{
    const login_path = '/login';
    const refresh_path = '/refresh';

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

    #[Route('register', methods: 'GET', name: 'register-page')]
    public function registerPage()
    {
        return $this->render('register.html.twig');
    }

    #[Route('api/register', methods: 'POST', name: 'register')]
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

        $hashed_password = $this->password_hasher->hash(
            $app_user->password,
            $app_user->username,
        );

        $app_user->password = $hashed_password;

        $manager = $this->doctrine->getManager();

        $manager->persist($app_user);
        $manager->flush();
        
        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    #[Route('login', methods: 'GET', name: 'login-page')]
    public function loginPage()
    {
        return $this->render('login.html.twig');
    }

    #[Route('api/login', methods: 'POST', name: 'login')]
    public function login(Request $request)
    {
        $response = new JsonResponse();

        $now = new DateTimeImmutable();
        $cookie_expiration = $now->modify('+1 day');
        $access_expiration = $now->modify('+1 minute');
        $refresh_expiration = $now->modify('+1 month');

        $security_user = $this->getUser();

        assert($security_user instanceof SecurityUser);

        $payload = $this->createPayload($security_user->id, $security_user->username);

        $access_jwt = $this->jwt_handler->create($payload, $access_expiration);
        $refresh_jwt = $this->jwt_handler->create($payload, $refresh_expiration);

        $manager = $this->doctrine->getManager();

        $app_user = $this->doctrine->getRepository(AppUser::class)
        ->findOneBy(['username' => $security_user->getUserIdentifier()]);

        if (!$app_user) {

            return new JsonResponse([
                'error' => ErrorMessage::UNEXPECTED_SERVER_ERROR
            ]);

        }

        $active_sessions = $this->doctrine->getRepository(ActiveSession::class)
        ->findBy(['app_user' => $app_user]);

        foreach ($active_sessions as $active_session) {
            $manager->remove($active_session);
        }
        
        $session = new ActiveSession();

        $session->jwt = $refresh_jwt;
        $session->started_at = $now;
        $session->app_user = $app_user;
        $session->expires_at = $refresh_expiration;

        $manager->persist($session);

        $manager->flush();

        $access_cookie = Cookie::create(JWTCookieNames::access)
        ->withValue($access_jwt)
        ->withExpires($cookie_expiration)
        ->withSecure(true);

        $refresh_cookie = Cookie::create(JWTCookieNames::refresh)
        ->withValue($refresh_jwt)
        ->withExpires($cookie_expiration)
        ->withSecure(true);

        // sets the refresh token as HttpOnly cookie
        $response->headers->setCookie($access_cookie);
        $response->headers->setCookie($refresh_cookie);
        
        return $response;
    }
    
    #[Route(SecurityController::refresh_path, methods: 'GET', name: 'refresh')]
    public function refresh(Request $request)
    {
        $referer = $request->headers->get('referer');

        if (is_null($referer)) {
            return new RedirectResponse('/flow/retreive-untreated-orders');
        }
        
        $response = new RedirectResponse($referer);

        $domain = $request->getHost();

        $now = new DateTimeImmutable();
        $access_expiration = $now->modify('+1 minute');

        $security_user = $this->getUser();

        assert($security_user instanceof SecurityUser);
        
        $access_jwt = $this->jwt_handler->create($this->createPayload($security_user->id, $security_user->username), $access_expiration);

        $access_cookie = Cookie::create(JWTCookieNames::access)
        ->withValue($access_jwt)
        ->withExpires($now->modify('+1 day'))
        ->withSecure(true);

        $response->headers->setCookie($access_cookie);
        
        return $response;
    }
    
    #[Route('api/logout', methods: 'POST', name: 'revoke')]
    public function logout()
    {
        $app_user = $this->doctrine->getRepository(AppUser::class)
        ->findOneBy(['username']);

        if (!$app_user) {

            return new JsonResponse([
                'error' => ErrorMessage::UNEXPECTED_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

        }
        
        $active_sessions = $this->doctrine->getRepository(ActiveSession::class)
        ->find(['app_user' => $app_user]);

        $manager = $this->doctrine->getManager();

        foreach ($active_sessions as $active_session) {
            $manager->remove($active_session);
        }

        $manager->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }
}