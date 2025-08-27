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
    public function midtransCreate(Request $request)
    {
        $client = new Client();
        $body = [
            "payment_type" => "bank_transfer",
            "bank_transfer" => ["bank" => "bri"],
            "transaction_details" => [
                "order_id" => $request->code,
                "gross_amount" => $request->amount
            ],
            "customer_details" => [
                "first_name" => $request->name,
                "last_name" => "",
                "email" => "merch@darul-hikmah.sch.id",
                "phone" => $request->phone,
            ],
            "custom_expiry" => [
                "expiry_duration" => 2,
                "unit" => "day"
            ],
        ];
        try {
            $response = $client->request('POST', config('midtrans.api_url').'charge', [
                'body' => json_encode($body),
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Basic '. base64_encode(config('midtrans.server_key').":"),
                    'content-type' => 'application/json',
                ],
            ])->getBody();
            $response = json_decode($response);
            return $response->status_code == 201 ? response([
                'result' => $response,
                'message' => 'Generate pembayaran berhasil.'
            ]) : throw new Exception($response->status_message);
        } catch (GuzzleException $e) {
            return response([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function midtransCallback()
    {

    }
}
