<?php

namespace App\Security\Guard;

use App\Definition\ErrorMessage;
use App\Entity\AppUser;
use App\Definition\Exception\InvalidRequestContent;
use App\Definition\Exception\InvalidRequestFormat;
use App\Security\Hasher\CustomHasher;
use App\Definition\SecurityUser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class AuthenticationGuard extends AbstractAuthenticator
{
    private $password_hasher;
    private $doctrine;
    
    public function __construct(CustomHasher $password_hasher, ManagerRegistry $doctrine)
    {
        $this->password_hasher = $password_hasher;
        $this->doctrine = $doctrine;
    }

    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() == '/api/login';
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $request->toArray();

        if (!array_key_exists('username', $credentials)
            || !array_key_exists('password', $credentials)) {
            throw new InvalidRequestFormat(ErrorMessage::INVALID_USERNAME_OR_PASSWORD);
        }

        $username = $credentials['username'];
        $password = $credentials['password'];

        $user = $this->doctrine->getRepository(AppUser::class)
        ->findOneBy(['username' => $username]);

        assert($user instanceof AppUser);

        if (!$user || !$this->password_hasher->verify($password, $user->username, $user->password)) {
            throw new InvalidRequestContent(ErrorMessage::INVALID_USERNAME_OR_PASSWORD);
        }

        return new SelfValidatingPassport(
            new UserBadge($username, fn($_) => new SecurityUser(
                $user->id,
                $user->username,
            ))
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($exception instanceof InvalidRequestFormat) {
            
            return new JsonResponse([
                'error' => ErrorMessage::INVALID_REQUEST_FORMAT
            ], Response::HTTP_BAD_REQUEST);

        }

        if ($exception instanceof InvalidRequestContent) {
            
            return new JsonResponse([
                'error' => ErrorMessage::INVALID_USERNAME_OR_PASSWORD
            ], Response::HTTP_UNAUTHORIZED);

        }
            
        return new JsonResponse([
            'error' => ErrorMessage::UNEXPECTED_SERVER_ERROR
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}