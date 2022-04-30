<?php

namespace App\Service;

use App\Definition\ErrorMessage;
use App\Definition\ServiceResponse\AppFailureResponse;
use App\Definition\ServiceResponse\AppSuccessResponse;
use App\Definition\ServiceResponse\Base\AppResponse;
use DateTimeImmutable;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Path;

class JWTHandler
{
    private $configuration;

    public function __construct(ContainerBagInterface $parameters)
    {
        $configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(Path::join($parameters->get('kernel.project_dir'), 'keys', 'private.pem')),
            InMemory::file(Path::join($parameters->get('kernel.project_dir'), 'keys', 'public.pem')),
        );

        $configuration->setValidationConstraints(
            new SignedWith($configuration->signer(), $configuration->verificationKey())
        );
        
        $this->configuration = $configuration;
    }
    
    public function create(array $data, DateTimeImmutable $expires_at): string
    {
        $token_builder = $this->configuration->builder()
        ->expiresAt($expires_at);

        foreach($data as $key => $value) {
            $token_builder->withClaim($key, $value);
        }
        
        return $token_builder->getToken($this->configuration->signer(), $this->configuration->signingKey())
        ->toString();
    }

    public function verify(string $jwt): AppResponse
    {
        try {
            $token = $this->configuration
            ->parser()
            ->parse($jwt);
        }

        catch (Exception) {
            return new AppFailureResponse([
                'error' => ErrorMessage::PARSING_FAILURE
            ]);
        }

        assert($token instanceof UnencryptedToken);

        try {
            $this->configuration->validator()
            ->assert($token, ...$this->configuration->validationConstraints());
        }

        catch (Exception) {
            return new AppFailureResponse();
        }
        
        return new AppSuccessResponse($token);
    }

    public function parse(UnencryptedToken $token): array
    {
        return $token->claims()->all();
    }
}