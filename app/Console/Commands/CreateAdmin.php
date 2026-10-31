<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Rules\HashablePassword;
use App\Support\IranianMobile;
use App\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin {phone : Iranian mobile number} {--name= : Administrator display name}';

    protected $description = 'Create a new administrator with a hidden password prompt; never promotes existing accounts';

    public function handle(): int
    {
        $phone = IranianMobile::normalize($this->argument('phone'));
        $name = $this->option('name') ?: $this->ask('Administrator name');
        $validator = Validator::make(['phone' => $phone, 'name' => $name], [
            'phone' => [...IranianMobile::rules(), 'unique:users,phone'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $password = $this->secret('Password (12+ characters, upper/lowercase and a number)');
        $confirmation = $this->secret('Confirm password');
        $validator = Validator::make(['password' => $password, 'password_confirmation' => $confirmation], [
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers(), 'max:72', new HashablePassword, 'confirmed'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $user = new User(['name' => $name, 'phone' => $phone, 'password' => $password]);
        $user->role = UserRole::Admin;
        $user->save();
        $this->info('Administrator created. Sign in with the phone number and password.');

        return self::SUCCESS;
    }
}
