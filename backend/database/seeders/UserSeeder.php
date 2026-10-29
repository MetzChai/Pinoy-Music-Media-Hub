<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = config('auth.admin.email', env('ADMIN_EMAIL', 'admin@mymovieapp.com'));
        $adminName = config('auth.admin.name', env('ADMIN_NAME', 'System Administrator'));
        $adminPassword = config('auth.admin.password', env('ADMIN_PASSWORD', 'admin12345'));

        if (empty($adminEmail)) {
            $this->command?->warn('No admin email configured. Skipping UserSeeder.');
            return;
        }

        $admin = User::where('email', $adminEmail)->first();

        if (! $admin) {
            $user = new User();
            $user->name = $adminName;
            $user->email = $adminEmail;
            $user->password = Hash::make($adminPassword);
            $user->role = 'admin';
            $user->save();

            $this->command?->info("Administrator account created for {$adminEmail}.");
        } else {
            $admin->role = 'admin';
            if (!empty($adminPassword)) {
                $admin->password = Hash::make($adminPassword);
            }
            $admin->save();
            $this->command?->info("Administrator account {$adminEmail} synchronized.");
        }
    }
}
