<?php

use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

it('renders one public modal with both forms and header and main triggers', function () {
    $this->get(route('home'))->assertOk()
        ->assertSee('id="auth-modal"', false)
        ->assertSee('data-auth-form="login"', false)
        ->assertSee('data-auth-form="register"', false)
        ->assertSee('data-auth-open="login"', false)
        ->assertSee('data-auth-open="register"', false);
});

it('routes legacy authentication entry points to the shared modal', function (string $mode) {
    $this->get('/'.$mode)->assertRedirect(route('home', ['auth' => $mode]));
    $this->get('/?auth='.$mode)->assertOk()->assertSee('data-auto-open', false);
})->with(['login', 'register']);

it('registers a normalized phone account as a regular user despite injected privileges', function () {
    Event::fake([Registered::class]);

    $response = $this->postJson(route('register.store'), [
        'name' => 'مریم رضایی',
        'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
        'password' => 'StrongPassword123',
        'password_confirmation' => 'StrongPassword123',
        'role' => 'admin',
        'email_verified_at' => now(),
        'email' => 'injected@example.com',
        'redirect' => 'https://evil.example',
    ]);

    $response->assertCreated()->assertJsonPath('redirect', route('dashboard'));
    $user = User::sole();
    expect($user->role)->toBe(UserRole::User);
    expect($user->phone)->toBe('+989123456789');
    expect($user->email)->toBeNull();
    expect($user->email_verified_at)->toBeNull();
    expect(Hash::check('StrongPassword123', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
    Event::assertDispatched(Registered::class, fn (Registered $event) => $event->user->is($user));
});

it('rejects equivalent duplicate phone formats', function (string $phone) {
    User::factory()->create(['phone' => '09123456789']);

    $this->postJson(route('register.store'), [
        'name' => 'سارا', 'phone' => $phone,
        'password' => 'StrongPassword123', 'password_confirmation' => 'StrongPassword123',
    ])->assertUnprocessable()->assertJsonValidationErrors(['phone']);

    $this->assertDatabaseCount('users', 1);
    $this->assertGuest();
})->with(['09123456789', '+989123456789', '00989123456789', '۰۹۱۲۳۴۵۶۷۸۹']);

it('rejects missing registration fields', function () {
    $this->postJson(route('register.store'), [])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'phone', 'password'])
        ->assertJsonPath('errors.phone.0', 'شماره موبایل را وارد کنید.');

    $this->assertDatabaseCount('users', 0);
    $this->assertGuest();
});

it('rejects invalid registration values', function (array $change, string $field) {
    $this->postJson(route('register.store'), array_replace([
        'name' => 'سارا', 'phone' => '09123456789',
        'password' => 'StrongPassword123', 'password_confirmation' => 'StrongPassword123',
    ], $change))->assertUnprocessable()->assertJsonValidationErrors([$field]);

    $this->assertDatabaseCount('users', 0);
})->with([
    'invalid mobile' => [['phone' => '02112345678'], 'phone'],
    'nonstring mobile' => [['phone' => ['09123456789']], 'phone'],
    'invalid name' => [['name' => str_repeat('x', 256)], 'name'],
    'short password' => [['password' => 'Abc123', 'password_confirmation' => 'Abc123'], 'password'],
    'weak password' => [['password' => 'abcdefghijkl', 'password_confirmation' => 'abcdefghijkl'], 'password'],
    'long password' => [['password' => str_repeat('Abc123', 13), 'password_confirmation' => str_repeat('Abc123', 13)], 'password'],
    'multibyte password over bcrypt limit' => [['password' => 'Abc123'.str_repeat('پ', 34), 'password_confirmation' => 'Abc123'.str_repeat('پ', 34)], 'password'],
    'null byte password' => [['password' => "StrongPassword123\0", 'password_confirmation' => "StrongPassword123\0"], 'password'],
    'mismatched password' => [['password_confirmation' => 'Different123'], 'password'],
]);

it('returns standard form errors to the open registration modal without flashing passwords', function () {
    $this->from(route('home'))->post(route('register.store'), [
        'auth_mode' => 'register', 'phone' => 'invalid', 'password' => 'secret',
    ])->assertRedirect(route('home'));

    $this->get(route('home'))->assertOk()->assertSee('data-mode="register"', false)
        ->assertSee('data-auto-open', false)->assertSee('نام خود را وارد کنید.')->assertDontSee('value="secret"', false);
    expect(session()->getOldInput('password'))->toBeNull();
});

