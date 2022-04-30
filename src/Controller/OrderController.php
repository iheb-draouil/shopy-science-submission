<?php

namespace App\Controller;

use App\Definition\ErrorMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;

use App\Definition\ServiceResponse\AppFailureResponse;
use App\Definition\ServiceResponse\AppSuccessResponse;
use App\Definition\ServiceResponse\Base\AppResponse;
use App\Entity\AppOrder;
use App\Entity\AppUser;
use App\Entity\Article;
use App\Repository\AppOrderRepository;
use App\Service\CSVBuilder;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderController extends BaseController
{
    const contacts_api_path = 'contacts';
    const csv_content_type = 'text/csv';
    const orders_api_path = 'orders';

    private $http_client;
    private $csv_builder;
    private $logger;

    public function __construct(
        HttpClientInterface $http_client,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ManagerRegistry $doctrine,
        LoggerInterface $logger,
        CSVBuilder $csv_builder,
    ) {
        parent::__construct($serializer, $validator, $doctrine);
        $this->http_client = $http_client;
        $this->csv_builder = $csv_builder;
        $this->logger = $logger;
    }

    private function getDataFromEcommerceAPI(string $path): AppResponse
    {
        $response = $this->http_client->request('GET', $this->getParameter('app.e-comerce-api-url') . "/$path", [
            'headers' => ['x-api-key' => $this->getParameter('app.e-comerce-api-key')]
        ]);

        if ($response->getStatusCode() !== 200) {
            return new AppFailureResponse();
        }

        $data = json_decode($response->getContent());

        return new AppSuccessResponse($data->results);
    }

    #[Route('flow/retreive-untreated-orders', methods: 'GET', name: 'untreated-orders-ui')]
    public function getOrdersTobeTreatedUI()
    {
        return $this->render('orders-to-csv.html.twig');
    }

    #[Route('flow/orders_to_csv', methods: 'GET', name: 'untreated-orders-csv')]
    public function getOrdersTobeTreatedAsCSV()
    {
        // todo: person x downloaded this file at xxx
        $orders_response = $this->getDataFromEcommerceAPI(OrderController::orders_api_path);
        $contacts_response = $this->getDataFromEcommerceAPI(OrderController::contacts_api_path);

        if ($orders_response instanceof AppFailureResponse
            || $contacts_response instanceof AppFailureResponse) {
            
            return new Response(null, 500, [
                'Content-Type' => OrderController::csv_content_type
            ]);

        }
        
        $builder_response = $this->csv_builder->toCSV($orders_response->data, $contacts_response->data);

        if ($builder_response instanceof AppFailureResponse) {
            
            return new Response(null, 500, [
                'Content-Type' => OrderController::csv_content_type
            ]);

        }

        $csv = $builder_response->data;

        return new Response($csv, Response::HTTP_OK, [
            'Content-Type' => OrderController::csv_content_type,
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT, 'orders-to-be-treated.csv'
            )
        ]);
    }

    #[Route('order', methods: 'GET', name: 'register-order-ui')]
    public function registerOrderUI(Request $request)
    {
        return $this->render('register-order.html.twig', [
            'articles' => $this->doctrine->getRepository(Article::class)
            ->findAll()
        ]);
    }

    #[Route('api/order', methods: 'POST', name: 'register-order-api')]
    public function registerOrder(Request $request)
    {
        try {
            $order = $this->serializer
            ->deserialize($request->getContent(), AppOrder::class, 'json');
        }

        catch (Exception) {
            
            return new JsonResponse([
                'error' => ErrorMessage::DESERIALIZATION_FAILURE,
            ]);

        }

        assert($order instanceof AppOrder);
        
        $validation = $this->validator->validate($order);

        if (count($validation) != 0) {

            return new JsonResponse([
                'error' => ErrorMessage::VALIDATION_FAILURE,
                'fields' => $this->toValidationErrorMap($validation)
            ], Response::HTTP_BAD_REQUEST);

        }

        $app_order_repository = $this->doctrine->getRepository(AppOrder::class);

        assert($app_order_repository instanceof AppOrderRepository);

        $other_orders = $app_order_repository
        ->findHavingCodeOrNumber($order->order_number, $order->code);

        if (count($other_orders) != 0) {

            return new JsonResponse([
                'error' => ErrorMessage::DUPLICATE_RECORD,
            ], Response::HTTP_CONFLICT);

        }

        $entity_manager = $this->doctrine->getManager();
        $entity_manager->persist($order);
        $entity_manager->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    #[Route('orders', methods: 'GET', name: 'display-orders')]
    public function displayOrders()
    {
        return $this->render('treated-orders.html.twig', [
            'order' => $this->doctrine->getRepository(AppOrder::class)
            ->findAll()
        ]);
    }
}