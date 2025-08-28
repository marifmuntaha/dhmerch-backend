<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
                "order_id" => $this->generateUniqueRandomString(),
                "gross_amount" => $request->amount
            ],
            "item_details" => [
                [
                    "id" => $request->productId,
                    'price' => $request->amount,
                    'quantity' => '1',
                    'name' => $request->productId,
                ]
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

    public function midtransCallback(Request $request)
    {
        try {
            if ($order = Order::whereCode($request->order_id)->first()) {
                if ($request->transaction_status === 'settlement') {
                    $order->status = 2;
                    $order->save();
                    return response([
                        'statusCode' => '200',
                        'statusMessage' => 'Success'
                    ]);
                }
                else {
                    return response([
                        'responseCode' => '200',
                        'responseMessage' => 'Kode Status Salah'
                    ]);
                }
            } else {
                throw new Exception('Transaksi tidak ditemukan.', 412);
            }
        } catch (Exception $e) {
            return response([
                'responseCode' => $e->getCode(),
                'responseMessage' => $e->getMessage()
            ], $e->getCode());
        }
    }

    private function generateUniqueRandomString()
    {
        do {
            $randomNumber = mt_rand(10000000, 99999999);
        } while (Order::whereCode($randomNumber)->exists());

        return $randomNumber;
    }

    public function tripayCallback(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, config('tripay.privateKey'));

        if ($signature !== (string)$callbackSignature) {
            return response([
                'success' => false,
                'message' => 'Invalid signature',
            ]);
        }

        if ('payment_status' !== (string)$request->server('HTTP_X_CALLBACK_EVENT')) {
            return response([
                'success' => false,
                'message' => 'Unrecognized callback event, no action was taken',
            ]);
        }

        $data = json_decode($json);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return response([
                'success' => false,
                'message' => 'Invalid data sent by tripay',
            ]);
        }

        $invoiceId = $data->merchant_ref;
        $tripayReference = $data->reference;
        $status = strtoupper((string)$data->status);

        if ($data->is_closed_payment === 1) {
            $invoice = Order::whereReference($tripayReference)
                ->where('status', '=', '1')
                ->first();

            if (!$invoice) {
                return response([
                    'success' => false,
                    'message' => 'No invoice found or already paid: ' . $invoiceId,
                ]);
            }

            switch ($status) {
                case 'PAID':
                    $invoice->update(['status' => '2']);
                    break;

                case 'EXPIRED':
                    $invoice->update(['status' => '4']);
                    break;

                case 'FAILED':
                    $invoice->update(['status' => '5']);
                    break;

                default:
                    return response([
                        'success' => false,
                        'message' => 'Unrecognized payment status',
                    ]);
            }
            try {
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('n8n.webhook_token'),
                ])->post(config('n8n.webhook_url_testing'), [
                    'whatsappId' => $invoice->whatsappId,
                    'code' => $invoice->code,
                    'name' => $invoice->name,
                    'price' => number_format($invoice->price),
                ]);
            } catch (ConnectionException $e) {
                echo $e->getMessage();
            }
            return response(['success' => true]);
        } else {
            return response([
                'success' => false,
                'message' => 'Invalid data sent by tripay',
            ]);
        }
    }
}
