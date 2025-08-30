<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginStoreRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginStoreRequest $request)
    {
        try {
            return (Auth::attempt($request->all())) ?
                response([
                    'message' => 'Berhasil masuk, anda akan dialihkan dalam 2 detik.',
                    'result' => Arr::collapse([$request->user()->toArray(), [
                        'token' => $request->user()->createToken($request->user()->email)->plainTextToken,
                    ]])
                ]) : throw new Exception('Nama pengguna/kata sandi salah.');
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 401);
        }
    }

//    public function forgetPassword(Request $request)
//    {
//        try {
//            return ($request->username == 'admin')
//                ? response([
//                    'message' => 'Reset password Success',
//                    'result' => [
//                        'token' => fake()->uuid(),
//                    ]
//                ]) : throw new Exception('Reset password Failed');
//        } catch (Exception $e) {
//            return response([
//                'message' => $e->getMessage(),
//            ], 422);
//        }
//    }

//    public function resetPassword(StorePasswordRequest $request)
//    {
//        try {
//            return ($request->email == 'marifmuntaha@gmail.com' && $request->token == 'a3ca7c56-abaa-3154-9550-27bb7bf2189b')
//                ? response([
//                    'message' => 'Reset password Success',
//                ]) : throw new Exception('Reset password Failed');
//        } catch (Exception $e) {
//            return response([
//                'message' => $e->getMessage(),
//            ], 422);
//        }
//    }

    public function logout(Request $request)
    {
        try {
            $user = User::find($request->user('sanctum')->id);
            return ($status = $user->tokens()->delete())
                ? response([
                    'result' => [],
                    'message' => 'Berhasil keluar.',
                ]) : throw new Exception(__($status));
        } catch (Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
