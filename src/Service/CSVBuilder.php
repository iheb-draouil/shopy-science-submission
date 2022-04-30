<?php

namespace App\Service;

use League\Csv\Writer;

use App\Definition\ServiceResponse\AppFailureResponse;
use App\Definition\ServiceResponse\AppSuccessResponse;

class CSVBuilder
{
    const csv_header = [
        'order',
        'delivery_name',
        'delivery_address',
        'delivery_country',
        'delivery_zipcode',
        'delivery_city',
        'items_count',
        'item_index',
        'item_id',
        'item_quantity',
        'line_price_excl_vat',
        'line_price_incl_vat',
    ];

    public function toCSV(array $orders, array $contacts)
    {
        $contacts_map = array_reduce($contacts, function($a, $e) {
            return !isset($a[$e->ID]) ? $a + [$e->ID => $e] : $a;
        },[]);

        $csv = Writer::createFromString();

        $csv->insertOne(CSVBuilder::csv_header);
        
        foreach ($orders as $order) {

            if (!property_exists($order, 'DeliverTo')
                || !array_key_exists($order->DeliverTo, $contacts_map)) {
                return new AppFailureResponse();
            }

            $contact = $contacts_map[$order->DeliverTo];

            foreach ($order->SalesOrderLines->results as $order_line_index => $order_line) {

                $csv->insertOne([
                    $order->OrderNumber,
                    $contact->AccountName,
                    $contact->AddressLine1,
                    $contact->Country,
                    $contact->ZipCode,
                    $contact->City,
                    count($order->SalesOrderLines->results),
                    $order_line_index + 1,
                    $order_line->Item,
                    $order_line->Quantity,
                    $order_line->Amount - $order_line->VATAmount,
                    $order_line->Amount,
                ]);

            }

        }
        
        return new AppSuccessResponse($csv);
    }
}