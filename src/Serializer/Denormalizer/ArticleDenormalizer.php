<?php

namespace App\Serializer\Denormalizer;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ArticleDenormalizer implements DenormalizerInterface
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        return $this->doctrine->getRepository(Article::class)
        ->find($data);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        return $type == Article::class && is_int($data);
    }
}
