<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature   = 'app:make-admin {email} {--revoke : Quita el permiso en vez de otorgarlo}';
    protected $description = 'Marca (o desmarca con --revoke) a un usuario como administrador del sistema';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user  = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No existe ningún usuario con email {$email}.");
            return self::FAILURE;
        }

        $user->is_admin = ! $this->option('revoke');
        $user->save();

        $this->info($user->is_admin
            ? "✓ {$user->name} ({$user->email}) ahora es administrador."
            : "✓ {$user->name} ({$user->email}) ya no es administrador.");

        return self::SUCCESS;
    }
}
