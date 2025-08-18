<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = Users::where('username', $request->username)->first();

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {

            $userAgent = $request->header('User-Agent');

            $deviceToken = $user->deviceToken()->where('department_code', 'DP01')->first();
            $deviceCookie = $deviceToken ?  $request->cookie('device_id') : Str::random(40);
            $currentDeviceToken = hash('sha256', $userAgent . $deviceCookie);

            if ($deviceToken) {
                // Block login if device token doesn't match
                if ($deviceToken->device_token !== $currentDeviceToken && $user->role !='RO01') {
                    return back()->withErrors([
                        'email' => 'This account is locked to another device. Please reset the device to log in.',
                    ])->onlyInput('email');
                }

                // Update last login time
                $deviceToken->update(['last_login_at' => now()]);
            } else {
                // First login: Register the device
                DeviceToken::create([
                    'user_id' => $user->id,
                    'device_id' => $deviceCookie,
                    'department_code'=>'DP01',
                    'device_token' => $currentDeviceToken,
                    'user_agent' => $userAgent,
                    'last_login_at' => now(),
                ]);

                // Ensure the cookie is set

            }
            Cookie::queue('device_id', $deviceCookie, 60 * 24 * 365 * 5, '/', null, true, true);

            $request->session()->regenerate();
            Auth::login($user);
            if(has_access('sales_invoice')){
                return redirect()->intended('/TDS/transaction/sales-invoice');
            }else if(has_access('bank_in')){
                return redirect()->intended('/TDS/transaction/bank-cash-in');
            }else if(has_access('bank_out')){
                return redirect()->intended('/TDS/transaction/bank-cash-out');
            }else if(has_access('general_journal')){
                return redirect()->intended('/TDS/transaction/general-journal');
            }else if(has_access('receivable_payment')){
                return redirect()->intended('/TDS/transaction/receivable-payment');
            }else if(has_access('closing')){
                return redirect()->intended('/TDS/transaction/closing/show');
            }else if(has_access('master_item')){
                return redirect()->intended('/TDS/master/item/');
            }else if(has_access('master_item_sales')){
                return redirect()->intended('/TDS/master/item-sales-price');
            }else if(has_access('master_item_purchase')){
                return redirect()->intended('/TDS/master/item-purchase-price');
            }else if(has_access('master_coa')){
                return redirect()->intended('/TDS/master/coa');
            }else if(has_access('master_user')){
                return redirect()->intended('/TDS/master/users');
            }else if(has_access('master_customer')){
                return redirect()->intended('/TDS/master/customer');
            }
            return back()->withErrors([
            'username' => 'Department not allowed.',
            ]);
        }

        return back()->withErrors([
            'username' => 'Invalid credentials.',
        ]);
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/TDS/auth/login');
    }

    public function forgetPassword(Request $request){
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request)
    {

        return view('auth.reset-password')->with([
            'token' => $request->query('token'),
            'email' => $request->query('email'),

        ]);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password'=>'confirmed'
        ]);

        Log::info('Attempting password reset for email: ' . $request->email . ' with token: ' . $request->token);

        $resetStatus = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password); // Correct password update
                $user->save(); // Save the updated password
            }
        );

        if ($resetStatus === Password::PASSWORD_RESET) {
            return redirect('/TDS/auth/login')->with('status', __($resetStatus));
        }

        return back()->withErrors(['email' => __($resetStatus)]);

    }

    public function resetDevice(Request $request)
    {
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_user'];
        if(in_array('delete', $privileges)){
            $user = Users::where('username',$request->username )->first();
            $user->deviceToken()->delete();
        }
        return redirect()->back()->with('success', 'Device token reset successfully. The user can now log in from a new device.');
    }
}
