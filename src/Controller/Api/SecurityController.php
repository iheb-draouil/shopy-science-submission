<?php

namespace App\Controller\Api;

use App\Definition\ErrorMessage;
use App\Definition\SecurityUser;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('api/login', methods: 'post', name: 'login')]
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
        // ->withSecure(true)
        ;

        $refresh_cookie = Cookie::create(JWTCookieNames::refresh)
        ->withValue($refresh_jwt)
        ->withExpires($cookie_expiration)
        // ->withSecure(true)
        ;

        // sets the refresh token as HttpOnly cookie
        $response->headers->setCookie($access_cookie);
        $response->headers->setCookie($refresh_cookie);
        
        return $response;
    }
    
    #[Route('api/refresh', methods: 'get', name: 'refresh')]
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
    
    #[Route('api/logout', methods: 'post', name: 'revoke')]
    public function logout()
    {
        $security_user = $this->getUser();

        assert($security_user instanceof SecurityUser);

        $app_user = $this->doctrine->getRepository(AppUser::class)
        ->find($security_user->id);

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