it('logs users in by normalized phone and redirects according to their stored role', function (string $role, string $destination) {
    $user = User::factory()->create(['phone' => '09123456789', 'role' => UserRole::from($role)]);
    $this->withSession(['url.intended' => 'https://evil.example']);
    $oldSessionId = session()->getId();

    $this->postJson(route('login.store'), [
        'phone' => '+98 912 345 6789', 'password' => 'password', 'role' => 'admin',
    ])->assertOk()->assertJsonPath('redirect', route($destination))->assertSessionMissing('url.intended');

    $this->assertAuthenticatedAs($user);
    expect(session()->getId())->not->toBe($oldSessionId);
})->with([
    'user' => ['user', 'dashboard'],
    'admin' => ['admin', 'admin.dashboard'],
]);

it('supports remembering a successful phone login', function () {
    $user = User::factory()->create();

    $response = $this->postJson(route('login.store'), ['phone' => $user->phone, 'password' => 'password', 'remember' => true]);

    $response->assertOk()->assertCookie(auth()->guard('web')->getRecallerName());
    $this->assertAuthenticatedAs($user);
});

it('does not accept email as a login credential', function () {
    $user = User::factory()->create();

    $this->postJson(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertUnprocessable()->assertJsonValidationErrors(['phone']);

    $this->assertGuest();
});

it('returns the same error for an unknown phone and an incorrect password', function (bool $known) {
    if ($known) {
        User::factory()->create(['phone' => '09121112222']);
    }

    $this->postJson(route('login.store'), ['phone' => '09121112222', 'password' => 'wrong'])
        ->assertUnprocessable()->assertJsonPath('errors.phone.0', 'شماره موبایل یا رمز عبور نادرست است.');

    $this->assertGuest();
})->with([true, false]);

it('limits repeated failed login attempts across equivalent phone formats', function () {
    $user = User::factory()->create(['phone' => '09123334444']);
    $key = 'login:'.hash('sha256', $user->phone.'|127.0.0.1');
    RateLimiter::clear($key);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->postJson(route('login.store'), ['phone' => '09123334444', 'password' => 'wrong'])->assertUnprocessable();
    }
    $this->postJson(route('login.store'), ['phone' => '+989123334444', 'password' => 'password'])
        ->assertUnprocessable()->assertJsonValidationErrors(['phone']);
    $this->assertGuest();

    $this->travel(61)->seconds();
    $this->postJson(route('login.store'), ['phone' => $user->phone, 'password' => 'password'])->assertOk();
    $this->assertAuthenticatedAs($user);
});

it('redirects guests from protected dashboards to the modal', function (string $route) {
    $this->get(route($route))->assertRedirect(route('home', ['auth' => 'login']));
    $this->getJson(route($route))->assertUnauthorized();
})->with(['dashboard', 'admin.dashboard']);

it('enforces both sides of the dashboard role boundary', function (string $role, string $allowed, string $forbidden) {
    $user = User::factory()->create(['role' => UserRole::from($role)]);

    $this->actingAs($user)->get(route($allowed))->assertOk()->assertSee($user->name);
    $this->get(route($forbidden))->assertForbidden();
})->with([
    ['user', 'dashboard', 'admin.dashboard'],
    ['admin', 'admin.dashboard', 'dashboard'],
]);

it('escapes account names on dashboards', function () {
    $user = User::factory()->create(['name' => '<script>alert(1)</script>']);

    $this->actingAs($user)->get(route('dashboard'))->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});

it('guards the role against mass assignment', function () {
    $user = new User(['name' => 'سارا', 'phone' => '09129998888', 'password' => 'password', 'role' => 'admin']);
    $user->save();

    expect($user->refresh()->role)->toBe(UserRole::User);
});

it('logs out and invalidates the session and csrf token', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->withSession(['private' => 'value']);
    $token = session()->token();
    $id = session()->getId();

    $this->post(route('logout'))->assertRedirect(route('home'))->assertSessionMissing('private');

    $this->assertGuest();
    expect(session()->getId())->not->toBe($id);
    expect(session()->token())->not->toBe($token);
    $this->get(route('dashboard'))->assertRedirect(route('home', ['auth' => 'login']));
});

it('redirects standard successful submissions to the user dashboard', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['phone' => $user->phone, 'password' => 'password'])->assertRedirect(route('dashboard'));
});

it('does not render authentication forms for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('home'))->assertOk()->assertDontSee('id="auth-modal"', false);
    $this->get(route('login'))->assertRedirect(route('dashboard'));
});
