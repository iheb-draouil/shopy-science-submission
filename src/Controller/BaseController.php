<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController extends AbstractController
{
    protected $serializer;
    protected $validator;
    protected $manager_registry;

    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ManagerRegistry $manager_registry,
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->manager_registry = $manager_registry;
    }

    protected function toValidationErrorMap(ConstraintViolationListInterface $constraint_violation_list): array
    {
        $errors = [];

        foreach ($constraint_violation_list as $constraint_violation) {

            $path = $constraint_violation->getPropertyPath();

            if (!array_key_exists($path, $errors)) {
                $errors[$path] = [];
            }

            $errors[$path][] = $constraint_violation->getMessage();
        }

        return $errors;
    }
}