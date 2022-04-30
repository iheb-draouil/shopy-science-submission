<?php

namespace App\Service;

use App\Definition\ErrorMessage;
use App\Definition\ServiceResponse\AppFailureResponse;
use App\Definition\ServiceResponse\AppSuccessResponse;
use App\Definition\ServiceResponse\Base\AppResponse;
use App\Service\JWTHandler;
use DateTimeImmutable;
use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JWTExtractor
{
    private $jwt_handler;

    public function __construct(JWTHandler $jwt_handler)
    {
        $this->jwt_handler = $jwt_handler;
    }

    public function extractFroomCookie(Request $request, string $cookie_name): AppResponse
    {
        $cookie_value = $request->cookies->get($cookie_name);

        if (!$cookie_value) {
            return new AppFailureResponse(ErrorMessage::ABSENT_COOKIE);
        }

        $validation = $this->jwt_handler->verify($cookie_value);

        if ($validation instanceof AppFailureResponse) {
            return new AppFailureResponse(ErrorMessage::INVALID_JWT_TOKEN);
        }

        $token = $validation->data;
        
        assert($token instanceof UnencryptedToken);

        if ($token->isExpired(new DateTimeImmutable())) {
            return new AppFailureResponse(ErrorMessage::EXPIRED_JWT_TOKEN);
        }
        
        return new AppSuccessResponse($token);
    }

    // public function extractAccessToken(Request $request): UnencryptedToken
    // {
    //     $authorization_header = $request->headers->get('Authorization');

    //     if (!$authorization_header) {
    //         throw new AuthenticationException(ErrorMessage::ABSENT_AUTHORIZATION_HEADER);
    //     }

    //     $validation = $this->jwt_handler->verify(str_replace('Bearer ', '', $authorization_header));

    //     if ($validation instanceof AppFailureResponse) {
    //         throw new AuthenticationException(ErrorMessage::INVALID_ACCESS_TOKEN);
    //     }

    //     $token = $validation->data;
        
    //     assert($token instanceof UnencryptedToken);

    //     if ($token->isExpired(new DateTimeImmutable())) {
    //         throw new AuthenticationException(ErrorMessage::EXPIRED_ACCESS_TOKEN);
    //     }
        
    //     return $token;
    // }
}