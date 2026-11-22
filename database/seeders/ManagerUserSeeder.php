<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class ManagerUserSeeder extends Seeder
{
    /**
     * Creates the "Admin quản lý" account from MANAGER_EMAIL / MANAGER_PASSWORD in .env.
     */
    public function run(): void
    {
        $email = config('site.manager.email');
        $password = config('site.manager.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            $this->command?->warn('Bỏ qua ManagerUserSeeder: cần MANAGER_EMAIL và MANAGER_PASSWORD trong .env.');

            return;
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $user->name ?: 'Admin quản lý';
        $user->role = UserRole::Manager;
        $user->password = $password;
        $user->save();
    }
}
