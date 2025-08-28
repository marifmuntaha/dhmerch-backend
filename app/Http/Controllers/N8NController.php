<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Whatsapp;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class N8NController extends Controller
{
    public function store(Request $request)
    {
        try {
            $client = Whatsapp::whereWhatsappid($request->whatsappId)->first();
            $price = 0;
            switch ($request->size) {
                case 'S':
                case 'M':
                case 'L':
                case 'XS':
                    $price = 80000;
                    break;
                case "2XL":
                    $price = 85000;
                    break;
                case "3XL":
                    $price = 90000;
                    break;
                case "4XL":
                    $price = 95000;
                    break;
            }
            switch ($request->arm) {
                case "Panjang":
                    $price = $price + 15000;
                    break;
                default:
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
                $message .= 'Size: ' . $order->size . PHP_EOL;
                $message .= 'Lengan: ' . $order->arm . PHP_EOL;
                $message .= 'Harga: ' . number_format($order->price) . PHP_EOL;
                $message .= 'Biaya Layanan: ' . number_format(4250) . PHP_EOL;
                $message .= 'Total: *' . number_format($order->price + 4250) . "*" . PHP_EOL . PHP_EOL;
                $message .= '1. Kirim Pesanan' . PHP_EOL;
                $message .= '2. Batal';
                return response([
                    'message' => $message,
                ]);
            } else {
                return false;
            }
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ]);
        }
    }
}
