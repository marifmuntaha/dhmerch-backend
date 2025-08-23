<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Carbon\Carbon;
use Exception;
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
            $request->request->add(['code' => Carbon::now()->format('YmdHis')]);
            $request->request->add(['productId' => 'DM-251']);
            return ($order = Order::create($request->all()))
                ? response([
                    'result' => new OrderResource($order),
                    'message' => 'Pesanan berhasil dibuat!'
                ]) : throw new Exception('Pesanan gagal dibuat!');
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 422);
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
