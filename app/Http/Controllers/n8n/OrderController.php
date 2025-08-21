<?php

namespace App\Http\Controllers\n8n;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Whatsapp;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

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
                $message .= 'Harga: ' . number_format($order->price) . PHP_EOL . PHP_EOL;
                $message .= 'Biaya Layanan: ' . number_format(4250) . PHP_EOL;
                $message .= 'Total: *' . number_format($order->price + 4250) ."*". PHP_EOL;
                $message .= '1. Kirim Pesanan'. PHP_EOL;
                $message .= '00. Batal';
                return response([
                    'message' => $message,
                ]);
            }
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ]);
        }
    }
}
