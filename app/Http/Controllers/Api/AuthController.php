<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponser;

class AuthController extends Controller
{
    use ApiResponser;

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Tài khoản hoặc mật khẩu không chính xác.', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Đăng nhập thành công');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'usertype' => 'user',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Đăng ký thành công', 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Đăng xuất thành công');
    }

    public function me(Request $request)
    {
        return $this->success($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email', 'phone'));

        if ($request->has('password') && !empty($request->password)) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return $this->success($user, 'Cập nhật thông tin thành công');
    }
}
