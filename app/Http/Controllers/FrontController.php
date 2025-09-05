<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;

class FrontController extends Controller
{
    public function index()
    {
        // return view('errors.503');
        return view('auth.login');
    }
    public function register()
    {
        return view('auth.register');
    }
    public function homeAdmin(){
        return view('admin.home');
    }
    public function logout()
    {
        // Menghapus sesi dan logout
        Session::flush();
        Auth::logout();
        // Redirect ke halaman utama
        return redirect('/');
    }
}
