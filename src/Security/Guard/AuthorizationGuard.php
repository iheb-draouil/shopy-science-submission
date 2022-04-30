<?php

namespace App\Security\Guard;

use App\Controller\SecurityController;
use App\Definition\ErrorMessage;
use App\Definition\Exception\LoginRequired;
use App\Definition\Exception\RefreshRequired;
use App\Definition\JWTCookieNames;
use App\Definition\ServiceResponse\AppFailureResponse;
use App\Definition\SecurityUser;
use App\Service\JWTExtractor;
use App\Service\JWTHandler;
use DateTimeImmutable;
use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class AuthorizationGuard extends AbstractAuthenticator
{
    const WHITE_LIST = [
        SecurityController::refresh_path,
        '/api/register',
        '/api/login',
        '/register',
        '/login',
    ];

    private $jwt_extractor;
    private $jwt_handler;

    public function __construct(JWTExtractor $jwt_extractor, JWTHandler $jwt_handler)
    {
        $this->jwt_extractor = $jwt_extractor;
        $this->jwt_handler = $jwt_handler;
    }

    public function supports(Request $request): ?bool
    {
        return !in_array($request->getPathInfo(), AuthorizationGuard::WHITE_LIST);
    }

    public function authenticate(Request $request): Passport
    {
        // todo auth failure logs
        $access_token_response = $this->jwt_extractor->extractFroomCookie($request, JWTCookieNames::access);

        if ($access_token_response instanceof AppFailureResponse) {
            
            $refresh_token_response = $this->jwt_extractor->extractFroomCookie($request, JWTCookieNames::refresh);

            if ($refresh_token_response instanceof AppFailureResponse) {
                throw new LoginRequired();
            }

            throw new RefreshRequired($refresh_token_response->data);
        }

        assert($access_token_response->data instanceof UnencryptedToken);

        [
            'id' => $id,
            'username' => $username,
        ] = $this->jwt_handler->parse($access_token_response->data);

        return new SelfValidatingPassport(
            new UserBadge($username, fn($_) => new SecurityUser(
                $id,
                $username,
        )));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($exception instanceof RefreshRequired) {

            $response = new RedirectResponse($request->getPathInfo());
    
            $now = new DateTimeImmutable();
            
            [
                'id' => $id,
                'username' => $username,
            ] = $this->jwt_handler->parse($exception->token);
            
            $access_jwt = $this->jwt_handler->create([
                'id' => $id,
                'username' => $username,
            ], $now->modify('+1 minute'));
    
            $access_cookie = Cookie::create(JWTCookieNames::access)
            ->withValue($access_jwt)
            ->withExpires($now->modify('+1 day'))
            ->withSecure(true);
    
            $response->headers->setCookie($access_cookie);
    
            return new RedirectResponse($request->getPathInfo());
        }

        return new RedirectResponse(SecurityController::login_path);
    }
}
