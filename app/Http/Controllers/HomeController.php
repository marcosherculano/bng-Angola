<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.painel');
        }

        if ($user->role === 'client') {
            return redirect()->route('client.painel');
        }

        if (in_array($user->role, ['pharmacy_normal', 'pharmacy_matrix', 'pharmacy_branch'], true)) {
            return redirect()->route('pharmacy.painel');
        }

        return redirect('/');
    }
}
