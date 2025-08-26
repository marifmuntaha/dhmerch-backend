<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Storage;

//use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = new Product();
            $products = $products->orderBy('created_at', 'desc')->get();
            return response([
                'result' => ProductResource::collection($products),
            ]);
        } catch (Exception $e) {
            return response([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(ProductStoreRequest $request)
    {
        try {
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs(('images'), $imageName, 'public');
                $request->request->add(['image' => 'images/'.$imageName]);
            }
            return ($product = Product::create($request->all()))
                ? response([
                    'result' => new ProductResource($product),
                    'message' => 'Data Produk berhasil disimpan'
                ], 201) : throw new Exception('Data Produk gagal disimpan');
        } catch (Exception $e) {
            return response([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Product $product)
    {
        try {
            return response([
                'result' => new ProductResource($product),
            ]);
        } catch (Exception $e) {
            return response([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        try {
            return $product->update(array_filter($request->all()))
                ? response([
                    'result' => new ProductResource($product),
                    'message' => 'Data Produk berhasil diupdate'
                ]) : throw new Exception('Data Produk gagal diupdate');
        } catch (Exception $e) {
            return response([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Product $product)
    {
        try {
            return $product->delete()
                ? response([
                    'result' => new ProductResource($product),
                    'message' => 'Data Produk berhasil dihapus'
                ]) : throw new Exception('Data Produk gagal dihapus');
        } catch (Exception $e) {
            return response([
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
