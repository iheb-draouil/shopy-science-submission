<?php

namespace App\Controller;

use App\Definition\ErrorMessage;
use Exception;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\ArticleInstance;
use App\Entity\Article;
use App\Repository\ArticleRepository;

class ResourceController extends BaseController
{
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ManagerRegistry $doctrine,
    ) {
        parent::__construct($serializer, $validator, $doctrine);
    }

    #[Route('api/article', methods: 'POST', name: 'create-article')]
    public function createArticle(Request $request)
    {
        try {
            $article = $this->serializer->deserialize($request->getContent(), Article::class, 'json');
        }

        catch (Exception) {

            return new JsonResponse([
                'error' => ErrorMessage::DESERIALIZATION_FAILURE
            ], Response::HTTP_BAD_REQUEST);

        }

        $validation = $this->validator->validate($article);

        if (count($validation) != 0) {

            return new JsonResponse([
                'error' => ErrorMessage::VALIDATION_FAILURE,
                'fields' => $this->toValidationErrorMap($validation)
            ], Response::HTTP_BAD_REQUEST);

        }

        $article_repository = $this->doctrine->getRepository(Article::class);

        assert($article_repository instanceof ArticleRepository);

        $other_articles = $article_repository
        ->findHavingCodeOrName($article->name, $article->code);

        if (count($other_articles) != 0) {

            return new JsonResponse([
                'error' => ErrorMessage::DUPLICATE_RECORD,
            ], Response::HTTP_CONFLICT);

        }

        $entity_manager = $this->doctrine->getManager();
        $entity_manager->persist($article);
        $entity_manager->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    #[Route('api/article', methods: 'GET', name: 'view-articles')]
    public function viewArticles()
    {
        $articles = $this->doctrine->getRepository(Article::class)
        ->findAll();

        return new JsonResponse([
            'results' => $articles
        ]);
    }

    #[Route('api/article/{id}', methods: 'GET', name: 'view-article')]
    public function viewArticle(int $id)
    {
        $article = $this->doctrine->getRepository(Article::class)
        ->find($id);

        if (!$article) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($article);
    }

    #[Route('api/article-instance', methods: 'POST', name: 'create-article-instance')]
    public function createArticleInstance(Request $request)
    {
        try {
            $article_instance = $this->serializer->deserialize($request->getContent(), ArticleInstance::class, 'json');
        }

        catch (Exception) {

            return new JsonResponse([
                'error' => ErrorMessage::DESERIALIZATION_FAILURE
            ], Response::HTTP_BAD_REQUEST);

        }

        $validation = $this->validator->validate($article_instance);

        if (count($validation) != 0) {

            return new JsonResponse([
                'error' => ErrorMessage::VALIDATION_FAILURE,
                'fields' => $this->toValidationErrorMap($validation)
            ], Response::HTTP_BAD_REQUEST);

        }

        $other_article_instance = $this->doctrine->getRepository(ArticleInstance::class)
        ->findOneBy(['code' => $article_instance->code]);

        if ($other_article_instance) {

            return new JsonResponse([
                'error' => ErrorMessage::DUPLICATE_RECORD,
            ], Response::HTTP_CONFLICT);

        }

        $entity_manager = $this->doctrine->getManager();
        $entity_manager->persist($article_instance);
        $entity_manager->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    #[Route('api/article-instance', methods: 'GET', name: 'view-article-instances')]
    public function viewArticleInstances()
    {
        $article_instances = $this->doctrine->getRepository(ArticleInstance::class)
        ->findAll();

        return new JsonResponse([
            'results' => $article_instances
        ]);
    }

    #[Route('api/article-instance/{id}', methods: 'GET', name: 'view-article-instance')]
    public function viewArticleInstance(int $id)
    {
        $article_instance = $this->doctrine->getRepository(ArticleInstance::class)
        ->find($id);

        if (!$article_instance) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($article_instance);
    }
}