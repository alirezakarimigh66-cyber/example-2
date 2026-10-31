<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function store(RegisterRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $user = new User($request->safe()->only(['name', 'phone', 'password']));
            $user->role = UserRole::User;
            $user->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['phone' => $request->messages()['phone.unique']]);
        }

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');
        $destination = route($user->role->dashboardRoute());

        return $request->expectsJson()
            ? response()->json(['redirect' => $destination, 'message' => 'حساب شما ساخته شد.'], 201)
            : redirect()->to($destination);
    }
}
