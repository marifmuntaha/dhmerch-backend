<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
class PaymentController extends Controller
{
    /**
     * @throws Exception
     */
    public function midtrans(Request $request)
    {
        $client = new Client();
        $body = [
            "payment_type" => "bank_transfer",
            "transaction_details" => [
                "order_id" => "order-id-124",
                "gross_amount" => 100000
            ],
            "customer_details" => [
                "first_name" => "Budi",
                "last_name" => "Utomo",
                "email" => "budi.utomo@midtrans.com",
                "phone" => "081223323423",
            ],
//            "item_details" => '[{"id": "1388998298204", "price": 5000, "quantity": 1, "name": "Ayam Zozozo"}]',
            "custom_expiry" => [
                "expiry_duration" => 2,
                "unit" => "day"
            ],
            "bank_transfer" => ["bank" => "bri"]
        ];
        try {
            $response = $client->request('POST', config('midtrans.api_url').'charge', [
                'body' => json_encode($body),
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Basic '. base64_encode(config('midtrans.server_key').":"),
                    'content-type' => 'application/json',
                ],
            ])->getBody()->getContents();
            $response = json_decode($response);
            return $response->status_code == 201 ? response([
                'result' => $response->status_message,
                'message' => 'Generate pembayaran berhasil.'
            ]) : throw new Exception($response->status_message);
        } catch (GuzzleException $e) {
            return response([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
