<?php

namespace App\Http\Controllers\n8n;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Whatsapp;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WhatsappController extends Controller
{
    public function index(Request $request)
    {
        if ($client = Whatsapp::whereWhatsappid($request->whatsappId)->first()) {
            if ($client->session == '0' && $client->name == null) {
                $client->name = $request->message;
                $client->session = '1';
                $client->save();
                return response([
                    'message' => $this->messageMain($client),
                ]);
            } else if ($client->session == '1') {
                if ($request->message == '1') {
                    $message = "Untuk pemesanan silahkan isi format berikut:" . PHP_EOL . PHP_EOL;
                    $message .= "#ORDER" . PHP_EOL;
                    $message .= "*Nama:* " . PHP_EOL;
                    $message .= "*Alamat:* " . PHP_EOL;
                    $message .= "*Nomor WA:* " . PHP_EOL;
                    $message .= "*Kode Produk:* DM-251" . PHP_EOL;
                    $message .= "*Tipe:* Anak/Dewasa" . PHP_EOL;
                    $message .= "*Size:* XS/S/M/L/XL/XXL/3XL/4XL" . PHP_EOL;
                    $message .= "*Lengan:* Panjang/Pendek";
                    return response([
                        'message' => $message,
                    ]);
                } else if ($request->message == '2') {
                    return response([
                        'message' => $this->messageMain($client),
                        'image' => asset(Storage::url('public/images/4.jpeg')),
                    ]);
                } else if ($request->message == '3') {
                    $order = Order::whereWhatsappid($request->whatsappId)->orderBy('created_at', 'desc')->get();
                    $message = "*Semua Transaksi Anda :*" . PHP_EOL;
                    foreach ($order as $item) {
                        $message .= "############################" . PHP_EOL;
                        $message .= "Nomor Pesanan: " . $item->code . PHP_EOL;
                        $message .= "Nama: " . $item->name . PHP_EOL;
                        $message .= "Diskripsi: " . $item->type . "/" . $item->size . "/" . $item->arm . PHP_EOL;
                        $message .= "Harga: " . $item->price . PHP_EOL;
                        $message .= "Kode Bayar: " . $item->payCode . PHP_EOL;
                        $message .= "Status: " . $this->status($item->status) . PHP_EOL . PHP_EOL;
                    }
                    return response([
                        'message' => $message,
                    ]);
                } else if ($request->message == '4') {
                    $client->session = '0';
                    $client->name = '';
                    $client->save();
                    return response([
                        'message' => 'Silahkan ketikkan Nama Lengkap anda :',
                    ]);
                } else {
                    return response([
                        'message' => $this->messageMain($client),
                    ]);
                }
            } else if ($client->session == '2') {
                if ($request->message == '1') {
                    $order = Order::whereWhatsappid($request->whatsappId)->latest()->first();
                    $data = [
                        'method' => 'BRIVA',
                        'merchant_ref' => $order->code,
                        'amount' => (int)$order->price,
                        'customer_name' => $order->name,
                        'customer_email' => 'merch@darul-hikmah.sch.id',
                        'customer_phone' => $order->phone,
                        'order_items' => [
                            [
                                'sku' => $order->productId,
                                'name' => 'KAOS HARLAH ' . $order->productId,
                                'price' => (int)$order->price,
                                'quantity' => 1,
                                'product_url' => '#',
                                'image_url' => '#',
                            ],
                        ],
                        'expired_time' => (time() + (48 * 60 * 60)), // 24 jam
                        'signature' => hash_hmac('sha256', config('tripay.merchantCode') . $order->code . (int)$order->price, config('tripay.privateKey')),
                    ];
                    try {
                        $response = Http::withHeaders([
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer ' . config('tripay.apiKey'),
                        ])
                            ->post('https://tripay.co.id/api-sandbox/transaction/create', $data);
                        $payment = $response->json('data');
                        $order = Order::whereWhatsappid($request->whatsappId)->latest()->first();
                        $order->reference = $payment['reference'];
                        $order->payCode =$payment['pay_code'];
                        $order->save();
                        $message = "Terimakasih telah melakukan pesanan." . PHP_EOL;
                        $message .= "Nomor Pesanan anda adalah: " . $order->code . PHP_EOL;
                        $message .= "Kode pembayaran anda adalah : *BRIVA " . $payment['pay_code'] . "*" . PHP_EOL;
                        $message .= "Silahkan melakukan pembayaran sebelum " . Carbon::createFromTimestamp($payment['expired_time'])->translatedFormat('d F Y H:i').PHP_EOL;
                        $message .= "00. Kembali".PHP_EOL;
                    } catch (ConnectionException $e) {
                        $message = $e->getMessage();
                    }
                    return response([
                        'message' => $message,
                    ]);

                } else if ($request->message == '2') {
                    $client->session = '1';
                    $client->save();
                    $order = Order::whereWhatsappid($request->whatsappId)->latest()->first();
                    $order->delete();
                    return response([
                        'message' => $this->messageMain($client),
                    ]);
                } else if ($request->message == '00') {
                    $client->session = '1';
                    $client->save();
                    return response([
                        'message' => $this->messageMain($client),
                    ]);
                } else {
                    return response([
                        'message' => 'Mohon maaf, Kami tidak mengerti perintah anda',
                    ]);
                }
            } else {
                return response([
                    'message' => 'Silahkan ketikkan Nama Lengkap anda :',
                ]);
            }
        } else {
            Whatsapp::create([
                'whatsappId' => $request->whatsappId,
                'session' => '0',
                'name' => null,
            ]);
            return response([
                'message' => 'Selamat Datang di Whatsapp-bot DHMERCH. ' . PHP_EOL . 'Silahkan ketikkan Nama Lengkap anda :'
            ]);
        }
    }

    private function messageMain($client)
    {
        $message = 'Selamat Datang *' . $client->name . '*' . PHP_EOL;
        $message .= 'Silahkan Pilih menu dibawah ini:' . PHP_EOL;
        $message .= '1. Buat Pesanan' . PHP_EOL;
        $message .= '2. Lihat Produk' . PHP_EOL;
        $message .= '3. Lihat Transaksi' . PHP_EOL;
        $message .= '4. Keluar' . PHP_EOL;
        return $message;
    }

    private function status($status)
    {
        return match ($status) {
            '1' => 'Menunggu Pembayaran',
            '2' => 'Lunas',
            '3' => 'Sudah Diambil',
            default => 'Menunggu Konfirmasi',
        };
    }
}
