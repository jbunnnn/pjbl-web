<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('users:make-admin {email}')]
#[Description('Promote an existing user account to administrator')]
class MakeUserAdmin extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('No user account exists with that email address.');

            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->info("{$user->email} is already an administrator.");

            return self::SUCCESS;
        }

        $user->forceFill(['role' => 'admin'])->save();

        $this->info("{$user->email} is now an administrator.");

        return self::SUCCESS;
    }
}
