<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-admin
                            {email : Email address of the user to promote}
                            {--create : Create the user if it does not exist}
                            {--name= : Name to use when creating the user}
                            {--password= : Password to use when creating the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant the admin role to a user (creates the user with --create)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));

        $user = User::where('email', $email)->first();

        if (!$user) {
            if (!$this->option('create')) {
                $this->error("User [{$email}] not found. Re-run with --create to create them.");

                return self::FAILURE;
            }

            $name = $this->option('name') ?: strstr($email, '@', true);
            $password = $this->option('password');

            if (!$password) {
                $password = \Illuminate\Support\Str::random(16);
                $this->warn("Generated password: {$password}");
            }

            $user = new User();
            $user->name = $name ?: $email;
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->save();

            $this->info("Created user [{$email}].");
        }

        // Idempotent: assigning the same role again is a no-op.
        if ($user->role === 'admin') {
            $this->info("[{$email}] is already an admin.");

            return self::SUCCESS;
        }

        // Assign the role directly - it is not mass assignable.
        $user->role = 'admin';
        $user->save();

        $this->info("[{$email}] is now an admin.");

        return self::SUCCESS;
    }
}
