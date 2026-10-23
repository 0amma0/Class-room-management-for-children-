<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('app:admin-create')]
#[Description('Create an administrator account using hidden interactive password prompts')]
class CreateAdminAccount extends Command
{
    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('This command requires an interactive terminal to protect the password.');

            return self::FAILURE;
        }

        $identity = [
            'name' => trim((string) $this->ask('Nama Admin')),
            'username' => trim((string) $this->ask('Username')),
            'email' => mb_strtolower(trim((string) $this->ask('Email'))),
        ];
        $identityValidator = Validator::make($identity, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);
        if ($identityValidator->fails()) {
            foreach ($identityValidator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $password = (string) $this->secret('Kata sandi (minimal 12 karakter)');
        $confirmation = (string) $this->secret('Ulangi kata sandi');
        $passwordValidator = Validator::make([
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        if ($passwordValidator->fails()) {
            foreach ($passwordValidator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $admin = User::query()->create([
            ...$identity,
            'password' => Hash::make($password),
            'role' => 'admin',
            'status' => 'active',
            'must_change_password' => true,
        ]);

        $this->info('Admin berhasil dibuat untuk '.$admin->email.'. Kata sandi tidak ditampilkan.');

        return self::SUCCESS;
    }
}
