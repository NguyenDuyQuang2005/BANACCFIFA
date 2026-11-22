<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('site.admin.email');
        $password = config('site.admin.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            $this->command?->warn('Bỏ qua AdminUserSeeder: cần ADMIN_EMAIL và ADMIN_PASSWORD trong .env.');

            return;
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $user->name ?: 'Quản trị viên';
        $user->role = UserRole::Admin;
        $user->password = $password;
        $user->save();
    }
}
