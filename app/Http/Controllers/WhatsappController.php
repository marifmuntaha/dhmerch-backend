<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Whatsapp;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
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
                    $message .= "*Kode Produk:* DM-211/DM-212/DM-213" . PHP_EOL;
                    $message .= "*Size:* XS/S/M/L/XL/XXL/3XL/4XL" . PHP_EOL;
                    $message .= "*Lengan:* Panjang/Pendek";
                    return response([
                        'message' => $message,
                    ]);
                } else if ($request->message == '2') {
                    $product = Product::get();
                    $image = $product->map(function ($item) {
                        return asset(Storage::url($item->image));
                    });
                    return response([
                        'message' => $this->messageMain($client),
                        'image' => $image,
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
                    $body = [
                        "payment_type" => "bank_transfer",
                        "bank_transfer" => ["bank" => "bri"],
                        "transaction_details" => [
                            "order_id" => $this->generateUniqueRandomString(),
                            "gross_amount" => $order->price
                        ],
                        "item_details" => [
                            [
                                "id" => $order->productId,
                                'price' => $order->price,
                                'quantity' => '1',
                                'name' => $order->productId,
                            ]
                        ],
                        "customer_details" => [
                            "first_name" => $order->name,
                            "last_name" => "",
                            "email" => "merch@darul-hikmah.sch.id",
                            "phone" => $order->phone,
                        ],
                        "custom_expiry" => [
                            "expiry_duration" => 2,
                            "unit" => "day"
                        ],
                    ];
                    try {
                        $client = new Client();
                        $response = $client->request('POST', config('midtrans.api_url').'charge', [
                            'body' => json_encode($body),
                            'headers' => [
                                'accept' => 'application/json',
                                'authorization' => 'Basic '. base64_encode(config('midtrans.server_key').":"),
                                'content-type' => 'application/json',
                            ],
                        ])->getBody()->getContents();
                        $response = json_decode($response);
                        if($response->status_code == 201) {
                            $order->reference = $response->transaction_id;
                            $order->payCode =$response->va_numbers[0]->va_number;
                            $order->save();
                            $message = "Terimakasih telah melakukan pesanan." . PHP_EOL;
                            $message .= "Nomor Pesanan anda adalah: " . $order->code . PHP_EOL;
                            $message .= "Kode pembayaran anda adalah : *BRIVA " . $order->payCode . "*" . PHP_EOL;
                            $message .= "Silahkan melakukan pembayaran sebelum " . Carbon::now()->addDays(2)->translatedFormat('d F Y H:i').PHP_EOL;
                            $message .= "00. Kembali".PHP_EOL;
                            return response([
                                'message' => $message,
                            ]);
                        }
                        else {
                            throw new  Exception("Transaksi gagal, silahkan coba lagi.");
                        }
                    } catch (GuzzleException $e) {
                        $message = $e->getMessage();
                    }
                    catch (Exception $e) {
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

    private function generateUniqueRandomString()
    {
        do {
            $randomNumber = mt_rand(10000000, 99999999);
        } while (Order::whereCode($randomNumber)->exists());

        return $randomNumber;
    }
}
