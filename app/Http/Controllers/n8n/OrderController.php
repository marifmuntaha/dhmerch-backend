<?php

namespace App\Http\Controllers\n8n;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Whatsapp;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try {
            $client = Whatsapp::whereWhatsappid($request->whatsappId)->first();
            $price = 0;
            if ($request->type == "Anak") {
                $price = 75000;
            } else {
                if ($request->arm == "Pendek") {
                    if (in_array($request->size, ['S', 'M', 'L'])) {
                        $price = 80000;
                    } else {
                        $price = 85000;
                    }
                } else {
                    if (in_array($request->size, ['S', 'M', 'L'])) {
                        $price = 85000;
                    } else {
                        $price = 95000;
                    }
                }
            }
            $request->request->add(['code' => Carbon::now()->format('YmdHis')]);
            $request->request->add(['price' => $price]);
            if ($order = Order::create($request->all())) {
                $client->session = '2';
                $client->save();
                $message = 'Konfirmasi pesanan anda:' . PHP_EOL . PHP_EOL;
                $message .= 'Nomor Pesanan: ' . $order->code . PHP_EOL;
                $message .= 'Nama: ' . $order->name . PHP_EOL;
                $message .= 'Alamat: ' . $order->address . PHP_EOL;
                $message .= 'Nomor WA: ' . $order->phone . PHP_EOL;
                $message .= 'Kode Produk: ' . $order->productId . PHP_EOL;
                $message .= 'Tipe: ' . $order->type . PHP_EOL;
                $message .= 'Size: ' . $order->size . PHP_EOL;
                $message .= 'Lengan: ' . $order->arm . PHP_EOL;
                $message .= 'Harga: ' . number_format($order->price) . PHP_EOL;
                $message .= 'Biaya Layanan: ' . number_format(4250) . PHP_EOL;
                $message .= 'Total: *' . number_format($order->price + 4250) ."*". PHP_EOL . PHP_EOL;
                $message .= '1. Kirim Pesanan'. PHP_EOL;
                $message .= '2. Batal';
                return response([
                    'message' => $message,
                ]);
            }
            else {
                return false;
            }
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function handle(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, config('tripay.privateKey'));

        if ($signature !== (string) $callbackSignature) {
            return response([
                'success' => false,
                'message' => 'Invalid signature',
            ]);
        }

        if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
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
        $status = strtoupper((string) $data->status);

        if ($data->is_closed_payment === 1) {
            $invoice = Order::whereReference($tripayReference)
                ->where('status', '=', '1')
                ->first();

            if (! $invoice) {
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
        }
        else {
            return response([
                'success' => false,
                'message' => 'Invalid data sent by tripay',
            ]);
        }
    }
}
