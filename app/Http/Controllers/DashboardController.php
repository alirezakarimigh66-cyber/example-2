<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('dashboard', [
            'user' => $request->user(),
            'userCount' => $request->user()->role === UserRole::Admin ? User::count() : null,
        ]);
    }
}
