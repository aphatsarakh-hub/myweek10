<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return $this->redirectUser(Auth::user());
        }

        return back()->withErrors([
            'username' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง',
        ])->withInput($request->only('username'));
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:account,username|max:50',
            'password' => 'required|string|min:4',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        // Check if customer email exists
        if (Customer::where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'อีเมลนี้ถูกใช้งานแล้ว'])->withInput();
        }

        // Create Customer
        $customer = Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address ?? '',
        ]);

        // Create Account
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'customer_id' => $customer->customer_id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.booking')->with('message', 'สมัครสมาชิกสำเร็จ');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('message', 'ออกจากระบบเรียบร้อย');
    }

    protected function redirectUser($user)
    {
        switch ($user->role) {
            case 'admin':
                return redirect()->intended('/admin/dashboard');
            case 'cashier':
                return redirect()->intended('/cashier/bookings');
            case 'customer':
                return redirect()->intended('/customer/booking');
            default:
                return redirect()->route('home');
        }
    }
}
