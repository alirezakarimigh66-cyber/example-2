<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function store(LoginRequest $request): JsonResponse|RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        $request->session()->forget('url.intended');
        $destination = route($request->user()->role->dashboardRoute());

        return $request->expectsJson()
            ? response()->json(['redirect' => $destination, 'message' => 'با موفقیت وارد شدید.'])
            : redirect()->to($destination);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'با موفقیت خارج شدید.');
    }
}
