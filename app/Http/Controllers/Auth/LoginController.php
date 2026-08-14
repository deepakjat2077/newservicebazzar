<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Libs\Booster;
use App\Models\Setting;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = 'user/dashboard';
    protected $setting;
    protected $theme;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->setting = Setting::first();
        $this->theme = 'themes.'.$this->setting->theme.'.';
    }

    public function showLoginForm()
    {
        $setting = $this->setting;
        $data['login'] = Theme::where('name','login')->where('is_multiple',0)->first();
        return view($this->theme.'user.auth.login', $data);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $request->login;
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $loginInput, 'password' => $request->password], $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $booster = new Booster();
            $booster->createLoginLog($user);

            \App\Support\Tracking\Tracker::conversion($request, 'login', ['user_id' => $user->id]);
            \App\Support\Tracking\Tracker::linkUser($request, $user->id);

            return redirect()->intended($this->redirectTo);
        }

        return back()->withErrors([
            'login' => 'Invalid credentials.',
        ])->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/user/login');
    }
}