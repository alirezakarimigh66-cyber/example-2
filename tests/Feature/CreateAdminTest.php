<?php

use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\Hash;

it('creates an administrator with a normalized phone and a prompted hashed password', function () {
    $this->artisan('app:create-admin', ['phone' => '09123456789', '--name' => 'مدیر دوپامین'])
        ->expectsQuestion('Password (12+ characters, upper/lowercase and a number)', 'StrongPassword123')
        ->expectsQuestion('Confirm password', 'StrongPassword123')
        ->expectsOutput('Administrator created. Sign in with the phone number and password.')
        ->assertSuccessful();

    $user = User::sole();
    expect($user->role)->toBe(UserRole::Admin);
    expect($user->phone)->toBe('+989123456789');
    expect(Hash::check('StrongPassword123', $user->password))->toBeTrue();
});

it('refuses to promote or overwrite an existing account', function () {
    $user = User::factory()->create(['phone' => '09123456789']);

    $this->artisan('app:create-admin', ['phone' => '+989123456789', '--name' => 'مدیر'])->assertFailed();

    expect($user->refresh()->role)->toBe(UserRole::User);
    $this->assertDatabaseCount('users', 1);
});

it('rejects an invalid administrator phone', function () {
    $this->artisan('app:create-admin', ['phone' => 'invalid', '--name' => 'مدیر'])->assertFailed();

    $this->assertDatabaseCount('users', 0);
});

it('rejects weak or mismatched administrator passwords', function (string $password, string $confirmation) {
    $this->artisan('app:create-admin', ['phone' => '09123456789', '--name' => 'مدیر'])
        ->expectsQuestion('Password (12+ characters, upper/lowercase and a number)', $password)
        ->expectsQuestion('Confirm password', $confirmation)
        ->assertFailed();

    $this->assertDatabaseCount('users', 0);
})->with([
    ['weak', 'weak'],
    ['StrongPassword123', 'DifferentPassword123'],
]);
