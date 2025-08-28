<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $orders = new Order();
            $orders = $orders->orderBy('created_at', 'desc')->get();
            return response([
                'result' => OrderResource::collection($orders)
            ]);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(OrderStoreRequest $request)
    {
        try {
            $request->request->add(['whatsappId' => Str::replaceFirst('0', '62', $request->phone). '@c.us']);
            $request->request->add(['code' => $request->code]);
            $request->request->add(['productId' => 'DM-251']);
            if ($order = Order::create($request->all())) {
                $prefix = "62";
                if (str_starts_with($request->phone, '0')) {
                    $newNumber = $prefix . ltrim($request->phone, '0');
                } else {
                    $newNumber = $request->phone;
                }
                $message = "Terimakasih ".$order->name." telah melakukan pesanan." . PHP_EOL;
                $message .= "Nomor Pesanan anda adalah: " . $order->code . PHP_EOL;
                $message .= "Produk: " . $order->productId . PHP_EOL;
                $message .= "Size: " . $order->size . PHP_EOL;
                $message .= "Lengan: " . $order->arm . PHP_EOL;
                $message .= "Harga: " . number_format($order->price) . PHP_EOL;
                if ($order->payment == '2') {
                    $message .= "Biaya Layanan: " . number_format(4250) . PHP_EOL;
                    $message .= "Kode pembayaran anda adalah : *BRIVA " . $order->payCode . "*" . PHP_EOL;
                    $message .= "Silahkan melakukan pembayaran sebelum " . Carbon::now()->addDays(2)->translatedFormat('d F Y H:i');
                }
                $response = new Client();
                $response->request('POST', 'https://waha-frsk9g0swtcm.axpq.sumopod.my.id/api/sendText', [
                    'body' => json_encode([
                        'chatId' => $newNumber.'@c.us',
                        "reply_to" =>  null,
                        "text" => $message,
                        "linkPreview" => true,
                        "linkPreviewHighQuality" => false,
                        "session" => "testing"
                    ]),
                    'headers' => [
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                        'X-Api-Key' => '4iWZqrhISiNn9KULUUy6Zv931gQMJg3T'
                    ],
                ]);
                return response([
                    'result' => new OrderResource($order),
                    'message' => 'Pesanan berhasil dibuat!'
                ]);
            } else {
                throw new Exception('Pesanan gagal dibuat!');
            }
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 422);
        } catch (GuzzleException $e) {
            return response([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show(Order $order)
    {
        try {
            return new OrderResource($order);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Order $order)
    {
        try {
            return $order->update(array_filter($request->all()))
                ? response([
                    'result' => new OrderResource($order),
                    'message' => 'Pesanan berhasil diubah!'
                ]) : throw new Exception('Pesanan gagal diubah!');
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(Order $order)
    {
        try {
            return ($order->delete()) ? response([
                'result' => new OrderResource($order),
                'message' => 'Pesanan berhasil dihapus!'
            ]) : throw new Exception('Pesanan gagal dihapus!');
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
